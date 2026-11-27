<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ProjectTask_ChartSettings_View extends Vtiger_Index_View
{
    public function checkPermission(Vtiger_Request $request): bool
    {
        if (!Users_Record_Model::getCurrentUserModel()->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $model = new ProjectTask_ClosedStatus_Model();
        $viewer = $this->getViewer($request);
        $ready = $model->isAvailable();
        $viewer->assign('MODULE', 'ProjectTask')->assign('STATUS_OPTIONS', $ready ? $model->getOptions() : [])->assign('SETTINGS_READY', $ready);
        $viewer->view('ChartSettings.tpl', 'ProjectTask');
    }

    public function getHeaderScripts(Vtiger_Request $request): array
    {
        return array_merge(parent::getHeaderScripts($request), $this->checkAndConvertJsScripts(['modules.ProjectTask.resources.ChartSettings']));
    }
}
