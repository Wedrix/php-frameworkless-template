<?php

declare(strict_types=1);

namespace App\DataMapper;

use Doctrine\ORM\Mapping\NamingStrategy;

function NamingStrategy(): NamingStrategy
{
    static $namingStrategy;
    
    $namingStrategy ??= new class() implements NamingStrategy
    {
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
            return UnderscoreNamingStrategy()->propertyToColumnName($propertyName, $className);
        }
    
        public function embeddedFieldToColumnName(
            $propertyName, 
            $embeddedColumnName, 
            $className = null, 
            $embeddedClassName = null
        )
        {
            return UnderscoreNamingStrategy()->embeddedFieldToColumnName(
                $propertyName, 
                $embeddedColumnName, 
                $className, 
                $embeddedClassName
            );
        }
    
        public function referenceColumnName()
        {
            return UnderscoreNamingStrategy()->referenceColumnName();
        }
    
        public function joinColumnName($propertyName)
        {
            return UnderscoreNamingStrategy()->joinColumnName($propertyName);
        }
    };

    return $namingStrategy;
}