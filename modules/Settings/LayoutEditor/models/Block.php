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

class Settings_LayoutEditor_Block_Model extends Vtiger_Block_Model
{
    public function isActionsAllowed()
    {
        if (strtolower($this->module->name) == 'events' && $this->get('label') == 'LBL_INVITE_USER_BLOCK') {
            return false;
        }

        return true;
    }

    /**
     * Function to check whether adding custom field is allowed or not
     * @return <Boolean> true/false
     */
    public function isAddCustomFieldEnabled()
    {
        $actionNotSupportedModules = array_merge(getInventoryModules(), ['Faq', 'HelpDesk']);
        $blocksEliminatedArray = [
            'HelpDesk'      => ['LBL_TICKET_RESOLUTION', 'LBL_COMMENTS'],
            'Faq'           => ['LBL_COMMENT_INFORMATION'],
            'Invoice'       => ['LBL_ITEM_DETAILS'],
            'Quotes'        => ['LBL_ITEM_DETAILS'],
            'SalesOrder'    => ['LBL_ITEM_DETAILS'],
            'PurchaseOrder' => ['LBL_ITEM_DETAILS'],
        ];
        if (in_array($this->module->name, $actionNotSupportedModules)) {
            if (!empty($blocksEliminatedArray[$this->module->name])) {
                if (in_array($this->get('label'), $blocksEliminatedArray[$this->module->name])) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    public static function updateFieldSequenceNumber($blockFieldSequence, $moduleModel = false)
    {
        $fieldIdList = [];
        $db = PearDatabase::getInstance();

        $query = 'UPDATE vtiger_field SET ';
        $query .= ' sequence= CASE ';
        foreach ($blockFieldSequence as $newFieldSequence) {
            $fieldId = $newFieldSequence['fieldid'];
            $sequence = $newFieldSequence['sequence'];
            $block = $newFieldSequence['block'];
            $fieldIdList[] = $fieldId;

            $query .= ' WHEN fieldid=' . $fieldId . ' THEN ' . $sequence;
        }

        $query .= ' END, block=CASE ';

        foreach ($blockFieldSequence as $newFieldSequence) {
            $fieldId = $newFieldSequence['fieldid'];
            $sequence = $newFieldSequence['sequence'];
            $block = $newFieldSequence['block'];
            $query .= ' WHEN fieldid=' . $fieldId . ' THEN ' . $block;
        }
        $query .= ' END ';

        $query .= ' WHERE fieldid IN (' . generateQuestionMarks($fieldIdList) . ')';

        $db->pquery($query, [$fieldIdList]);

        // Clearing cache
        Vtiger_Cache::flushModuleandBlockFieldsCache($moduleModel);
    }

    public static function getInstance($value, $moduleInstance = false)
    {
        $blockInstance = parent::getInstance($value, $moduleInstance);
        $blockModel = self::getInstanceFromBlockObject($blockInstance);

        return $blockModel;
    }

    /**
     * Function to retrieve block instance from Vtiger_Block object
     *
     * @param Vtiger_Block $blockObject - vtlib block object
     *
     * @return Vtiger_Block_Model
     */
    public static function getInstanceFromBlockObject(Vtiger_Block $blockObject)
    {
        $objectProperties = get_object_vars($blockObject);
        $blockModel = new self();
        foreach ($objectProperties as $properName => $propertyValue) {
            $blockModel->$properName = $propertyValue;
        }

        return $blockModel;
    }

    /**
     * Function to retrieve block instances for a module
     *
     * @param <type> $moduleModel - module instance
     *
     * @return <array> - list of Vtiger_Block_Model
     */
    public static function getAllForModule($moduleModel)
    {
        $blockObjects = parent::getAllForModule($moduleModel);
        $blockModelList = [];

        if ($blockObjects) {
            foreach ($blockObjects as $blockObject) {
                $blockModelList[] = self::getInstanceFromBlockObject($blockObject);
            }
        }

        return $blockModelList;
    }

    public function getLayoutBlockActiveFields()
    {
        $fields = $this->getFields();
        $activeFields = [];
        foreach ($fields as $fieldName => $fieldModel) {
            if ($fieldModel->get('displaytype') != 3 && $fieldModel->getDisplayType() != 6 && $fieldModel->isActiveField() && ($fieldModel->get('uitype') != '83'
                    || ($fieldModel->get('uitype') == '83' && $fieldName == 'taxclass' && in_array($this->module->name, ['Products', 'Services'])))) {
                $activeFields[$fieldName] = $fieldModel;
            }
        }

        return $activeFields;
    }

    public function getCustomFieldsCount()
    {
        $customFieldsCount = 0;
        $blockFields = $this->getFields();
        foreach ($blockFields as $fieldName => $fieldModel) {
            if ($fieldModel && $fieldModel->isCustomField()) {
                $customFieldsCount++;
            }
        }

        return $customFieldsCount;
    }

    public function getFields()
    {
        if (!$this->fields) {
            $blockFields = parent::getFields();
            $this->fields = [];

            foreach ($blockFields as $fieldName => $fieldModel) {
                $fieldModel = Settings_LayoutEditor_Field_Model::getInstanceFromFieldObject($fieldModel);
                $this->fields[$fieldName] = $fieldModel;
            }
        }

        return $this->fields;
    }
}