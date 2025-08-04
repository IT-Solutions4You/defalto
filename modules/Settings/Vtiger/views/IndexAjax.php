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

class Settings_Vtiger_IndexAjax_View extends Settings_Vtiger_Index_View
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getSettingsShortCutBlock');
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        return;
    }

    public function postProcess(Vtiger_Request $request)
    {
        return;
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if ($mode) {
            echo $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    public function getSettingsShortCutBlock(Vtiger_Request $request)
    {
        $fieldid = $request->get('fieldid');
        $viewer = $this->getViewer($request);
        $qualifiedModuleName = $request->getModule(false);
        $pinnedSettingsShortcuts = Settings_Vtiger_MenuItem_Model::getPinnedItems();
        $viewer->assign('SETTINGS_SHORTCUT', $pinnedSettingsShortcuts[$fieldid]);
        $viewer->assign('MODULE', $qualifiedModuleName);
        $viewer->view('SettingsShortCut.tpl', $qualifiedModuleName);
    }
}