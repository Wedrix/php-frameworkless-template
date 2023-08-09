<?php

declare(strict_types=1);

namespace App\Server;

function ConfiguredJobs(): Jobs
{
    static $jobs;
    
    $jobs ??= new class() implements Jobs {
        /**
         * @var array<int,Job>
         */
        private readonly array $elements;

        public function __construct()
        {
            $this->elements = [
                ProcessTaskQueueIndefinitelyJob(),
                //TODO: ... jobs go here
            ];
        }

        public function getIterator(): \Traversable
        {
            return new \ArrayObject($this->elements);
        }
    };

    return $jobs;
}