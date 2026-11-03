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
     * Returns the currency id of the payment, falling back to the base currency.
     *
     * @return int
     */
    public function getCurrencyId(): int
    {
        $currencyId = (int)$this->get('currency_id');

        return $currencyId > 0 ? $currencyId : (int)CurrencyField::getDBCurrencyId();
    }
}
