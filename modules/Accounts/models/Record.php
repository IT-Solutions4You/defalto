<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

class Accounts_Record_Model extends Vtiger_Record_Model
{
    /**
     * Function returns the details of Accounts Hierarchy
     * @return <Array>
     */
    function getAccountHierarchy()
    {
        $focus = CRMEntity::getInstance($this->getModuleName());
        $hierarchy = $focus->getAccountHierarchy($this->getId());
        $i = 0;
        foreach ($hierarchy['entries'] as $accountId => $accountInfo) {
            preg_match('/<a href="+/', $accountInfo[0], $matches);
            if ($matches != null) {
                preg_match('/[.\s]+/', $accountInfo[0], $dashes);
                preg_match("/<a(.*)>(.*)<\/a>/i", $accountInfo[0], $name);

                $recordModel = Vtiger_Record_Model::getCleanInstance('Accounts');
                $recordModel->setId($accountId);
                $hierarchy['entries'][$accountId][0] = $dashes[0] . "<a href=" . $recordModel->getDetailViewUrl() . ">" . $name[2] . "</a>";
            }
        }

        return $hierarchy;
    }

    /**
     * Function to check duplicate exists or not
     * @return <boolean>
     */
    public function checkDuplicate()
    {
        $db = PearDatabase::getInstance();

        $query = "SELECT 1 FROM vtiger_crmentity WHERE setype = ? AND label = ? AND deleted = 0";
        $params = [$this->getModule()->getName(), decode_html($this->getName())];

        $record = $this->getId();
        if ($record) {
            $query .= " AND crmid != ?";
            array_push($params, $record);
        }

        $result = $db->pquery($query, $params);
        if ($db->num_rows($result)) {
            return true;
        }

        return false;
    }

    /**
     * Function to get List of Fields which are related from Accounts to Inventory Record.
     * @return <array>
     */
    public function getInventoryMappingFields()
    {
        return [
            //Billing Address Fields
            ['parentField' => 'bill_city', 'inventoryField' => 'bill_city', 'defaultValue' => ''],
            ['parentField' => 'bill_street', 'inventoryField' => 'bill_street', 'defaultValue' => ''],
            ['parentField' => 'bill_state', 'inventoryField' => 'bill_state', 'defaultValue' => ''],
            ['parentField' => 'bill_code', 'inventoryField' => 'bill_code', 'defaultValue' => ''],
            ['parentField' => 'bill_country_id', 'inventoryField' => 'bill_country_id', 'defaultValue' => ''],
            ['parentField' => 'bill_pobox', 'inventoryField' => 'bill_pobox', 'defaultValue' => ''],

            //Shipping Address Fields
            ['parentField' => 'ship_city', 'inventoryField' => 'ship_city', 'defaultValue' => ''],
            ['parentField' => 'ship_street', 'inventoryField' => 'ship_street', 'defaultValue' => ''],
            ['parentField' => 'ship_state', 'inventoryField' => 'ship_state', 'defaultValue' => ''],
            ['parentField' => 'ship_code', 'inventoryField' => 'ship_code', 'defaultValue' => ''],
            ['parentField' => 'ship_country_id', 'inventoryField' => 'ship_country_id', 'defaultValue' => ''],
            ['parentField' => 'ship_pobox', 'inventoryField' => 'ship_pobox', 'defaultValue' => '']
        ];
    }
}