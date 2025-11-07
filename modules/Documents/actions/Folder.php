<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Documents_Folder_Action extends Core_Controller_Action
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('save');
        $this->exposeMethod('delete');
    }

    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];

        return $permissions;
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);
        }
    }

    public function save($request)
    {
        $moduleName = $request->getModule();
        $folderName = $request->get('foldername');
        $folderDesc = $request->get('folderdesc');
        $result = [];

        if (!empty ($folderName)) {
            $saveMode = $request->get('savemode');
            $folderModel = Documents_Folder_Model::getInstance();
            if ($saveMode == 'edit') {
                $folderId = $request->get('folderid');
                $folderModel = Documents_Folder_Model::getInstanceById($folderId);
                $folderModel->set('mode', 'edit');
            }

            $folderModel->set('foldername', $folderName);
            $folderModel->set('description', $folderDesc);

            if ($folderModel->checkDuplicate()) {
                throw new Exception(vtranslate('LBL_FOLDER_EXISTS', $moduleName));
                exit;
            }

            $folderModel->save();
            $result = ['success' => true, 'message' => vtranslate('LBL_FOLDER_SAVED', $moduleName), 'info' => $folderModel->getInfoArray()];

            $response = new Vtiger_Response();
            $response->setResult($result);
            $response->emit();
        }
    }

    public function delete($request)
    {
        $moduleName = $request->getModule();
        $folderId = $request->get('folderid');
        $result = [];

        if (!empty ($folderId)) {
            $folderModel = Documents_Folder_Model::getInstanceById($folderId);
            if (!($folderModel->hasDocuments())) {
                $folderModel->delete();
                $result = ['success' => true, 'message' => vtranslate('LBL_FOLDER_DELETED', $moduleName)];
            } else {
                $result = ['success' => false, 'message' => vtranslate('LBL_FOLDER_HAS_DOCUMENTS', $moduleName)];
            }
        }

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}