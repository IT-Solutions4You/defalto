<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ITS4YouCalendar_Delete_Action extends Vtiger_Delete_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void|Vtiger_Response
     */
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $ajaxDelete = $request->get('ajaxDelete');

        if (!$request->isEmpty('recurringEditMode')) {
            $recurringEditMode = $request->get('recurringEditMode');
            $deleteRecords = ITS4YouCalendar_Recurrence_Model::getRecurringRecordsByType($recordId, $recurringEditMode);

            foreach ($deleteRecords as $deleteRecord) {
                if (!empty($deleteRecord) && isRecordExists($deleteRecord)) {
                    $recordModel = Vtiger_Record_Model::getInstanceById($deleteRecord, $moduleName);
                    $recordModel->delete();

                    ITS4YouCalendar_Recurrence_Model::deleteRelation($deleteRecord);
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