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

class Settings_Vtiger_OutgoingServerDetail_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $systemDetailsModel = Settings_Vtiger_Systems_Model::getInstanceFromServerType('email', 'OutgoingServer');
        $viewer = $this->getViewer($request);
        $qualifiedName = $request->getModule(false);

        $viewer->assign('MODEL', $systemDetailsModel);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedName);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('OD_DEFAULT_MAIL_SERVER_IP', VtigerConfig::getOD('OD_DEFAULT_MAIL_SERVER_IP'));

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('OutgoingServerDetail.tpl', $qualifiedName);
    }

    /**
     * @inheritDoc
     */
    public function getPageTitle(Vtiger_Request $request): string
    {
        $qualifiedModuleName = $request->getModule(false);

        return vtranslate('LBL_OUTGOING_SERVER', $qualifiedModuleName);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            "modules.Settings.$moduleName.resources.OutgoingServer"
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}