<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function App\SysAdmin;

function GenerateWatchtowerSchemaCommand(): Command
{
    static $command;
    
    $command ??= new class() extends Command {
        protected static $defaultName = 'watchtower:schema:generate';
        
        protected static $defaultDescription = 'Generate the schema file.';
    
        public function execute(
            InputInterface $input,
            OutputInterface $output
        ): int
        {
            $output->writeln("<info>Generating Schema ...</info>");

            SysAdmin()->generateWatchtowerSchema();
            
            $output->writeln("<info>Done!</info>");
                
            return Command::SUCCESS;
        }
    };

    return $command;
}