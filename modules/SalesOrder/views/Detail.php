<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class SalesOrder_Detail_View extends Vtiger_Detail_View
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