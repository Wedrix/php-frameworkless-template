<?php

declare(strict_types=1);

namespace App;

use Comet\Request;
use Slim\Exception\HttpNotFoundException;
use Workerman\Crontab\Crontab;
use Workerman\Worker;
use Workerman\Protocols\Http;
use Workerman\Protocols\Http\Response;

use function App\Server\ConfiguredJobs;
use function App\Server\Logger;
use function App\Server\ProcessTaskQueueIndefinitelyJob;
use function App\Server\RequestDispatcher;

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
                'Windows' => throw new \Exception('Windows is currently not supported. Kindly consider using sub-system for linux.'),
                default => throw new \Exception('Operating system not supported. Only MacOS and Linux are currently supported.')
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
            $worker = new Worker('http://' . ServerConfig()->host() . ':' . ServerConfig()->port());
            $worker->count = $this->numberOfHttpWorkers;
            $worker->name = AppConfig()->name();
    
            // Initialize JOB workers
            foreach (ConfiguredJobs() as $jobIndex => $job) {
                $jobWorker = new Worker('text://' . ServerConfig()->host() . ':' . \strval(65432 + $jobIndex));

                $jobWorker->count = $this->numbersOfJobWorkers[$jobIndex];

                $jobWorker->name = AppConfig()->name() .' [job] ' . $job->name();

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
            $worker->onMessage = static function($connection, Request $request) {
                try {
                    /**
                     * @var \Comet\Response
                    */
                    $response = RequestDispatcher()->handle($request);
            
                    $headers = $response->getHeaders();
                    
                    $headers['Server'] ??= AppConfig()->name();
                    $headers['Content-Type'] ??= 'text/html; charset=utf-8';
            
                    $response->withHeaders($headers);
                    
                    $connection->send($response);
    
                } catch(HttpNotFoundException $error) {
                    $connection->send(new Response(404));
                } catch(\Throwable $error) {
                    if (AppConfig()->environment() === 'development') {
                        echo "\n[ERR] " . $error->getFile() . ':' . $error->getLine() . ' >> ' . $error->getMessage();
                    }
                    Logger()->error($error->getFile() . ':' . $error->getLine() . ' >> ' . $error->getMessage());
                    $connection->send(new Response(500));
                }
            };
    
            // Start Event Loop
            Worker::runAll();
    
            $this->isRunning = true;
        }
    };

    return $server;
}