<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_Detail_View extends Vtiger_Index_View
{
    public function __construct()
    {
        parent::__construct();

        $this->exposeMethod('showDocuments');
        $this->exposeMethod('showDetail');
        $this->exposeMethod('showRelatedList');
        $this->exposeMethod('showEmailCampaigns');
        $this->exposeMethod('showEmailWorkflows');
    }

    public function process(Vtiger_Request $request)
    {
        $this->getProcess($request);
    }

    public function getProcess(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);
            return;
        }
        echo $this->showModuleDetailView($request);
    }

    public function showModuleDetailView(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();

        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        if ($EMAILMaker->CheckPermissions("DETAIL") == false) {
            $EMAILMaker->DieDuePermission();
        }

        $viewer = $this->getViewer($request);

        if ($request->has('record') && !$request->isEmpty('record')) {
            $record = $request->get('record');
            if (substr($record, 0, 1) == "t") {
                $record = substr($record, 1);
            }

            if (!$this->record) {
                $this->record = EMAILMaker_DetailView_Model::getInstance("EMAILMaker", $record);
            }

            $recordModel = $this->record->getRecord();

            $emailtemplateResult = $EMAILMaker->GetDetailViewData($record);
            $viewer->assign("TEMPLATENAME", $emailtemplateResult["templatename"]);
            $viewer->assign("SUBJECT", $emailtemplateResult["subject"]);
            $viewer->assign("DESCRIPTION", $emailtemplateResult["description"]);
            $viewer->assign("TEMPLATEID", $emailtemplateResult["templateid"]);
            $viewer->assign("MODULENAME", getTranslatedString($emailtemplateResult["module"]));
            $email_body = decode_html($emailtemplateResult["body"]);

            if (vtlib_isModuleActive("ITS4YouStyles")) {
                $ITS4YouStylesModuleModel = new ITS4YouStyles_Module_Model();
                $email_body = $ITS4YouStylesModuleModel->addStyles($email_body, $emailtemplateResult["templateid"], "EMAILMaker");
            }
            $viewer->assign("BODY", $email_body);

            $viewer->assign("IS_ACTIVE", $emailtemplateResult["is_active"]);
            $viewer->assign("IS_DEFAULT", $emailtemplateResult["is_default"]);
            $viewer->assign("ACTIVATE_BUTTON", $emailtemplateResult["activateButton"]);
            $viewer->assign("DEFAULT_BUTTON", $emailtemplateResult["defaultButton"]);
            $detailViewLinks = $EMAILMaker->getDetailViewLinks($record);
            $viewer->assign('EMAILMAKER_RECORD_MODEL', $recordModel);
            $viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
        }

        $category = getParentTab();
        $viewer->assign("CATEGORY", $category);
        $viewer->assign("IS_MASSEMAIL", "no");


        $viewer->view('Detail.tpl', 'EMAILMaker');
    }

    public function preProcessTplName(Vtiger_Request $request)
    {
        return 'DetailViewPreProcess.tpl';
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        Vtiger_Basic_View::preProcess($request, false);

        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);

        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();

        if (!empty($moduleName)) {
            $moduleModel = new EMAILMaker_EMAILMaker_Model('EMAILMaker');
            $currentUser = Users_Record_Model::getCurrentUserModel();
            $userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
            $permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
            $viewer->assign('MODULE', $moduleName);

            if (!$permission) {
                $viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
                $viewer->view('OperationNotPermitted.tpl', $moduleName);
                exit;
            }

            $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
            $linkModels = $moduleModel->getSideBarLinks($linkParams);
            $viewer->assign('QUICK_LINKS', $linkModels);
        }

        if ($request->has('record') && !$request->isEmpty('record')) {
            $recordId = $request->get('record');

            if (substr($recordId, 0, 1) == "t") {
                $recordId = substr($recordId, 1);
            }

            $emailtemplateResult = $EMAILMaker->GetDetailViewData($recordId);
            $viewer->assign("TEMPLATENAME", $emailtemplateResult["templatename"]);
            $viewer->assign("SUBJECT", $emailtemplateResult["subject"]);
            $viewer->assign("DESCRIPTION", $emailtemplateResult["description"]);
            $viewer->assign("TEMPLATEID", $emailtemplateResult["templateid"]);
            $viewer->assign("MODULENAME", getTranslatedString($emailtemplateResult["module"]));

            $email_body = decode_html($emailtemplateResult["body"]);
            if (class_exists('ITS4YouStyles_Module_Model') && vtlib_isModuleActive("ITS4YouStyles")) {
                $viewer->assign("ISSTYLESACTIVE", "yes");
                $ITS4YouStylesModuleModel = new ITS4YouStyles_Module_Model();
                $ITS4YouStylesModuleModel->loadStyles($emailtemplateResult["templateid"], "EMAILMaker");
                $email_body = $ITS4YouStylesModuleModel->addStyles($email_body);

                $Styles_List = $ITS4YouStylesModuleModel->getRelatedRecords($emailtemplateResult["templateid"], "EMAILMaker", "asc", true);
                $viewer->assign("STYLES_LIST", $Styles_List);
            }

            $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
            $documentsInstance = Vtiger_Module_Model::getInstance('Documents');
            if ($userPrivilegesModel->hasModuleActionPermission($documentsInstance->getId(), 'DetailView')) {
                $viewer->assign("ISDOCUMENTSACTIVE", "yes");
                $Documents_Header = array("title" => 'Title', "folder" => 'Folder Name', "assigned_to" => 'Assigned To', "name" => 'File Name');
                $viewer->assign('DOCUMENTS_HEADERS', $Documents_Header);
                $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
                $Documents_Records = $EMAILMaker->getEmailTemplateDocuments($recordId);
                $Template_Permissions_Data = $EMAILMaker->returnTemplatePermissionsData("", $recordId);
                if ($Template_Permissions_Data["edit"]) {
                    $viewer->assign("IS_DELETABLE", "yes");
                    $viewer->assign("EDIT", "permitted");
                }

                if (count($Documents_Records) > 0) {
                    $viewer->assign('DOCUMENTS_RECORDS', $Documents_Records);
                }
            }

            $viewer->assign("BODY", $email_body);

            $viewer->assign("IS_ACTIVE", $emailtemplateResult["is_active"]);
            $viewer->assign("IS_DEFAULT", $emailtemplateResult["is_default"]);
            $viewer->assign("ACTIVATE_BUTTON", $emailtemplateResult["activateButton"]);
            $viewer->assign("DEFAULT_BUTTON", $emailtemplateResult["defaultButton"]);
            $detailViewLinks = $EMAILMaker->getDetailViewLinks($recordId);
            $viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);

            if ($emailtemplateResult["permissions"]["edit"]) {
                $viewer->assign("EXPORT", "yes");
            }

            if ($emailtemplateResult["permissions"]["edit"]) {
                $viewer->assign("EDIT", "permitted");
                $viewer->assign("IMPORT", "yes");
            }

            if ($emailtemplateResult["permissions"]["delete"]) {
                $viewer->assign("DELETE", "permitted");
            }

            if (!$this->record) {
                $this->record = EMAILMaker_DetailView_Model::getInstance($moduleName, $recordId);
            }

            $recordModel = $this->record->getRecord();

            $viewer->assign('RECORD', $recordModel);
            $viewer->assign('MODULE_MODEL', $this->record->getModule());

            $detailViewLinkParams = array('MODULE' => $moduleName, 'RECORD' => $recordId);

            $detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);
            $viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);

        }

        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_VIEW', $request->get('view'));
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('MODULE_BASIC_ACTIONS', []);

        $mode = $request->getMode();

        $viewer->assign('MODE', $mode);

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    public function showRelatedList(Vtiger_Request $request)
    {

        $related_module = $request->get("relatedModule");
        if ($related_module == "ITS4YouStyles") {
            $viewer = $this->getViewer($request);
            $ITS4YouStyles_Module_Model = new ITS4YouStyles_Module_Model();
            echo $ITS4YouStyles_Module_Model->showITS4YouStyles($request, $viewer);
        } else {
            echo $this->showDocuments($request);
        }
    }

    public function showDocuments(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $record = $request->get('record');
        if (substr($record, 0, 1) == "t") {
            $record = substr($record, 1);
        }

        $viewer->assign('VIEW', $request->get('view'));
        $Documents_Header = array("title" => 'Title', "folder" => 'Folder Name', "assigned_to" => 'Assigned To', "name" => 'File Name');
        $viewer->assign('DOCUMENTS_HEADERS', $Documents_Header);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign("TEMPLATEID", $record);
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $Documents_Records = $EMAILMaker->getEmailTemplateDocuments($record);
        $Template_Permissions_Data = $EMAILMaker->returnTemplatePermissionsData("", $record);
        if ($Template_Permissions_Data["edit"]) {
            $viewer->assign("IS_DELETABLE", "yes");
            $viewer->assign("EDIT", "permitted");
        }
        if (count($Documents_Records) > 0) {
            $viewer->assign('DOCUMENTS_RECORDS', $Documents_Records);
        }

        echo $viewer->view('DetailViewDocuments.tpl', 'EMAILMaker', 'true');
    }

    public function showEmailCampaigns(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $EMAILMakerListMEView = new EMAILMaker_ListME_View();
        $EMAILMakerListMEView->initializeListViewContents($request, $viewer, $EMAILMaker);
        $viewer->view("DetailViewListME.tpl", 'EMAILMaker');
    }

    public function showEmailWorkflows(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $EMAILMakerListWFView = new EMAILMaker_ListWF_View();
        $EMAILMakerListWFView->initializeListViewContents($request, $viewer, $EMAILMaker);
        $viewer->view("DetailViewListWF.tpl", 'EMAILMaker');
    }

    public function postProcess(Vtiger_Request $request)
    {
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $selectedTabLabel = $request->get('tab_label');
        if (empty($selectedTabLabel)) {
            $selectedTabLabel = vtranslate('LBL_PROPERTIES', 'EMAILMaker');
        }
        $viewer = $this->getViewer($request);
        $viewer->assign('SELECTED_TAB_LABEL', $selectedTabLabel);
        if ($request->has('record') && !$request->isEmpty('record')) {
            $record = $request->get('record');
            $detailViewLinks = $EMAILMaker->getDetailViewLinks($record);
            $viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
        }
        $mode = $request->getMode();
        $viewer->assign('MODE', $mode);
        $viewer->view('DetailViewPostProcess.tpl', 'EMAILMaker');
        parent::postProcess($request);
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $layout = Vtiger_Viewer::getLayoutName();
        $jsFileNames = array(
            "layouts.$layout.modules.Vtiger.resources.List",
            "layouts.$layout.modules.Vtiger.resources.Detail",
            "layouts.$layout.modules.EMAILMaker.resources.Detail",
            "layouts.$layout.modules.Vtiger.resources.RelatedList"
        );
        if (vtlib_isModuleActive("ITS4YouStyles")) {
            $jsFileNames[] = "modules.ITS4YouStyles.resources.CodeMirror.lib.codemirror";
            $jsFileNames[] = "modules.ITS4YouStyles.resources.CodeMirror.mode.javascript.javascript";
            $jsFileNames[] = "modules.ITS4YouStyles.resources.CodeMirror.addon.selection.active-line";
            $jsFileNames[] = "modules.ITS4YouStyles.resources.CodeMirror.addon.edit.matchbrackets";
            $jsFileNames[] = "modules.ITS4YouStyles.resources.CodeMirror.addon.runmode.runmode";
        }

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }

    public function getHeaderCss(Vtiger_Request $request)
    {
        $headerCssInstances = parent::getHeaderCss($request);
        if (vtlib_isModuleActive("ITS4YouStyles")) {
            $cssFileNames = array(
                '~/modules/ITS4YouStyles/resources/CodeMirror/lib/codemirror.css',
            );
            $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
            $headerCssInstances = array_merge($headerCssInstances, $cssInstances);
        }
        return $headerCssInstances;
    }
}