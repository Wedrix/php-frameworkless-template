<?php

declare(strict_types=1);

namespace App;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;

use function App\DataStore\NamingStrategy;

interface DataStore extends EntityManagerInterface
{
    /**
    * Finds an object by its identifier.
    *
    * This is just a convenient shortcut for getRepository($className)->find($id).
    *
    * @param string $className The class name of the object to find.
    * @param mixed  $id        The identity of the object to find.
    * @param mixed $lockMode
    * @param mixed $lockVersion
    * @psalm-param class-string<T> $className
    *
    * @return object The found object.
    * @psalm-return T
    *
    * @template T of object
    */
   public function find($className, $id, $lockMode = null, $lockVersion = null): object;
}

function DataStore(): DataStore
{
    static $dataStore;
    
    /**
     * No need to check for an open connection and reconnect since it's handled internally
     * @see https://github.com/doctrine/dbal/pull/4966#issuecomment-1015006379
     */
    $dataStore ??= new class() extends EntityManagerDecorator implements DataStore {
        public function __construct() {
            $entityManagerConfig = ORMSetup::createAttributeMetadataConfiguration(
                paths: \explode(',', (string) Config()->doctrineModelsDirectories()), 
                isDevMode: Config()->doctrineIsDevMode(), 
                proxyDir: (string) Config()->doctrineProxiesDirectory(),
                cache: Cache()
            );
            
            $entityManagerConfig->setNamingStrategy(NamingStrategy());
            
            /**
             * @see https://github.com/doctrine/orm/issues/9432#issuecomment-1384115782
             */
            $entityManagerConfig->setLazyGhostObjectEnabled(true);
    
            $connection = DriverManager::getConnection(Config()->doctrineConnection(), $entityManagerConfig);

            $entityManager = new EntityManager(
                conn: $connection,
                config: $entityManagerConfig
            );
    
            parent::__construct(
                wrapped: $entityManager
            );
        }

        public function find($className, $id, $lockMode = null, $lockVersion = null): object
        {
            return parent::find($className, $id, $lockMode, $lockVersion)
                ?? throw new \Exception(
                    "The $className entity with id '$id' was not found."
                );
        }
    };

    return $dataStore;
}