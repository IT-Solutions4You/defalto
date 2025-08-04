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

vimport('~~/include/Webservices/Custom/DeleteUser.php');

class Users_Import_View extends Vtiger_Import_View
{
    function checkPermission(Vtiger_Request $request)
    {
        parent::checkPermission($request);

        if ($request->getMode() == 'import') {
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
            if (!$currentUserModel->isAdminUser()) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        return true;
    }

    public function initializeMappingParameters(Vtiger_Request $request)
    {
        parent::initializeMappingParameters($request);
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $moduleFields = $moduleModel->getFields();

        $viewer = $this->getViewer($request);
        $viewer->assign('IMPORTABLE_FIELDS', $moduleModel->getImportableFieldModels($moduleName));
    }

    public function process(Vtiger_Request $request)
    {
        if ($request->getMode() != 'undoImport') {
            parent::process($request);
        } else {
            $viewer = new Vtiger_Viewer();
            $db = PearDatabase::getInstance();

            $moduleName = $request->getModule();

            $user = Users_Record_Model::getCurrentUserModel();
            $dbTableName = Import_Utils_Helper::getDbTableName($user);

            $query = "SELECT recordid FROM $dbTableName WHERE status = ? AND recordid IS NOT NULL";
            $result = $db->pquery($query, [Import_Data_Action::$IMPORT_RECORD_CREATED]);
            $noOfRecords = $db->num_rows($result);
            $noOfRecordsDeleted = 0;
            $activeAdminId = Users::getActiveAdminId();
            $userModel = Users_Record_Model::getCurrentUserModel();
            for ($i = 0; $i < $noOfRecords; $i++) {
                $recordId = $db->query_result($result, $i, 'recordid');
                $userId = vtws_getWebserviceEntityId($moduleName, $recordId);
                $transformUserId = vtws_getWebserviceEntityId($moduleName, $activeAdminId);
                vtws_deleteUser($userId, $transformUserId, $userModel);
                Users_Record_Model::deleteUserPermanently($recordId, $activeAdminId);
                $noOfRecordsDeleted++;
            }
            $viewer->assign('FOR_MODULE', $moduleName);
            $viewer->assign('MODULE', 'Import');
            $viewer->assign('TOTAL_RECORDS', $noOfRecords);
            $viewer->assign('DELETED_RECORDS_COUNT', $noOfRecordsDeleted);
            $viewer->view('ImportUndoResult.tpl', 'Import');
        }
    }
}