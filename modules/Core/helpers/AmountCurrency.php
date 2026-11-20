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
 * Names of the companion fields and columns of an amount with its own currency (uitype 35).
 * The amount field <name> has the hidden fields <name>_currency_id and <name>_conversion_rate and the column
 * <column>_base with the amount in the base currency, all in the table of the amount.
 */
class Core_AmountCurrency_Helper
{
    public const UITYPE = 35;
    public const CURRENCY_SUFFIX = '_currency_id';
    public const RATE_SUFFIX = '_conversion_rate';
    public const BASE_SUFFIX = '_base';

    /**
     * Tells whether a uitype is the amount with its own currency.
     *
     * @param int|string|null $uitype Uitype of a field
     * @return bool
     */
    public static function isAmountCurrencyUIType(int|string|null $uitype): bool
    {
        return self::UITYPE === (int)$uitype;
    }

    /**
     * Returns the name of the hidden currency field (and column) of an amount field or column.
     *
     * @param string $name Amount field or column name
     * @return string
     */
    public static function getCurrencyName(string $name): string
    {
        return $name . self::CURRENCY_SUFFIX;
    }

    /**
     * Returns the name of the hidden conversion rate field (and column) of an amount field or column.
     *
     * @param string $name Amount field or column name
     * @return string
     */
    public static function getRateName(string $name): string
    {
        return $name . self::RATE_SUFFIX;
    }

    /**
     * Sets the currencies of the amounts with their own currency from a request on a record shown in a form, e.g. a
     * new record prefilled from another one. The form views set only editable fields from the request, and the hidden
     * currency field is not editable, so without this the form would offer the user's currency.
     *
     * @param Vtiger_Record_Model $recordModel Record of the form
     * @param Vtiger_Request      $request     Request of the edit or quick create view
     * @return void
     */
    public static function setRequestCurrencies(Vtiger_Record_Model $recordModel, Vtiger_Request $request): void
    {
        foreach ($recordModel->getModule()->getFields() as $fieldName => $fieldModel) {
            $currencyName = self::getCurrencyName((string)$fieldName);
            $currencyId = (int)$request->get($currencyName);

            if (0 < $currencyId && $fieldModel->isAmountCurrencyField() && $fieldModel->isEditable()) {
                $recordModel->set($currencyName, $currencyId);
            }
        }
    }

    /**
     * Returns the column with the amount in the base currency.
     *
     * @param string $columnName Amount column name
     * @return string
     */
    public static function getBaseColumnName(string $columnName): string
    {
        return $columnName . self::BASE_SUFFIX;
    }
}
