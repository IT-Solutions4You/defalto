<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Project_ChartPreference_Action extends Core_Controller_Action
{
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    public function checkPermission(Vtiger_Request $request): bool
    {
        $id = (int)$request->get('record');

        if (getSalesEntityType($id) !== 'Project' || !Users_Privileges_Model::isPermitted('Project', 'DetailView', $id)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $model = new Project_ChartPreference_Model();
        $projectId = (int)$request->get('record');
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();

        if ($request->get('reset')) {
            $model->clearPreferences($projectId, $userId);
        } else {
            $settings = $request->get('settings');

            if (!is_array($settings)) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }

            $model->savePreferences($projectId, $userId, $settings);
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => true]);
        $response->emit();
    }
}
