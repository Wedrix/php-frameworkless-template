<?php

declare(strict_types=1);

namespace App\Server;

function AllJobs(): Jobs
{
    static $allJobs;

    $allJobs ??= new class() implements Jobs {
        public function getIterator(): \Traversable
        {
            global $server_jobs;

            return new \ArrayObject($server_jobs);
        }
    };

    return $allJobs;
}