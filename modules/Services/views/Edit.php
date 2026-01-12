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

class Services_Edit_View extends Products_Edit_View
{
    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);

        $moduleName = $request->getModule();
        $moduleEditFile = 'modules.' . $moduleName . '.resources.Edit';
        unset($headerScriptInstances[$moduleEditFile]);

        $jsFileNames = [
            'modules.Products.resources.Edit',
        ];

        $jsFileNames[] = $moduleEditFile;
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    public function getOverlayHeaderScripts(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $jsFileNames = [
            "modules.Products.resources.Edit",
            "modules.$moduleName.resources.Edit",
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return $jsScriptInstances;
    }
}