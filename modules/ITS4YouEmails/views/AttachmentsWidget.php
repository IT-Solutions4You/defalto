<?php
/*********************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class ITS4YouEmails_AttachmentsWidget_View extends Vtiger_Basic_View
{
    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        $recordModel = ITS4YouEmails_Record_Model::getInstanceById($recordId, $moduleName);
        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        $viewer->assign('RECORD_ID', $recordId);

        if($recordModel) {
            $viewer->assign('ATTACHMENTS', $recordModel->getAttachments());
        }

        $viewer->view('AttachmentsWidget.tpl', $moduleName);
    }
}