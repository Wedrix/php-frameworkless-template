<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function App\SysAdmin;

function UpdateWatchtowerSchemaCommand(): Command
{
    static $command;
    
    $command ??= new class() extends Command {
        protected static $defaultName = 'watchtower:schema:update';

        protected static $defaultDescription = 'Update queries in the schema file to match the project\'s Doctrine models.';
    
        public function execute(
            InputInterface $input,
            OutputInterface $output
        ): int
        {
            $output->writeln("<info>Updating Schema ...</info>");
    
            SysAdmin()->updateWatchtowerSchema();
            
            $output->writeln("<info>Done!</info>");
                
            return Command::SUCCESS;
        }
    };

    return $command;
}