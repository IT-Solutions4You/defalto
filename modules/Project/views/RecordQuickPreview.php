<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Project_RecordQuickPreview_View extends Vtiger_RecordQuickPreview_View {
    public function process(Vtiger_Request $request)
    {
        $recordModel = Vtiger_Record_Model::getInstanceById($request->get('record'), $request->get('module'));

        $viewer = $this->getViewer($request);
        $viewer->assign('SUMMARY_INFORMATION', $recordModel->getSummaryInfo());

        parent::process($request);
    }
}