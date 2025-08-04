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
 * Description of VtigerTaxMeta
 */
class VtigerTaxMeta extends VtigerCRMActorMeta
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
            if (strcasecmp('taxname', $dbField->name) === 0 || strcasecmp('deleted', $dbField->name)) {
                $field['displaytype'] = 2;
            }
            $webserviceField = WebserviceField::fromArray($this->pearDB, $field);
            $fieldDataType = $this->getFieldType($dbField, $tableName);
            if ($fieldDataType === null) {
                $fieldDataType = $this->getFieldDataTypeFromDBType($dbField->type);
            }
            $webserviceField->setFieldDataType($fieldDataType);
            if (strcasecmp($fieldDataType, 'reference') === 0) {
                $webserviceField->setReferenceList($this->getReferenceList($dbField));
            }
            array_push($tableFieldList, $webserviceField);
        }

        return $tableFieldList;
    }

    public function getEntityDeletedQuery()
    {
        return 'vtiger_inventorytaxinfo.deleted=0';
    }
}