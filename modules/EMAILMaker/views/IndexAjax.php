<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_IndexAjax_View extends Vtiger_IndexAjax_View
{

    public function __construct()
    {
        parent::__construct();
        $Methods = array('showSettingsList', 'editCustomLabel', 'showCustomLabelValues', 'editLicense', 'showComposeEmailForm', 'report', 'getModuleConditions', 'showMESummary');
        foreach ($Methods as $method) {
            $this->exposeMethod($method);
        }
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        return true;
    }

    public function postProcess(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }

        $type = $request->get('type');
    }

    public function report(Vtiger_Request $request)
    {
    }

    public function showSettingsList(Vtiger_Request $request)
    {
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->assign('MODULE', $moduleName);
        $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'), 'MODE' => $request->get('mode'));
        $linkModels = $EMAILMaker->getSideBarLinks($linkParams);
        $viewer->assign('QUICK_LINKS', $linkModels);
        $parent_view = $request->get('pview');
        if ($parent_view == "EditProductBlock") {
            $parent_view = "ProductBlocks";
        }
        $viewer->assign('CURRENT_PVIEW', $parent_view);
        echo $viewer->view('SettingsList.tpl', 'EMAILMaker', true);
    }

    public function editCustomLabel(Vtiger_Request $request)
    {
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $viewer = $this->getViewer($request);
        $slabelid = $request->get('labelid');
        $slangid = $request->get('langid');
        $currentLanguage = Vtiger_Language_Handler::getLanguage();
        $moduleName = $request->getModule();
        $viewer->assign('MODULE', $moduleName);

        list($oLabels, $languages) = $EMAILMaker->GetCustomLabels();
        $currLang = array();
        foreach ($languages as $langId => $langVal) {
            if (($langId == $slangid && $slangid != "") || ($slangid == "" && $langVal["prefix"] == $currentLanguage)) {
                $currLang["id"] = $langId;
                $currLang["name"] = $langVal["name"];
                $currLang["label"] = $langVal["label"];
                $currLang["prefix"] = $langVal["prefix"];
                break;
            }
        }
        if ($slangid == "") {
            $slangid = $currLang["id"];
        }
        $viewer->assign('LABELID', $slabelid);
        $viewer->assign('LANGID', $slangid);

        $viewLabels = array();

        foreach ($oLabels as $lblId => $oLabel) {
            if ($slabelid == $lblId) {
                $l_key = substr($oLabel->GetKey(), 2);
                $l_values = $oLabel->GetLangValsArr();

                $viewer->assign("CUSTOM_LABEL_KEY", $l_key);
                $viewer->assign("CUSTOM_LABEL_VALUE", $l_values[$currLang["id"]]);
                break;
            }
        }

        $viewer->assign("CURR_LANG", $currLang);
        $viewer->view('ModalEditCustomLabelContent.tpl', 'EMAILMaker');
    }

    public function showCustomLabelValues(Vtiger_Request $request)
    {
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();

        list($labelObjects, $languages) = $EMAILMaker->GetCustomLabels();

        $labelId = $request->get('labelid');
        $currLangId = $request->get('langid');
        $labelObject = $labelObjects[$labelId];
        $key = $labelObject->GetKey();

        $viewer = $this->getViewer($request);
        $viewer->assign('LBLKEY', $key);
        $viewer->assign('LABELID', $labelId);
        $viewer->assign('LANGID', $currLangId);

        $languageValues = $labelObject->GetLangValsArr();
        $newLanguageValues = array();

        foreach ($languageValues as $langId => $langVal) {
            if ($langId == $currLangId) {
                continue;
            }

            $label = $languages[$langId]['label'];
            $newLanguageValues[] = array(
                'id' => $langId,
                'value' => $langVal,
                'label' => $label
            );
        }

        $viewer->assign('LANGVALSARR', $newLanguageValues);
        $viewer->assign('MODULE', 'EMAILMaker');
        $viewer->view('ModalCustomLabelValuesContent.tpl', 'EMAILMaker');
    }

    public function editLicense(Vtiger_Request $request)
    {
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $type = $request->get('type');
        $viewer->assign("TYPE", $type);
        $key = $request->get('key');
        $viewer->assign("LICENSEKEY", $key);
        $viewer->assign("MODULE", $moduleName);

        echo $viewer->view('EditLicense.tpl', 'EMAILMaker', true);
    }

    /**
     * @throws Exception
     */
    public function showComposeEmailForm(Vtiger_Request $request)
    {
        $moduleName = 'EMAILMaker';
        $cvId = $request->get('viewname');
        $selectedIds = $request->get('selected_ids');
        $excludedIds = $request->get('excluded_ids');
        $step = $request->get('step');
        $selectedFields = $request->get('selectedFields');
        $relatedLoad = $request->get('relatedLoad');
        $selecttemplates = $request->get('selecttemplates');
        $parentModule = $sourceModule = $request->get('sourceModule');
        $parentRecord = $request->get('sourceRecord');
        $basic = $request->get('basic');
        $single_record = false;
        $forview = $request->get('forview');

        $adb = PearDatabase::getInstance();
        $current_user = Users_Record_Model::getCurrentUserModel();
        $currentLanguage = Vtiger_Language_Handler::getLanguage();
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $recordIds = (array)$this->getRecordsListFromRequest($request);
        $viewer = $this->getViewer($request);
        if (count($recordIds) == 1) {
            $single_record = true;
        }

        $viewer->assign('SEARCH_PARAMS', $request->get('search_params'));

        $cid = "";
        if ($request->has('cid') && !$request->isEmpty('cid')) {
            $parentRecord = $cid = $request->get('cid');
            $parentModule = "Campaigns";

            $viewer->assign('FOR_CAMPAIGN', $cid);
            if ($recordIds == 'all') {
                $single_record = false;
            }

        }

        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $emailTypes = $EMAILMaker->getRecordsEmails($sourceModule, $recordIds, $basic);
        $emailFieldsList = array();
        $i = 0;
        $totalEmailFieldListCount = 0;
        $totalEmailOptOut = 0;

        foreach ($emailTypes as $emailType => $emailFields) {
            foreach ($emailFields as $emailField) {
                $emailFieldListCount = 0;

                foreach ($emailField['emails'] as $emailFieldModel) {
                    if ($emailFieldModel->isViewEnabled()) {
                        $email_name = $emailFieldModel->get('name');
                        $email_label = $emailFieldModel->get('label');
                        $email_value = $emailField['data'][$email_name];
                        $emailOptOut = isset($emailField['data']['emailoptout']) ? intval($emailField['data']['emailoptout']) : 0;

                        if (!empty($email_value) || !$single_record) {
                            if (!isset($emailFieldsList[$emailField['label']][$email_name])) {
                                $emailFieldListCount++;
                                $totalEmailFieldListCount++;

                                if ($emailOptOut && $single_record) {
                                    $totalEmailOptOut++;
                                }

                                $entityName = getEntityName($emailField['module'], $emailField['crmid']);
                                $crmName = $entityName[$emailField['crmid']];
                                $emailDetails = array(
                                    'crmid' => $emailField['crmid'],
                                    'crmname' => $emailField['name'],
                                    'name' => $email_name,
                                    'module' => $emailField['module'],
                                    'fieldlabel' => $emailField['label'],
                                    'label' => vtranslate($email_label, $emailField['module']),
                                    'value' => $email_value,
                                    'fieldname' => $email_name,
                                    'emailoptout' => $emailOptOut,
                                );

                                $label = $emailField['label'];

                                if (empty($label)) {
                                    $emailFieldModule = $emailField['module'];
                                    $singleModuleLabel = 'SINGLE_' . $emailFieldModule;
                                    $label = vtranslate($singleModuleLabel, $emailFieldModule);

                                    if ($label === $singleModuleLabel) {
                                        $label = vtranslate($emailFieldModule, $emailFieldModule);
                                    }
                                }

                                if (!empty($crmName)) {
                                    $label .= ': ' . $crmName;
                                }

                                $emailFieldsList[$label][$email_name] = $emailDetails;
                            }
                        }
                    }
                }
                $i++;
            }
        }
        $viewer->assign('TOTAL_EMAILOPTOUT', $totalEmailOptOut);
        $viewer->assign('EMAIL_FIELDS_LIST', $emailFieldsList);
        $viewer->assign('EMAIL_FIELDS_COUNT', $totalEmailFieldListCount);

        if ($single_record && !$totalEmailFieldListCount) {
            $recordid = $recordIds[0];
            $source_name = getEntityName($sourceModule, $recordid);
            $viewer->assign('SOURCE_NAME', $source_name[$recordid]);
        }

        $viewer->assign('BASIC', $basic);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('VIEWNAME', $cvId);
        $viewer->assign('SELECTED_IDS', $selectedIds);
        $viewer->assign('EXCLUDED_IDS', $excludedIds);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
        $operator = $request->get('operator');
        if (!empty($operator)) {
            $viewer->assign('OPERATOR', $operator);
            $viewer->assign('ALPHABET_VALUE', $searchValue);
            $viewer->assign('SEARCH_KEY', $searchKey);
        }


        if (!empty($parentModule)) {
            $viewer->assign('PARENT_MODULE', $parentModule);
            $viewer->assign('PARENT_RECORD', $parentRecord);
            $viewer->assign('RELATED_MODULE', $sourceModule);
        }
        if ($relatedLoad) {
            $viewer->assign('RELATED_LOAD', true);
        }

        if ($single_record) {
            $viewer->assign('SINGLE_RECORD', 'yes');
        }
        if ($selecttemplates == "true") {
            $forListView = true;
            $pid = false;

            if ($request->has('selected_ids') && !$request->isEmpty('selected_ids') && 'Detail' === $forview && $single_record) {
                $selected_ids = $request->get('selected_ids');

                if (is_numeric($selected_ids)) {
                    $pid = $selected_ids;
                    $forListView = false;
                }

                if (is_array($selected_ids) && 1 === count($selected_ids)) {
                    $pid = $selected_ids[0];
                    $forListView = false;
                }
            }

            $templates = $EMAILMaker->GetAvailableTemplatesArray($sourceModule, $forListView, $pid, false, true);

            if ($cid != "") {

                $campaign_templates = $EMAILMaker->GetAvailableTemplatesArray("Campaigns", true);

                if (count((array)$campaign_templates[0]) > 0) {
                    if (count((array)$templates[0]) > 0) {
                        $templates[0] = array_merge($templates[0], $campaign_templates[0]);
                    } else {
                        $templates[0] = $campaign_templates[0];
                    }
                }
                if (count((array)$campaign_templates[1]) > 0) {

                    if (count((array)$templates[1]) > 0) {
                        $templates[1] = array_merge($templates[1], $campaign_templates[1]);
                    } else {
                        $templates[1] = $campaign_templates[1];
                    }
                }
            }

            if (count($templates) > 0) {
                $no_templates_exist = 0;
            } else {
                $no_templates_exist = 1;
            }

            $viewer->assign('CRM_TEMPLATES', $templates);
            $viewer->assign('CRM_TEMPLATES_EXIST', $no_templates_exist);


            if (!isset($_SESSION["template_languages"]) || $_SESSION["template_languages"] == "") {
                $temp_res = $adb->pquery("SELECT label, prefix FROM vtiger_language WHERE active = ?", array('1'));
                while ($temp_row = $adb->fetchByAssoc($temp_res)) {
                    $template_languages[$temp_row["prefix"]] = $temp_row["label"];
                }
                $_SESSION["template_languages"] = $template_languages;
            }
            $viewer->assign('TEMPLATE_LANGUAGES', $_SESSION["template_languages"]);

            $def_templateid = $EMAILMaker->GetDefaultTemplateId($sourceModule, ($forview == "List" ? true : false));
            $viewer->assign('DEFAULT_TEMPLATE', $def_templateid);
        }
        $no_pdftemplates = true;
        if (EMAILMaker_Module_Model::isPDFMakerInstalled()) {
            $pdftemplateid = $request->get('pdftemplateid');

            if ($request->has('pdflanguage') && !$request->isEmpty('pdflanguage')) {
                $currentLanguage = $request->get('pdflanguage');
            }

            if ($pdftemplateid != "") {
                $viewer->assign('PDFTEMPLATEID', $pdftemplateid);

                if ($pdftemplateid) {
                    $PDFTemplateIds = explode(";", $pdftemplateid);
                }
                $viewer->assign('PDFTEMPLATEIDS', $PDFTemplateIds);
            }
            if (class_exists('PDFMaker_PDFMaker_Model')) {
                $PDFMakerModel = Vtiger_Module_Model::getInstance('PDFMaker');
                $pdf_templates = $PDFMakerModel->GetAvailableTemplates($sourceModule);
                $viewer->assign('PDF_TEMPLATES', $pdf_templates);
                if (count($pdf_templates) > 0) {
                    $viewer->assign('IS_PDFMAKER', 'yes');
                    $no_pdftemplates = false;
                }
            }
        }

        if ((!$no_templates_exist || !$no_pdftemplates) && $selecttemplates == "true") {
            $for_list_view = "yes";
        } else {
            $for_list_view = "no";
        }

        $viewer->assign('FORLISTVIEW', $for_list_view);
        $viewer->assign('CURRENT_LANGUAGE', $currentLanguage);

        if ($step == 'step1') {
            $viewer->view('SelectEmailFields.tpl', $moduleName);
        }
    }

    public function getModuleConditions(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request);

        $selectedModuleName = $request->get("source_module");
        $selectedModuleModel = Vtiger_Module_Model::getInstance($selectedModuleName);
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($selectedModuleModel);

        $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);

        $recordStructure = $recordStructureInstance->getStructure();
        if (in_array($selectedModuleName, InventoryItem_Utils_Helper::getInventoryItemModules())) {
            $itemsBlock = "LBL_ITEM_DETAILS";
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
        $viewer->assign('SELECTED_MODULE_NAME', $selectedModuleName);
        $viewer->assign('SOURCE_MODULE', $selectedModuleName);
        $viewer->assign('QUALIFIED_MODULE', 'EMAILMaker');
        $viewer->view('AdvanceFilter.tpl', 'EMAILMaker');
    }

    public function showMESummary(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request);

        $Data = $request->get('data');

        $currentUser = Users_Record_Model::getCurrentUserModel();

        $RecordME_Model = EMAILMaker_RecordME_Model::getCleanInstance();
        $RecordME_Model->setFromRequestData($Data);
        $RecordME_Model->setTemplateData();

        $start_of = $RecordME_Model->get("start_of");

        if ($start_of != "") {


            $hour_format = $currentUser->get('hour_format');

            if ($hour_format == "12") {
                $time_format = 'h:i a';
            } else {
                $time_format = 'H:i';
            }

            $convert_date_start = DateTimeField::convertToUserTimeZone($start_of);
            $start_of_date = $convert_date_start->format('Y-m-d');
            $start_of_time = $convert_date_start->format('H:00');
            $formated_time = $convert_date_start->format($time_format);
        } else {
            $start_of_date = date('Y-m-d', strtotime("+1 day"));
            $start_of_time = "00:00";
            $formated_time = "";
        }
        $user_start_of_date = DateTimeField::convertToUserFormat($start_of_date);
        $start_of = trim($user_start_of_date . " " . $formated_time);
        $RecordME_Model->set("start_of", $start_of);

        //$module_columns = EMAILMaker_RecordME_Model::getModuleColumns(, );
        $for_module = $RecordME_Model->get("module_name");
        $listid = $RecordME_Model->get("listid");

        $moduleModel = Vtiger_Module_Model::getInstance($for_module);
        $emailFieldModels = $moduleModel->getFieldsByType('email');
        $emailFieldModel = $emailFieldModels[$Data["selected_email_fieldname"]];

        $email_fieldname_label = vtranslate($emailFieldModel->get('label'), $for_module);
        $RecordME_Model->set("email_fieldname_label", $email_fieldname_label);

        $viewer->assign('MASSEMAILRECORDMODEL', $RecordME_Model);


        $recipients_count = $RecordME_Model->get("total_entries");

        if ($recipients_count == "") {
            $listViewModel = Vtiger_ListView_Model::getInstance($for_module, $listid);
            $recipients_count = $listViewModel->getListViewCount();
        }

        $viewer->assign('RECIPIENTS_COUNT', $recipients_count);

        $viewer->view('SummaryMEView.tpl', 'EMAILMaker');
    }
}