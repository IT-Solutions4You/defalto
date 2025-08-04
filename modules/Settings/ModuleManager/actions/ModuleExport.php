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

class Settings_ModuleManager_ModuleExport_Action extends Settings_Vtiger_IndexAjax_View
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('exportModule');
    }

    function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    protected function exportModule(Vtiger_Request $request)
    {
        $moduleName = $request->get('forModule');

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        if (!$moduleModel->isExportable()) {
            echo 'Module not exportable!';

            return;
        }

        $package = new Vtiger_PackageExport();
        $package->export($moduleModel, '', sprintf("%s-%s.zip", $moduleModel->get('name'), $moduleModel->get('version')), true);
    }
}