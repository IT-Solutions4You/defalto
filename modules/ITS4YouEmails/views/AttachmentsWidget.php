<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ITS4YouEmails_AttachmentsWidget_View extends Vtiger_Detail_View
{
    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        $recordModel = ITS4YouEmails_Record_Model::getInstanceById($recordId, $moduleName);
        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        $viewer->assign('RECORD_ID', $recordId);

        if ($recordModel) {
            $viewer->assign('ATTACHMENTS', $recordModel->getAttachments());
        }

        $viewer->view('AttachmentsWidget.tpl', $moduleName);
    }
}