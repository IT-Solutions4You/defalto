<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouEmails_Detail_View extends Vtiger_Detail_View
{
    public function showModuleBasicView(Vtiger_Request $request)
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

        echo parent::showModuleBasicView($request);
    }

    /**
     * @param object $recordModel
     * @return false
     */
    public function isAjaxEnabled($recordModel)
    {
        return false;
    }

    public function showModuleDetailView(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        $recordModel = ITS4YouEmails_Record_Model::getInstanceById($recordId, $moduleName);
        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);

        if($recordModel) {
            $viewer->assign('ATTACHMENTS', $recordModel->getAttachments());
        }

        echo parent::showModuleDetailView($request);
    }
}