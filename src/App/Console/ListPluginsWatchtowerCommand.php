<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

function ListPluginsWatchtowerCommand(): Command
{
    static $command;
    
    $command ??= new class() extends Command {
        protected static $defaultName = 'watchtower:plugins:list';
        
        protected static $defaultDescription = 'Lists all the project\'s plugins.';
    
        public function execute(
            InputInterface $input,
            OutputInterface $output
        ): int
        {
            if (!$output instanceof ConsoleOutputInterface) {
                throw new \Exception('This command accepts only an instance of "ConsoleOutputInterface".');
            }
    
            if (\iterator_count(WatchtowerConsole()->plugins()) > 0) {
                $styledOutput = new SymfonyStyle($input, $output);
        
                $styledOutput->table(
                    ['<comment>Type</comment>', '<comment>Name</comment>'],
                    (function (): array {
                        $results = [];
                        $parsedPluginTypes = [];
        
                        foreach (WatchtowerConsole()->plugins() as $plugin) {
                            if (!\in_array($plugin->type(), $parsedPluginTypes) && !empty($parsedPluginTypes)) {
                                $results[] = new TableSeparator();
                            }
        
                            $results[] = [
                                \in_array($plugin->type(), $parsedPluginTypes)
                                ? '' 
                                : '<info>'.\capitalize($plugin->type()).'</info>',
                                $plugin->name()
                            ];
        
                            if (!\in_array($plugin->type(), $parsedPluginTypes)) {
                                $parsedPluginTypes[] = $plugin->type();
                            }
                        }
        
                        return $results;
                    })()
                );
            }
            else {
                $output->writeln('<info>You have no plugins.</info>');
            }
    
            return Command::SUCCESS;
        }
    };

    return $command;
}