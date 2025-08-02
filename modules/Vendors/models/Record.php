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

class Vendors_Record_Model extends Vtiger_Record_Model
{
    function getCreatePurchaseOrderUrl()
    {
        $purchaseOrderModuleModel = Vtiger_Module_Model::getInstance('PurchaseOrder');

        return "index.php?module=" . $purchaseOrderModuleModel->getName() . "&view=" . $purchaseOrderModuleModel->getEditViewName() . "&vendor_id=" . $this->getId(
            ) . "&sourceModule=" . $this->getModuleName() . "&sourceRecord=" . $this->getId();
    }

    /**
     * Function to get List of Fields which are related from Vendors to Inventyory Record
     * @return <array>
     */
    public function getInventoryMappingFields()
    {
        return [
            //Billing Address Fields
            ['parentField' => 'city', 'inventoryField' => 'bill_city', 'defaultValue' => ''],
            ['parentField' => 'street', 'inventoryField' => 'bill_street', 'defaultValue' => ''],
            ['parentField' => 'state', 'inventoryField' => 'bill_state', 'defaultValue' => ''],
            ['parentField' => 'postalcode', 'inventoryField' => 'bill_code', 'defaultValue' => ''],
            ['parentField' => 'country_id', 'inventoryField' => 'bill_country_id', 'defaultValue' => ''],
            ['parentField' => 'pobox', 'inventoryField' => 'bill_pobox', 'defaultValue' => ''],

            //Shipping Address Fields
            ['parentField' => 'street', 'inventoryField' => 'ship_street', 'defaultValue' => ''],
            ['parentField' => 'city', 'inventoryField' => 'ship_city', 'defaultValue' => ''],
            ['parentField' => 'state', 'inventoryField' => 'ship_state', 'defaultValue' => ''],
            ['parentField' => 'postalcode', 'inventoryField' => 'ship_code', 'defaultValue' => ''],
            ['parentField' => 'country_id', 'inventoryField' => 'ship_country_id', 'defaultValue' => ''],
            ['parentField' => 'pobox', 'inventoryField' => 'ship_pobox', 'defaultValue' => '']
        ];
    }
}