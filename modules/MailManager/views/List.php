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

require_once 'modules/MailManager/runtime/Response.php';

class MailManager_List_View extends MailManager_Abstract_View
{
    static $controllers = [
        'mainui'   => ['class' => 'MailManager_MainUI_View'],
        'folder'   => ['class' => 'MailManager_Folder_View'],
        'mail'     => ['class' => 'MailManager_Mail_View'],
        'relation' => ['class' => 'MailManager_Relation_View'],
        'settings' => ['class' => 'MailManager_Settings_View'],
        'search'   => ['class' => 'MailManager_Search_View'],
    ];

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);

        $jsFileNames = [
            "libraries.jquery.ckeditor.ckeditor",
            "libraries.jquery.ckeditor.adapters.jquery",
            "modules.Vtiger.resources.CkEditor",
            "modules.MailManager.resources.List"
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    public function process(Vtiger_Request $request)
    {
        $request = MailManager_Request::getInstance($request);

        if (!$request->has('_operation')) {
            return $this->processRoot($request);
        }
        $operation = $request->getOperation();
        $controllerInfo = self::$controllers[$operation];
        $controller = new $controllerInfo['class'];

        // Making sure to close the open connection
        if ($controller) {
            $controller->closeConnector();
        }
        if ($controller->validateRequest($request)) {
            $response = $controller->process($request);
            if ($response) {
                $response->emit();
            }
        }
        unset($request);
        unset($response);
    }

    public function processRoot(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $moduleName);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'processRoot', $request->getModule(), $viewer, $request);

        $viewer->view('index.tpl', $moduleName);
    }
}