#!/usr/bin/env php
<?php

declare(strict_types=1);

use Doctrine\ORM\Tools\SchemaTool;

use function App\DoctrineEntityManager;

require \dirname(__DIR__).'/src/App.php';

App();

// Create the Schema
$schemaTool = new SchemaTool(DoctrineEntityManager());

$metadata = DoctrineEntityManager()->getMetadataFactory()->getAllMetadata();

$schemaTool->createSchema($metadata);