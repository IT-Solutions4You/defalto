<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_DeleteAjax_Action extends Vtiger_DeleteAjax_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $recurringEditMode = $request->get('recurringEditMode');
        $deletedRecords = [];

        if (!$request->isEmpty('recurringEditMode')) {
            $deleteRecords = Appointments_Recurrence_Model::getRecurringRecordsByType($recordId, $recurringEditMode);

            foreach ($deleteRecords as $deleteRecord) {
                if (!empty($deleteRecord) && isRecordExists($deleteRecord)) {
                    $recordModel = Vtiger_Record_Model::getInstanceById($deleteRecord, $moduleName);
                    $recordModel->delete();

                    Appointments_Recurrence_Model::deleteRelation($deleteRecord);

                    $deletedRecords[] = $deleteRecord;
                }
            }
        } else {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $recordModel->delete();

            $deletedRecords = [$recordId];
        }

        $cvId = $request->get('viewname');
        deleteRecordFromDetailViewNavigationRecords($recordId, $cvId, $moduleName);
        $response = new Vtiger_Response();
        $response->setResult(['viewname' => $cvId, 'module' => $moduleName, 'deletedRecords' => $deletedRecords]);
        $response->emit();
    }
}