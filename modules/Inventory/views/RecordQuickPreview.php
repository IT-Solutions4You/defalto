<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Inventory_RecordQuickPreview_View extends Vtiger_RecordQuickPreview_View {
    public function getQuickPreviewHeaderScripts(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $jsFileNames = array(
            "modules.Vtiger.resources.Detail",
            "modules.Inventory.resources.Detail",
            "modules.$moduleName.resources.Detail",
            "modules.Vtiger.resources.RelatedList",
            "modules.$moduleName.resources.RelatedList",
        );

        return $this->checkAndConvertJsScripts($jsFileNames);
    }
}