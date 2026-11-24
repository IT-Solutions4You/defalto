<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class Project_Dependency_Action extends Core_Controller_Action
{
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    public function checkPermission(Vtiger_Request $request): bool
    {
        $recordId = (int)$request->get('record');

        if (getSalesEntityType($recordId) !== 'Project'
            || !Users_Privileges_Model::isPermitted('Project', 'EditView', $recordId)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $model = new Project_Dependency_Model();
        $projectId = (int)$request->get('record');
        $sourceId = (int)$request->get('source');
        $targetId = (int)$request->get('target');

        if ($request->get('operation') === 'create') {
            $model->createDependency($projectId, $sourceId, $targetId);
        } elseif ($request->get('operation') === 'remove') {
            $model->removeDependency($projectId, $sourceId, $targetId);
        } else {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => true]);
        $response->emit();
    }
}
