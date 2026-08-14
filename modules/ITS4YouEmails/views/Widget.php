<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ITS4YouEmails_Widget_View extends Core_Widget_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('showBody');
        $this->exposeMethod('showAttachments');
    }

    public function showBody(Vtiger_Request $request): string
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_ID', $request->getRecord());
        $viewer->assign('MODULE_NAME', $moduleName);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'showBody', $moduleName, $viewer, $request);

        return $viewer->view('BodyWidget.tpl', $moduleName, true);
    }

    public function showAttachments(Vtiger_Request $request): string
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();
        $recordModel = ITS4YouEmails_Record_Model::getInstanceById($recordId, $moduleName);
        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        $viewer->assign('RECORD_ID', $recordId);

        if ($recordModel) {
            $viewer->assign('ATTACHMENTS', $recordModel->getAttachments());
        }

        Core_Modifiers_Model::modifyForClass(get_class($this), 'showAttachments', $moduleName, $viewer, $request);

        return $viewer->view('AttachmentsWidget.tpl', $moduleName, true);
    }
}
