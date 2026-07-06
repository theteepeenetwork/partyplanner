<?php

namespace App\Libraries;

/**
 * Single source of truth for the customer deposit percentage.
 *
 * The customer deposit is 10% of a quote/basket total, rounded to 2 decimal
 * places. Controllers must call {@see forTotal()} rather than hardcoding the
 * percentage so the value only ever needs to change in one place.
 */
class DepositCalculator
{
    /**
     * Customer deposit percentage, expressed as a decimal fraction.
     */
    public const PERCENT = 0.10;

    /**
     * Compute the deposit amount for a given total, rounded to 2 decimal places.
     */
    public static function forTotal(float $total): float
    {
        return round($total * self::PERCENT, 2);
    }

    /**
     * The deposit percentage expressed as a whole number (e.g. 10 for 10%),
     * for display in views.
     */
    public static function percentDisplay(): int
    {
        return (int) round(self::PERCENT * 100);
    }
}
