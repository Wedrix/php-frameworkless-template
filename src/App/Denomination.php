<?php

declare(strict_types=1);

namespace App;

/**
 * Only one denomination per Currency
 * The denomination must be the minor denomination
 */
final class Denomination
{
    private function __construct(
        private readonly string $name,
        private readonly int $unitToBaseRatio,
        private readonly string $baseSymbol
    ){}

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @param array<string> $arguments
     */
    public static function __callStatic(
        string $name, 
        array $arguments
    ): self
    {
        static $denominations;
        
        $denominations ??= [
            'USDMill' => new Denomination(
                name: 'USDMill',
                unitToBaseRatio: 1000,
                baseSymbol: 'US$'
            ),
            //TODO: ... denominations go here
        ];

        return $denominations[$name];
    }

    public static function exchangeRate(
        Denomination $denominationA,
        Denomination $denominationB
    ): float
    {
        // TODO: Implement Method
        return 1;
    }
}