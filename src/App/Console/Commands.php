<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Command\Command;

/**
 * @extends \IteratorAggregate<int,Command>
 */
interface Commands extends \IteratorAggregate
{
}