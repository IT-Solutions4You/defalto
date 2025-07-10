<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class Vtiger_Percentage_UIType extends Core_Number_UIType
{
    /**
     * @return string
     */
    public function getTemplateName()
    {
        return 'uitypes/Percentage.tpl';
    }

    public function getDisplayValue($value, $record = false, $recordInstance = false): string
    {
        return parent::getDisplayValue($value, $record, $recordInstance) . ' %';
    }
}
