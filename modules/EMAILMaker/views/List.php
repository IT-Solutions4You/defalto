<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_List_View extends Vtiger_Index_View
{
    protected $listViewLinks = false;

    public function __construct()
    {
        parent::__construct();

        $this->exposeMethod('getList');
    }

    /**
     * @param Vtiger_Request $request
     * @param bool           $display
     *
     * @return void
     */
    public function preProcess(Vtiger_Request $request, $display = true)
    {
        vtws_addDefaultModuleTypeEntity($request->getModule());

        parent::preProcess($request, false);

        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        $viewer = $this->getViewer($request);

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

            $linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view')];
            $viewer->assign('QUICK_LINKS', $moduleModel->getSideBarLinks($linkParams));
        }

        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_VIEW', $request->get('view'));

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    public function preProcessTplName(Vtiger_Request $request)
    {
        return 'ListViewPreProcess.tpl';
    }

    public function postProcess(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $viewer->view('IndexPostProcess.tpl');
        parent::postProcess($request);
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $this->invokeExposedMethod('getList', $request);
    }

    /**
     * @throws Exception
     */
    public function getList(Vtiger_Request $request)
    {
        /**
         * @var EMAILMaker_Module_Model $moduleModel
         */
        global $mod_strings, $app_strings, $theme, $image_path;

        $adb = PearDatabase::getInstance();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        EMAILMaker_Debugger_Model::GetInstance()->Init();

        $moduleModel = Vtiger_Module_Model::getInstance('EMAILMaker');

        if (!$moduleModel->CheckPermissions('DETAIL')) {
            $moduleModel->DieDuePermission();
        }

        $orderby = 'templateid';
        $dir = $request->get('sortorder', 'asc');

        if (isset($_REQUEST['dir']) && 'desc' === $_REQUEST['dir']) {
            $dir = 'desc';
        }

        if (isset($_REQUEST['orderby'])) {
            switch ($_REQUEST['orderby']) {
                case 'name':
                    $orderby = 'templatename';
                    break;
                default:
                    $orderby = $_REQUEST['orderby'];
                    break;
            }
        }

        $viewer->assign('VERSION_TYPE', 'profesional');

        if ($moduleModel->CheckPermissions('EDIT')) {
            $viewer->assign('EXPORT', 'yes');
        }
        if ($moduleModel->CheckPermissions('EDIT')) {
            $viewer->assign('EDIT', 'permitted');
            $viewer->assign('IMPORT', 'yes');
        }
        if ($moduleModel->CheckPermissions('DELETE')) {
            $viewer->assign('DELETE', 'permitted');
        }

        $viewer->assign('MOD', $mod_strings);
        $viewer->assign('APP', $app_strings);
        $viewer->assign('THEME', $theme);
        $viewer->assign('PARENTTAB', getParentTab());
        $viewer->assign('IMAGE_PATH', $image_path);
        $viewer->assign('ORDERBY', $orderby);
        $viewer->assign('DIR', $dir);
        $viewer->assign('SEARCHSELECTBOXDATA', $moduleModel->getSearchSelectboxData());
        $viewer->assign('CATEGORY', getParentTab());

        $current_user = Users_Record_Model::getCurrentUserModel();

        $linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view')];

        $linkModels = $moduleModel->getListViewLinks($linkParams);

        $viewer->assign('LISTVIEW_MASSACTIONS', $linkModels['LISTVIEWMASSACTION']);
        $viewer->assign('LISTVIEW_LINKS', $linkModels);

        if (is_admin($current_user)) {
            $viewer->assign('IS_ADMIN', '1');
        }

        $WTemplateIds = [];
        $workflows_query = $moduleModel->geEmailWorkflowsQuery();
        $workflows_result = $adb->pquery($workflows_query, []);
        $workflows_num_rows = $adb->num_rows($workflows_result);

        if ($workflows_num_rows > 0) {
            require_once('modules/EMAILMaker/workflow/VTEMAILMakerMailTask.php');

            for ($i = 0; $i < $workflows_num_rows; $i++) {
                $data = $adb->raw_query_result_rowdata($workflows_result, $i);
                $task = $data["task"];
                $taskObject = unserialize($task);
                $wtemplateid = $taskObject->template;

                if (!in_array($wtemplateid, $WTemplateIds)) {
                    $WTemplateIds[] = $wtemplateid;
                }
            }
        }

        $viewer->assign('WTEMPLATESIDS', $WTemplateIds);

        $emailTemplates = $moduleModel->GetListviewData($orderby, $dir, "", false, $request);

        if (!$request->isEmpty('search_workflow')) {
            $search_workflow = $request->get('search_workflow');

            foreach ($emailTemplates as $templateKey => $templateData) {
                if ('wf_0' === $search_workflow) {
                    if (in_array($templateData['templateid'], $WTemplateIds)) {
                        echo sprintf(' unset %s<br>', $templateData['templateid']);
                        unset($emailTemplates[$templateKey]);
                    }
                } else {
                    if (!in_array($templateData['templateid'], $WTemplateIds)) {
                        unset($emailTemplates[$templateKey]);
                    }
                }
            }
        }

        $viewer->assign('EMAILTEMPLATES', $emailTemplates);
        $viewer->assign('SHARINGTYPES', EMAILMaker_Field_Model::getSharingTypes());
        $viewer->assign('STATUSOPTIONS', EMAILMaker_Field_Model::getStatusOptions());
        $viewer->assign('WFOPTIONS', EMAILMaker_Field_Model::getWorkflowOptions());

        $searchTypes = EMAILMaker_Field_Model::getSearchTypes();
        $searchDetails = [];

        if (!$request->isEmpty('search_params')) {
            $searchParams = $request->get('search_params');

            foreach ($searchParams as $groupInfo) {
                if (empty($groupInfo)) {
                    continue;
                }
                foreach ($groupInfo as $fieldSearchInfo) {
                    [$fieldName, $operator, $searchValue] = $fieldSearchInfo;

                    $viewer->assign('SEARCH_' . strtoupper($fieldName) . 'VAL', $searchValue);

                    $searchDetails[$fieldName] = $fieldSearchInfo;
                }
            }
        }

        $viewer->assign('MAIN_PRODUCT_SUPPORT', '');
        $viewer->assign('MAIN_PRODUCT_WHITELABEL', '');
        $viewer->assign('MODULE', 'EMAILMaker');
        $viewer->assign('SEARCH_DETAILS', $searchDetails);

        $viewer->view('ListEMAILTemplatesContents.tpl', 'EMAILMaker');
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = [
            'modules.Vtiger.resources.List',
            "modules.$moduleName.resources.List",
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}