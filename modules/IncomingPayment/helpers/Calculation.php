<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Stateless amount and currency calculations of incoming payments.
 */
class IncomingPayment_Calculation_Helper
{
    /**
     * Tolerance used when two amounts are compared.
     */
    public const EPSILON = 0.000001;

    /**
     * Decimals of the stored amount columns (decimal(25,8)); used for persisted values so they do not depend on the user.
     */
    public const STORED_DECIMALS = 8;

    /**
     * Converts a request or database amount into a float the same way the entity save does.
     *
     * @param mixed $value Amount as entered by the user or stored in the database
     * @return float
     */
    public static function parseAmount(mixed $value): float
    {
        if ($value === null || $value === '' || $value === false) {
            return 0.0;
        }

        return (float)CurrencyField::convertToDBFormat($value, null, true);
    }

    /**
     * Returns the conversion rate of a currency against the base currency; 1 when the currency is unknown.
     *
     * @param int $currencyId Currency id
     * @return float
     */
    public static function getConversionRate(int $currencyId): float
    {
        if ($currencyId <= 0) {
            return 1.0;
        }

        $info = Vtiger_Functions::getCurrencySymbolandRate($currencyId);
        $rate = (float)($info['rate'] ?? 0);

        return $rate > 0 ? $rate : 1.0;
    }

    /**
     * Converts an amount from a currency into the base currency.
     *
     * @param float $amount Amount in the source currency
     * @param float $rate   Conversion rate of the source currency
     * @return float
     */
    public static function convertToBaseCurrency(float $amount, float $rate): float
    {
        return $rate > 0 ? $amount / $rate : $amount;
    }

    /**
     * Converts an amount from the base currency into a currency.
     *
     * @param float $amount Amount in the base currency
     * @param float $rate   Conversion rate of the target currency
     * @return float
     */
    public static function convertFromBaseCurrency(float $amount, float $rate): float
    {
        return $amount * $rate;
    }

    /**
     * Converts an amount between two currencies through the base currency.
     *
     * @param float $amount   Amount in the source currency
     * @param float $fromRate Conversion rate of the source currency
     * @param float $toRate   Conversion rate of the target currency
     * @return float
     */
    public static function convertBetweenCurrencies(float $amount, float $fromRate, float $toRate): float
    {
        return self::convertFromBaseCurrency(self::convertToBaseCurrency($amount, $fromRate), $toRate);
    }

    /**
     * Rounds an amount to the number of currency decimals of the current user.
     *
     * @param float    $amount   Amount
     * @param int|null $decimals Decimals; the user setting when null
     * @return float
     */
    public static function roundAmount(float $amount, ?int $decimals = null): float
    {
        return round($amount, $decimals ?? (int)getCurrencyDecimalPlaces());
    }

    /**
     * Rounds an amount to the precision of the stored amount columns.
     *
     * @param float $amount Amount
     * @return float
     */
    public static function roundStoredAmount(float $amount): float
    {
        return round($amount, self::STORED_DECIMALS);
    }

    /**
     * Tells whether two amounts are equal within the calculation tolerance.
     *
     * @param float $first  First amount
     * @param float $second Second amount
     * @return bool
     */
    public static function isSameAmount(float $first, float $second): bool
    {
        return abs($first - $second) < self::EPSILON;
    }

    /**
     * Formats an amount in the user number format with the symbol of the given currency.
     *
     * @param float $amount     Amount in the given currency
     * @param int   $currencyId Currency id
     * @return string
     */
    public static function formatAmount(float $amount, int $currencyId): string
    {
        $info = $currencyId > 0 ? Vtiger_Functions::getCurrencySymbolandRate($currencyId) : [];
        $value = CurrencyField::convertToUserFormat($amount, null, true, false, true);

        // appendCurrencySymbol() returns an empty string for "0", so the symbol is placed around a placeholder first.
        $pattern = CurrencyField::appendCurrencySymbol('#', (string)($info['symbol'] ?? ''));

        // Currency symbols are stored HTML-encoded (&euro;); templates escape the result again.
        return decode_html(str_replace('#', (string)$value, $pattern));
    }

    /**
     * Formats an amount as a plain decimal number for request values of unit price fields.
     * The edit view and the entity save cast such values with (float), so no user formatting is applied.
     *
     * @param float $amount Amount
     * @return string
     */
    public static function formatEditAmount(float $amount): string
    {
        return number_format(self::roundAmount($amount), (int)getCurrencyDecimalPlaces(), '.', '');
    }
}
