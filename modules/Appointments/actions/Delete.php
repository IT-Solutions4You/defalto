<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_Delete_Action extends Vtiger_Delete_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void|Vtiger_Response
     */
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $ajaxDelete = $request->get('ajaxDelete');

        if (!$request->isEmpty('recurringEditMode')) {
            $recurringEditMode = $request->get('recurringEditMode');
            $deleteRecords = Appointments_Recurrence_Model::getRecurringRecordsByType($recordId, $recurringEditMode);

            foreach ($deleteRecords as $deleteRecord) {
                if (!empty($deleteRecord) && isRecordExists($deleteRecord)) {
                    $recordModel = Vtiger_Record_Model::getInstanceById($deleteRecord, $moduleName);
                    $recordModel->delete();

                    Appointments_Recurrence_Model::deleteRelation($deleteRecord);
                }
            }
        } else {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $recordModel->delete();
        }

        $cv = new CustomView();
        $cvId = $cv->getViewId($moduleName);
        deleteRecordFromDetailViewNavigationRecords($recordId, $cvId, $moduleName);

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $listViewUrl = $moduleModel->getListViewUrl();

        if ($ajaxDelete) {
            $response = new Vtiger_Response();
            $response->setResult($listViewUrl);

            return $response;
        } else {
            header("Location: $listViewUrl");
        }
    }
}