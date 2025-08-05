<?php
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Description of VtigerLineItemMeta
 */
class VtigerLineItemMeta extends VtigerCRMActorMeta
{
    protected function getTableFieldList($tableName)
    {
        $tableFieldList = [];

        $factory = WebserviceField::fromArray($this->pearDB, ['tablename' => $tableName]);
        $dbTableFields = $factory->getTableFields();
        foreach ($dbTableFields as $dbField) {
            if ($dbField->primary_key) {
                if ($this->idColumn === null) {
                    $this->idColumn = $dbField->name;
                } else {
                    throw new WebServiceException(
                        WebServiceErrorCode::$UNKOWNENTITY,
                        "Entity table with multi column primary key is not supported"
                    );
                }
            }
            $field = $this->getFieldArrayFromDBField($dbField, $tableName);
            if (preg_match('/tax\d+/', $dbField->name) != 0) {
                $taxLabel = $this->getTaxLabelFromName($dbField->name);
                if (!empty($taxLabel)) {
                    $field['fieldlabel'] = $taxLabel;
                }
            }
            $webserviceField = WebserviceField::fromArray($this->pearDB, $field);
            $fieldDataType = $this->getFieldType($dbField, $tableName);
            if ($fieldDataType === null) {
                $fieldDataType = $this->getFieldDataTypeFromDBType($dbField->type);
            }
            $webserviceField->setFieldDataType($fieldDataType);
            if (strcasecmp($fieldDataType, 'reference') === 0) {
                if ($webserviceField->getFieldName() == 'parent_id') {
                    $webserviceField->setReferenceList(InventoryItem_Utils_Helper::getInventoryItemModules());
                } else {
                    $webserviceField->setReferenceList(['Products', 'Services']);
                }
            }
            array_push($tableFieldList, $webserviceField);
        }

        return $tableFieldList;
    }

    private function getTaxLabelFromName($name)
    {
        $db = PearDatabase::getInstance();
        $sql = 'SELECT * FROM vtiger_inventorytaxinfo WHERE taxname=? AND deleted=0';
        $params = [$name];
        $result = $db->pquery($sql, $params);
        $it = new SqlResultIterator($db, $result);
        foreach ($it as $row) {
            return $row->taxlabel;
        }

        return null;
    }

    protected function getFieldArrayFromDBField($dbField, $tableName)
    {
        $mandatoryFieldList = ['parent_id', 'productid', 'quantity'];
        $field = [];
        $field['fieldname'] = $dbField->name;
        $field['columnname'] = $dbField->name;
        $field['tablename'] = $tableName;
        $field['fieldlabel'] = str_replace('_', ' ', $dbField->name);
        $field['displaytype'] = 1;
        $field['uitype'] = 1;

        if ($dbField->name == 'id') {
            $field['fieldname'] = 'parent_id';
            $field['fieldlabel'] = 'parent id';
        }

        $fieldDataType = $this->getFieldType($dbField, $tableName);
        if ($fieldDataType !== null) {
            $fieldType = $this->getTypeOfDataForType($fieldDataType);
        } else {
            $fieldType = $this->getTypeOfDataForType($dbField->type);
        }
        $typeOfData = null;
        $fieldName = $dbField->name;

        if (in_array($fieldName, $mandatoryFieldList)) {
            $typeOfData = $fieldType . '~M';
        } elseif (($dbField->not_null == 1 && $fieldName != 'incrementondel'
                && $dbField->primary_key != 1) || $dbField->unique_key == 1) {
            $typeOfData = $fieldType . '~M';
        } else {
            $typeOfData = $fieldType . '~O';
        }
        $field['typeofdata'] = $typeOfData;
        $field['tabid'] = null;
        $field['fieldid'] = null;
        $field['masseditable'] = 0;

        return $field;
    }
}