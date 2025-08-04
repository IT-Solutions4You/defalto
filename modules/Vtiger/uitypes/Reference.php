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

class Vtiger_Reference_UIType extends Vtiger_Base_UIType
{
    /**
     * Function to get the Template name for the current UI Type object
     * @return <String> - Template Name
     */
    public function getTemplateName()
    {
        return 'uitypes/Reference.tpl';
    }

    /**
     * Function to get the Display Value, for the current field type with given DB Insert Value
     *
     * @param <Object> $value
     *
     * @return <Object>
     */
    public function getReferenceModule($value)
    {
        $fieldModel = $this->get('field');
        $referenceModuleList = $fieldModel->getReferenceList();
        $referenceEntityType = getSalesEntityType($value);
        if (in_array($referenceEntityType, $referenceModuleList)) {
            return Vtiger_Module_Model::getInstance($referenceEntityType);
        } elseif (in_array('Users', $referenceModuleList)) {
            return Vtiger_Module_Model::getInstance('Users');
        }

        return null;
    }

    /**
     * Function to get the display value in detail view
     *
     * @param <Integer> crmid of record
     *
     * @return <String>
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        $referenceModule = $this->getReferenceModule($value);
        if ($referenceModule && !empty($value)) {
            $referenceModuleName = $referenceModule->get('name');
            if ($referenceModuleName == 'Users') {
                $db = PearDatabase::getInstance();
                $nameResult = $db->pquery('SELECT userlabel FROM vtiger_users WHERE id = ?', [$value]);
                if ($db->num_rows($nameResult)) {
                    return $db->query_result($nameResult, 0, 'userlabel');
                }
            } else {
                $fieldModel = $this->get('field');
                $entityNames = getEntityName($referenceModuleName, [$value]);
                $moduleIcon = $referenceModule->getModuleIcon();
                $linkValue = "<a class='text-primary' href='index.php?module=$referenceModuleName&view=" . $referenceModule->getDetailViewName() . "&record=$value'
							title='" . vtranslate($referenceModuleName, $referenceModuleName) . ":" . $entityNames[$value] . "' "
                    . "data-original-title='" . vtranslate($referenceModuleName, $referenceModuleName) . "'>$moduleIcon<span class='ms-2'>$entityNames[$value]</span></a>";

                return $linkValue;
            }
        }

        return '';
    }

    /**
     * Function to get the display value in edit view
     *
     * @param $value - record id
     *
     * @return string
     * @throws Exception
     */
    public function getEditViewDisplayValue($value): string
    {
        if (!$value) {
            return '';
        }

        $referenceModule = $this->getReferenceModule($value);

        if (!$referenceModule) {
            return '';
        }

        $referenceModuleName = $referenceModule->get('name');
        $entityNames = getEntityName($referenceModuleName, [$value]);

        return $entityNames[$value] ?: '';
    }

    public function getListSearchTemplateName()
    {
        $fieldModel = $this->get('field');
        if ($fieldModel->get('uitype') == '52' || $fieldModel->get('uitype') == '77') {
            return 'uitypes/OwnerFieldSearchView.tpl';
        }

        return parent::getListSearchTemplateName();
    }
}