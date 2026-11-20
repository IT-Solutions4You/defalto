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
     * Returns the currency of the payment, which is the currency of its amount (uitype 35), falling back to the base
     * currency. The payment table has no record currency column of its own.
     *
     * @return int
     */
    public function getCurrencyId(): int
    {
        $currencyId = $this->getAmountCurrencyId(IncomingPayment_Config_Helper::FIELD_AMOUNT);

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
