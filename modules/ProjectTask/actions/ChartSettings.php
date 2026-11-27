<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ProjectTask_ChartSettings_Action extends Core_Controller_Action
{
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    public function checkPermission(Vtiger_Request $request): bool
    {
        if (!Users_Record_Model::getCurrentUserModel()->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $ids = $request->get('closed_statuses', []);

        if (!is_array($ids)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        (new ProjectTask_ClosedStatus_Model())->saveClosedStatuses($ids);
        $response = new Vtiger_Response();
        $response->setResult(['success' => true]);
        $response->emit();
    }
}
