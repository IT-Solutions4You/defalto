<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Quotes_Detail_View extends Vtiger_Detail_View
{
    use InventoryItem_Detail_Trait;

    /**
     * Function returns Inventory details
     *
     * @param Vtiger_Request $request
     *
     * @return bool|html
     */
    function showModuleDetailView(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $this->adaptDetail($request, $viewer);

        return parent::showModuleDetailView($request);
    }
}