<?php

declare(strict_types=1);

namespace App;

interface SysAdmin
{
    public function clearServerLogs(): void;

    public function generateWatchtowerSchema(): void;

    public function updateWatchtowerSchema(): void;

    public function addWatchtowerFilterPlugin(
        string $parentNodeType,
        string $filterName
    ): void;

    public function addWatchtowerOrderingPlugin(
        string $parentNodeType,
        string $orderingName
    ): void;

    public function addWatchtowerSelectorPlugin(
        string $parentNodeType,
        string $fieldName
    ): void;

    public function addWatchtowerResolverPlugin(
        string $parentNodeType,
        string $fieldName
    ): void;

    public function addWatchtowerAuthorizorPlugin(
        string $nodeType,
        bool $isForCollections
    ): void;

    public function addWatchtowerMutationPlugin(
        string $fieldName
    ): void;

    public function addWatchtowerSubscriptionPlugin(
        string $fieldName
    ): void;

    public function addWatchtowerScalarTypeDefinition(
        string $typeName
    ): void;

    public function generateWatchtowerCache(): void;
}

function SysAdmin(): SysAdmin
{
    static $sysAdmin;

    $sysAdmin ??= new class() implements SysAdmin {
        public function clearServerLogs(): void
        {
            $logFiles = new \RegexIterator(
                iterator: new \DirectoryIterator((string) Config()->serverLogFilesDirectory()),
                pattern: '/.+\.log/i',
                mode: \RegexIterator::MATCH
            );

            foreach ($logFiles as $logFile) {
                if ($fileHandle = \fopen($logFile->getPathname(), 'w')) {
                    $maxAttempts = 10;
                    $attempt = 0;
    
                    while (!\flock($fileHandle, \LOCK_EX | \LOCK_NB)) {
                        if (++$attempt >= $maxAttempts) {
                            \fclose($fileHandle);
    
                            throw new \Exception("Unable to secure lock for the log file '$logFile'.");
                        }
    
                        \usleep(100000);
                    }
    
                    \unlink($logFile->getPathname());
    
                    \flock($fileHandle, \LOCK_UN);
    
                    \fclose($fileHandle);
                } 
                else {
                    throw new \Exception("Unable to open the log file '$logFile'.");
                }
            }
        }

        public function generateWatchtowerSchema(): void
        {
            WatchtowerConsole()->generateSchema();
        }

        public function updateWatchtowerSchema(): void
        {
            WatchtowerConsole()->updateSchema();
        }

        public function addWatchtowerFilterPlugin(
            string $parentNodeType, 
            string $filterName
        ): void
        {
            WatchtowerConsole()->addFilterPlugin(
                parentNodeType: $parentNodeType,
                filterName: $filterName
            );
        }

        public function addWatchtowerOrderingPlugin(
            string $parentNodeType, 
            string $orderingName
        ): void
        {
            WatchtowerConsole()->addOrderingPlugin(
                parentNodeType: $parentNodeType,
                orderingName: $orderingName
            );
        }

        public function addWatchtowerSelectorPlugin(
            string $parentNodeType, 
            string $fieldName
        ): void
        {
            WatchtowerConsole()->addSelectorPlugin(
                parentNodeType: $parentNodeType,
                fieldName: $fieldName
            );
        }

        public function addWatchtowerResolverPlugin(
            string $parentNodeType, 
            string $fieldName
        ): void
        {
            WatchtowerConsole()->addResolverPlugin(
                parentNodeType: $parentNodeType,
                fieldName: $fieldName
            );
        }

        public function addWatchtowerAuthorizorPlugin(
            string $nodeType, 
            bool $isForCollections
        ): void
        {
            WatchtowerConsole()->addAuthorizorPlugin(
                nodeType: $nodeType,
                isForCollections: $isForCollections
            );
        }

        public function addWatchtowerMutationPlugin(
            string $fieldName
        ): void
        {
            WatchtowerConsole()->addMutationPlugin(
                fieldName: $fieldName
            );
        }

        public function addWatchtowerSubscriptionPlugin(
            string $fieldName
        ): void
        {
            WatchtowerConsole()->addSubscriptionPlugin(
                fieldName: $fieldName
            );
        }

        public function addWatchtowerScalarTypeDefinition(
            string $typeName
        ): void
        {
            WatchtowerConsole()->addScalarTypeDefinition(
                typeName: $typeName
            );
        }

        public function generateWatchtowerCache(): void
        {
            WatchtowerConsole()->generateCache();
        }
    };

    return $sysAdmin;
}