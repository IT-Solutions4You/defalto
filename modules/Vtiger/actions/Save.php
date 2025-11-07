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

class Vtiger_Save_Action extends Core_Controller_Action
{
    public $savedRecordId;

    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $moduleParameter = $request->get('source_module');
        if (!$moduleParameter) {
            $moduleParameter = 'module';
        } else {
            $moduleParameter = 'source_module';
        }
        $record = $request->get('record');
        $recordId = $request->get('id');
        if (!$record) {
            $recordParameter = '';
        } else {
            $recordParameter = 'record';
        }
        $actionName = ($record || $recordId) ? 'EditView' : 'CreateView';
        $permissions[] = ['module_parameter' => $moduleParameter, 'action' => 'DetailView', 'record_parameter' => $recordParameter];
        $permissions[] = ['module_parameter' => $moduleParameter, 'action' => $actionName, 'record_parameter' => $recordParameter];

        return $permissions;
    }

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $moduleName = $request->getModule();
        $record = $request->get('record');

        $nonEntityModules = ['Users', 'Portal', 'Rss'];
        if ($record && !in_array($moduleName, $nonEntityModules)) {
            $recordEntityName = getSalesEntityType($record);
            if ($recordEntityName !== $moduleName) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        return parent::checkPermission($request);
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    public function process(Vtiger_Request $request)
    {
        try {
            $recordModel = $this->saveRecord($request);
            if ($request->get('returntab_label')) {
                $loadUrl = 'index.php?' . $request->getReturnURL();
            } elseif ($request->get('relationOperation')) {
                $parentModuleName = $request->get('sourceModule');
                $parentRecordId = $request->get('sourceRecord');
                $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
                //TODO : Url should load the related list instead of detail view of record
                $loadUrl = $parentRecordModel->getDetailViewUrl();
            } elseif ($request->get('returnToList')) {
                $loadUrl = $recordModel->getModule()->getListViewUrl();
            } elseif ($request->get('returnmodule') && $request->get('returnview')) {
                $loadUrl = 'index.php?' . $request->getReturnURL();
            } else {
                $loadUrl = $recordModel->getDetailViewUrl();
            }
            //append App name to callback url
            //Special handling for vtiger7.
            $appName = $request->get('appName');
            if (strlen($appName) > 0) {
                $loadUrl = $loadUrl . $appName;
            }
            header("Location: $loadUrl");
        } catch (DuplicateException $e) {
            $requestData = $request->getAll();
            $moduleName = $request->getModule();
            unset($requestData['action']);
            unset($requestData['__vtrftk']);

            if ($request->isAjax()) {
                $response = new Vtiger_Response();
                $response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
                $response->emit();
            } else {
                $requestData['view'] = 'Edit';
                $requestData['duplicateRecords'] = $e->getDuplicateRecordIds();
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

                global $defalto_current_version;
                $viewer = new Vtiger_Viewer();

                $viewer->assign('REQUEST_DATA', $requestData);
                $viewer->assign('REQUEST_URL', $moduleModel->getCreateRecordUrl() . '&record=' . $request->get('record'));
                $viewer->view('RedirectToEditView.tpl', 'Vtiger');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Function to save record
     *
     * @param <Vtiger_Request> $request - values of the record
     *
     * @return <RecordModel> - record Model of saved record
     */
    public function saveRecord($request)
    {
        $recordModel = $this->getRecordModelFromRequest($request);
        if ($request->get('imgDeleted')) {
            $imageIds = $request->get('imageid');
            foreach ($imageIds as $imageId) {
                $status = $recordModel->deleteImage($imageId);
            }
        }
        $recordModel->save();

        if ($request->get('relationOperation') && $request->get('sourceModule')) {
            $parentModuleName = $request->get('sourceModule');
            $parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
            $parentRecordId = $request->get('sourceRecord');
            $relatedModule = $recordModel->getModule();
            $relatedRecordId = $recordModel->getId();
            $relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);

            if ($relationModel) {
                $relationModel->addRelation($parentRecordId, $relatedRecordId);
            }
        }

        $this->savedRecordId = $recordModel->getId();

        return $recordModel;
    }

    /**
     * Function to get the record model based on the request parameters
     *
     * @param Vtiger_Request $request
     *
     * @return Vtiger_Record_Model or Module specific Record Model instance
     */
    protected function getRecordModelFromRequest(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        if (!empty($recordId)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $recordModel->set('id', $recordId);
            $recordModel->set('mode', 'edit');
        } else {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            $recordModel->set('mode', '');
        }

        $fieldModelList = $moduleModel->getFields();

        foreach ($fieldModelList as $fieldName => $fieldModel) {
            $fieldValue = $request->get($fieldName, null);
            $fieldValue = $fieldModel->getUITypeModel()->getRequestValue($fieldValue);

            if (null !== $fieldValue) {
                $recordModel->set($fieldName, $fieldValue);
            }
        }

        return $recordModel;
    }
}