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

class Documents_MoveDocuments_Action extends Vtiger_Mass_Action
{
    public function requiresPermission(Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];

        return $permissions;
    }

    public function checkPermission(Vtiger_Request $request)
    {
        return parent::checkPermission($request);
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $documentIdsList = $this->getRecordsListFromRequest($request);
        $folderId = $request->get('folderid');

        if (!empty ($documentIdsList)) {
            foreach ($documentIdsList as $documentId) {
                $documentModel = Vtiger_Record_Model::getInstanceById($documentId, $moduleName);
                if (Users_Privileges_Model::isPermitted($moduleName, 'EditView', $documentId)) {
                    $documentModel->set('folderid', $folderId);
                    $documentModel->set('mode', 'edit');
                    $documentModel->save();
                } else {
                    $documentsMoveDenied[] = $documentModel->getName();
                }
            }
        }
        if (empty ($documentsMoveDenied)) {
            $result = ['success' => true, 'message' => vtranslate('LBL_DOCUMENTS_MOVED_SUCCESSFULLY', $moduleName)];
        } else {
            $result = ['success' => false, 'message' => vtranslate('LBL_DENIED_DOCUMENTS', $moduleName), 'LBL_RECORDS_LIST' => $documentsMoveDenied];
        }

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}