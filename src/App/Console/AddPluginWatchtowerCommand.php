<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

function AddPluginWatchtowerCommand(): Command
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
            if (!$output instanceof ConsoleOutputInterface) {
                throw new \Exception('This command only accepts an instance of "ConsoleOutputInterface".');
            }
            
            $pluginType = (function () use ($input, $output): string {
                $helper = $this->getHelper('question');
    
                if (!$helper instanceof QuestionHelper) {
                    throw new \Exception("Instance of ".QuestionHelper::class." expected, ".get_class($helper)." given.");
                }
    
                return $helper->ask($input, $output, new ChoiceQuestion(
                    question: "What's the plugin type? ",
                    choices: [
                        'filter','ordering','selector','resolver','authorizor','mutation','subscription'
                    ],
                ));
            })();
    
            if ($pluginType === 'filter') {
                WatchtowerConsole()->addFilterPlugin(
                    parentNodeType: (function () use ($input, $output): string {
                        $helper = $this->getHelper('question');

                        if (!$helper instanceof QuestionHelper) {
                            throw new \Exception("Instance of ".QuestionHelper::class." expected, ".get_class($helper)." given.");
                        }

                        return $helper->ask($input, $output, new Question(
                            question: "What's the parent node type? "
                        ));
                    })(),
                    filterName: (function () use ($input, $output): string {
                        $helper = $this->getHelper('question');
        
                        if (!$helper instanceof QuestionHelper) {
                            throw new \Exception("Instance of ".QuestionHelper::class." expected, ".get_class($helper)." given.");
                        }
            
                        return $helper->ask($input, $output, new Question(
                            question: "What's the filter name? "
                        ));
                    })()
                );
            }
    
            if ($pluginType === 'ordering') {
                WatchtowerConsole()->addOrderingPlugin(
                    parentNodeType: (function () use ($input, $output): string {
                        $helper = $this->getHelper('question');

                        if (!$helper instanceof QuestionHelper) {
                            throw new \Exception("Instance of ".QuestionHelper::class." expected, ".get_class($helper)." given.");
                        }

                        return $helper->ask($input, $output, new Question(
                            question: "What's the parent node type? "
                        ));
                    })(),
                    orderingName: (function () use ($input, $output): string {
                        $helper = $this->getHelper('question');
        
                        if (!$helper instanceof QuestionHelper) {
                            throw new \Exception("Instance of ".QuestionHelper::class." expected, ".get_class($helper)." given.");
                        }
            
                        return $helper->ask($input, $output, new Question(
                            question: "What's the ordering name? "
                        ));
                    })()
                );
            }
    
            if ($pluginType === 'selector') {
                WatchtowerConsole()->addSelectorPlugin(
                    parentNodeType: (function () use ($input, $output): string {
                        $helper = $this->getHelper('question');

                        if (!$helper instanceof QuestionHelper) {
                            throw new \Exception("Instance of ".QuestionHelper::class." expected, ".get_class($helper)." given.");
                        }

                        return $helper->ask($input, $output, new Question(
                            question: "What's the parent node type? "
                        ));
                    })(),
                    fieldName: (function () use ($input, $output): string {
                        $helper = $this->getHelper('question');

                        if (!$helper instanceof QuestionHelper) {
                            throw new \Exception("Instance of ".QuestionHelper::class." expected, ".get_class($helper)." given.");
                        }

                        return $helper->ask($input, $output, new Question(
                            question: "What's the field name? "
                        ));
                    })()
                );
            }
    
            if ($pluginType === 'resolver') {
                WatchtowerConsole()->addResolverPlugin(
                    parentNodeType: (function () use ($input, $output): string {
                        $helper = $this->getHelper('question');

                        if (!$helper instanceof QuestionHelper) {
                            throw new \Exception("Instance of ".QuestionHelper::class." expected, ".get_class($helper)." given.");
                        }

                        return $helper->ask($input, $output, new Question(
                            question: "What's the parent node type? "
                        ));
                    })(),
                    fieldName: (function () use ($input, $output): string {
                        $helper = $this->getHelper('question');

                        if (!$helper instanceof QuestionHelper) {
                            throw new \Exception("Instance of ".QuestionHelper::class." expected, ".get_class($helper)." given.");
                        }

                        return $helper->ask($input, $output, new Question(
                            question: "What's the field name? "
                        ));
                    })()
                );
            }
    
            if ($pluginType === 'authorizor') {
                WatchtowerConsole()->addAuthorizorPlugin(
                    nodeType: $nodeType = (function () use ($input, $output): string {
                        $helper = $this->getHelper('question');
        
                        if (!$helper instanceof QuestionHelper) {
                            throw new \Exception("Instance of ".QuestionHelper::class." expected, ".get_class($helper)." given.");
                        }
            
                        return $helper->ask($input, $output, new Question(
                            question: "What's the node type? "
                        ));
                    })(),
                    isForCollections: (function () use ($input, $output, $nodeType): bool {
                        $helper = $this->getHelper('question');
        
                        if (!$helper instanceof QuestionHelper) {
                            throw new \Exception("Instance of ".QuestionHelper::class." expected, ".get_class($helper)." given.");
                        }
            
                        return $helper->ask($input, $output, new ConfirmationQuestion(
                            question: "Is the authorizor for collections of $nodeType? "
                        ));
                    })()
                );
            }
    
            if ($pluginType === 'mutation') {
                WatchtowerConsole()->addMutationPlugin(
                    fieldName: (function () use ($input, $output): string {
                        $helper = $this->getHelper('question');

                        if (!$helper instanceof QuestionHelper) {
                            throw new \Exception("Instance of ".QuestionHelper::class." expected, ".get_class($helper)." given.");
                        }

                        return $helper->ask($input, $output, new Question(
                            question: "What's the field name? "
                        ));
                    })()
                );
            }
    
            if ($pluginType === 'subscription') {
                WatchtowerConsole()->addSubscriptionPlugin(
                    fieldName: (function () use ($input, $output): string {
                        $helper = $this->getHelper('question');

                        if (!$helper instanceof QuestionHelper) {
                            throw new \Exception("Instance of ".QuestionHelper::class." expected, ".get_class($helper)." given.");
                        }

                        return $helper->ask($input, $output, new Question(
                            question: "What's the field name? "
                        ));
                    })()
                );
            }
    
            return Command::SUCCESS;
        }
    };

    return $command;
}