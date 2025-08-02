<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_MailManagerReference_UIType extends Vtiger_Base_UIType
{
    /**
     * Function to get the Display Value, for the current field type with given DB Insert Value
     *
     * @param       $value
     * @param false $record
     * @param false $recordInstance
     *
     * @return int
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        return (int)$value;
    }

    /**
     * Function to get the Template name for the current UI Type Object
     * @return string - Template Name
     */
    public function getTemplateName()
    {
        return 'uitypes/MailManagerReference.tpl';
    }

    /**
     * Function to get the Template name for the current UI Type Object
     * @return string - Template Name
     */
    public function getDetailViewTemplateName()
    {
        return 'uitypes/MailManagerReferenceDetailView.tpl';
    }
}