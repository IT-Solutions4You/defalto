<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_Fields_Model extends Core_TemplateFields_Helper
{
    public $cu_language = '';

    public function getSelectModuleFields($module, $forfieldname = '')
    {
        $labelModule = 'PDFMaker';
        $SelectModuleFields = [
            vtranslate('LBL_CUSTOM', $labelModule) => [
                strtoupper($module . '_CRMID') => vtranslate('Record ID', $labelModule),
            ],
        ];
        $adb = PearDatabase::getInstance();
        $Blocks = $this->getModuleFields($module);

        $cu_model = Users_Record_Model::getCurrentUserModel();
        $this->cu_language = $cu_model->get('language');
        $app_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($this->cu_language);
        $app_strings = $app_strings_big['languageStrings'];

        $current_mod_strings = $this->getModuleLanguageArray($module);
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $forfieldname = strtoupper($forfieldname ?: $module);

        foreach ($Blocks as $block_label => $block_fields) {
            if ($block_label != 'TEMP_MODCOMMENTS_BLOCK') {
                $optgroup_value = vtranslate($block_label, $module);

                if ($optgroup_value == $block_label) {
                    $optgroup_value = vtranslate($block_label, $labelModule);
                }
            } else {
                $optgroup_value = vtranslate('LBL_MODCOMMENTS_INFORMATION', $labelModule);
            }

            if (count($block_fields) > 0) {
                $sql1 = 'SELECT * FROM vtiger_field WHERE fieldid IN (' . generateQuestionMarks($block_fields) . ')';
                $result1 = $adb->pquery($sql1, $block_fields);

                while ($row1 = $adb->fetchByAssoc($result1)) {
                    $fieldname = $row1['fieldname'];
                    $fieldlabel = $row1['fieldlabel'];

                    $fieldModel = Vtiger_Field_Model::getInstance($fieldname, $moduleModel);

                    if (!$fieldModel || !$fieldModel->getPermissions('readonly')) {
                        continue;
                    }

                    $option_key = strtoupper($forfieldname . '_' . $fieldname);

                    if (isset($current_mod_strings[$fieldlabel]) and $current_mod_strings[$fieldlabel] != '') {
                        $option_value = $current_mod_strings[$fieldlabel];
                    } elseif (isset($app_strings[$fieldlabel]) and $app_strings[$fieldlabel] != '') {
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

    function getModuleLanguageArray($module)
    {
        if (file_exists('languages/' . $this->cu_language . '/' . $module . '.php')) {
            $current_mod_strings_lang = $this->cu_language;
        } else {
            $current_mod_strings_lang = 'en_us';
        }

        $current_mod_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($current_mod_strings_lang, $module);

        return $current_mod_strings_big['languageStrings'];
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function getCompanyOptions(): array
    {
        $company = Settings_Vtiger_CompanyDetails_Model::getInstance();
        $fields = $company->getFields();
        $options = [];

        foreach ($fields as $fieldName => $fieldType) {
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

            $options['COMPANY_' . strtoupper($fieldName)] = $label;
        }

        return $options;
    }
}