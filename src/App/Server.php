<?php

declare(strict_types=1);

namespace App
{
    use App\Server\Request;
    use App\Server\Response;
    use Workerman\Connection\TcpConnection;
    use Workerman\Crontab\Crontab;
    use Workerman\Worker;
    use Workerman\Protocols\Http;

    use function App\Server\AllJobs;
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
                };
    
                $numberOfHttpWorkers = 1;
    
                $minimumNumberOfWorkers = ($numberOfJobs = \iterator_count(AllJobs())) + $numberOfHttpWorkers;
    
                $numbersOfJobWorkers = \array_fill(0, $numberOfJobs, 1);
    
                /**
                 * Distribute the remaining workers between the HTTP Worker and the ProcessTaskQueueIndefinitelyJob if set.
                 * If the ProcessTaskQueueIndefinitelyJob is set, share the remaining workers:
                 *  Give up to 50% of the ideal number of workers (total worker pool) to the HTTP Worker and whatever remains to the ProcessTaskQueueIndefinitelyJob.
                 * If the ProcessTaskQueueIndefinitelyJob is not set, give all the remaining workers to the HTTP Worker.
                 */
                if ($idealNumberOfWorkers > $minimumNumberOfWorkers) {
                    $remainingNumberOfWorkers = $idealNumberOfWorkers - $numberOfHttpWorkers - \array_sum($numbersOfJobWorkers);
    
                    $processTaskQueueIndefinitelyJobIndex = (static function(): int {
                        foreach (AllJobs() as $jobIndex => $job) {
                            if ($job === ProcessTaskQueueIndefinitelyJob()) {
                                return $jobIndex;
                            }
                        }
    
                        return -1;
                    })();
    
                    if ($processTaskQueueIndefinitelyJobIndex > -1) {
                        for ($remainingWorkerIndex = 0; $remainingWorkerIndex < $remainingNumberOfWorkers; $remainingWorkerIndex++) {
                            if ($numberOfHttpWorkers / $idealNumberOfWorkers < 0.5) {
                                $numberOfHttpWorkers++;
                            }
                        }
        
                        $remainingNumberOfWorkersStill = $idealNumberOfWorkers - $numberOfHttpWorkers - \array_sum($numbersOfJobWorkers);
        
                        if ($remainingNumberOfWorkersStill > 0) {
                            $numbersOfJobWorkers[$processTaskQueueIndefinitelyJobIndex] += $remainingNumberOfWorkersStill;
                        }
                    }
                    else {
                        $numberOfHttpWorkers += $remainingNumberOfWorkers;
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
                    throw new \ConstraintViolationException("The server is already running.");
                }
        
                // Point Workerman to our Request class to use it within onMessage
                Http::requestClass(Request::class);
        
                // Init HTTP workers
                $worker = new Worker('http://' . Config()->serverHost() . ':' . Config()->serverPort());
                $worker->count = $this->numberOfHttpWorkers;
                $worker->name = Config()->appName();
                $worker->onMessage = static function(TcpConnection $connection, Request $request) use($worker) {
                    try {
                        $response = new Response();
    
                        RequestHandler()->handle(request: $request, response: $response);
                        
                        $response->setHeader('Server', Config()->appName());
    
                        $connection->send($response);
                    } 
                    catch (\Throwable $e) {
                        $eMessage = "\r\n[REQUEST ERROR] $e\r\n";
    
                        if (Config()->appEnvironment() === 'development') {
                            echo $eMessage;
                        }
    
                        Logger()->log($eMessage);
    
                        $connection->send(new Response(500));

                        $worker->stop(); // Stop worker, preventing state pollution of subsequent requests.
                    }
                };
        
                // Init JOB workers
                foreach (AllJobs() as $jobIndex => $job) {
                    $jobWorker = new Worker('text://' . Config()->serverHost() . ':' . \strval(65432 + $jobIndex));
    
                    $jobWorker->count = $this->numbersOfJobWorkers[$jobIndex];
    
                    $jobWorker->name = Config()->appName() .' [job] ' . $job->name();
    
                    $jobWorker->onWorkerStart = static function() use($job, $jobWorker) {
                        $callback = static function() use($job, $jobWorker) {
                            try {
                                $job->run();
                            }
                            catch (\Throwable $e) {
                                $eMessage = "\r\n[JOB ERROR] $e\r\n";
        
                                if (Config()->appEnvironment() === 'development') {
                                    echo $eMessage;
                                }
            
                                Logger()->log($eMessage);
    
                                $jobWorker->stop(); // Stop worker, preventing state pollution of subsequent requests.
                            }
                        };

                        /**
                         * Supports non-standard @reboot macro for one-time job
                         */
                        if ($job->cronSchedule() === '@reboot') {
                            $callback();
                        }
                        else {
                            new Crontab($job->cronSchedule(), $callback, $job->name()); 
                        }   		
                    };
                }
        
                // Start Event Loop
                Worker::runAll();
        
                $this->isRunning = true;
            }
        };
    
        return $server;
    }
}

namespace App\Server
{
    use App\Server\Job;

    /**
     * @var array<int,Job>
     */
    global $server_jobs;

    $server_jobs = [
        ProcessTaskQueueIndefinitelyJob(),
        //TODO: Other jobs go here ...
    ];
}