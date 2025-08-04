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

class Vtiger_ProcessDuplicates_Action extends Vtiger_Action_Controller
{
    public function requiresPermission(Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];
        $permissions[] = ['module_parameter' => 'module', 'action' => 'EditView'];

        return $permissions;
    }

    function checkPermission(Vtiger_Request $request)
    {
        parent::checkPermission($request);
        $module = $request->getModule();
        $records = $request->get('records');
        if ($records) {
            foreach ($records as $record) {
                $recordPermission = Users_Privileges_Model::isPermitted($module, 'EditView', $record);
                if (!$recordPermission) {
                    throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
                }
            }
        }

        return true;
    }

    function process(Vtiger_Request $request)
    {
        global $skipDuplicateCheck;
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $records = $request->get('records');
        $primaryRecord = $request->get('primaryRecord');
        $primaryRecordModel = Vtiger_Record_Model::getInstanceById($primaryRecord, $moduleName);

        $response = new Vtiger_Response();
        try {
            $skipDuplicateCheckOldValue = $skipDuplicateCheck;
            $skipDuplicateCheck = true;

            $fields = $moduleModel->getFields();
            foreach ($fields as $field) {
                $fieldValue = $request->get($field->getName());
                if ($field->isEditable()) {
                    $primaryRecordModel->set($field->getName(), $fieldValue);
                }
            }
            $primaryRecordModel->set('mode', 'edit');
            $primaryRecordModel->save();

            $deleteRecords = array_diff($records, [$primaryRecord]);
            foreach ($deleteRecords as $deleteRecord) {
                $recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'Delete', $deleteRecord);
                if ($recordPermission) {
                    $primaryRecordModel->transferRelationInfoOfRecords([$deleteRecord]);
                    $record = Vtiger_Record_Model::getInstanceById($deleteRecord);
                    $record->delete();
                }
            }
            $skipDuplicateCheck = $skipDuplicateCheckOldValue;

            $response->setResult(true);
        } catch (DuplicateException $e) {
            $response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
        } catch (Exception $e) {
            $response->setError($e->getMessage());
        }
        $response->emit();
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateWriteAccess();
    }
}