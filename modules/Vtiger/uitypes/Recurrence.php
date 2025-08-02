<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Vtiger_Recurrence_UIType extends Vtiger_Date_UIType
{
    /**
     * Function to get the Template name for the current UI Type object
     * @return <String> - Template Name
     */
    public function getTemplateName()
    {
        return 'uitypes/Recurrence.tpl';
    }

    /**
     * Function to get the Detailview template name for the current UI Type Object
     * @return <String> - Template Name
     */
    public function getDetailViewTemplateName()
    {
        return 'uitypes/RecurrenceDetailView.tpl';
    }

    /**
     * Function to get the display value in edit view
     *
     * @param $value
     *
     * @return converted value
     */
    public function getEditViewDisplayValue($value)
    {
        return $this->getDisplayValue($value);
    }

    /**
     * @return string
     */
    public function getTomorrowDate(): string
    {
        return DateTimeField::convertToUserFormat(date('Y-m-d', strtotime('+1 day')));
    }
}