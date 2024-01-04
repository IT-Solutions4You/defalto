<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_Edit_View extends Vtiger_Index_View
{
    public $cu_language = "";
    private $ModuleFields = array();
    private $All_Related_Modules = array();

    public function __construct()
    {
        parent::__construct();

        $this->exposeMethod('selectTheme');
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $this->getProcess($request);
    }

    /**
     * @throws Exception
     */
    public function getProcess(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if (!empty($mode) && 'EditTheme' !== $mode) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }

        $this->showModuleEditView($request);
    }

    /**
     * @throws Exception
     */
    public function showModuleEditView(Vtiger_Request $request)
    {
        global $image_path, $current_language, $site_URL;

        $adb = PearDatabase::getInstance();
        $qualifiedModuleName = $request->getModule(false);
        $moduleName = $request->getModule();

        EMAILMaker_Debugger_Model::GetInstance()->Init();

        $mode = $request->get('mode');
        $theme_mode = ('EditTheme' === $mode);

        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $viewer = $this->getViewer($request);
        $current_user = Users_Record_Model::getCurrentUserModel();

        $viewer->assign('RECIPIENTMODULENAMES', $EMAILMaker->getRecipientModulenames());

        if ($request->has('record') && !$request->isEmpty('record')) {
            $templateId = $request->get('record');
            $emailTemplateResult = $EMAILMaker->GetEditViewData($templateId);
            $select_module = $emailTemplateResult['module'];
            $email_category = $emailTemplateResult['category'];
            $is_listview = $emailTemplateResult['is_listview'];
            $is_active = $emailTemplateResult['is_active'];
            $is_default = $emailTemplateResult['is_default'];
            $order = $emailTemplateResult['order'];
            $owner = $emailTemplateResult['owner'];
            $sharingType = $emailTemplateResult['sharingtype'];
            $sharingMemberArray = $EMAILMaker->GetSharingMemberArray($templateId, true);

            if (vtlib_isModuleActive('ITS4YouStyles')) {
                $StylesModuleModel = new ITS4YouStyles_Module_Model();
                $Style_Files = $StylesModuleModel->getStyleFiles($templateId, $moduleName);
                $viewer->assign('ITS4YOUSTYLE_FILES', $Style_Files);

                $Style_Content = $StylesModuleModel->getStyleContent($templateId, $moduleName);
                $viewer->assign('STYLES_CONTENT', $Style_Content);
            }
        } elseif ($request->has('themeid') && !$request->isEmpty('themeid') && $theme_mode) {
            $templateId = $request->get('themeid');
            $emailTemplateResult = $EMAILMaker->GetEditViewData($templateId);
            $select_module = $emailTemplateResult['module'];
            $email_category = $emailTemplateResult['category'];
            $is_listview = $emailTemplateResult['is_listview'];
            $is_active = $emailTemplateResult['is_active'];
            $is_default = $emailTemplateResult['is_default'];
            $order = $emailTemplateResult['order'];
            $owner = $emailTemplateResult['owner'];
            $sharingType = $emailTemplateResult['sharingtype'];
            $sharingMemberArray = $EMAILMaker->GetSharingMemberArray($templateId);
        } else {
            $emailTemplateResult = array();
            $emailTemplateResult['permissions'] = $EMAILMaker->returnTemplatePermissionsData();

            $templateId = $select_module = $email_category = '';
            $is_listview = $is_default = '0';
            $is_active = $order = '1';
            $owner = intval($current_user->getId());
            $sharingMemberArray = array();

            if (getTabId(EMAILMaker_EMAILMaker_Model::MULTI_COMPANY) && vtlib_isModuleActive(EMAILMaker_EMAILMaker_Model::MULTI_COMPANY)) {
                $companyRecord = ITS4YouMultiCompany_Record_Model::getCompanyByUserId($owner);

                if ($companyRecord) {
                    $sharingType = 'share';
                    $companyId = $companyRecord->getId();
                    $sharingMemberArray['Companies'] = array('Companies:' . $companyId => $companyId);
                } else {
                    $sharingType = 'private';
                }
            } else {
                $sharingType = 'public';
            }

            if ($request->has('theme') && !$request->isEmpty('theme')) {
                $theme = $request->get('theme');

                if ('new' !== $theme) {
                    $theme_path = getcwd() . '/modules/EMAILMaker/templates/' . $theme . '/index.html';
                    $theme_content = file_get_contents($theme_path);

                    if (file_exists($theme_path)) {
                        $emailTemplateResult['body'] = str_replace('[site_URL]', $site_URL, $theme_content);
                    }
                }
            }

            if ($request->has('themeid') && !$request->isEmpty('themeid')) {
                $themeId = $request->get('themeid');
                $emailThemeResult = $EMAILMaker->GetEditViewData($themeId);
                $emailTemplateResult['body'] = $emailThemeResult['body'];
            }
        }

        $viewer->assign('EMAIL_TEMPLATE_RESULT', $emailTemplateResult);
        $viewer->assign('THEME_MODE', $theme_mode);

        if (!$emailTemplateResult['permissions']['edit']) {
            $EMAILMaker->DieDuePermission();
        }

        if ($request->has('isDuplicate') && 'true' === $request->get('isDuplicate')) {
            $viewer->assign('TEMPLATENAME', '');
            $viewer->assign('DUPLICATE_TEMPLATENAME', $emailTemplateResult['templatename']);
        } else {
            $viewer->assign('TEMPLATENAME', $emailTemplateResult['templatename']);
        }

        if (!$request->has('isDuplicate') or ($request->has('isDuplicate') && 'true' !== $request->get('isDuplicate'))) {
            $viewer->assign('SAVETEMPLATEID', $templateId);
        }

        if (!empty($templateId)) {
            $viewer->assign('EMODE', 'edit');
        }

        $viewer->assign('TEMPLATEID', $templateId);

        if (!empty($select_module)) {
            $viewer->assign('MODULENAME', vtranslate($select_module, $select_module));
            $viewer->assign('SELECTMODULE', $select_module);
        }
        $this->cu_language = $current_user->get('language');

        $viewer->assign('THEME', $theme);
        $viewer->assign('IMAGE_PATH', $image_path);
        $app_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($this->cu_language);
        $app_strings = $app_strings_big['languageStrings'];
        $viewer->assign('APP', $app_strings);
        $viewer->assign('PARENTTAB', getParentTab());

        $modules = $EMAILMaker->GetAllModules();

        $viewer->assign('MODULENAMES', $modules[0]);
        $viewer->assign('MODULEIDS', $modules[1]);
        $viewer->assign('CUI_BLOCKS', EMAILMaker_Fields_Model::getUserTypeOptions());

        $companyImages =  EMAILMaker_Record_Model::getCompanyImages();
        $viewer->assign('COMPANYLOGO', $companyImages['logoname_img']);
        $viewer->assign('COMPANY_STAMP_SIGNATURE', $companyImages['stamp_signature_img']);
        $viewer->assign('COMPANY_HEADER_SIGNATURE', $companyImages['header_img']);

        $viewer->assign('ACCOUNTINFORMATIONS', EMAILMaker_Fields_Model::getCompanyOptions());

        if (getTabId(EMAILMaker_EMAILMaker_Model::MULTI_COMPANY) && vtlib_isModuleActive(EMAILMaker_EMAILMaker_Model::MULTI_COMPANY)) {
            $viewer->assign('MULTICOMPANYINFORMATIONS', EMAILMaker_Fields_Model::getMultiCompanyOptions());
            $viewer->assign('LBL_MULTICOMPANY', vtranslate('MultiCompany', EMAILMaker_EMAILMaker_Model::MULTI_COMPANY));
        }

        $viewer->assign('USERINFORMATIONS', EMAILMaker_Fields_Model::getUserOptions());
        $viewer->assign('INVENTORYTERMSANDCONDITIONS', EMAILMaker_Fields_Model::getInventoryTermsAndConditionsOptions());
        $viewer->assign('CUSTOM_FUNCTIONS', $this->getCustomFunctionsList());

        $global_lang_labels = @array_flip($app_strings);
        $global_lang_labels = @array_flip($global_lang_labels);
        asort($global_lang_labels);

        $viewer->assign('GLOBAL_LANG_LABELS', $global_lang_labels);

        $module_lang_labels = array();

        if (!empty($select_module)) {
            $mod_lang = EMAILMaker_EMAILMaker_Model::getModuleLanguageArray($select_module);
            $module_lang_labels = @array_flip($mod_lang);
            $module_lang_labels = @array_flip($module_lang_labels);
            asort($module_lang_labels);
        } else {
            $module_lang_labels[''] = vtranslate('LBL_SELECT_MODULE_FIELD', 'EMAILMaker');
        }

        $viewer->assign('MODULE_LANG_LABELS', $module_lang_labels);

        list($custom_labels, $languages) = $EMAILMaker->GetCustomLabels();

        $currLangId = '';

        foreach ($languages as $langId => $langVal) {
            if ($langVal['prefix'] == $current_language) {
                $currLangId = $langId;
                break;
            }
        }

        $vcustom_labels = array();

        if (count($custom_labels) > 0) {
            foreach ($custom_labels as $oLbl) {
                $currLangVal = $oLbl->GetLangValue($currLangId);

                if (empty($currLangVal)) {
                    $currLangVal = $oLbl->GetFirstNonEmptyValue();
                }

                $vcustom_labels[$oLbl->GetKey()] = $currLangVal;
            }

            asort($vcustom_labels);
        } else {
            $vcustom_labels = vtranslate('LBL_SELECT_MODULE_FIELD', 'EMAILMaker');
        }

        $viewer->assign('CUSTOM_LANG_LABELS', $vcustom_labels);
        $viewer->assign('DATE_VARS', EMAILMaker_Fields_Model::getDateOptions());
        $viewer->assign('EMAIL_CATEGORY', $email_category);
        $viewer->assign('SELECTED_DEFAULT_FROM', EMAILMaker_Record_Model::getDefaultFromEmail($templateId));
        $viewer->assign('DEFAULT_FROM_OPTIONS', EMAILMaker_Fields_Model::getDefaultFromOptions());
        $viewer->assign('STATUS', EMAILMaker_Fields_Model::getStatusOptions());
        $viewer->assign('IS_ACTIVE', $is_active);

        if ('0' == $is_active) {
            $viewer->assign('IS_DEFAULT_DV_CHECKED', 'disabled="disabled"');
            $viewer->assign('IS_DEFAULT_LV_CHECKED', 'disabled="disabled"');
        } elseif ($is_default > 0) {
            $is_default_bin = str_pad(base_convert($is_default, 10, 2), 2, "0", STR_PAD_LEFT);
            $is_default_lv = substr($is_default_bin, 0, 1);
            $is_default_dv = substr($is_default_bin, 1, 1);

            if ('1' == $is_default_lv) {
                $viewer->assign('IS_DEFAULT_LV_CHECKED', 'checked="checked"');
            }

            if ('1' == $is_default_dv) {
                $viewer->assign('IS_DEFAULT_DV_CHECKED', 'checked="checked"');
            }
        }

        $viewer->assign('ORDER', $order);

        if ('1' == $is_listview) {
            $viewer->assign('IS_LISTVIEW_CHECKED', 'yes');
        }

        $template_owners = get_user_array(false);
        $viewer->assign('TEMPLATE_OWNERS', $template_owners);
        $viewer->assign('TEMPLATE_OWNER', $owner);
        $viewer->assign('SHARINGTYPES', EMAILMaker_Fields_Model::getSharingTypeOptions());
        $viewer->assign('SHARINGTYPE', $sharingType);
        $viewer->assign('CMOD', EMAILMaker_EMAILMaker_Model::getModuleLanguageArray('Settings'));

        //Constructing the Role Array
        $viewer->assign('SELECTED_MEMBERS_GROUP', $sharingMemberArray);
        $viewer->assign('MEMBER_GROUPS', EMAILMaker_Fields_Model::getMemberGroups());
        $viewer->assign('DECIMALS', EMAILMaker_Record_Model::getDecimalSettings());
        $viewer->assign('IGNORE_PICKLIST_VALUES', EMAILMaker_Record_Model::getIgnorePicklistValues());

        foreach (array('VAT', 'CHARGES') as $blockType) {
            $viewer->assign($blockType . 'BLOCK_TABLE', EMAILMaker_Fields_Model::getBlockTable($blockType, $app_strings));
        }

        $viewer->assign('LISTVIEW_BLOCK_TPL', EMAILMaker_Fields_Model::getListViewBlockOptions());
        $viewer->assign('PRODUCT_BLOC_TPL', EMAILMaker_Fields_Model::getProductBlockTemplates());

        $ProductBlockFields = $EMAILMaker->GetProductBlockFields();

        foreach ($ProductBlockFields as $viewer_key => $pbFields) {
            $viewer->assign($viewer_key, $pbFields);
        }

        $viewer->assign('RELATED_BLOCKS', $EMAILMaker->GetRelatedBlocks($select_module));
        $viewer->assign('SUBJECT_FIELDS', $EMAILMaker->getSubjectFields());

        if (!empty($select_module)) {
            $EMAILMakerFieldsModel = new EMAILMaker_Fields_Model();
            $SelectModuleFields = $EMAILMakerFieldsModel->getSelectModuleFields($select_module);

            $viewer->assign('RELATED_MODULES', $EMAILMakerFieldsModel->getRelatedModules($select_module));
            $viewer->assign('SELECT_MODULE_FIELD', $SelectModuleFields);

            if (in_array($select_module, ['Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder', 'Issuecards', 'Receiptcards', 'Creditnote', 'StornoInvoice'])) {
                unset($SelectModuleFields['Details']);
            }

            $viewer->assign('SELECT_MODULE_FIELD_SUBJECT', $SelectModuleFields);
        }

        $viewer->assign('VERSION', EMAILMaker_Version_Helper::$version);
        $viewer->assign('CATEGORY', getParentTab());

        if (!empty($select_module)) {
            $selectedModuleName = $select_module;
            $selectedModuleModel = Vtiger_Module_Model::getInstance($selectedModuleName);
            $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($selectedModuleModel);

            $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);

            $recordStructure = $recordStructureInstance->getStructure();

            if (in_array($selectedModuleName, getInventoryModules())) {
                $itemsBlock = 'LBL_ITEM_DETAILS';
                unset($recordStructure[$itemsBlock]);
            }

            $viewer->assign('RECORD_STRUCTURE', $recordStructure);

            $dateFilters = Vtiger_Field_Model::getDateFilterTypes();

            foreach ($dateFilters as $comparatorKey => $comparatorInfo) {
                $comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
                $comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
                $comparatorInfo['label'] = vtranslate($comparatorInfo['label'], $qualifiedModuleName);
                $dateFilters[$comparatorKey] = $comparatorInfo;
            }

            $viewer->assign('DATE_FILTERS', $dateFilters);

            $viewer->assign('ADVANCED_FILTER_OPTIONS', EMAILMaker_Field_Model::getAdvancedFilterOptions());
            $viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', EMAILMaker_Field_Model::getAdvancedFilterOpsByFieldType());

            $viewer->assign('ADVANCE_CRITERIA', Zend_Json::decode(decode_html($emailTemplateResult["conditions"])));
            $viewer->assign('SELECTED_MODULE_NAME', $selectedModuleName);
            $viewer->assign('SOURCE_MODULE', $selectedModuleName);
        }

        $viewer->assign('GENERAL_FIELDS', EMAILMaker_Fields_Model::getGeneralFieldsOptions());

        $viewer->view('Edit.tpl', 'EMAILMaker');
    }

    /**
     * Function to get array with available custom functions
     * @return array - Array of Module languages
     */
    public function getCustomFunctionsList()
    {
        $ready = false;
        $function_name = "";
        $function_params = $functions = array();

        $files = glob('modules/EMAILMaker/resources/functions/*.php');
        foreach ($files as $file) {
            $filename = $file;
            $source = fread(fopen($filename, "r"), filesize($filename));
            $tokens = token_get_all($source);
            foreach ($tokens as $token) {
                if (is_array($token)) {
                    if ($token[0] == T_FUNCTION) {
                        $ready = true;
                    } elseif ($ready) {
                        if ($token[0] == T_STRING && $function_name == "") {
                            $function_name = $token[1];
                        } elseif ($token[0] == T_VARIABLE) {
                            $function_params[] = $token[1];
                        }
                    }
                } elseif ($ready && $token == "{") {
                    $ready = false;
                    $functions[$function_name] = $function_params;
                    $function_name = "";
                    $function_params = array();
                }
            }
        }

        $customFunctions[""] = vtranslate("LBL_PLS_SELECT", 'EMAILMaker');
        foreach ($functions as $funName => $params) {
            $parString = implode("|", $params);
            $custFun = trim($funName . "|" . str_replace("$", "", $parString), "|");
            $customFunctions[$custFun] = $funName;
        }

        return $customFunctions;
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        Vtiger_Basic_View::preProcess($request, false);

        $moduleName = $request->getModule();

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);

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

        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_VIEW', $request->get('view'));
        $viewer->assign('MODULE_BASIC_ACTIONS', []);

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            "modules.EMAILMaker.resources.ckeditor.ckeditor",
            "libraries.jquery.ckeditor.adapters.jquery",
            "libraries.jquery.jquery_windowmsg",
            "modules.$moduleName.resources.AdvanceFilter"
        );

        if (vtlib_isModuleActive("ITS4YouStyles")) {
            $jsFileNames[] = "modules.ITS4YouStyles.resources.CodeMirror.lib.codemirror";
            $jsFileNames[] = "modules.ITS4YouStyles.resources.CodeMirror.mode.javascript.javascript";
            $jsFileNames[] = "modules.ITS4YouStyles.resources.CodeMirror.addon.selection.active-line";
            $jsFileNames[] = "modules.ITS4YouStyles.resources.CodeMirror.addon.edit.matchbrackets";
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

    public function selectTheme(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();

        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        if ($EMAILMaker->CheckPermissions("EDIT") == false) {
            $EMAILMaker->DieDuePermission();
        }

        $viewer = $this->getViewer($request);

        $viewer->assign("VERSION", EMAILMaker_Version_Helper::$version);
        $source_path = getcwd() . "/modules/EMAILMaker/templates";

        $dir_iterator = new RecursiveDirectoryIterator($source_path);
        $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

        $i = 0;
        foreach ($iterator as $folder) {
            $folder_name = substr($folder, strlen($source_path) + 1);
            if ($folder->isDir()) {
                $other_folder = strpos($folder_name, "/");
                if ($other_folder === false && file_exists($folder . "/index.html") && file_exists($folder . "/image.png")) {
                    $EmailTemplates[] = $folder_name;
                }
            }
            $i++;
        }

        asort($EmailTemplates);
        $viewer->assign("EMAILTEMPLATESPATH", $source_path);
        $viewer->assign("EMAILTEMPLATES", $EmailTemplates);
        $Themes_Data = $EMAILMaker->GetThemesData();
        $viewer->assign("EMAILTHEMES", $Themes_Data);
        $viewer->assign("CATEGORY", getParentTab());
        $viewer->view('EditSelectContent.tpl', 'EMAILMaker');
    }
}