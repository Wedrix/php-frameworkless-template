<?php

declare(strict_types=1);

namespace App\Server;

interface Job
{
    public function name(): string;

    public function cronSchedule(): string;

    public function run(): void;
}