<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

function ListScalarTypeDefinitionsWatchtowerCommand(): Command
{
    static $command;
    
    $command ??= new class() extends Command {
        protected static $defaultName = 'watchtower:scalar-type-definitions:list';
        
        protected static $defaultDescription = 'Lists all the project\'s scalar type definitions.';
    
        public function execute(
            InputInterface $input,
            OutputInterface $output
        ): int
        {
            if (!$output instanceof ConsoleOutputInterface) {
                throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
            }
    
            if (\iterator_count(WatchtowerConsole()->scalarTypeDefinitions()) > 0) {
                $styledOutput = new SymfonyStyle($input, $output);
        
                $styledOutput->table(
                    ['<comment>Type Name</comment>', '<comment>Directory</comment>'],
                    (function (): array {
                        $results = [];
        
                        foreach (WatchtowerConsole()->scalarTypeDefinitions() as $scalarTypeDefinition) {
                            $results[] = [
                                $scalarTypeDefinition->typeName(),
                                WatchtowerConsole()->scalarTypeDefinitions()->directory($scalarTypeDefinition)
                            ];
                        }
        
                        return $results;
                    })()
                );
            }
            else {
                $output->writeln('<info>You have no scalar type definitions.</info>');
            }
    
            return Command::SUCCESS;
        }
    };

    return $command;
}