<?php

declare(strict_types=1);

namespace App\DoctrineEntityManager;

use Doctrine\ORM\Mapping\NamingStrategy;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;

function CustomNamingStrategy(): NamingStrategy
{
    static $customNamingStrategy;
    
    $customNamingStrategy ??= new class() implements NamingStrategy
    {
        private readonly NamingStrategy $underscoreNamingStrategy;

        public function __construct()
        {
            $this->underscoreNamingStrategy = new UnderscoreNamingStrategy(
                case: \CASE_LOWER, 
                numberAware: false
            );
        }

        public function classToTableName(
            $className
        )
        {
            if (\strpos($className, '\\') !== false) {
                $className = \substr($className, \strrpos($className, '\\') + 1);
            }
    
            return \strtolower(\pluralize($className));
        }
    
        public function joinTableName(
            $sourceEntity, 
            $targetEntity, 
            $propertyName = null
        )
        {
            return $this->classToTableName($sourceEntity) . '_' . $this->classToTableName($targetEntity);
        }
    
        public function joinKeyColumnName(
            $entityName, 
            $referencedColumnName = null
        )
        {
            return \strtolower(\singularize($this->classToTableName($entityName)))  . '_' .
                    ($referencedColumnName ?? $this->referenceColumnName());
        }
    
        public function propertyToColumnName(
            $propertyName, 
            $className = null
        )
        {
            return $this->underscoreNamingStrategy->propertyToColumnName($propertyName, $className);
        }
    
        public function embeddedFieldToColumnName(
            $propertyName, 
            $embeddedColumnName, 
            $className = null, 
            $embeddedClassName = null
        )
        {
            return $this->underscoreNamingStrategy->embeddedFieldToColumnName(
                $propertyName, 
                $embeddedColumnName, 
                $className, 
                $embeddedClassName
            );
        }
    
        public function referenceColumnName()
        {
            return $this->underscoreNamingStrategy->referenceColumnName();
        }
    
        public function joinColumnName($propertyName)
        {
            return $this->underscoreNamingStrategy->joinColumnName($propertyName);
        }
    };

    return $customNamingStrategy;
}