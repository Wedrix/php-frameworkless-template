<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use function App\SysAdmin;

function AddWatchtowerScalarTypeDefinitionCommand(): Command
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
            $typeName = (function() use ($input, $output): string {
                /**
                 * @var QuestionHelper
                 */
                $helper = $this->getHelper('question');
    
                return $helper->ask($input, $output, new Question(
                    question: 'What is the custom scalar\'s name? '
                ));
            })();
    
            SysAdmin()->addWatchtowerScalarTypeDefinition(
                typeName: $typeName
            );
    
            return Command::SUCCESS;
        }
    };

    return $command;
}