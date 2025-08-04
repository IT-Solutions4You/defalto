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

require_once "include/Webservices/VtigerCRMObjectMeta.php";

/**
 * Description of VtigerInventoryMeta
 */
class VtigerInventoryMeta extends VtigerCRMObjectMeta
{
    private $metaTableList = ['vtiger_inventorytaxinfo', 'vtiger_shippingtaxinfo'];
    private $metaTablePrefix = ['vtiger_inventorytaxinfo' => '', 'vtiger_shippingtaxinfo' => 'S & H '];

    public function retrieveMeta()
    {
        parent::retrieveMeta();
        $this->retrieveMetaForTables();
    }

    function retrieveMetaForTables()
    {
        $db = PearDatabase::getInstance();
        foreach ($this->metaTableList as $tableName) {
            $sql = "SELECT * FROM $tableName WHERE deleted=0";
            $params = [];
            $result = $db->pquery($sql, $params);
            if (!empty($result)) {
                $it = new SqlResultIterator($db, $result);
                foreach ($it as $row) {
                    $fieldArray = $this->getFieldArrayFromTaxRow(
                        $row,
                        $tableName,
                        $this->metaTablePrefix[$tableName]
                    );
                    $webserviceField = WebserviceField::fromArray($db, $fieldArray);
                    $webserviceField->setDefault($row->percentage);
                    $this->moduleFields[$webserviceField->getFieldName()] = $webserviceField;
                }
            }
        }
    }

    function getFieldArrayFromTaxRow($row, $tableName, $prefix)
    {
        $field = [];
        $field['fieldname'] = $row->taxname;
        $field['columnname'] = $row->taxname;
        $field['tablename'] = $tableName;
        $field['fieldlabel'] = $prefix . $row->taxlabel;
        $field['displaytype'] = 1;
        $field['uitype'] = 1;
        $fieldDataType = 'V';
        $typeOfData = $fieldType . '~O';

        $field['typeofdata'] = $typeOfData;
        $field['tabid'] = null;
        $field['fieldid'] = null;
        $field['masseditable'] = 0;

        return $field;
    }
}