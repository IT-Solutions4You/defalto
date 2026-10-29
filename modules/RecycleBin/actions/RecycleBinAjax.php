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
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (!$currentUserModel->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
    }

    /**
     * @inheritDoc
     */
    protected function getRecordsListFromRequest(Vtiger_Request $request, $model = false)
    {
        $selectedIds = $request->get('selected_ids');
        $sourceModule = $request->get('sourceModule');

        if (!$request->has('selected_ids') || !$this->hasValidSelectionState($selectedIds) || !$this->isValidSourceModule($sourceModule)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        if ($selectedIds === 'all' && !$this->isSourceModuleView($request->get('viewname'), $sourceModule)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        return parent::getRecordsListFromRequest($request, $model);
    }

    protected function hasValidSelectionState($selectedIds): bool
    {
        return $selectedIds === 'all' || (is_array($selectedIds) && !empty($selectedIds));
    }

    protected function isValidSourceModule(string $sourceModule): bool
    {
        $recycleBinModule = new RecycleBin_Module_Model();

        return $recycleBinModule->isSourceModuleAllowed($sourceModule);
    }

    protected function isSourceModuleView($viewId, string $sourceModule): bool
    {
        if (empty($viewId) || $viewId === 'undefined') {
            $viewId = CustomView_Record_Model::getAllFilterByModule($sourceModule)->getId();
        }

        $customViewModel = CustomView_Record_Model::getInstanceById($viewId);

        return $customViewModel && $customViewModel->getModule()->getName() === $sourceModule;
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
            $recycleBinModule->deleteRecords($recordIds, $request->get('sourceModule'));
        }

        $response->setResult([true]);
        $response->emit();
    }
}
