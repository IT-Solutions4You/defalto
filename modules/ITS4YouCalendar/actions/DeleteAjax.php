<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ITS4YouCalendar_DeleteAjax_Action extends Vtiger_DeleteAjax_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $recurringEditMode = $request->get('recurringEditMode');
        $deletedRecords = [];

        if (!$request->isEmpty('recurringEditMode')) {
            $deleteRecords = ITS4YouCalendar_Recurrence_Model::getRecurringRecordsByType($recordId, $recurringEditMode);

            foreach ($deleteRecords as $deleteRecord) {
                if (!empty($deleteRecord) && isRecordExists($deleteRecord)) {
                    $recordModel = Vtiger_Record_Model::getInstanceById($deleteRecord, $moduleName);
                    $recordModel->delete();

                    ITS4YouCalendar_Recurrence_Model::deleteRelation($deleteRecord);

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
        $response->setResult(array('viewname' => $cvId, 'module' => $moduleName, 'deletedRecords' => $deletedRecords));
        $response->emit();
    }
}