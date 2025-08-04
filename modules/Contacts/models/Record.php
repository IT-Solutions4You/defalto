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

class Contacts_Record_Model extends Vtiger_Record_Model
{
    /**
     * Function to get List of Fields which are related from Contacts to Inventory Record
     * @return <array>
     */
    public function getInventoryMappingFields()
    {
        return [
            ['parentField' => 'account_id', 'inventoryField' => 'account_id', 'defaultValue' => ''],

            //Billing Address Fields
            ['parentField' => 'mailingcity', 'inventoryField' => 'bill_city', 'defaultValue' => ''],
            ['parentField' => 'mailingstreet', 'inventoryField' => 'bill_street', 'defaultValue' => ''],
            ['parentField' => 'mailingstate', 'inventoryField' => 'bill_state', 'defaultValue' => ''],
            ['parentField' => 'mailingzip', 'inventoryField' => 'bill_code', 'defaultValue' => ''],
            ['parentField' => 'mailingcountry_id', 'inventoryField' => 'bill_country_id', 'defaultValue' => ''],
            ['parentField' => 'mailingpobox', 'inventoryField' => 'bill_pobox', 'defaultValue' => ''],

            //Shipping Address Fields
            ['parentField' => 'otherstreet', 'inventoryField' => 'ship_street', 'defaultValue' => ''],
            ['parentField' => 'othercity', 'inventoryField' => 'ship_city', 'defaultValue' => ''],
            ['parentField' => 'otherstate', 'inventoryField' => 'ship_state', 'defaultValue' => ''],
            ['parentField' => 'otherzip', 'inventoryField' => 'ship_code', 'defaultValue' => ''],
            ['parentField' => 'othercountry_id', 'inventoryField' => 'ship_country_id', 'defaultValue' => ''],
            ['parentField' => 'otherpobox', 'inventoryField' => 'ship_pobox', 'defaultValue' => '']
        ];
    }
}