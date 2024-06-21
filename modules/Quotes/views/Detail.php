<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

//class Quotes_Detail_View extends Inventory_Detail_View {
class Quotes_Detail_View extends Vtiger_Detail_View{
    use InventoryItem_Detail_Trait;
}

$module = Vtiger_Module::getInstance('InventoryItem');
$headerBlock = Vtiger_Block::getInstance('LBL_INVENTORYITEM_INFORMATION', $module);
$tax = new Vtiger_Field();
$tax->table = $module->basetable;
$tax->name = 'tax';
$tax->column = 'tax';
$tax->label = 'Tax';
$tax->uitype = 7;
$tax->presence = 0;
$tax->sequence = 12;
$tax->columntype = 'DECIMAL(25,4) DEFAULT NULL';
$tax->typeofdata = 'N~O';
$tax->quickcreate = 1;
$tax->masseditable = 0;
$tax->summaryfield = 0;
$tax->save($headerBlock);
