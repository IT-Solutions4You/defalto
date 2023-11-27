<?php
/* * *******************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class EMAILMaker_Fields_Model extends Vtiger_Base_Model
{

    public $cu_language = "";
    public $ModuleFields = array();
    public $All_Related_Modules = array();

    public function getAllModuleFields($ModuleIDS)
    {

        foreach ($ModuleIDS as $module => $module_id) {
            $this->setModuleFields($module, $module_id);
        }
    }

    public function getRelatedModules($module)
    {

        return $this->All_Related_Modules[$module];
    }

    public function getSelectModuleFields($module, $forfieldname = "")
    {

        $SelectModuleFields = array();
        $adb = PearDatabase::getInstance();

        $Blocks = $this->getModuleFields($module);

        $cu_model = Users_Record_Model::getCurrentUserModel();
        $this->cu_language = $cu_model->get('language');
        $app_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($this->cu_language);
        $app_strings = $app_strings_big['languageStrings'];

        $current_mod_strings = $this->getModuleLanguageArray($module);
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $b = 0;

        if ($forfieldname == "") {
            $forfieldname = $module;
        } elseif ($forfieldname != "company") {
            $forfieldname = strtolower($forfieldname . "-" . $module);
        }


        if ($module == 'Calendar') {
            $b++;
            $SelectModuleFields['Calendar'][vtranslate('Calendar')][$forfieldname . "_CRMID"] = "Record ID";

            $EventModel = Vtiger_Module_Model::getInstance('Events');
        }

        foreach ($Blocks as $block_label => $block_fields) {
            $b++;

            $Options = array();

            if ($block_label != "TEMP_MODCOMMENTS_BLOCK") {

                $optgroup_value = vtranslate($block_label, $module);

                if ($optgroup_value == $block_label) {
                    $optgroup_value = vtranslate($block_label, 'EMAILMaker');
                }

            } else {
                $optgroup_value = vtranslate("LBL_MODCOMMENTS_INFORMATION", 'EMAILMaker');
            }

            if (count($block_fields) > 0) {
                $sql1 = "SELECT * FROM vtiger_field WHERE fieldid IN (" . generateQuestionMarks($block_fields) . ") AND presence != '1'";
                $result1 = $adb->pquery($sql1, $block_fields);

                while ($row1 = $adb->fetchByAssoc($result1)) {
                    $fieldname = $row1['fieldname'];
                    $fieldlabel = $row1['fieldlabel'];

                    $fieldModel = Vtiger_Field_Model::getInstance($fieldname, $moduleModel);

                    if (!$fieldModel || !$fieldModel->getPermissions('readonly')) {
                        if ($module == 'Calendar') {
                            $eventFieldModel = Vtiger_Field_Model::getInstance($fieldname, $EventModel);
                            if (!$eventFieldModel || !$eventFieldModel->getPermissions('readonly')) {
                                continue;
                            }
                        } else {
                            continue;
                        }
                    }

                    if ($module == "ITS4YouMultiCompany" && $forfieldname == "company") {
                        if ($fieldname == "companyname") {
                            $fieldname = "name";
                        } elseif ($fieldname == "street") {
                            $fieldname = "address";
                        } elseif ($fieldname == "code") {
                            $fieldname = "zip";
                        }
                    }

                    $option_key = strtolower($forfieldname . "-" . $fieldname);

                    if (isset($current_mod_strings[$fieldlabel]) and $current_mod_strings[$fieldlabel] != "") {
                        $option_value = $current_mod_strings[$fieldlabel];
                    } elseif (isset($app_strings[$fieldlabel]) and $app_strings[$fieldlabel] != "") {
                        $option_value = $app_strings[$fieldlabel];
                    } else {
                        $option_value = $fieldlabel;
                    }

                    $option_value = nl2br($option_value);

                    if ($module == 'Calendar') {
                        if ($option_key == 'CALENDAR_ACTIVITYTYPE' || $option_key == 'CALENDAR_DUE_DATE') {
                            $SelectModuleFields[vtranslate('Calendar')][$option_key] = $option_value;
                            continue;
                        } elseif (!isset($Existing_ModuleFields[$option_key])) {
                            $Existing_ModuleFields[$option_key] = $optgroup_value;
                        } else {
                            $SelectModuleFields[vtranslate('Calendar')][$option_key] = $option_value;
                            $Unset_Module_Fields[] = '"' . $option_value . '","' . $option_key . '"';
                            unset($SelectModuleFields['Calendar'][$Existing_ModuleFields[$option_key]][$option_key]);
                            continue;
                        }
                    }
                    $Options[] = '"' . $option_value . '","' . $option_key . '"';
                    $SelectModuleFields[$optgroup_value][$option_key] = $option_value;
                }
            }

            //variable RECORD ID added
            if ($b == 1) {
                $option_value = "Record ID";
                $option_key = strtolower($module . "-CRMID");
                $Options[] = '"' . $option_value . '","' . $option_key . '"';
                $SelectModuleFields[$optgroup_value][$option_key] = $option_value;
                $option_value = vtranslate('Created Time') . ' (' . vtranslate('Due Date & Time') . ')';
                $option_key = strtolower($module . "-CREATEDTIME_DATETIME");
                $Options[] = '"' . $option_value . '","' . $option_key . '"';
                $SelectModuleFields[$optgroup_value][$option_key] = $option_value;
                $option_value = vtranslate('Modified Time') . ' (' . vtranslate('Due Date & Time') . ')';
                $option_key = strtolower($module . "-MODIFIEDTIME_DATETIME");
                $Options[] = '"' . $option_value . '","' . $option_key . '"';
                $SelectModuleFields[$optgroup_value][$option_key] = $option_value;
            }
            //end

            if ($block_label == "LBL_TERMS_INFORMATION" && isset($tacModules[$module])) {
                $option_value = vtranslate("LBL_TAC4YOU", 'EMAILMaker');
                $option_key = strtolower($module . "-TAC4YOU");
                $Options[] = '"' . $option_value . '","' . $option_key . '"';
                $SelectModuleFields[$optgroup_value][$option_key] = $option_value;
            }

            if ($block_label == "LBL_DESCRIPTION_INFORMATION" && isset($desc4youModules[$module])) {
                $option_value = vtranslate("LBL_DESC4YOU", 'EMAILMaker');
                $option_key = strtolower($module . "-DESC4YOU");
                $Options[] = '"' . $option_value . '","' . $option_key . '"';
                $SelectModuleFields[$optgroup_value][$option_key] = $option_value;
            }

            $OptionsRelMod = array();
            if (($block_label == "LBL_DETAILS_BLOCK" || $block_label == "LBL_ITEM_DETAILS") && ($module == "Quotes" || $module == "Invoice" || $module == "SalesOrder" || $module == "PurchaseOrder" || $module == "Issuecards" || $module == "Receiptcards" || $module == "Creditnote" || $module == "StornoInvoice" || is_subclass_of($module . '_Module_Model', 'Inventory_Module_Model'))) {
                //$Set_More_Fields = $More_Fields;

                $Set_More_Fields = array(/* "SUBTOTAL"=>vtranslate("LBL_VARIABLE_SUM",'EMAILMaker'), */
                    "CURRENCYNAME" => vtranslate("LBL_CURRENCY_NAME", 'EMAILMaker'),
                    "CURRENCYSYMBOL" => vtranslate("LBL_CURRENCY_SYMBOL", 'EMAILMaker'),
                    "CURRENCYCODE" => vtranslate("LBL_CURRENCY_CODE", 'EMAILMaker'),
                    "TOTALWITHOUTVAT" => vtranslate("LBL_VARIABLE_SUMWITHOUTVAT", 'EMAILMaker'),
                    "TOTALDISCOUNT" => vtranslate("LBL_VARIABLE_TOTALDISCOUNT", 'EMAILMaker'),
                    "TOTALDISCOUNTPERCENT" => vtranslate("LBL_VARIABLE_TOTALDISCOUNT_PERCENT", 'EMAILMaker'),
                    "TOTALAFTERDISCOUNT" => vtranslate("LBL_VARIABLE_TOTALAFTERDISCOUNT", 'EMAILMaker'),
                    "VAT" => vtranslate("LBL_VARIABLE_VAT", 'EMAILMaker'),
                    "VATPERCENT" => vtranslate("LBL_VARIABLE_VAT_PERCENT", 'EMAILMaker'),
                    "VATBLOCK" => vtranslate("LBL_VARIABLE_VAT_BLOCK", 'EMAILMaker'),
                    "CHARGESBLOCK" => vtranslate("LBL_VARIABLE_CHARGES_BLOCK", 'EMAILMaker'),
                    "DEDUCTEDTAXESBLOCK" => vtranslate("LBL_DEDUCTED_TAXES_BLOCK", 'EMAILMaker'),
                    "DEDUCTEDTAXESTOTAL" => vtranslate("LBL_DEDUCTED_TAXES_TOTAL", 'EMAILMaker'),
                    "TOTALWITHVAT" => vtranslate("LBL_VARIABLE_SUMWITHVAT", 'EMAILMaker'),
                    "SHTAXTOTAL" => vtranslate("LBL_SHTAXTOTAL", 'EMAILMaker'),
                    "SHTAXAMOUNT" => vtranslate("LBL_SHTAXAMOUNT", 'EMAILMaker'),
                    "ADJUSTMENT" => vtranslate("LBL_ADJUSTMENT", 'EMAILMaker'),
                    "TOTAL" => vtranslate("LBL_VARIABLE_TOTALSUM", 'EMAILMaker')
                );


                if ($module == "Invoice") {
                    $Set_More_Fields[$forfieldname . "_RECEIVED"] = vtranslate("Received", $module);
                }
                if ($module == "Invoice" || $module == "PurchaseOrder") {
                    $Set_More_Fields[$forfieldname . "_BALANCE"] = vtranslate("Balance", $module);
                }

                foreach ($Set_More_Fields as $variable => $variable_name) {
                    $variable_key = strtolower($variable);
                    $Options[] = '"' . $variable_name . '","' . $variable_key . '"';
                    $SelectModuleFields[$optgroup_value][$variable_key] = $variable_name;
                    if ($variable_key != "VATBLOCK") {
                        $OptionsRelMod[] = '"' . $variable_name . '","' . strtolower($module) . '-' . $variable_key . '"';
                    }
                }
            }
        }

        return $SelectModuleFields;
    }

    public function getModuleFields($module)
    {

        if (!isset($this->ModuleFields[$module])) {

            $module_id = getTabid($module);
            $this->setModuleFields($module, $module_id);
        }

        return $this->ModuleFields[$module];
    }

    public function setModuleFields($module, $module_id, $skip_related = false)
    {

        if (isset($this->ModuleFields[$module])) {
            return false;
        }

        $adb = PearDatabase::getInstance();

        if ($module == 'Calendar') {
            $sql1 = "SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid IN (9,16) ORDER BY sequence ASC";
        } elseif ($module == "Quotes" || $module == "Invoice" || $module == "SalesOrder" || $module == "PurchaseOrder" || $module == "Issuecards" || $module == "Receiptcards" || $module == "Creditnote" || $module == "StornoInvoice") {
            $sql1 = "SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid=" . $module_id . " AND blocklabel != 'LBL_DETAILS_BLOCK' AND blocklabel != 'LBL_ITEM_DETAILS' ORDER BY sequence ASC";
        } elseif ($module == "Users") {
            $sql1 = "SELECT blockid, blocklabel FROM vtiger_blocks INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_blocks.tabid WHERE vtiger_tab.name = 'Users' AND (blocklabel = 'LBL_USERLOGIN_ROLE' OR blocklabel = 'LBL_ADDRESS_INFORMATION' ) ORDER BY sequence ASC";
        } else {
            $sql1 = "SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid=" . $module_id . " ORDER BY sequence ASC";
        }

        $res1 = $adb->pquery($sql1, array());
        $block_info_arr = array();
        while ($row = $adb->fetch_array($res1)) {
            if ($row['blockid'] == '41' && $row['blocklabel'] == '') {
                $row['blocklabel'] = 'LBL_EVENT_INFORMATION';
            }
            $sql2 = "SELECT fieldid, uitype, columnname, fieldlabel
             FROM vtiger_field WHERE block= ? AND (displaytype != 3 OR uitype = 55) AND displaytype != 4 AND fieldlabel != 'Add Comment' AND presence != ?
             ORDER BY sequence ASC";
            $res2 = $adb->pquery($sql2, array($row['blockid'], '1'));
            $num_rows2 = $adb->num_rows($res2);

            if ($num_rows2 > 0) {
                $field_id_array = array();

                while ($row2 = $adb->fetch_array($res2)) {

                    $field_id_array[] = $row2['fieldid'];
                    $tmpArr = array($row2["columnname"], vtranslate($row2["fieldlabel"], $module));
                    if (!$skip_related) {
                        switch ($row2['uitype']) {
                            case "51":
                                $this->All_Related_Modules[$module][] = array_merge($tmpArr, array(vtranslate("Accounts", "Accounts"), "Accounts"));
                                break;
                            case "57":
                                $this->All_Related_Modules[$module][] = array_merge($tmpArr, array(vtranslate("Contacts", "Contacts"), "Contacts"));
                                break;
                            case "58":
                                $this->All_Related_Modules[$module][] = array_merge($tmpArr, array(vtranslate("Campaigns", "Campaigns"), "Campaigns"));
                                break;
                            case "59":
                                $this->All_Related_Modules[$module][] = array_merge($tmpArr, array(vtranslate("Products", "Products"), "Products"));
                                break;
                            case "73":
                                $this->All_Related_Modules[$module][] = array_merge($tmpArr, array(vtranslate("Accounts", "Accounts"), "Accounts"));
                                break;
                            case "75":
                                $this->All_Related_Modules[$module][] = array_merge($tmpArr, array(vtranslate("Vendors", "Vendors"), "Vendors"));
                                break;
                            case "81":
                                $this->All_Related_Modules[$module][] = array_merge($tmpArr, array(vtranslate("Vendors", "Vendors"), "Vendors"));
                                break;
                            case "76":
                                $this->All_Related_Modules[$module][] = array_merge($tmpArr, array(vtranslate("Potentials", "Potentials"), "Potentials"));
                                break;
                            case "78":
                                $this->All_Related_Modules[$module][] = array_merge($tmpArr, array(vtranslate("Quotes", "Quotes"), "Quotes"));
                                break;
                            case "80":
                                $this->All_Related_Modules[$module][] = array_merge($tmpArr, array(vtranslate("SalesOrder", "SalesOrder"), "SalesOrder"));
                                break;
                            case "101":
                                $this->All_Related_Modules[$module][] = array_merge($tmpArr, array(vtranslate("Users", "Users"), "Users"));
                                $this->setModuleFields("Users", "", true);
                                break;
                            case "68":
                                $this->All_Related_Modules[$module][] = array_merge($tmpArr, array(vtranslate("Accounts", "Accounts"), "Accounts"));
                                $this->All_Related_Modules[$module][] = array_merge($tmpArr, array(vtranslate("Contacts", "Contacts"), "Contacts"));
                                break;
                            case "10":
                                $fmrs = $adb->pquery('SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid = ?', array($row2['fieldid']));
                                while ($rm = $adb->fetch_array($fmrs)) {
                                    $this->All_Related_Modules[$module][] = array_merge($tmpArr, array(vtranslate($rm['relmodule'], $rm['relmodule']), $rm['relmodule']));
                                }
                                break;
                        }
                    }
                }
                // ITS4YOU MaJu
                //$block_info_arr[$row['blocklabel']] = $field_id_array;
                if (!empty($block_info_arr[$row['blocklabel']])) {
                    foreach ($field_id_array as $field_id_array_value) {
                        $block_info_arr[$row['blocklabel']][] = $field_id_array_value;
                    }
                } else {
                    $block_info_arr[$row['blocklabel']] = $field_id_array;
                }
                // ITS4YOU-END
            }
        }

        if ($module == "Quotes" || $module == "Invoice" || $module == "SalesOrder" || $module == "PurchaseOrder" || $module == "Issuecards" || $module == "Receiptcards" || $module == "Creditnote" || $module == "StornoInvoice") {
            $block_info_arr["LBL_DETAILS_BLOCK"] = array();
        }

        $this->ModuleFields[$module] = $block_info_arr;
    }

    public function getModuleLanguageArray($module)
    {

        if (file_exists("languages/" . $this->cu_language . "/" . $module . ".php")) {
            $current_mod_strings_lang = $this->cu_language;
        } else {
            $current_mod_strings_lang = "en_us";
        }

        $current_mod_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($current_mod_strings_lang, $module);
        return $current_mod_strings_big['languageStrings'];
    }

    public function getFilenameFields()
    {
        $filenameFields = array(
            "#TEMPLATE_NAME#" => vtranslate("LBL_EMAIL_NAME", 'EMAILMaker'),
            "#DD-MM-YYYY#" => vtranslate("LBL_CURDATE_DD-MM-YYYY", 'EMAILMaker'),
            "#MM-DD-YYYY#" => vtranslate("LBL_CURDATE_MM-DD-YYYY", 'EMAILMaker'),
            "#YYYY-MM-DD#" => vtranslate("LBL_CURDATE_YYYY-MM-DD", 'EMAILMaker')
        );

        return $filenameFields;
    }

    /**
     * @return array
     */
    public static function getCompanyOptions()
    {
        $data = array();

        if (getTabId('ITS4YouMultiCompany') && vtlib_isModuleActive('ITS4YouMultiCompany')) {
            $EMAILMakerFieldsModel = new EMAILMaker_Fields_Model();
            $data = $EMAILMakerFieldsModel->getSelectModuleFields('ITS4YouMultiCompany', 'company');
        } else {
            $companyDetails = Settings_Vtiger_CompanyDetails_Model::getInstance();
            $companyDetailsFields = $companyDetails->getFields();

            foreach ($companyDetailsFields as $fieldName => $fieldType) {
                if ($fieldName == 'organizationname') {
                    $fieldName = 'name';
                } elseif ($fieldName == 'code') {
                    $fieldName = 'zip';
                } elseif ($fieldName == 'logoname') {
                    continue;
                }

                $l = 'LBL_COMPANY_' . strtoupper($fieldName);
                $label = vtranslate($l, 'EMAILMaker');

                if (empty($label) || $l == $label) {
                    $label = $fieldName;
                }

                $data['company-' . $fieldName] = $label;
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public static function getMultiCompanyOptions()
    {
        $company = EMAILMaker_EMAILMaker_Model::MULTI_COMPANY;

        return array(
            '' => vtranslate('LBL_PLS_SELECT', 'EMAILMaker'),
            'multicompany-companyname' => vtranslate('LBL_COMPANY_NAME', 'EMAILMaker'),
            'multicompany-street' => vtranslate('Street', $company),
            'multicompany-city' => vtranslate('City', $company),
            'multicompany-code' => vtranslate('Code', $company),
            'multicompany-state' => vtranslate('State', $company),
            'multicompany-country' => vtranslate('Country', $company),
            'multicompany-phone' => vtranslate('phone', $company),
            'multicompany-fax' => vtranslate('Fax', $company),
            'multicompany-email' => vtranslate('email', $company),
            'multicompany-website' => vtranslate('Website', $company),
            'multicompany-logo' => vtranslate('Logo', $company),
            'multicompany-stamp' => vtranslate('Stamp', $company),
            'multicompany-bankname' => vtranslate('BankName', $company),
            'multicompany-bankaccountno' => vtranslate('BankAccountNo', $company),
            'multicompany-iban' => vtranslate('IBAN', $company),
            'multicompany-swift' => vtranslate('SWIFT', $company),
            'multicompany-registrationno' => vtranslate('RegistrationNo', $company),
            'multicompany-vatno' => vtranslate('VATNo', $company),
            'multicompany-taxid' => vtranslate('TaxId', $company),
            'multicompany-additionalinformations' => vtranslate('AdditionalInformations', $company),
        );
    }

    /**
     * @return array
     */
    public static function getUserOptions()
    {
        $adb = PearDatabase::getInstance();
        $data = array();
        $res_user_block = $adb->pquery('SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid = ? ORDER BY sequence ASC', array('29'));
        $user_block_info_arr = array();

        while ($row_user_block = $adb->fetch_array($res_user_block)) {
            $sql_user_field = 'SELECT fieldid, uitype FROM vtiger_field WHERE block = ? AND (displaytype != ? OR uitype = ? ) ORDER BY sequence ASC';
            $res_user_field = $adb->pquery($sql_user_field, array($row_user_block['blockid'], '3', '55'));
            $num_user_field = $adb->num_rows($res_user_field);

            if ($num_user_field > 0) {
                $user_field_id_array = array();

                while ($row_user_field = $adb->fetch_array($res_user_field)) {
                    $user_field_id_array[] = $row_user_field['fieldid'];
                }

                $user_block_info_arr[$row_user_block['blocklabel']] = $user_field_id_array;
            }
        }

        $user_mod_strings = EMAILMaker_EMAILMaker_Model::getModuleLanguageArray('Users');
        $b = 0;
        $User_Types = array('s', 'l', 'm', 'c');

        foreach ($user_block_info_arr as $block_label => $block_fields) {
            $b++;

            if (isset($user_mod_strings[$block_label]) and !empty($user_mod_strings[$block_label])) {
                $optgroup_value = $user_mod_strings[$block_label];
            } else {
                $optgroup_value = vtranslate($block_label, 'EMAILMaker');
            }

            if (count($block_fields) > 0) {
                $sql1 = 'SELECT * FROM vtiger_field WHERE fieldid IN (' . generateQuestionMarks($block_fields) . ') AND presence != 1';
                $result1 = $adb->pquery($sql1, $block_fields);

                while ($row1 = $adb->fetchByAssoc($result1)) {
                    $fieldname = $row1['fieldname'];
                    $fieldlabel = $row1['fieldlabel'];

                    $option_key = strtolower('users' . '-' . $fieldname);

                    if (isset($current_mod_strings[$fieldlabel]) and $current_mod_strings[$fieldlabel] != "") {
                        $option_value = $current_mod_strings[$fieldlabel];
                    } elseif (isset($app_strings[$fieldlabel]) and $app_strings[$fieldlabel] != "") {
                        $option_value = $app_strings[$fieldlabel];
                    } else {
                        $option_value = $fieldlabel;
                    }

                    foreach ($User_Types as $user_prefix) {
                        if ($fieldname == 'currency_id') {
                            $data[$user_prefix][$optgroup_value][$user_prefix . '-' . $option_key] = vtranslate('LBL_CURRENCY_ID', 'EMAILMaker');
                            $data[$user_prefix][$optgroup_value][$user_prefix . '-users-currency_name'] = $option_value;
                            $data[$user_prefix][$optgroup_value][$user_prefix . '-users-currency_code'] = vtranslate('LBL_CURRENCY_CODE', 'EMAILMaker');
                            $data[$user_prefix][$optgroup_value][$user_prefix . '-users-currency_symbol'] = vtranslate('LBL_CURRENCY_SYMBOL', 'EMAILMaker');
                        } else {
                            $data[$user_prefix][$optgroup_value][$user_prefix . '-' . $option_key] = $option_value;
                        }
                    }
                }
            }
            if ($b == 1) {
                $option_value = 'Record ID';
                $option_key = strtolower('USERS_CRMID');

                foreach ($User_Types as $user_prefix) {
                    $data[$user_prefix][$optgroup_value][$user_prefix . "-" . $option_key] = $option_value;
                }
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public static function getInventoryTermsAndConditionsOptions()
    {
        return array(
            '' => vtranslate('LBL_PLS_SELECT', 'EMAILMaker'),
            'terms-and-conditions' => vtranslate('LBL_TERMS_AND_CONDITIONS', 'EMAILMaker')
        );
    }

    /**
     * @return array
     */
    public static function getUserTypeOptions()
    {
        return array(
            'Assigned' => vtranslate('LBL_USER_INFO', 'EMAILMaker'),
            'Logged' => vtranslate('LBL_LOGGED_USER_INFO', 'EMAILMaker'),
            'Modifiedby' => vtranslate('LBL_MODIFIEDBY_USER_INFO', 'EMAILMaker'),
            'Creator' => vtranslate('LBL_CREATOR_USER_INFO', 'EMAILMaker'),
        );
    }

    /**
     * @return array
     */
    public static function getDateOptions()
    {
        return array(
            '##DD.MM.YYYY##' => vtranslate('LBL_DATE_DD.MM.YYYY', 'EMAILMaker'),
            '##DD-MM-YYYY##' => vtranslate('LBL_DATE_DD-MM-YYYY', 'EMAILMaker'),
            '##MM-DD-YYYY##' => vtranslate('LBL_DATE_MM-DD-YYYY', 'EMAILMaker'),
            '##YYYY-MM-DD##' => vtranslate('LBL_DATE_YYYY-MM-DD', 'EMAILMaker'),
            '##HH:II:SS##' => vtranslate('LBL_TIME_HH:II:SS', 'EMAILMaker'),
            '##HH:II##' => vtranslate('LBL_TIME_HH:II', 'EMAILMaker'),
        );
    }

    /**
     * @throws Exception
     */
    public static function getDefaultFromOptions()
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $adb = PearDatabase::getInstance();
        $options = array(
            '' => vtranslate('LBL_NONE')
        );
        $result1 = $adb->pquery('SELECT * FROM vtiger_systems WHERE from_email_field != ? AND server_type = ?', array('', 'email'));
        $from_email_field = $adb->query_result($result1, 0, 'from_email_field');

        if (!empty($from_email_field)) {
            $result2 = $adb->pquery('SELECT * FROM vtiger_organizationdetails WHERE organizationname != ""', array());

            while ($row2 = $adb->fetchByAssoc($result2)) {
                $options['0_organization_email'] = vtranslate('LBL_COMPANY_EMAIL', 'EMAILMaker') . ' <' . $from_email_field . '>';
            }
        }

        $result3 = $adb->pquery('SELECT fieldname, fieldlabel FROM vtiger_field WHERE tabid = ? AND uitype IN ( ? , ? ) ORDER BY fieldid ASC', array('29', '104', '13'));

        while ($row3 = $adb->fetchByAssoc($result3)) {
            $email = $currentUser->get($row3['fieldname']);

            if (!empty($email)) {
                $options['1_' . $row3['fieldname']] = sprintf('%s &lt;%s&gt;', vtranslate($row3['fieldlabel'], 'Users'), $email);
            }
        }

        return $options;
    }

    /**
     * @return array
     */
    public static function getProductBlockTemplates()
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM vtiger_emakertemplates_productbloc_tpl', array());
        $data = array(
            '' => vtranslate('LBL_PLS_SELECT', 'EMAILMaker'),
        );

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['body']] = $row['name'];
        }

        return $data;
    }

    /**
     * @return array
     */
    public static function getListViewBlockOptions()
    {
        return array(
            '' => vtranslate('LBL_PLS_SELECT', 'EMAILMaker'),
            'LISTVIEWBLOCK_START' => vtranslate('LBL_ARTICLE_START', 'EMAILMaker'),
            'LISTVIEWBLOCK_END' => vtranslate('LBL_ARTICLE_END', 'EMAILMaker'),
            'CRIDX' => vtranslate('LBL_COUNTER', 'EMAILMaker'),
        );
    }

    /**
     * @param string $type
     * @param array $labels
     * @return array
     */
    public static function getBlockTable($type, $labels)
    {
        $blockTable = '<table border="1" cellpadding="3" cellspacing="0" style="border-collapse:collapse;">
                                        <tr>
                                            <td>' . $labels['Name'] . '</td>';
        if ('CHARGES' === $type) {
            $tableColspan = '2';
            $blockTable .= '<td>' . vtranslate('LBL_' . $type . 'BLOCK_SUM', 'EMAILMaker') . '</td>';
        } else {
            $tableColspan = '4';
            $blockTable .= '<td>' . vtranslate('LBL_' . $type . 'BLOCK_VAT_PERCENT', 'EMAILMaker') . '</td>
                                                        <td>' . vtranslate('LBL_' . $type . 'BLOCK_SUM', 'EMAILMaker') . '</td>
                                                        <td>' . vtranslate('LBL_' . $type . 'BLOCK_VAT_VALUE', 'EMAILMaker') . '</td>';
        }

        $blockTable .= '</tr>
                                        <tr>
                                            <td colspan="' . $tableColspan . '">#' . $type . 'BLOCK_START#</td>
                                        </tr>
                                            <tr>
                                                    <td>$' . $type . 'BLOCK_LABEL$</td>
                                                    <td>$' . $type . 'BLOCK_VALUE$</td>';
        if ('CHARGES' !== $type) {
            $blockTable .= '<td>$' . $type . 'BLOCK_NETTO$</td>
                <td>$' . $type . 'BLOCK_VAT$</td>';
        }

        $blockTable .= '</tr>
                        <tr>
                    <td colspan="' . $tableColspan . '">#' . $type . 'BLOCK_END#</td>
                </tr>
            </table>';

        return str_replace(array("\r\n", "\r", "\n", "\t"), '', $blockTable);
    }

    /**
     * @return array
     */
    public static function getSharingTypeOptions()
    {
        return array(
            'public' => vtranslate('PUBLIC_FILTER', 'EMAILMaker'),
            'private' => vtranslate('PRIVATE_FILTER', 'EMAILMaker'),
            'share' => vtranslate('SHARE_FILTER', 'EMAILMaker'),
        );
    }

    /**
     * @return array
     */
    public static function getStatusOptions()
    {
        return array(
            '1' => vtranslate('Active', 'EMAILMaker'),
            '0' => vtranslate('Inactive', 'EMAILMaker'),
        );
    }

    public static function getGeneralFieldsOptions()
    {
        return array(
            'crmdetailviewurl' => vtranslate('CRM Detail View Url', 'EMAILMaker'),
            'portaldetailviewurl' => vtranslate('Portal Detail View Url', 'EMAILMaker'),
            'siteurl' => vtranslate('Site Url', 'EMAILMaker'),
            'portalurl' => vtranslate('Portal Url', 'EMAILMaker'),
            'dbtimezone' => vtranslate('System Timezone', 'EMAILMaker'),
            'support_name' => vtranslate('Helpdesk Support Name', 'EMAILMaker'),
            'support_email_id' => vtranslate('Helpdesk Support Email-Id', 'EMAILMaker'),
            'portalpdfurl' => vtranslate('Portal Pdf Url', 'EMAILMaker'),
        );
    }

    public static function getMemberGroups()
    {
        if (getTabId('ITS4YouMultiCompany') && vtlib_isModuleActive('ITS4YouMultiCompany')) {
            $members = Settings_ITS4YouMultiCompany_Member_Model::getAll();
        } else {
            $members = Settings_Groups_Member_Model::getAll();
        }

        $currentUser = Users_Record_Model::getCurrentUserModel();

        $users = $currentUser->getAccessibleUsers('', 'EMAILMaker');
        self::updateMembersGroups($members, 'Users', $users);

        $groups = $currentUser->getAccessibleGroups('', 'EMAILMaker');
        self::updateMembersGroups($members, 'Groups', $groups);

        return $members;
    }

    public static function updateMembersGroups(&$members, $type, $data)
    {
        foreach ($members[$type] as $memberId => $memberData) {
            list($sharingType, $sharingId) = explode(':', $memberId);

            if (empty($data[$sharingId])) {
                unset($members[$sharingType][$memberId]);
            }
        }
    }
}