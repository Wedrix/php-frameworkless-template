<?php

declare(strict_types=1);

namespace App\Console;

function AllCommands(): Commands
{
    static $allCommands;

    $allCommands ??= new class() implements Commands {
        public function getIterator(): \Traversable
        {
            global $console_commands;

            return new \ArrayObject($console_commands);
        }
    };

    return $allCommands;
}