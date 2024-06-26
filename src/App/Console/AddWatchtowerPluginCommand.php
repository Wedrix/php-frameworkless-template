<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

use function App\SysAdmin;

function AddWatchtowerPluginCommand(): Command
{
    static $command;
    
    $command ??= new class() extends Command {
        protected static $defaultName = 'watchtower:plugins:add';

        protected static $defaultDescription = 'Generates a plugin file.';
    
        public function execute(
            InputInterface $input,
            OutputInterface $output
        ): int
        {
            $pluginType = (function() use ($input, $output): string {
                /**
                 * @var QuestionHelper
                 */
                $helper = $this->getHelper('question');
    
                return $helper->ask($input, $output, new ChoiceQuestion(
                    question: 'What\'s the plugin type? ',
                    choices: [
                        'filter','ordering','selector','resolver','authorizor','mutation','subscription'
                    ],
                ));
            })();
    
            if ($pluginType === 'filter') {
                SysAdmin()->addWatchtowerFilterPlugin(
                    parentNodeType: (function() use ($input, $output): string {
                        /**
                         * @var QuestionHelper
                         */
                        $helper = $this->getHelper('question');

                        return $helper->ask($input, $output, new Question(
                            question: 'What\'s the parent node type? '
                        ));
                    })(),
                    filterName: (function() use ($input, $output): string {
                        /**
                         * @var QuestionHelper
                         */
                        $helper = $this->getHelper('question');
            
                        return $helper->ask($input, $output, new Question(
                            question: 'What\'s the filter name? '
                        ));
                    })()
                );
            }
    
            if ($pluginType === 'ordering') {
                SysAdmin()->addWatchtowerOrderingPlugin(
                    parentNodeType: (function() use ($input, $output): string {
                        /**
                         * @var QuestionHelper
                         */
                        $helper = $this->getHelper('question');

                        return $helper->ask($input, $output, new Question(
                            question: 'What\'s the parent node type? '
                        ));
                    })(),
                    orderingName: (function() use ($input, $output): string {
                        /**
                         * @var QuestionHelper
                         */
                        $helper = $this->getHelper('question');
            
                        return $helper->ask($input, $output, new Question(
                            question: 'What\'s the ordering name? '
                        ));
                    })()
                );
            }
    
            if ($pluginType === 'selector') {
                SysAdmin()->addWatchtowerSelectorPlugin(
                    parentNodeType: (function() use ($input, $output): string {
                        /**
                         * @var QuestionHelper
                         */
                        $helper = $this->getHelper('question');

                        return $helper->ask($input, $output, new Question(
                            question: 'What\'s the parent node type? '
                        ));
                    })(),
                    fieldName: (function() use ($input, $output): string {
                        /**
                         * @var QuestionHelper
                         */
                        $helper = $this->getHelper('question');

                        return $helper->ask($input, $output, new Question(
                            question: 'What\'s the field name? '
                        ));
                    })()
                );
            }
    
            if ($pluginType === 'resolver') {
                SysAdmin()->addWatchtowerResolverPlugin(
                    parentNodeType: (function() use ($input, $output): string {
                        /**
                         * @var QuestionHelper
                         */
                        $helper = $this->getHelper('question');

                        return $helper->ask($input, $output, new Question(
                            question: 'What\'s the parent node type? '
                        ));
                    })(),
                    fieldName: (function() use ($input, $output): string {
                        /**
                         * @var QuestionHelper
                         */
                        $helper = $this->getHelper('question');

                        return $helper->ask($input, $output, new Question(
                            question: 'What\'s the field name? '
                        ));
                    })()
                );
            }
    
            if ($pluginType === 'authorizor') {
                SysAdmin()->addWatchtowerAuthorizorPlugin(
                    nodeType: $nodeType = (function() use ($input, $output): string {
                        /**
                         * @var QuestionHelper
                         */
                        $helper = $this->getHelper('question');
            
                        return $helper->ask($input, $output, new Question(
                            question: 'What\'s the node type? '
                        ));
                    })(),
                    isForCollections: (function() use ($input, $output, $nodeType): bool {
                        /**
                         * @var QuestionHelper
                         */
                        $helper = $this->getHelper('question');
            
                        return $helper->ask($input, $output, new ConfirmationQuestion(
                            question: "Is the authorizor for collections of $nodeType? "
                        ));
                    })()
                );
            }
    
            if ($pluginType === 'mutation') {
                SysAdmin()->addWatchtowerMutationPlugin(
                    fieldName: (function() use ($input, $output): string {
                        /**
                         * @var QuestionHelper
                         */
                        $helper = $this->getHelper('question');

                        return $helper->ask($input, $output, new Question(
                            question: 'What\'s the field name? '
                        ));
                    })()
                );
            }
    
            if ($pluginType === 'subscription') {
                SysAdmin()->addWatchtowerSubscriptionPlugin(
                    fieldName: (function() use ($input, $output): string {
                        /**
                         * @var QuestionHelper
                         */
                        $helper = $this->getHelper('question');

                        return $helper->ask($input, $output, new Question(
                            question: 'What\'s the field name? '
                        ));
                    })()
                );
            }
    
            return Command::SUCCESS;
        }
    };

    return $command;
}