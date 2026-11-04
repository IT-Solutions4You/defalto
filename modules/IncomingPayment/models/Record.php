<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class IncomingPayment_Record_Model extends Vtiger_Record_Model
{
    /**
     * Returns the currency id of the payment, falling back to the base currency. List and related list rows do not
     * load the currency column, so the stored currency is read from the database then.
     *
     * @return int
     */
    public function getCurrencyId(): int
    {
        if (!$this->has('currency_id')) {
            return parent::getCurrencyId();
        }

        $currencyId = (int)$this->get('currency_id');

        return $currencyId > 0 ? $currencyId : (int)CurrencyField::getDBCurrencyId();
    }

    /**
     * Returns the display currency of a currency field: the amount in base currency is shown in the base currency,
     * the other amounts in the payment currency.
     *
     * @param string $fieldName Currency field name
     * @return int Currency id
     */
    public function getFieldCurrencyId(string $fieldName): int
    {
        if (IncomingPayment_Config_Helper::FIELD_AMOUNT_BASE === $fieldName) {
            return (int)CurrencyField::getDBCurrencyId();
        }

        return parent::getFieldCurrencyId($fieldName);
    }
}
