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
            return self::transformToDisplayValue($value, $referenceModule->get('name'));
        }

        return '';
    }

    /**
     * @param int|string $fieldValue
     * @param string $referenceModuleName
     * @return string
     * @throws Exception
     */
    public static function transformToDisplayValue(int|string $fieldValue, string $referenceModuleName): string
    {
        $records = explode(';', $fieldValue);

        if(empty($records)) {
            return '';
        }

        if (1 < php7_count($records)) {
            $displayValues = [];
            foreach ($records as $recordId) {
                $displayValues[] = self::transformToDisplayValue($recordId, $referenceModuleName);
            }

            return implode(' ', $displayValues);
        }

        if ('Users' === $referenceModuleName) {
            $db = PearDatabase::getInstance();
            $nameResult = $db->pquery('SELECT userlabel FROM vtiger_users WHERE id = ?', [$fieldValue]);

            return $db->query_result($nameResult, 0, 'userlabel');
        }

        $referenceModule = Vtiger_Module_Model::getInstance($referenceModuleName);

        if(!$referenceModule || !$referenceModule->isEntityModule()) {
            return '';
        }

        $entityNames = getEntityName($referenceModuleName, [$fieldValue]);
        $recordId = (int)$fieldValue;
        $recordName = $entityNames[$fieldValue] ?: '';
        $moduleIcon = $referenceModule->getModuleIcon();
        $detailViewName = $referenceModule->getDetailViewName();
        $moduleName = $referenceModuleName;
        $moduleLabel = vtranslate($referenceModuleName, $referenceModuleName);

        return sprintf(
            '<a class="js-reference-display-value text-primary" href="index.php?module=%s&view=%s&record=%s" title="%s:%s" data-original-title="%s">%s<span class="ms-2">%s</span></a>',
            $moduleName,
            $detailViewName,
            $recordId,
            $moduleLabel,
            $recordName,
            $moduleLabel,
            $moduleIcon,
            $recordName,
        );
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

    /**
     * @param mixed       $value
     * @param bool|int    $record
     * @param object|bool $recordInstance
     *
     * @return string
     * @throws Exception
     */
    public function getRelatedBlockDisplayValue(mixed $value, bool|int $record, object|bool $recordInstance): string
    {
        return strip_tags($this->getDisplayValue($value, $record, $recordInstance));
    }
}