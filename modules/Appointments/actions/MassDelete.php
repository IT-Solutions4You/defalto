<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_MassDelete_Action extends Vtiger_MassDelete_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        if ('all' === $request->get('selected_ids') && 'FindDuplicates' === $request->get('mode')) {
            $recordIds = Vtiger_FindDuplicate_Model::getMassDeleteRecords($request);
        } else {
            $recordIds = $this->getRecordsListFromRequest($request);
        }

        $cvId = $request->get('viewname');

        foreach ($recordIds as $recordId) {
            if (!empty($recordId) && isRecordExists($recordId) && Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
                $recordModel->delete();

                Appointments_Recurrence_Model::deleteRelation($recordId);

                deleteRecordFromDetailViewNavigationRecords($recordId, $cvId, $moduleName);
            }
        }

        $response = new Vtiger_Response();
        $response->setResult(['viewname' => $cvId, 'module' => $moduleName]);
        $response->emit();
    }
}