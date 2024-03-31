<?php

declare(strict_types=1);

namespace App;

use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;

function DoctrineEntityManagerProvider(): EntityManagerProvider
{
    static $doctrineEntityManagerProvider;

    $doctrineEntityManagerProvider ??= new SingleManagerProvider(DoctrineEntityManager());

    return $doctrineEntityManagerProvider;
}