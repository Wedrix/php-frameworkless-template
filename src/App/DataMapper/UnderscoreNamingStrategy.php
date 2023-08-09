<?php

declare(strict_types=1);

namespace App\DataMapper;

use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;

function UnderscoreNamingStrategy(): UnderscoreNamingStrategy
{
    static $underscoreNamingStrategy;
    
    $underscoreNamingStrategy ??= new UnderscoreNamingStrategy(
        case: CASE_LOWER, 
        numberAware: false
    );

    return $underscoreNamingStrategy;
}