<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
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

class PurchaseOrder_Detail_View extends Vtiger_Detail_View
{
    use InventoryItem_Detail_Trait;

    /**
     * Function returns Inventory details
     *
     * @param Vtiger_Request $request
     *
     * @return bool|html
     * @throws AppException
     */
    function showModuleDetailView(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $this->adaptDetail($request, $viewer);

        return parent::showModuleDetailView($request);
    }

    /**
     * Get the header scripts for the view.
     *
     * @param Vtiger_Request $request The request object
     *
     * @return array Merged header script instances
     */
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $jsFileNames = [
        ];

        if (method_exists($this, 'adaptHeaderScripts')) {
            $jsFileNames = array_merge($jsFileNames, $this->adaptHeaderScripts());
        }

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}
