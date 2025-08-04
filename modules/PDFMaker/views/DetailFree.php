<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_DetailFree_View extends Vtiger_Index_View
{
    function __construct()
    {
        parent::__construct();
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        parent::preProcess($request, false);
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->assign('QUALIFIED_MODULE', $moduleName);

        $moduleName = $request->getModule();

        if (!empty($moduleName)) {
            $moduleModel = new PDFMaker_PDFMaker_Model('PDFMaker');
            $currentUser = Users_Record_Model::getCurrentUserModel();
            $userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
            $permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
            $viewer->assign('MODULE', $moduleName);

            if (!$permission) {
                $viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
                $viewer->view('OperationNotPermitted.tpl', $moduleName);
                exit;
            }

            $linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view')];
            $linkModels = $moduleModel->getSideBarLinks($linkParams);

            $viewer->assign('QUICK_LINKS', $linkModels);
        }

        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_VIEW', $request->get('view'));

        $recordId = $request->get('templateid');
        $this->record = PDFMaker_DetailView_Model::getInstance($moduleName, $recordId);
        $recordModel = $this->record->getRecord();

        $viewer->assign('RECORD', $recordModel);

        $viewer->assign('MODULE_MODEL', $this->record->getModule());

        $detailViewLinkParams = ['MODULE' => $moduleName, 'RECORD' => $recordId];
        $detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);
        $viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    public function process(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();

        $PDFMaker = new PDFMaker_PDFMaker_Model();

        $viewer = $this->getViewer($request);

        $templateid = $request->get('templateid');
        $pdftemplateResult = $PDFMaker->GetDetailViewData($templateid);
        $recordModel = PDFMaker_Record_Model::getInstanceById($templateid);

        $formodule = getTranslatedString($pdftemplateResult['module']);
        $viewer->assign('FILENAME', $formodule);
        $viewer->assign('DESCRIPTION', $pdftemplateResult['description']);
        $viewer->assign('TEMPLATEID', $pdftemplateResult['templateid']);
        $viewer->assign('MODULENAME', $formodule);

        $pdf_body = decode_html($pdftemplateResult['body']);
        $pdf_header = decode_html($pdftemplateResult['header']);
        $pdf_footer = decode_html($pdftemplateResult['footer']);

        $viewer->assign('BODY', $pdf_body);
        $viewer->assign('HEADER', $pdf_header);
        $viewer->assign('FOOTER', $pdf_footer);

        $category = getParentTab();
        $viewer->assign('CATEGORY', $category);
        $viewer->assign('PDFMAKER_RECORD_MODEL', $recordModel);

        $viewer->view('DetailFree.tpl', 'PDFMaker');
    }

    function preProcessTplName(Vtiger_Request $request)
    {
        return 'DetailViewPreProcess.tpl';
    }

    function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $jsFileNames = [
            'modules.Vtiger.resources.Detail',
            'modules.PDFMaker.resources.DetailFree'
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}