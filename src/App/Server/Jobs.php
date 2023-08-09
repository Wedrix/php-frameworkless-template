<?php

declare(strict_types=1);

namespace App\Server;

/**
 * @extends \IteratorAggregate<int,Job>
 */
interface Jobs extends \IteratorAggregate
{
}