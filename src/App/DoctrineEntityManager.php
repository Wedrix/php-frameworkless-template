<?php

declare(strict_types=1);

namespace App;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\TransactionIsolationLevel;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

use function App\DoctrineEntityManager\CustomNamingStrategy;

function DoctrineEntityManager(): EntityManagerDecorator
{
    static $doctrineEntityManager;
    
    /**
     * No need to check for an open connection and reconnect since it's handled internally
     * @see https://github.com/doctrine/dbal/pull/4966#issuecomment-1015006379
     */
    $doctrineEntityManager ??= new class() extends EntityManagerDecorator {
        private bool $userErrorOccurred = false;

        public function __construct() {
            $entityManagerConfig = ORMSetup::createAttributeMetadataConfiguration(
                paths: \explode(',', (string) Config()->doctrineModelsDirectories()), 
                isDevMode: Config()->doctrineIsDevMode(), 
                proxyDir: (string) Config()->doctrineProxiesDirectory(),
                cache: Cache()
            );
            
            $entityManagerConfig->setNamingStrategy(CustomNamingStrategy());
            
            /**
             * @see https://github.com/doctrine/orm/issues/9432#issuecomment-1384115782
             */
            $entityManagerConfig->setLazyGhostObjectEnabled(true);
    
            $connection = DriverManager::getConnection(Config()->doctrineConnection(), $entityManagerConfig);

            $connection->setTransactionIsolation(TransactionIsolationLevel::READ_COMMITTED);

            $entityManager = new EntityManager(
                conn: $connection,
                config: $entityManagerConfig
            );
    
            parent::__construct(
                wrapped: $entityManager
            );
        }

        public function wrapInTransaction(
            callable $func
        ): mixed
        {
            $connection = $this->getConnection();

            $connection->beginTransaction();
    
            try {
                $return = $func($this);

                if (($connection->getTransactionNestingLevel() == 1) && $this->userErrorOccurred) {
                    $connection->rollBack();

                    return $return;
                }

                $this->flush();

                if ($connection->getTransactionNestingLevel() == 1) {
                    $this->clear();
                }

                $connection->commit();
    
                return $return;
            } 
            catch (\Throwable $e) {
                if ($e instanceof \ConstraintViolationException) {
                    $this->userErrorOccurred = true;
                }
                
                $this->close();

                $connection->rollBack();
    
                throw $e;
            }
        }
    };

    if (!$doctrineEntityManager->isOpen()) {
        $doctrineEntityManager = new class(
            connection: $doctrineEntityManager->getConnection(),
            configuration: $doctrineEntityManager->getConfiguration()
        ) extends EntityManagerDecorator {
            private bool $userErrorOccurred = false;
    
            public function __construct(
                Connection $connection,
                Configuration $configuration
            ) {
                $entityManager = new EntityManager(
                    conn: $connection,
                    config: $configuration
                );
        
                parent::__construct(
                    wrapped: $entityManager
                );
            }
    
            public function wrapInTransaction(
                callable $func
            ): mixed
            {
                $connection = $this->getConnection();
    
                $connection->beginTransaction();
        
                try {
                    $return = $func($this);
    
                    if (($connection->getTransactionNestingLevel() == 1) && $this->userErrorOccurred) {
                        $connection->rollBack();
    
                        return $return;
                    }
    
                    $this->flush();
    
                    if ($connection->getTransactionNestingLevel() == 1) {
                        $this->clear();
                    }
    
                    $connection->commit();
        
                    return $return;
                } 
                catch (\Throwable $e) {
                    if ($e instanceof \ConstraintViolationException) {
                        $this->userErrorOccurred = true;
                    }
                    
                    $this->close();
    
                    $connection->rollBack();
        
                    throw $e;
                }
            }
        };
    }

    return $doctrineEntityManager;
}