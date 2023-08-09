<?php

declare(strict_types=1);

namespace App;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use LongitudeOne\Spatial\DBAL\Types\Geography\PointType as GeoPointType;
use LongitudeOne\Spatial\ORM\Query\AST\Functions\Standard\StDistance;

use function App\DataMapper\NamingStrategy;

function DataMapper(): DataMapper
{
    static $dataMapper;
    
    $dataMapper ??= new DataMapper();

    /**
     * Reset the EntityManager if closed
     * Doctrine closes the EntityManager on some Exceptions
     */
    if (!$dataMapper->isOpen()) {
        $dataMapper = new DataMapper(
            closed: $dataMapper
        );
    }

    return $dataMapper;
}

final class DataMapper extends EntityManagerDecorator
{
    public function __construct(
        ?self $closed = null
    )
    {
        if (!\is_null($closed)) {
            parent::__construct(
                wrapped: EntityManager::create(
                    /**
                     * No need to check for an open connection since it's handled internally
                     * https://github.com/doctrine/dbal/pull/4966#issuecomment-1015006379
                     */
                    connection: $closed->getConnection(),
                    config: $closed->getConfiguration(),
                    eventManager: $closed->getEventManager()
                )
            );
        } 
        else {
            $entityManagerConfig = ORMSetup::createAttributeMetadataConfiguration(
                paths: DoctrineConfig()->paths(), 
                isDevMode: DoctrineConfig()->isDevMode(), 
                proxyDir: DoctrineConfig()->proxyDirectory(),
                cache: Cache()
            );
            
            $entityManagerConfig->setNamingStrategy(NamingStrategy());
    
            $entityManagerConfig->addCustomNumericFunction('ST_Distance', StDistance::class);
            
            /**
             * @see https://github.com/doctrine/orm/issues/9432#issuecomment-1384115782
             */
            $entityManagerConfig->setLazyGhostObjectEnabled(true);
    
            $entityManager = EntityManager::create(DoctrineConfig()->connection(), $entityManagerConfig);
            
            Type::addType('geopoint', GeoPointType::class);
    
            $dbPlatform = $entityManager->getConnection()
                                        ->getDatabasePlatform();
    
            $dbPlatform->registerDoctrineTypeMapping('geography', 'geopoint');
    
            parent::__construct(
                wrapped: $entityManager
            );
        }
    }

    /**
     * Finds an object by its identifier.
     *
     * This is just a convenient shortcut for getRepository($className)->find($id).
     *
     * @param string $className The class name of the object to find.
     * @param mixed  $id        The identity of the object to find.
     * @psalm-param class-string<T> $className
     *
     * @return object The found object.
     * @psalm-return T
     *
     * @template T of object
     */
    public function find($className, $id, $lockMode = null, $lockVersion = null): object
    {
        return parent::find($className, $id, $lockMode, $lockVersion)
            ?? throw new \Exception(
                "The $className entity with id '$id' was not found."
            );
    }
}