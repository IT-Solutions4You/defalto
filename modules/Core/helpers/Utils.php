<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_Utils_Helper
{
    /**
     * @param mixed $value
     * @return int
     */
    public static function count(mixed $value): int
    {
        if (is_array($value)) {
            return count($value);
        }

        return 0;
    }

    /**
     * @param string $module
     * @return string
     * @throws Exception
     */
    public static function getTermsAndConditions(string $module): string
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT tandc FROM vtiger_inventory_tandc WHERE type = ?', [$module]);

        return (string)$adb->query_result($result, 0, 'tandc');
    }
}