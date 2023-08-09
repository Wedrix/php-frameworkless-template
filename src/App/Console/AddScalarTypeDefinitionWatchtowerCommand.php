<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

function AddScalarTypeDefinitionWatchtowerCommand(): Command
{
    static $command;
    
    $command ??= new class() extends Command {
        protected static $defaultName = 'watchtower:scalar-type-definitions:add';
        
        protected static $defaultDescription = 'Add a type definition for a custom scalar type.';
    
        public function execute(
            InputInterface $input,
            OutputInterface $output
        ): int
        {
            if (!$output instanceof ConsoleOutputInterface) {
                throw new \LogicException('This command only accepts an instance of "ConsoleOutputInterface".');
            }
            
            $typeName = (function () use ($input, $output): string {
                $helper = $this->getHelper('question');
    
                if (!$helper instanceof QuestionHelper) {
                    throw new \Exception("Instance of ".QuestionHelper::class." expected, ".get_class($helper)." given.");
                }
    
                return $helper->ask($input, $output, new Question(
                    question: "What is the custom scalar's name? "
                ));
            })();
    
            WatchtowerConsole()->addScalarTypeDefinition(
                typeName: $typeName
            );
    
            return Command::SUCCESS;
        }
    };

    return $command;
}