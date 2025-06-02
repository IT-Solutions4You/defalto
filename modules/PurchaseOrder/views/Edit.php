<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class PurchaseOrder_Edit_View extends Vtiger_Edit_View
{
    use InventoryItem_Edit_Trait;

    /**
     * @inheritDoc
     */
    function getHeaderScripts(Vtiger_Request $request)
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