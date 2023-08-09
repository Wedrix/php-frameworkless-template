<?php

declare(strict_types=1);

namespace App\Server;

use function App\TaskQueue;

function ProcessTaskQueueIndefinitelyJob(): Job
{
    static $job;
    
    $job ??= new class() implements Job {
        public function name(): string
        {
            return 'Process Task Queue Indefinitely';
        }
    
        public function run(): void
        {
            TaskQueue()->processIndefinitely();
        }
    
        public function cronSchedule(): string
        {
            return '@reboot'; // Once at boot
        }
    };

    return $job;
}