<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function App\SysAdmin;

function ClearServerLogsCommand(): Command
{
    static $command;
    
    $command ??= new class() extends Command {
        protected static $defaultName = 'app:server:logs:clear';
        
        protected static $defaultDescription = 'Clear the server logs.';
    
        public function execute(
            InputInterface $input,
            OutputInterface $output
        ): int
        {
            if (!$output instanceof ConsoleOutputInterface) {
                throw new \Exception('This command only accepts an instance of "ConsoleOutputInterface".');
            }

            $output->writeln("<info>Clearing...</info>");

            SysAdmin()->clearServerLogs();

            $output->writeln("<info>Done!</info>");
    
            return Command::SUCCESS;
        }
    };

    return $command;
}