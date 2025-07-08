<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class Vtiger_Double_UIType extends Core_Number_UIType
{
    /**
     * @param $value
     * @param $record
     * @param $recordInstance
     * @return string
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        return self::transformDisplayValue($value);
    }

    /**
     * @param $value
     * @return string
     */
    public static function transformDisplayValue($value): string
    {
        if('' == $value) {
            return '';
        }

        return strip_tags($value);
    }
}