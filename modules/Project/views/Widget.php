<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Project_Widget_View extends Core_Widget_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('showKeyMetrics');
    }

    public function showKeyMetrics(Vtiger_Request $request): string
    {
        $moduleName = $request->getModule();
        $recordModel = Vtiger_Record_Model::getInstanceById($request->getRecord(), $moduleName);
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('SUMMARY_INFORMATION', $recordModel->getSummaryInfo());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'showKeyMetrics', $moduleName, $viewer, $request);

        return $viewer->view('WidgetKeyMetrics.tpl', $moduleName, true);
    }
}
