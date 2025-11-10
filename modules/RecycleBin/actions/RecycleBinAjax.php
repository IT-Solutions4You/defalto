<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class RecycleBin_RecycleBinAjax_Action extends Vtiger_Mass_Action
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('restoreRecords');
        $this->exposeMethod('emptyRecycleBin');
        $this->exposeMethod('deleteRecords');
    }

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        if ($request->get('mode') == 'emptyRecycleBin') {
            //Only admin user can empty the recycle bin, so this check is mandatory
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
            if (!$currentUserModel->isAdminUser()) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
            }

            return true;
        }
        $targetModuleName = $request->get('sourceModule', $request->get('module'));
        $moduleModel = Vtiger_Module_Model::getInstance($targetModuleName);

        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
            throw new Exception(getTranslatedString('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');

        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    /**
     * Function to restore the deleted records.
     *
     * @param type $sourceModule
     * @param type $recordIds
     */
    public function restoreRecords(Vtiger_Request $request)
    {
        $sourceModule = $request->get('sourceModule');
        $recordIds = $this->getRecordsListFromRequest($request);
        $recycleBinModule = new RecycleBin_Module_Model();

        $response = new Vtiger_Response();
        if ($recordIds) {
            try {
                $recycleBinModule->restore($sourceModule, $recordIds);
                $response->setResult([true]);
            } catch (DuplicateException $e) {
                $response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
            } catch (Exception $e) {
                $response->setError($e->getMessage());
            }
        } else {
            $response->setResult([true]);
        }

        $response->emit();
    }

    /**
     * Function to delete the records permanently in vitger CRM database
     */
    public function emptyRecycleBin(Vtiger_Request $request)
    {
        $recycleBinModule = new RecycleBin_Module_Model();

        $status = $recycleBinModule->emptyRecycleBin();

        if ($status) {
            $response = new Vtiger_Response();
            $response->setResult([$status]);
            $response->emit();
        }
    }

    /**
     * Function to deleted the records permanently in CRM
     *
     * @param type $reocrdIds
     */
    public function deleteRecords(Vtiger_Request $request)
    {
        $recordIds = $this->getRecordsListFromRequest($request);
        $recycleBinModule = new RecycleBin_Module_Model();

        $response = new Vtiger_Response();
        if ($recordIds) {
            $recycleBinModule->deleteRecords($recordIds);
            $response->setResult([true]);
            $response->emit();
        }
    }
}