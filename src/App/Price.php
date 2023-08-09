<?php

declare(strict_types=1);

namespace App;

final class Price
{
    private function __construct(
        private readonly int $amount,
        private readonly Denomination $denomination
    )
    {
        if ($amount < 0) {
            throw new \Exception('Invalid Price. The amount must be greater than or equal to 0.');
        }
    }

    public function __toString(): string
    {
        return "{$this->denomination}{$this->amount}";
    }

    /**
     * @param array<string> $arguments
     */
    public static function __callStatic(
        string $name, 
        array $arguments
    ): mixed
    {
        $currency = \substr($name, 0, 3);

        $denominationAndAmount = \explode($currency, $name)[1];

        $amount = (int) \filter_var($denominationAndAmount, \FILTER_SANITIZE_NUMBER_INT);

        $denomination = $currency . \explode("$amount", $denominationAndAmount)[0];

        return new Price(
            amount: $amount,
            denomination: Denomination::{$denomination}()
        );
    }

    public static function add(
        Price $priceA, 
        Price $priceB
    ): Price
    {
        // Convert $priceB to denomination of $priceA
        $priceB = Price::convert($priceB, $priceA->denomination);

        $newAmount = $priceA->amount + $priceB->amount;

        return Price::{$priceA->denomination . $newAmount}();
    }

    public static function subtract(
        Price $priceA, 
        Price $priceB
    ): Price
    {
        // Convert $priceB to denomination of $priceA
        $priceB = Price::convert($priceB, $priceA->denomination);

        // If converted $priceB > $priceA, return 0 price. Don't throw exception.
        $newAmount = max (0, $priceA->amount - $priceB->amount);

        return Price::{$priceA->denomination . $newAmount}();
    }

    public static function multiply(
        Price $price,
        int|float $factor
    ): Price
    {
        // Use intval(ceil($value)) to round fractional amounts
        $newAmount = \intval(\ceil($price->amount * $factor));

        return Price::{$price->denomination . $newAmount}();
    }

    public static function divide(
        Price $price,
        int|float $divisor
    ): Price
    {
        // Use intval(ceil($value)) to round fractional amounts
        $newAmount =\intval(\ceil($price->amount / $divisor));

        return Price::{$price->denomination . $newAmount}();
    }

    public static function lessThan(
        Price $priceA, 
        Price $priceB
    ): bool
    {
        // Convert $priceB to denomination of $priceA
        $priceB = Price::convert($priceB, $priceA->denomination);

        return $priceA->amount < $priceB->amount;
    }

    public static function lessThanOrEqualTo(
        Price $priceA, 
        Price $priceB
    ): bool
    {
        // Convert $priceB to denomination of $priceA
        $priceB = Price::convert($priceB, $priceA->denomination);

        return $priceA->amount <= $priceB->amount;
    }

    public static function greaterThan(
        Price $priceA, 
        Price $priceB
    ): bool
    {
        // Convert $priceB to denomination of $priceA
        $priceB = Price::convert($priceB, $priceA->denomination);

        return $priceA->amount > $priceB->amount;
    }

    public static function greaterThanOrEqualTo(
        Price $priceA, 
        Price $priceB
    ): bool
    {
        // Convert $priceB to denomination of $priceA
        $priceB = Price::convert($priceB, $priceA->denomination);

        return $priceA->amount >= $priceB->amount;
    }

    public static function equalTo(
        Price $priceA, 
        Price $priceB
    ): bool
    {
        // Convert $priceB to denomination of $priceA
        $priceB = Price::convert($priceB, $priceA->denomination);

        return $priceA->amount == $priceB->amount;
    }

    public static function convert(
        Price $price,
        Denomination $denomination
    ): Price
    {
        // return $price if denomination is the same
        if ($price->denomination === $denomination) {
            return $price;
        }

        $exchangeRate = Denomination::exchangeRate($price->denomination, $denomination);

        return Price::multiply($price, $exchangeRate);
    }
}