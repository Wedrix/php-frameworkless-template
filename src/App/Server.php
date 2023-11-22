<?php

declare(strict_types=1);

namespace App;

use App\Server\Request;
use App\Server\Response;
use Workerman\Connection\TcpConnection;
use Workerman\Crontab\Crontab;
use Workerman\Worker;
use Workerman\Protocols\Http;

use function App\Server\ConfiguredJobs;
use function App\Server\Logger;
use function App\Server\ProcessTaskQueueIndefinitelyJob;
use function App\Server\RequestHandler;

interface Server
{
    public function run(): void;
}

function Server(): Server
{
    static $server;
    
    $server ??= new class() implements Server {
        private readonly int $numberOfHttpWorkers;
        /**
         * @var array<int,int>
         */
        private readonly array $numbersOfJobWorkers;
    
        private bool $isRunning = false;
    
        public function __construct()
        {
            $idealNumberOfWorkers = match(\PHP_OS_FAMILY) {
                'Linux' => (int) \shell_exec('nproc') * 4, // Linux
                'Darwin' => (int) \shell_exec('sysctl -n hw.logicalcpu') * 4, // MacOS
                'Windows' => 1, // Windows (only supports a single process)
                default => throw new \Exception('This application does not currently run on your operating system. Kindly consider running it on either Linux, MacOS, or Windows (using the Windows Subsystem for Linux).')
            };

            $minimumNumberOfWorkers = ($numberOfJobs = \iterator_count(ConfiguredJobs())) + 1; // plus one for the Http Worker

            $numberOfHttpWorkers = 1;

            $numbersOfJobWorkers = \array_fill(0, $numberOfJobs, 1);

            /**
             * Distribute the remaining workers if the ideal number of workers exceeds the minimum number of workers.
             * Distribute up to 80% to the http worker and give the remainder to the ProcessTaskQueueIndefinitelyJob.
             */
            if ($idealNumberOfWorkers > $minimumNumberOfWorkers) {
                $remainingNumberOfWorkers = $idealNumberOfWorkers - $numberOfHttpWorkers - \array_sum($numbersOfJobWorkers);

                for ($remainingWorkerIndex = 0; $remainingWorkerIndex < $remainingNumberOfWorkers; $remainingWorkerIndex++) {
                    if ($numberOfHttpWorkers / $idealNumberOfWorkers < 0.5) {
                        $numberOfHttpWorkers++;
                    }
                }

                $remainingNumberOfWorkersStill = $idealNumberOfWorkers - $numberOfHttpWorkers - \array_sum($numbersOfJobWorkers);

                if ($remainingNumberOfWorkersStill > 0) {
                    $processTaskQueueIndefinitelyJobIndex = (static function(): int {
                        foreach (ConfiguredJobs() as $jobIndex => $job) {
                            if ($job === ProcessTaskQueueIndefinitelyJob()) {
                                return $jobIndex;
                            }
                        }
    
                        throw new \Exception('ProcessTaskQueueIndefinitelyJob is required.');
                    })();

                    $numbersOfJobWorkers[$processTaskQueueIndefinitelyJobIndex] += $remainingNumberOfWorkersStill;
                }
            }

            $this->numberOfHttpWorkers = $numberOfHttpWorkers;

            $this->numbersOfJobWorkers = $numbersOfJobWorkers;
        }
    
        /**
         * Run the server
         */
        public function run(): void
        {
            if ($this->isRunning) {
                throw new \Exception("The server is already running.");
            }
    
            // Init HTTP workers
            $worker = new Worker('http://' . Config()->serverHost() . ':' . Config()->serverPort());
            $worker->count = $this->numberOfHttpWorkers;
            $worker->name = Config()->appName();
    
            // Initialize JOB workers
            foreach (ConfiguredJobs() as $jobIndex => $job) {
                $jobWorker = new Worker('text://' . Config()->serverHost() . ':' . \strval(65432 + $jobIndex));

                $jobWorker->count = $this->numbersOfJobWorkers[$jobIndex];

                $jobWorker->name = Config()->appName() .' [job] ' . $job->name();

                $jobWorker->onWorkerStart = static function() use($job) {
                    /**
                     * Supports non-standard @reboot macro for one-time job
                     */
                    // TODO: Create pull request to natively support macros in library?
                    if ($job->cronSchedule() === '@reboot') {
                        $job->run();
                    } else {
                        new Crontab($job->cronSchedule(), [$job, 'run'], $job->name()); 
                    }     		
                };
            }
    
            // Point Workerman to our Request class to use it within onMessage
            Http::requestClass(Request::class);
    
            // Main Loop
            $worker->onMessage = static function(TcpConnection $connection, Request $request) {
                try {
                    $response = new Response();

                    RequestHandler()->handle(request: $request, response: $response);
                    
                    $response->setHeader('Server', Config()->appName());

                    $connection->send($response);
                } 
                catch(\Throwable $error) {
                    if (Config()->appEnvironment() === 'development') {
                        echo "\n[ERR] " . $error->getFile() . ':' . $error->getLine() . ' >> ' . $error->getMessage();
                    }

                    Logger()->error($error->getFile() . ':' . $error->getLine() . ' >> ' . $error->getMessage());

                    $connection->send(new Response());
                }
            };
    
            // Start Event Loop
            Worker::runAll();
    
            $this->isRunning = true;
        }
    };

    return $server;
}