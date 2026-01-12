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

class Settings_MenuEditor_EditAjax_View extends Settings_Vtiger_Index_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('showAddModule');
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    function showAddModule(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $qualifiedModuleName = $request->getModule(false);
        $appName = $request->get('appname');

        $viewer->assign('SELECTED_APP_NAME', $appName);
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('AddModule.tpl', $qualifiedModuleName);
    }
}