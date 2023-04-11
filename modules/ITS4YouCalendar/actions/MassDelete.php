<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ITS4YouCalendar_MassDelete_Action extends Vtiger_MassDelete_Action
{
    /**
     * @param Vtiger_Request $request
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
            if (Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
                $recordModel->delete();

                ITS4YouCalendar_Recurrence_Model::deleteRelation($recordId);

                deleteRecordFromDetailViewNavigationRecords($recordId, $cvId, $moduleName);
            }
        }

        $response = new Vtiger_Response();
        $response->setResult(array('viewname' => $cvId, 'module' => $moduleName));
        $response->emit();
    }
}