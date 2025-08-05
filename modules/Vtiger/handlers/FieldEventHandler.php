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

require_once 'include/events/VTEventHandler.inc';

class FieldEventHandler extends VTEventHandler
{
    function handleEvent($eventName, $fieldEntity)
    {
        global $log, $adb;

        if ($eventName == 'vtiger.field.afterdelete') {
            $this->triggerPostDeleteEvents($fieldEntity);
        }
    }

    function triggerPostDeleteEvents($fieldEntity)
    {
        $db = PearDatabase::getInstance();

        $fieldId = $fieldEntity->id;
        $fieldName = $fieldEntity->name;
        $columnName = $fieldEntity->column;
        $fieldLabel = $fieldEntity->label;
        $tableName = $fieldEntity->table;
        $typeOfData = $fieldEntity->typeofdata;
        $fieldModuleName = $fieldEntity->getModuleName();
        $fieldType = explode('~', $typeOfData);

        $deleteColumnName = "$tableName:$columnName:" . $fieldName . ':' . $fieldModuleName . '_' . str_replace(' ', '_', $fieldLabel) . ':' . $fieldType[0];
        $columnCvStdFilter = "$tableName:$columnName:" . $fieldName . ':' . $fieldModuleName . '_' . str_replace(' ', '_', $fieldLabel);
        $selectColumnName = "$tableName:$columnName:" . $fieldModuleName . '_' . str_replace(' ', '_', $fieldLabel) . ':' . $fieldName . ':' . $fieldType[0];

        $query = 'ALTER TABLE ' . $db->sql_escape_string($tableName) . ' DROP COLUMN ' . $db->sql_escape_string($columnName);
        $db->pquery($query, []);

        //we have to remove the entries in customview and report related tables which have this field ($colName)
        $db->pquery('DELETE FROM vtiger_cvcolumnlist WHERE columnname = ?', [$deleteColumnName]);
        $db->pquery('DELETE FROM vtiger_cvstdfilter WHERE columnname = ?', [$columnCvStdFilter]);
        $db->pquery('DELETE FROM vtiger_cvadvfilter WHERE columnname = ?', [$deleteColumnName]);

        if ($fieldModuleName == 'Leads') {
            $db->pquery('DELETE FROM vtiger_convertleadmapping WHERE leadfid=?', [$fieldId]);
        } elseif ($fieldModuleName == 'Accounts' || $fieldModuleName == 'Contacts' || $fieldModuleName == 'Potentials') {
            $params = ['Accounts' => 'accountfid', 'Contacts' => 'contactfid', 'Potentials' => 'potentialfid'];
            $query = 'UPDATE vtiger_convertleadmapping SET ' . $params[$fieldModuleName] . '=0 WHERE ' . $params[$fieldModuleName] . '=?';
            $db->pquery($query, [$fieldId]);
        }

        if (in_array($fieldEntity->uitype, [15, 33])) {
            $db->pquery('DROP TABLE IF EXISTS vtiger_' . $db->sql_escape_string($columnName), []);
            $db->pquery('DROP TABLE IF EXISTS vtiger_' . $db->sql_escape_string($columnName) . '_seq', []); //To Delete Sequence Table
            $db->pquery('DELETE FROM vtiger_picklist_dependency WHERE sourcefield=? OR targetfield=?', [$columnName, $columnName]);

            //delete from picklist tables
            $picklistResult = $db->pquery('SELECT picklistid FROM vtiger_picklist WHERE name = ?', [$fieldName]);
            $picklistRow = $db->num_rows($picklistResult);
            if ($picklistRow) {
                $picklistId = $db->query_result($picklistResult, 0, 'picklistid');
                $db->pquery('DELETE FROM vtiger_picklist WHERE name = ?', [$fieldName]);
                $db->pquery('DELETE FROM vtiger_role2picklist WHERE picklistid = ?', [$picklistId]);
            }

            $rolesList = array_keys(getAllRoleDetails());
            Vtiger_Cache::flushPicklistCache($fieldName, $rolesList);
        }

        $this->triggerInventoryFieldPostDeleteEvents($fieldEntity);
    }

    public function triggerInventoryFieldPostDeleteEvents($fieldEntity)
    {
        $db = PearDatabase::getInstance();
        $fieldId = $fieldEntity->id;
        $fieldModuleName = $fieldEntity->getModuleName();

        if (!$db->tableExists('vtiger_inventorycustomfield')) {
            return;
        }

        if (in_array($fieldModuleName, InventoryItem_Utils_Helper::getInventoryItemModules())) {
            $db->pquery('DELETE FROM vtiger_inventorycustomfield WHERE fieldid=?', [$fieldId]);
        } elseif (in_array($fieldModuleName, ['Products', 'Services'])) {
            $refFieldName = ($fieldModuleName == 'Products') ? 'productfieldid' : 'servicefieldid';
            $refFieldDefaultValue = ($fieldModuleName == 'Products') ? 'productFieldDefaultValue' : 'serviceFieldDefaultValue';

            $query = "SELECT vtiger_inventorycustomfield.* FROM vtiger_inventorycustomfield
							INNER JOIN vtiger_field ON vtiger_field.fieldid = vtiger_inventorycustomfield.fieldid
							WHERE $refFieldName = ? AND defaultvalue LIKE ?";
            $result = $db->pquery($query, [$fieldId, '%productFieldDefaultValue%serviceFieldDefaultValue%']);

            $removeCacheModules = [];
            while ($rowData = $db->fetch_row($result)) {
                $lineItemFieldModel = Vtiger_Field_Model::getInstance($rowData['fieldid']);
                if ($lineItemFieldModel) {
                    $defaultValue = $lineItemFieldModel->getDefaultFieldValue();
                    if (is_array($defaultValue)) {
                        $defaultValue[$refFieldDefaultValue] = '';

                        if ($defaultValue['productFieldDefaultValue'] === '' && $defaultValue['serviceFieldDefaultValue'] === '') {
                            $defaultValue = '';
                        } else {
                            $defaultValue = Zend_Json::encode($defaultValue);
                        }

                        $lineItemFieldModel->set('defaultvalue', $defaultValue);
                        $lineItemFieldModel->save();
                    }

                    $removeCacheModules[$rowData['tabid']][] = $lineItemFieldModel->get('block')->id;
                }
            }

            foreach ($removeCacheModules as $tabId => $blockIdsList) {
                $moduleModel = Vtiger_Module_Model::getInstance($tabId);
                foreach ($blockIdsList as $blockId) {
                    Vtiger_Cache::flushModuleandBlockFieldsCache($moduleModel, $blockId);
                }
            }

            $db->pquery("UPDATE vtiger_inventorycustomfield SET $refFieldName=? WHERE fieldid=?", ['0', $fieldId]);
        }
    }
}