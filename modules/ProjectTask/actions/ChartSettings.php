<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
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
