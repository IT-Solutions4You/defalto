<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_Utils_Helper
{
    /**
     * @return array
     */
    public static function getCurrenciesConversionTable(): array
    {
        $db = PearDatabase::getInstance();
        $currencies = [];
        $sql = 'SELECT id, conversion_rate FROM vtiger_currency_info WHERE deleted = 0 AND currency_status = ?';
        $res = $db->pquery($sql, ['Active']);

        while ($row = $db->fetchByAssoc($res)) {
            $currencies[$row['id']] = $row['conversion_rate'];
        }

        return $currencies;
    }
}