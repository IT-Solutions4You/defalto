<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Project_RecordQuickPreview_View extends Vtiger_RecordQuickPreview_View
{
    public function process(Vtiger_Request $request)
    {
        $recordModel = Vtiger_Record_Model::getInstanceById($request->get('record'), $request->get('module'));

        $viewer = $this->getViewer($request);
        $viewer->assign('SUMMARY_INFORMATION', $recordModel->getSummaryInfo());

        parent::process($request);
    }
}