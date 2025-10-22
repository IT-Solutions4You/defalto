<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_EditFree_View extends Vtiger_Index_View
{
    public $cu_language = '';

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        parent::preProcess($request, false);
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->assign('QUALIFIED_MODULE', $moduleName);

        $moduleName = $request->getModule();

        $moduleModel = new PDFMaker_PDFMaker_Model('PDFMaker');
        $viewer->assign('MODULE', $moduleName);

        $linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view')];
        $linkModels = $moduleModel->getSideBarLinks($linkParams);

        $viewer->assign('QUICK_LINKS', $linkModels);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_VIEW', $request->get('view'));

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    function preProcessTplName(Vtiger_Request $request)
    {
        return 'EditViewPreProcess.tpl';
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        global $theme, $image_path;
        PDFMaker_Debugger_Model::GetInstance()->Init();

        $PDFMaker = new PDFMaker_PDFMaker_Model();
        $viewer = $this->getViewer($request);

        $cu_model = Users_Record_Model::getCurrentUserModel();

        $templateid = $request->get('templateid');
        $pdftemplateResult = $PDFMaker->GetEditViewData($templateid);

        $viewer->assign('PDF_TEMPLATE_RESULT', $pdftemplateResult);

        $select_module = $pdftemplateResult['module'];
        $select_format = $pdftemplateResult['format'];
        $select_orientation = $pdftemplateResult['orientation'];

        $disp_header = $pdftemplateResult['disp_header'];
        $disp_footer = $pdftemplateResult['disp_footer'];
        $viewer->assign('FILENAME', $pdftemplateResult['filename']);
        $viewer->assign('DESCRIPTION', $pdftemplateResult['description']);
        $viewer->assign('EMODE', 'edit');
        $viewer->assign('TEMPLATEID', $templateid);
        $viewer->assign('MODULENAME', vtranslate($select_module, $select_module));
        $viewer->assign('SELECTMODULE', $select_module);

        $viewer->assign('BODY', $pdftemplateResult['body']);

        $this->cu_language = $cu_model->get('language');

        $viewer->assign('THEME', $theme);
        $viewer->assign('IMAGE_PATH', $image_path);
        $app_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($this->cu_language);
        $app_strings = $app_strings_big['languageStrings'];
        $viewer->assign('APP', $app_strings);
        $viewer->assign('PARENTTAB', getParentTab());

// ******************************************   Company and User information: **********************************

        $CUI_BLOCKS['Account'] = vtranslate('LBL_COMPANY_INFO', 'PDFMaker');
        $CUI_BLOCKS['Assigned'] = vtranslate('LBL_USER_INFO', 'PDFMaker');
        $CUI_BLOCKS['Logged'] = vtranslate('LBL_LOGGED_USER_INFO', 'PDFMaker');
        $CUI_BLOCKS['Modifiedby'] = vtranslate('LBL_MODIFIEDBY_USER_INFO', 'PDFMaker');
        $CUI_BLOCKS['Creator'] = vtranslate('LBL_CREATOR_USER_INFO', 'PDFMaker');
        $viewer->assign('CUI_BLOCKS', $CUI_BLOCKS);

        $adb = PearDatabase::getInstance();
        $sql = 'SELECT * FROM vtiger_organizationdetails';
        $result = $adb->pquery($sql, []);

        $organization_logoname = decode_html($adb->query_result($result, 0, 'logoname'));
        $organization_header = decode_html($adb->query_result($result, 0, 'headername'));
        $organization_stamp_signature = $adb->query_result($result, 0, 'stamp_signature');

        global $site_URL;
        $path = $site_URL . '/test/logo/';

        if (isset($organization_logoname)) {
            $organization_logo_img = "<img src=\"" . $path . $organization_logoname . "\">";
            $viewer->assign('COMPANYLOGO', $organization_logo_img);
        }

        if (isset($organization_stamp_signature)) {
            $organization_stamp_signature_img = "<img src=\"" . $path . $organization_stamp_signature . "\">";
            $viewer->assign('COMPANY_STAMP_SIGNATURE', $organization_stamp_signature_img);
        }

        if (isset($organization_header)) {
            $organization_header_img = "<img src=\"" . $path . $organization_header . "\">";
            $viewer->assign('COMPANY_HEADER_SIGNATURE', $organization_header_img);
        }

        $viewer->assign('ACCOUNTINFORMATIONS', PDFMaker_Fields_Model::getCompanyOptions());

        $sql_user_block = 'SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid = ? ORDER BY sequence ASC';
        $res_user_block = $adb->pquery($sql_user_block, ['29']);
        $user_block_info_arr = [];

        while ($row_user_block = $adb->fetch_array($res_user_block)) {
            $sql_user_field = 'SELECT fieldid, uitype FROM vtiger_field WHERE block = ? and (displaytype != ? OR uitype = ?) ORDER BY sequence ASC';
            $res_user_field = $adb->pquery($sql_user_field, [$row_user_block['blockid'], '3', '55']);
            $num_user_field = $adb->num_rows($res_user_field);

            if ($num_user_field > 0) {
                $user_field_id_array = [];

                while ($row_user_field = $adb->fetch_array($res_user_field)) {
                    $user_field_id_array[] = $row_user_field['fieldid'];
                }

                $user_block_info_arr[$row_user_block['blocklabel']] = $user_field_id_array;
            }
        }

        $user_mod_strings = $this->getModuleLanguageArray('Users');

        $b = 0;

        $User_Types = ['a' => '', 'l' => 'R_', 'm' => 'M_', 'c' => 'C_'];

        foreach ($user_block_info_arr as $block_label => $block_fields) {
            $b++;

            if (isset($user_mod_strings[$block_label]) and $user_mod_strings[$block_label] != '') {
                $optgroup_value = $user_mod_strings[$block_label];
            } else {
                $optgroup_value = vtranslate($block_label, 'PDFMaker');
            }

            if (count($block_fields) > 0) {
                $sql1 = 'SELECT * FROM vtiger_field WHERE fieldid IN (' . generateQuestionMarks($block_fields) . ')';
                $result1 = $adb->pquery($sql1, $block_fields);

                while ($row1 = $adb->fetchByAssoc($result1)) {
                    $fieldname = $row1['fieldname'];
                    $fieldlabel = $row1['fieldlabel'];

                    $option_key = strtoupper('Users' . '_' . $fieldname);

                    if (isset($current_mod_strings[$fieldlabel]) and $current_mod_strings[$fieldlabel] != '') {
                        $option_value = $current_mod_strings[$fieldlabel];
                    } elseif (isset($app_strings[$fieldlabel]) and $app_strings[$fieldlabel] != '') {
                        $option_value = $app_strings[$fieldlabel];
                    } else {
                        $option_value = $fieldlabel;
                    }

                    foreach ($User_Types as $user_type => $user_prefix) {
                        if ($fieldname == 'currency_id') {
                            $User_Info[$user_type][$optgroup_value][$user_prefix . $option_key] = vtranslate('LBL_CURRENCY_ID', 'PDFMaker');
                            $User_Info[$user_type][$optgroup_value][$user_prefix . 'USERS_CURRENCY_NAME'] = $option_value;
                            $User_Info[$user_type][$optgroup_value][$user_prefix . 'USERS_CURRENCY_CODE'] = vtranslate('LBL_CURRENCY_CODE', 'PDFMaker');
                            $User_Info[$user_type][$optgroup_value][$user_prefix . 'USERS_CURRENCY_SYMBOL'] = vtranslate('LBL_CURRENCY_SYMBOL', 'PDFMaker');
                        } else {
                            $User_Info[$user_type][$optgroup_value][$user_prefix . $option_key] = $option_value;
                        }
                    }
                }
            }

            //variable RECORD ID added
            if ($b == 1) {
                $option_value = 'Record ID';
                $option_key = strtoupper('USERS_CRMID');
                foreach ($User_Types as $user_type => $user_prefix) {
                    $User_Info[$user_type][$user_prefix . $optgroup_value][$option_key] = $option_value;
                }
            }
            //end
        }

// ****************************************** END: Company and User information **********************************

        $viewer->assign('USERINFORMATIONS', $User_Info);

        $Invterandcon = [
            ''                     => vtranslate('LBL_PLS_SELECT', 'PDFMaker'),
            'TERMS_AND_CONDITIONS' => vtranslate('LBL_TERMS_AND_CONDITIONS', 'PDFMaker')
        ];

        $viewer->assign('INVENTORYTERMSANDCONDITIONS', $Invterandcon);

//labels

        $global_lang_labels = @array_flip($app_strings);
        $global_lang_labels = @array_flip($global_lang_labels);
        asort($global_lang_labels);
        $viewer->assign('GLOBAL_LANG_LABELS', $global_lang_labels);

        $mod_lang = $this->getModuleLanguageArray($select_module);
        $module_lang_labels = @array_flip($mod_lang);
        $module_lang_labels = @array_flip($module_lang_labels);
        asort($module_lang_labels);

        $viewer->assign('MODULE_LANG_LABELS', $module_lang_labels);

        $Header_Footer_Strings = [
            ''      => vtranslate('LBL_PLS_SELECT', 'PDFMaker'),
            'PAGE'  => vtranslate('Page', 'PDFMaker'),
            'PAGES' => vtranslate('Pages', 'PDFMaker'),
        ];

        $viewer->assign('HEADER_FOOTER_STRINGS', $Header_Footer_Strings);

//PDF FORMAT SETTINGS

        $Formats = [
            'A3'     => 'A3',
            'A4'     => 'A4',
            'A5'     => 'A5',
            'A6'     => 'A6',
            'Letter' => 'Letter',
            'Legal'  => 'Legal',
            'Custom' => 'Custom'
        ];

        $viewer->assign('FORMATS', $Formats);

        if (strpos($select_format, ';') > 0) {
            $tmpArr = explode(';', $select_format);

            $select_format = 'Custom';
            $custom_format['width'] = $tmpArr[0];
            $custom_format['height'] = $tmpArr[1];
            $viewer->assign('CUSTOM_FORMAT', $custom_format);
        }

        $viewer->assign('SELECT_FORMAT', $select_format);

//PDF ORIENTATION SETTINGS

        $Orientations = [
            'portrait'  => vtranslate('portrait', 'PDFMaker'),
            'landscape' => vtranslate('landscape', 'PDFMaker')
        ];

        $viewer->assign('ORIENTATIONS', $Orientations);

        $viewer->assign('SELECT_ORIENTATION', $select_orientation);

//PDF MARGIN SETTINGS

        $Margins = [
            'top'    => $pdftemplateResult['margin_top'],
            'bottom' => $pdftemplateResult['margin_bottom'],
            'left'   => $pdftemplateResult['margin_left'],
            'right'  => $pdftemplateResult['margin_right']
        ];

        $Decimals = [
            'point'     => $pdftemplateResult['decimal_point'],
            'decimals'  => $pdftemplateResult['decimals'],
            'thousands' => ($pdftemplateResult['thousands_separator'] != 'sp' ? $pdftemplateResult['thousands_separator'] : ' ')
        ];
        $viewer->assign('MARGINS', $Margins);
        $viewer->assign('DECIMALS', $Decimals);

//PDF HEADER / FOOTER
        $header = $pdftemplateResult['header'];
        $footer = $pdftemplateResult['footer'];

        $viewer->assign('HEADER', $header);
        $viewer->assign('FOOTER', $footer);

        $hfVariables = [
            '##PAGE##'           => vtranslate('LBL_CURRENT_PAGE', 'PDFMaker'),
            '##PAGES##'          => vtranslate('LBL_ALL_PAGES', 'PDFMaker'),
            '##PAGE##/##PAGES##' => vtranslate('LBL_PAGE_PAGES', 'PDFMaker')
        ];

        $viewer->assign('HEAD_FOOT_VARS', $hfVariables);

        $dateVariables = [
            '##DD.MM.YYYY##' => vtranslate('LBL_DATE_DD.MM.YYYY', 'PDFMaker'),
            '##DD-MM-YYYY##' => vtranslate('LBL_DATE_DD-MM-YYYY', 'PDFMaker'),
            '##MM-DD-YYYY##' => vtranslate('LBL_DATE_MM-DD-YYYY', 'PDFMaker'),
            '##YYYY-MM-DD##' => vtranslate('LBL_DATE_YYYY-MM-DD', 'PDFMaker')
        ];

        $viewer->assign('DATE_VARS', $dateVariables);

//Sharing
        $cmod = $this->getModuleLanguageArray('Settings');
        //$cmod = return_specified_module_language($current_language, 'Settings');
        $viewer->assign('CMOD', $cmod);
        //Constructing the Role Array

//Ignored picklist values
        $pvsql = 'SELECT value FROM vtiger_pdfmaker_ignorepicklistvalues';
        $pvresult = $adb->pquery($pvsql, []);
        $pvvalues = '';
        while ($pvrow = $adb->fetchByAssoc($pvresult)) {
            $pvvalues .= $pvrow['value'] . ', ';
        }
        $viewer->assign('IGNORE_PICKLIST_VALUES', rtrim($pvvalues, ', '));

//formatable VATBLOCK content
        $vatblock_table = '<table border="1" cellpadding="3" cellspacing="0" style="border-collapse:collapse;">
                		<tr>
                            <td>' . vtranslate('Name', 'PDFMaker') . '</td>
                            <td>' . vtranslate('LBL_VATBLOCK_VAT_PERCENT', 'PDFMaker') . '</td>
                            <td>' . vtranslate('LBL_VATBLOCK_SUM', 'PDFMaker') . '</td>
                            <td>' . vtranslate('LBL_VATBLOCK_VAT_VALUE', 'PDFMaker') . '</td>
                        </tr>
                		<tr>
                            <td colspan="4">#VATBLOCK_START#</td>
                        </tr>
                		<tr>
                			<td>$VATBLOCK_LABEL$</td>
                			<td>$VATBLOCK_VALUE$</td>
                			<td>$VATBLOCK_NETTO$</td>
                			<td>$VATBLOCK_VAT$</td>
                		</tr>
                		<tr>
                            <td colspan="4">#VATBLOCK_END#</td>
                        </tr>
                    </table>';

        $vatblock_table = str_replace(["\r\n", "\r", "\n", "\t"], '', $vatblock_table);
        //$vatblock_table = ereg_replace(" {2,}", ' ', $vatblock_table); //preg_replace
        $viewer->assign('VATBLOCK_TABLE', $vatblock_table);

        $PDFMakerFieldsModel = new PDFMaker_Fields_Model();
        $ProductBlockFields = $PDFMakerFieldsModel->getInventoryItemsBlockFields($select_module);

        foreach ($ProductBlockFields as $viewer_key => $pbFields) {
            $viewer->assign($viewer_key, $pbFields);
        }

//Product block fields end
        $SelectModuleFields = $PDFMakerFieldsModel->getSelectModuleFields($select_module);
        $RelatedModules = $PDFMakerFieldsModel->getRelatedModules($select_module);

        $viewer->assign('RELATED_MODULES', $RelatedModules);
        $viewer->assign('SELECT_MODULE_FIELD', $SelectModuleFields);

// header / footer display settings
        $disp_optionsArr = ['DH_FIRST', 'DH_OTHER'];
        $disp_header_bin = str_pad(base_convert($disp_header, 10, 2), 2, '0', STR_PAD_LEFT);

        for ($i = 0; $i < count($disp_optionsArr); $i++) {
            if (substr($disp_header_bin, $i, 1) == '1') {
                $viewer->assign($disp_optionsArr[$i], 'checked="checked"');
            }
        }

        if ($disp_header == '3') {
            $viewer->assign('DH_ALL', 'checked="checked"');
        }

        $disp_optionsArr = ['DF_FIRST', 'DF_LAST', 'DF_OTHER'];
        $disp_footer_bin = str_pad(base_convert($disp_footer, 10, 2), 3, '0', STR_PAD_LEFT);

        for ($i = 0; $i < count($disp_optionsArr); $i++) {
            if (substr($disp_footer_bin, $i, 1) == '1') {
                $viewer->assign($disp_optionsArr[$i], 'checked="checked"');
            }
        }

        if ($disp_footer == '7') {
            $viewer->assign('DF_ALL', 'checked="checked"');
        }

        $category = getParentTab();
        $viewer->assign('CATEGORY', $category);
        $selectedModuleName = $select_module;

        $viewer->assign('SELECTED_MODULE_NAME', $selectedModuleName);
        $viewer->assign('SOURCE_MODULE', $selectedModuleName);

        $viewer->view('EditFree.tpl', 'PDFMaker');
    }

    /**
     * Function to get array with module languages
     *
     * @param module name
     *
     * @return <Array> - Array of Module languages
     */
    function getModuleLanguageArray($module)
    {
        if (file_exists('languages/' . $this->cu_language . '/' . $module . '.php')) {
            $current_mod_strings_lang = $this->cu_language;
        } else {
            $current_mod_strings_lang = 'en_us';
        }

        $current_mod_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($current_mod_strings_lang, $module);

        return $current_mod_strings_big['languageStrings'] ?? [];
    }

    /**
     * Function to get the list of Script models to be included
     *
     * @param Vtiger_Request $request
     *
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $jsFileNames = [
            'modules.Vtiger.resources.Edit',
            'modules.PDFMaker.resources.EditFree',
            'modules.PDFMaker.resources.ckeditor.ckeditor',
            'libraries.jquery.ckeditor.adapters.jquery',
            'libraries.jquery.jquery_windowmsg',
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}