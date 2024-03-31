<?php

declare(strict_types=1);

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;

function Inflector(): Inflector
{
    static $inflector;
    
    $inflector ??= InflectorFactory::create()->build();

    return $inflector;
}