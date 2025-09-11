<?php
/*
 *
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Invoice_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
    use InventoryItem_Edit_Trait;

    /**
     * @param Vtiger_Request $request
     * @return array
     */
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $jsFileNames = [];

        if (method_exists($this, 'adaptHeaderScripts')) {
            $jsFileNames = array_merge($jsFileNames, $this->adaptHeaderScripts());
        }

        $jsFileNames[] = "modules.$moduleName.resources.Edit";

        return $this->checkAndConvertJsScripts($jsFileNames);
    }
}
