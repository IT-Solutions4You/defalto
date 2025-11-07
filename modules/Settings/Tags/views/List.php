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

class Settings_Tags_List_View extends Settings_Vtiger_List_View
{
    function checkPermission(Vtiger_Request $request)
    {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        if ($layout == 'vlayout') {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        return true;
    }

    public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer)
    {
        parent::initializeListViewContents($request, $viewer);
        $viewer->assign('SHOW_LISTVIEW_CHECKBOX', false);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);

        $jsFileNames = [
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/lib/jquery/floatThead/jquery.floatThead.js",
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/lib/jquery/perfect-scrollbar/js/perfect-scrollbar.jquery.js",
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        $headerCssInstances = parent::getHeaderCss($request);

        $cssFileNames = [
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/lib/jquery/perfect-scrollbar/css/perfect-scrollbar.css",
        ];
        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerCssInstances = array_merge($headerCssInstances, $cssInstances);

        return $headerCssInstances;
    }
}