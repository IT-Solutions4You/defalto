<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_Fields_Model extends Core_TemplateFields_Helper
{
    public $cu_language = "";

    public function getSelectModuleFields($module, $forfieldname = "")
    {
        $labelModule = 'EMAILMaker';
        $SelectModuleFields = [
            vtranslate('LBL_CUSTOM', $labelModule) => [
                strtolower($module . '-CRMID') => vtranslate('Record ID', $labelModule),
            ]
        ];
        $adb = PearDatabase::getInstance();
        $Blocks = $this->getModuleFields($module);

        $cu_model = Users_Record_Model::getCurrentUserModel();
        $this->cu_language = $cu_model->get('language');
        $app_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($this->cu_language);
        $app_strings = $app_strings_big['languageStrings'];

        $current_mod_strings = $this->getModuleLanguageArray($module);
        $moduleModel = Vtiger_Module_Model::getInstance($module);

        if ($forfieldname == "") {
            $forfieldname = $module;
        } elseif ($forfieldname != "company") {
            $forfieldname = strtolower($forfieldname . "-" . $module);
        }

        foreach ($Blocks as $block_label => $block_fields) {
            if ($block_label != "TEMP_MODCOMMENTS_BLOCK") {
                $optgroup_value = vtranslate($block_label, $module);

                if ($optgroup_value == $block_label) {
                    $optgroup_value = vtranslate($block_label, $labelModule);
                }
            } else {
                $optgroup_value = vtranslate("LBL_MODCOMMENTS_INFORMATION", $labelModule);
            }

            if (count($block_fields) > 0) {
                $sql1 = "SELECT * FROM vtiger_field WHERE fieldid IN (" . generateQuestionMarks($block_fields) . ") AND presence != '1'";
                $result1 = $adb->pquery($sql1, $block_fields);

                while ($row1 = $adb->fetchByAssoc($result1)) {
                    $fieldname = $row1['fieldname'];
                    $fieldlabel = $row1['fieldlabel'];

                    $fieldModel = Vtiger_Field_Model::getInstance($fieldname, $moduleModel);

                    if (!$fieldModel || !$fieldModel->getPermissions('readonly')) {
                        continue;
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
                    $SelectModuleFields[$optgroup_value][$option_key] = $option_value;

                    $this->retrieveSelectedModuleFieldByFieldName($SelectModuleFields[$optgroup_value], $fieldname);
                }
            }
        }

        return $SelectModuleFields;
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
        $filenameFields = [
            "#TEMPLATE_NAME#" => vtranslate("LBL_EMAIL_NAME", 'EMAILMaker'),
            "#DD-MM-YYYY#"    => vtranslate("LBL_CURDATE_DD-MM-YYYY", 'EMAILMaker'),
            "#MM-DD-YYYY#"    => vtranslate("LBL_CURDATE_MM-DD-YYYY", 'EMAILMaker'),
            "#YYYY-MM-DD#"    => vtranslate("LBL_CURDATE_YYYY-MM-DD", 'EMAILMaker')
        ];

        return $filenameFields;
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function getCompanyOptions()
    {
        $data = [];

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

                $labelKey = 'LBL_COMPANY_' . strtoupper($fieldName);
                $label = vtranslate($labelKey, 'EMAILMaker');

                if (empty($label) || $labelKey === $label) {
                    $label = vtranslate($fieldName, 'Settings:Vtiger');
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

        return [
            ''                                    => vtranslate('LBL_PLS_SELECT', 'EMAILMaker'),
            'multicompany-companyname'            => vtranslate('LBL_COMPANY_NAME', 'EMAILMaker'),
            'multicompany-street'                 => vtranslate('Street', $company),
            'multicompany-city'                   => vtranslate('City', $company),
            'multicompany-code'                   => vtranslate('Code', $company),
            'multicompany-state'                  => vtranslate('State', $company),
            'multicompany-country'                => vtranslate('Country', $company),
            'multicompany-phone'                  => vtranslate('phone', $company),
            'multicompany-fax'                    => vtranslate('Fax', $company),
            'multicompany-email'                  => vtranslate('email', $company),
            'multicompany-website'                => vtranslate('Website', $company),
            'multicompany-logo'                   => vtranslate('Logo', $company),
            'multicompany-stamp'                  => vtranslate('Stamp', $company),
            'multicompany-bankname'               => vtranslate('BankName', $company),
            'multicompany-bankaccountno'          => vtranslate('BankAccountNo', $company),
            'multicompany-iban'                   => vtranslate('IBAN', $company),
            'multicompany-swift'                  => vtranslate('SWIFT', $company),
            'multicompany-registrationno'         => vtranslate('RegistrationNo', $company),
            'multicompany-vatno'                  => vtranslate('VATNo', $company),
            'multicompany-taxid'                  => vtranslate('TaxId', $company),
            'multicompany-additionalinformations' => vtranslate('AdditionalInformations', $company),
        ];
    }

    /**
     * @return array
     */
    public static function getUserOptions()
    {
        $adb = PearDatabase::getInstance();
        $data = [];
        $res_user_block = $adb->pquery('SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid = ? ORDER BY sequence ASC', ['29']);
        $user_block_info_arr = [];

        while ($row_user_block = $adb->fetch_array($res_user_block)) {
            $sql_user_field = 'SELECT fieldid, uitype FROM vtiger_field WHERE block = ? AND (displaytype != ? OR uitype = ? ) ORDER BY sequence ASC';
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

        $user_mod_strings = EMAILMaker_EMAILMaker_Model::getModuleLanguageArray('Users');
        $b = 0;
        $User_Types = ['s', 'l', 'm', 'c'];

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
        return [
            ''                     => vtranslate('LBL_PLS_SELECT', 'EMAILMaker'),
            'terms-and-conditions' => vtranslate('LBL_TERMS_AND_CONDITIONS', 'EMAILMaker')
        ];
    }

    /**
     * @return array
     */
    public static function getUserTypeOptions()
    {
        return [
            'Assigned'   => vtranslate('LBL_USER_INFO', 'EMAILMaker'),
            'Logged'     => vtranslate('LBL_LOGGED_USER_INFO', 'EMAILMaker'),
            'Modifiedby' => vtranslate('LBL_MODIFIEDBY_USER_INFO', 'EMAILMaker'),
            'Creator'    => vtranslate('LBL_CREATOR_USER_INFO', 'EMAILMaker'),
        ];
    }

    /**
     * @return array
     */
    public static function getDateOptions()
    {
        return [
            '##DD.MM.YYYY##' => vtranslate('LBL_DATE_DD.MM.YYYY', 'EMAILMaker'),
            '##DD-MM-YYYY##' => vtranslate('LBL_DATE_DD-MM-YYYY', 'EMAILMaker'),
            '##MM-DD-YYYY##' => vtranslate('LBL_DATE_MM-DD-YYYY', 'EMAILMaker'),
            '##YYYY-MM-DD##' => vtranslate('LBL_DATE_YYYY-MM-DD', 'EMAILMaker'),
            '##HH:II:SS##'   => vtranslate('LBL_TIME_HH:II:SS', 'EMAILMaker'),
            '##HH:II##'      => vtranslate('LBL_TIME_HH:II', 'EMAILMaker'),
        ];
    }

    /**
     * @throws Exception
     */
    public static function getDefaultFromOptions()
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $adb = PearDatabase::getInstance();
        $options = [
            '' => vtranslate('LBL_NONE')
        ];
        $from_email_field = Settings_Vtiger_Systems_Model::getFromEmailField();

        if (!empty($from_email_field)) {
            $result2 = $adb->pquery('SELECT * FROM vtiger_organizationdetails WHERE organizationname != ""', []);

            while ($row2 = $adb->fetchByAssoc($result2)) {
                $options['0_organization_email'] = vtranslate('LBL_COMPANY_EMAIL', 'EMAILMaker') . ' <' . $from_email_field . '>';
            }
        }

        $result3 = $adb->pquery('SELECT fieldname, fieldlabel FROM vtiger_field WHERE tabid = ? AND uitype IN ( ? , ? ) ORDER BY fieldid ASC', ['29', '104', '13']);

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
    public static function getListViewBlockOptions()
    {
        return [
            ''                    => vtranslate('LBL_PLS_SELECT', 'EMAILMaker'),
            'LISTVIEWBLOCK_START' => vtranslate('LBL_ARTICLE_START', 'EMAILMaker'),
            'LISTVIEWBLOCK_END'   => vtranslate('LBL_ARTICLE_END', 'EMAILMaker'),
            'CRIDX'               => vtranslate('LBL_COUNTER', 'EMAILMaker'),
        ];
    }

    /**
     * @param string $type
     * @param array  $labels
     *
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

        return str_replace(["\r\n", "\r", "\n", "\t"], '', $blockTable);
    }

    /**
     * @return array
     */
    public static function getSharingTypeOptions()
    {
        return [
            'public'  => vtranslate('PUBLIC_FILTER', 'EMAILMaker'),
            'private' => vtranslate('PRIVATE_FILTER', 'EMAILMaker'),
            'share'   => vtranslate('SHARE_FILTER', 'EMAILMaker'),
        ];
    }

    /**
     * @return array
     */
    public static function getStatusOptions()
    {
        return [
            '1' => vtranslate('Active', 'EMAILMaker'),
            '0' => vtranslate('Inactive', 'EMAILMaker'),
        ];
    }

    public static function getGeneralFieldsOptions()
    {
        return [
            'crmdetailviewurl'    => vtranslate('CRM Detail View Url', 'EMAILMaker'),
            'portaldetailviewurl' => vtranslate('Portal Detail View Url', 'EMAILMaker'),
            'siteurl'             => vtranslate('Site Url', 'EMAILMaker'),
            'portalurl'           => vtranslate('Portal Url', 'EMAILMaker'),
            'dbtimezone'          => vtranslate('System Timezone', 'EMAILMaker'),
            'support_name'        => vtranslate('Helpdesk Support Name', 'EMAILMaker'),
            'support_email_id'    => vtranslate('Helpdesk Support Email-Id', 'EMAILMaker'),
            'portalpdfurl'        => vtranslate('Portal Pdf Url', 'EMAILMaker'),
        ];
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
            [$sharingType, $sharingId] = explode(':', $memberId);

            if (empty($data[$sharingId])) {
                unset($members[$sharingType][$memberId]);
            }
        }
    }
}