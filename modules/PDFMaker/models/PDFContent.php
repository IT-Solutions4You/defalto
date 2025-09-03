<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

$memory_limit = substr(ini_get('memory_limit'), 0, -1);

if ($memory_limit < 256) {
    ini_set('memory_limit', '256M');
}

class PDFMaker_PDFContent_Model extends PDFMaker_PDFContentUtils_Model
{
    public static $bridge2mpdf = [];
    private static $is_inventory_module = false;
    private static $module;
    private static $language;
    private static $focus;
    private static $db;
    private static $mod_strings;
    private static $def_charset;
    private static $site_url;
    private static $rowbreak;
    private static $ignored_picklist_values = [];
    private static $header;
    private static $footer;
    private static $body;
    private static $section_sep = '&#%ITS%%%@@@%%%ITS%#&';
    private static $execution_time_start;
    private static $inventory_table_array = [
        'PurchaseOrder' => 'vtiger_purchaseorder',
        'SalesOrder'    => 'vtiger_salesorder',
        'Quotes'        => 'vtiger_quotes',
        'Invoice'       => 'vtiger_invoice',
        'Issuecards'    => 'vtiger_issuecards',
        'Receiptcards'  => 'vtiger_receiptcards',
        'Creditnote'    => 'vtiger_creditnote',
        'StornoInvoice' => 'vtiger_stornoinvoice'
    ];
    private static $inventory_id_array = [
        'PurchaseOrder' => 'purchaseorderid',
        'SalesOrder'    => 'salesorderid',
        'Quotes'        => 'quoteid',
        'Invoice'       => 'invoiceid',
        'Issuecards'    => 'issuecardid',
        'Receiptcards'  => 'receiptcardid',
        'Creditnote'    => 'creditnote_id',
        'StornoInvoice' => 'stornoinvoice_id'
    ];
    private static $org_colsOLD = [
        'organizationname' => 'NAME',
        'address'          => 'ADDRESS',
        'city'             => 'CITY',
        'state'            => 'STATE',
        'code'             => 'ZIP',
        'country'          => 'COUNTRY',
        'phone'            => 'PHONE',
        'fax'              => 'FAX',
        'website'          => 'WEBSITE',
        'logo'             => 'LOGO'
    ];

    protected array $vatBlock = [];

    function __construct($l_module, $l_focus, $l_language)
    {
        parent::__construct();

        if (!defined('LOGO_PATH')) {
            define('LOGO_PATH', 'test/logo/');
        }

        PDFMaker_Debugger_Model::GetInstance()->Init();
        $dc = vglobal('default_charset');

        self::$db = PearDatabase::getInstance();
        self::$def_charset = $dc;
        self::$module = $l_module;
        self::$focus = $l_focus;
        self::$language = $l_language;

        $current_user = Users_Record_Model::getCurrentUserModel();
        $current_user->set('language', $l_language);
        $mod_strings_array = Vtiger_Language_Handler::getModuleStringsFromFile(self::$language, self::$module);
        self::$mod_strings = $mod_strings_array['languageStrings'];

        $this->PDFMaker = new PDFMaker_PDFMaker_Model();

        $this->getTemplateData();
        $this->getIgnoredPicklistValues();
        $this->retrieveRecordModel(self::$focus->id);

        self::$bridge2mpdf['record'] = self::$focus->id;
        self::$rowbreak = '<rowbreak />';
        self::$is_inventory_module[self::$module] = InventoryItem_Utils_Helper::usesInventoryItem(self::$module);
    }

    private function getTemplateData()
    {
        $i = 'site_URL';
        $salt = vglobal($i);
        self::$site_url = trim($salt, '/');

        $result = self::$db->pquery(
            'SELECT vtiger_pdfmaker.*, vtiger_pdfmaker_settings.* FROM vtiger_pdfmaker LEFT JOIN vtiger_pdfmaker_settings ON vtiger_pdfmaker_settings.templateid = vtiger_pdfmaker.templateid WHERE vtiger_pdfmaker.module=? AND vtiger_pdfmaker.module IN (?,?,?,?)',
            [self::$module, 'Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder']
        );
        $data = self::$db->fetch_array($result);

        self::$decimal_point = html_entity_decode($data['decimal_point'], ENT_QUOTES);
        self::$thousands_separator = html_entity_decode(($data['thousands_separator'] != 'sp' ? $data['thousands_separator'] : ' '), ENT_QUOTES);
        self::$decimals = $data['decimals'];
        self::$header = $data['header'];
        self::$footer = $data['footer'];
        self::$body = $data['body'];
        $formatPB = $data['format'];

        if (strpos($formatPB, ';') > 0) {
            $tmpArr = explode(';', $formatPB);
            $formatPB = $tmpArr[0] . 'mm ' . $tmpArr[1] . 'mm';
        } elseif ($data['orientation'] == 'landscape') {
            $formatPB .= '-L';
        }

        self::$pagebreak = '<pagebreak sheet-size="' . $formatPB . '" orientation="' . $data['orientation'] . '" margin-left="' . ($data['margin_left'] * 10) . 'mm" margin-right="' . ($data['margin_right'] * 10) . 'mm" margin-top="0mm" margin-bottom="0mm" margin-header="' . ($data['margin_top'] * 10) . 'mm" margin-footer="' . ($data['margin_bottom'] * 10) . 'mm" />';
    }

    private function getIgnoredPicklistValues()
    {
        $result = self::$db->pquery('SELECT value FROM vtiger_pdfmaker_ignorepicklistvalues', []);

        while ($row = self::$db->fetchByAssoc($result)) {
            self::$ignored_picklist_values[] = $row['value'];
        }
    }

    /**
     * @throws Exception
     */
    public function getContent()
    {
        self::$execution_time_start = microtime(true);
        self::$content = self::$body;
        self::$content = self::$header . self::$section_sep;
        self::$content .= self::$body . self::$section_sep;
        self::$content .= self::$footer;

        self::$rep['$siteurl$'] = self::$site_url;
        self::$rep['[BARCODE|'] = '<barcode>';
        self::$rep['|BARCODE]'] = '</barcode>';
        self::$rep['&nbsp;'] = ' ';
        self::$rep['##PAGE##'] = '{PAGENO}';
        self::$rep['##PAGES##'] = '{nb}';
        self::$rep['##DD-MM-YYYY##'] = date('d-m-Y');
        self::$rep['##DD.MM.YYYY##'] = date('d.m.Y');
        self::$rep['##MM-DD-YYYY##'] = date('m-d-Y');
        self::$rep['##YYYY-MM-DD##'] = date('Y-m-d');
        self::$rep["src='"] = "src='" . vglobal('img_root_directory');
        self::$rep['$' . strtoupper(self::$module) . '_CRMID$'] = self::$recordModel->getId();
        self::$rep['$' . strtoupper(self::$module) . '_CREATEDTIME_DATETIME$'] = self::$recordModel->getDisplayValue('createdtime');
        self::$rep['$' . strtoupper(self::$module) . '_MODIFIEDTIME_DATETIME$'] = self::$recordModel->getDisplayValue('modifiedtime');

        $this->convertEntityImages();
        $this->replaceContent();

        self::$content = html_entity_decode(self::$content, ENT_QUOTES, self::$def_charset);

        PDFMaker_PDFContent_Model::includeSimpleHtmlDom();
        $html = str_get_html(self::$content);

        if (is_array($html->find('div[style^=page-break-after]'))) {
            foreach ($html->find('div[style^=page-break-after]') as $div_page_break) {
                $div_page_break->outertext = self::$pagebreak;
                self::$content = $html->save();
            }
        }

        if (is_array($html->find('div[style^=PAGE-BREAK-AFTER]'))) {
            foreach ($html->find('div[style^=PAGE-BREAK-AFTER]') as $div_page_break) {
                $div_page_break->outertext = self::$pagebreak;
                self::$content = $html->save();
            }
        }

        $this->convertRelatedModule();
        $this->replaceFieldsToContent(self::$module, self::$focus);
        $this->convertInventoryBlocks();
        $this->convertVatBlocks();
        $this->retrieveAssignedUserId();
        $this->convertCurrencyInfo();
        $this->convertCopyHeader();
        $this->convertPageBreak();
        $this->handleRowbreak();
        $this->replaceUserCompanyFields();
        $this->replaceLabels();
        $this->convertHideTR();

        self::$rep['%EXECUTIONTIME%'] = 'Total execution time in seconds: ' . (microtime(true) - self::$execution_time_start);
        $this->replaceContent();

        self::$content = $this->fixImg(self::$content);

        if (strtoupper(self::$def_charset) != 'UTF-8') {
            self::$content = iconv(self::$def_charset, 'UTF-8//TRANSLIT', self::$content);
        }

        $PDF_content = [];
        [$PDF_content['header'], $PDF_content['body'], $PDF_content['footer']] = explode(self::$section_sep, self::$content);

        return $PDF_content;
    }

    public function retrieveAssignedUserId()
    {
        if (self::$focus->column_fields['assigned_user_id'] == '') {
            $result = self::$db->pquery('SELECT assigned_user_id FROM vtiger_crmentity WHERE crmid = ?', [self::$focus->id]);

            self::$focus->column_fields['assigned_user_id'] = self::$db->query_result($result, 0, 'assigned_user_id');
        }
    }

    private function convertEntityImages()
    {
        switch (self::$module) {
            case 'Contacts':
                self::$rep['$CONTACTS_IMAGENAME$'] = $this->getContactImage(self::$focus->id, self::$site_url);
                break;
            case 'Products':
                self::$rep['$PRODUCTS_IMAGENAME$'] = $this->getProductImage(self::$focus->id, self::$site_url);
                break;
        }
    }

    /**
     * @throws Exception
     */
    private function convertRelatedModule()
    {
        $v = 'vtiger_current_version';
        $vcv = vglobal($v);
        $field_inf = '_fieldinfo_cache';
        $fieldModRel = $this->GetFieldModuleRel();

        $module_tabid = getTabId(self::$module);
        $Query_Parr = ['3', '64', $module_tabid];
        $sql = 'SELECT fieldid, fieldname, uitype, columnname FROM vtiger_field WHERE (displaytype != ? OR fieldid = ?) AND tabid = ?';
        $result = self::$db->pquery($sql, $Query_Parr);
        $num_rows = self::$db->num_rows($result);

        if ($num_rows > 0) {
            while ($row = self::$db->fetch_array($result)) {
                $columnname = $row['columnname'];
                $fk_record = self::$focus->column_fields[$row['fieldname']];
                $related_module = $this->getUITypeRelatedModule($row['uitype'], $fk_record);

                if ($related_module != '') {
                    $displayValueModified = $displayValueCreated = $related_module_id = '';
                    $tabid = getTabId($related_module);
                    $temp = &VTCacheUtils::$$field_inf;

                    unset($temp[$tabid]);

                    $focus2 = CRMEntity::getInstance($related_module);

                    if ($fk_record != '' && $fk_record != '0') {
                        if ($related_module == 'Users') {
                            $control_sql = 'vtiger_users WHERE id=';
                        } else {
                            $control_sql = 'vtiger_crmentity WHERE crmid=';
                        }

                        $result_delete = self::$db->pquery('SELECT deleted FROM ' . $control_sql . '? AND deleted=0', [$fk_record]);

                        if (self::$db->num_rows($result_delete) > 0) {
                            $focus2->retrieve_entity_info($fk_record, $related_module);
                            $related_module_id = $focus2->id = $fk_record;

                            if (!empty($focus2->column_fields['createdtime'])) {
                                $createdtime = new DateTimeField($focus2->column_fields['createdtime']);
                                $displayValueCreated = $createdtime->getDisplayDateTimeValue();
                            }

                            if (!empty($focus2->column_fields['modifiedtime'])) {
                                $modifiedtime = new DateTimeField($focus2->column_fields['modifiedtime']);
                                $displayValueModified = $modifiedtime->getDisplayDateTimeValue();
                            }
                        }
                    }

                    self::$rep['$R_' . strtoupper($columnname) . '_CRMID$'] = $related_module_id;
                    self::$rep['$R_' . strtoupper($columnname) . '_CREATEDTIME_DATETIME$'] = $displayValueCreated;
                    self::$rep['$R_' . strtoupper($columnname) . '_MODIFIEDTIME_DATETIME$'] = $displayValueModified;

                    if ($related_module != 'Users') {
                        self::$rep['$R_' . strtoupper($related_module) . '_CRMID$'] = $related_module_id;
                        self::$rep['$R_' . strtoupper($related_module) . '_CREATEDTIME_DATETIME$'] = $displayValueCreated;
                        self::$rep['$R_' . strtoupper($related_module) . '_MODIFIEDTIME_DATETIME$'] = $displayValueModified;
                    }

                    if (isset($related_module)) {
                        $entityImg = '';

                        switch ($related_module) {
                            case 'Contacts':
                                $entityImg = $this->getContactImage($related_module_id, self::$site_url);
                                break;
                            case 'Products':
                                $entityImg = $this->getProductImage($related_module_id, self::$site_url);
                                break;
                        }

                        if ($related_module != 'Users') {
                            self::$rep['$R_' . strtoupper($related_module) . '_IMAGENAME$'] = $entityImg;
                        }

                        self::$rep['$R_' . strtoupper($columnname) . '_IMAGENAME$'] = $entityImg;
                    }

                    $this->replaceContent();

                    if ($related_module != 'Users') {
                        $this->replaceFieldsToContent($related_module, $focus2, true);
                    }

                    $this->replaceFieldsToContent($related_module, $focus2, $columnname);

                    unset($focus2);
                }
                if ($row['uitype'] == '68') {
                    $fieldModRel[$row['fieldid']][] = 'Contacts';
                    $fieldModRel[$row['fieldid']][] = 'Accounts';
                }

                if (isset($fieldModRel[$row['fieldid']])) {
                    foreach ($fieldModRel[$row['fieldid']] as $idx => $relMod) {
                        if ($relMod == $related_module) {
                            continue;
                        }

                        $tmpTabId = getTabId($relMod);
                        $temp = &VTCacheUtils::$$field_inf;

                        unset($temp[$tmpTabId]);

                        if (file_exists('modules/' . $relMod . '/' . $relMod . '.php')) {
                            $tmpFocus = CRMEntity::getInstance($relMod);

                            if ($related_module != 'Users') {
                                self::$rep['$R_' . strtoupper($relMod) . '_CRMID$'] = '';
                                self::$rep['$R_' . strtoupper($relMod) . '_CREATEDTIME_DATETIME$'] = '';
                                self::$rep['$R_' . strtoupper($relMod) . '_MODIFIEDTIME_DATETIME$'] = '';
                                $this->replaceFieldsToContent($relMod, $tmpFocus, true);
                            }

                            self::$rep['$R_' . strtoupper($columnname) . '_CRMID$'] = '';
                            self::$rep['$R_' . strtoupper($columnname) . '_CREATEDTIME_DATETIME$'] = '';
                            self::$rep['$R_' . strtoupper($columnname) . '_MODIFIEDTIME_DATETIME$'] = '';

                            $this->replaceFieldsToContent($relMod, $tmpFocus, $columnname);

                            unset($tmpFocus);
                        }
                    }
                }
            }
        }
    }

    private function replaceFieldsToContent($emodule, $efocus, $is_related = false, $inventory_currency = false, $related = 'R_')
    {
        $current_user = Users_Record_Model::getCurrentUserModel();

        if ($inventory_currency !== false) {
            $inventory_content = [];
        }

        $convEntity = $emodule;

        if ($is_related === false) {
            $related = '';
        } elseif ($is_related !== true) {
            $convEntity = $is_related;
        }

        if (!empty($efocus->id)) {
            $VtigerDetailViewModel = Vtiger_DetailView_Model::getInstance($emodule, $efocus->id);
            $recordModel = $VtigerDetailViewModel->getRecord();
            $recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, '');
        } else {
            $moduleModel = Vtiger_Module_Model::getInstance($emodule);
            $recordStrucure = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel, '');
        }

        $stucturedValues = $recordStrucure->getStructure();

        foreach ($stucturedValues as $BLOCK_LABEL => $BLOCK_FIELDS) {
            foreach ($BLOCK_FIELDS as $FIELD_NAME => $FIELD_MODEL) {
                $fieldname = $FIELD_MODEL->get('name');
                $fieldlabel = $FIELD_MODEL->get('label');

                $FIELD_DISPLAY_VALUE = '';

                if (!empty($efocus->id)) {
                    $fieldvalue = $FIELD_MODEL->get('fieldvalue');

                    $fieldDataType = $FIELD_MODEL->getFieldDataType();

                    if ($fieldDataType == 'multipicklist') {
                        $FIELD_DISPLAY_VALUE = $FIELD_MODEL->getDisplayValue($fieldvalue);
                    } elseif ($fieldDataType == 'reference' || $fieldDataType == 'owner') {
                        $FIELD_DISPLAY_VALUE = $FIELD_MODEL->getEditViewDisplayValue($fieldvalue);
                    } elseif ($fieldDataType == 'double' || $fieldDataType == 'percentage') {
                        $FIELD_DISPLAY_VALUE = $this->formatNumberToPDF($fieldvalue);
                    } elseif ($fieldDataType == 'currency') {
                        if (is_numeric($fieldvalue)) {
                            if ($inventory_currency === false) {
                                $user_currency_data = getCurrencySymbolandCRate($current_user->currency_id);
                                $crate = $user_currency_data['rate'];
                            } else {
                                $crate = $inventory_currency['conversion_rate'];
                            }

                            $fieldvalue = $fieldvalue * $crate;
                        }

                        $FIELD_DISPLAY_VALUE = $this->formatNumberToPDF($fieldvalue);
                    } elseif ($fieldDataType == 'text') {
                        $FIELD_DISPLAY_VALUE = htmlspecialchars_decode($FIELD_MODEL->getDisplayValue($fieldvalue));
                    } else {
                        $FIELD_DISPLAY_VALUE = $FIELD_MODEL->getDisplayValue($fieldvalue);
                    }

                }

                self::$rep['%' . strtoupper($related . $convEntity . '_' . $fieldname) . '%'] = vtranslate($fieldlabel, $emodule);
                self::$rep['%M_' . $fieldlabel . '%'] = vtranslate($fieldlabel, $emodule);

                if (!$is_related) {
                    self::$rep[$this->getVariableLabel($fieldname)] = vtranslate($fieldlabel, $emodule);
                    self::$rep[$this->getVariable($fieldname)] = $FIELD_DISPLAY_VALUE;
                }

                if ($inventory_currency !== false) {
                    $inventory_content[strtoupper($emodule . '_' . $fieldname)] = $FIELD_DISPLAY_VALUE;
                } else {
                    self::$rep['$' . $related . strtoupper($convEntity . '_' . $fieldname) . '$'] = $FIELD_DISPLAY_VALUE;
                }
            }
        }

        if ($inventory_currency !== false) {
            return $inventory_content;
        } else {
            $this->replaceContent();

            return true;
        }
    }

    private function formatNumberToPDF($value)
    {
        $number = '';

        if (is_numeric($value)) {
            $number = number_format($value, self::$decimals, self::$decimal_point, self::$thousands_separator);
        }

        return $number;
    }

    private function getInventoryCurrencyInfoCustom($module, $focus): array
    {
        $record_id = '';
        $inventory_table = self::$inventory_table_array[$module];
        $inventory_id = self::$inventory_id_array[$module];

        if (!empty($focus->id)) {
            $record_id = $focus->id;
        }

        return $this->getInventoryCurrencyInfoCustomArray($inventory_table, $inventory_id, $record_id);
    }

    /**
     * @param string $taxKey
     * @param string $taxLabel
     * @param float  $taxValue
     * @param float  $nett
     * @param float  $vat
     *
     * @return void
     */
    public function setVatBlock($taxKey, $taxLabel, $taxValue, $nett, $vat)
    {
        if (empty($this->vatBlock[$taxKey])) {
            $this->vatBlock[$taxKey] = [
                'netto' => 0,
                'vat'   => 0,
            ];
        }

        $this->vatBlock[$taxKey]['label'] = $taxLabel;
        $this->vatBlock[$taxKey]['value'] = $taxValue;
        $this->vatBlock[$taxKey]['netto'] += $nett;
        $this->vatBlock[$taxKey]['vat'] += $vat;
    }

    public function getVatBlock()
    {
        return $this->vatBlock;
    }

    private function retrieve_entity_infoCustom(&$focus, $record, $module)
    {
        $result = [];

        foreach ($focus->tab_name_index as $table_name => $index) {
            $result[$table_name] = self::$db->pquery('SELECT * FROM ' . $table_name . ' WHERE ' . $index . '=?', [$record]);
        }

        $tabid = getTabid($module);
        $result1 = self::$db->pquery('SELECT fieldname, fieldid, fieldlabel, columnname, tablename, uitype, typeofdata, presence FROM vtiger_field WHERE tabid=?', [$tabid]);
        $noofrows = self::$db->num_rows($result1);

        if ($noofrows) {
            while ($resultrow = self::$db->fetch_array($result1)) {
                $fieldcolname = $resultrow['columnname'];
                $tablename = $resultrow['tablename'];
                $fieldname = $resultrow['fieldname'];
                $fld_value = '';

                if (isset($result[$tablename])) {
                    $fld_value = self::$db->query_result($result[$tablename], 0, $fieldcolname);
                }

                $focus->column_fields[$fieldname] = $fld_value;
            }
        }

        $focus->column_fields['record_id'] = $record;
        $focus->column_fields['record_module'] = $module;
    }

    private function handleRowbreak()
    {
        PDFMaker_PDFContent_Model::includeSimpleHtmlDom();
        $html = str_get_html(self::$content);
        $toSkip = 0;

        if (is_array($html->find('rowbreak'))) {
            foreach ($html->find('rowbreak') as $pb) {
                if ($pb->outertext == self::$rowbreak) {
                    $tmpPb = $pb;

                    while ($tmpPb != null && $tmpPb->tag != 'td') {
                        $tmpPb = $tmpPb->parent();
                    }

                    if ($tmpPb->tag == 'td') {
                        if ($toSkip > 0) {
                            $toSkip--;
                            continue;
                        }

                        $prev_sibling = $tmpPb->prev_sibling();
                        $prev_sibling_styles = [];

                        while ($prev_sibling != null) {
                            $prev_sibling_styles[] = $this->getDOMElementAtts($prev_sibling);
                            $prev_sibling = $prev_sibling->prev_sibling();
                        }

                        $next_sibling = $tmpPb->next_sibling();
                        $next_sibling_styles = [];

                        while ($next_sibling != null) {
                            $next_sibling_styles[] = $this->getDOMElementAtts($next_sibling);
                            $next_sibling = $next_sibling->next_sibling();
                        }

                        $partsArr = explode(self::$rowbreak, $tmpPb->innertext);

                        for ($i = 0; $i < (php7_count($partsArr) - 1); $i++) {
                            $tmpPb->innertext = $partsArr[$i];
                            $addition = '<tr>';

                            for ($j = 0; $j < php7_count($prev_sibling_styles); $j++) {
                                $addition .= '<td ' . $prev_sibling_styles[$j] . '>&nbsp;</td>';
                            }

                            $addition .= '<td style="' . $tmpPb->getAttribute('style') . '">' . $partsArr[$i + 1] . '</td>';

                            for ($j = 0; $j < php7_count($next_sibling_styles); $j++) {
                                $addition .= '<td ' . $next_sibling_styles[$j] . '>&nbsp;</td>';
                            }

                            $addition .= '</tr>';

                            $tmpPb->parent()->outertext = $tmpPb->parent()->outertext . $addition;
                        }

                        $toSkip = php7_count($partsArr) - 2;
                    }
                }
            }

            self::$content = $html->save();
        }
    }

    public static function getSiteUrl()
    {
        return rtrim(vglobal('site_URL'), '/') . '/';
    }

    /**
     * @throws Exception
     */
    public function replaceCompanyFields(): void
    {
        $userId = self::$focus->column_fields['assigned_user_id'];
        $fields = Core_TemplateContent_Helper::getCompanyFields($userId, self::$language);

        self::$rep = array_merge(self::$rep, $fields);
    }

    /**
     * @throws Exception
     */
    private function replaceUserCompanyFields(): void
    {
        $this->replaceCompanyFields();
        $this->replaceTermsAndConditions();

        $current_user = Users_Record_Model::getCurrentUserModel();

        if (self::$focus->column_fields['assigned_user_id'] != '') {
            $user_res = self::$db->pquery('SELECT * FROM vtiger_users WHERE id = ?', [self::$focus->column_fields['assigned_user_id']]);
            $user_row = self::$db->fetchByAssoc($user_res);

            $this->replaceUserData($user_row['id'], $user_row, 'USER');
        } else {
            $this->replaceUserData($current_user->id, $current_user, 'USER');
        }

        $this->replaceUserData($current_user->id, $current_user, 'L_USER');

        $focus_user = CRMEntity::getInstance('Users');
        $focus_user->id = self::$focus->column_fields['assigned_user_id'];
        $this->retrieve_entity_infoCustom($focus_user, $focus_user->id, 'Users');
        $this->replaceFieldsToContent('Users', $focus_user, false);
        $curr_user_focus = CRMEntity::getInstance('Users');
        $curr_user_focus->id = $current_user->id;
        $this->retrieve_entity_infoCustom($curr_user_focus, $curr_user_focus->id, 'Users');
        $this->replaceFieldsToContent('Users', $curr_user_focus, true);
        self::$rep['$USERS_CRMID$'] = $focus_user->id;
        self::$rep['$R_USERS_CRMID$'] = $curr_user_focus->id;

        $modifiedby_user_res = self::$db->pquery(
            'SELECT vtiger_users.* FROM vtiger_users INNER JOIN vtiger_crmentity ON vtiger_crmentity.modifiedby = vtiger_users.id  WHERE  vtiger_crmentity.crmid = ?',
            [self::$focus->id]
        );
        $modifiedby_user_row = self::$db->fetchByAssoc($modifiedby_user_res);
        $this->replaceUserData($modifiedby_user_row['id'], $modifiedby_user_row, 'M_USER');
        $modifiedby_user_focus = CRMEntity::getInstance('Users');
        $modifiedby_user_focus->id = $modifiedby_user_row['id'];
        $this->retrieve_entity_infoCustom($modifiedby_user_focus, $modifiedby_user_focus->id, 'Users');
        $this->replaceFieldsToContent('Users', $modifiedby_user_focus, true, false, 'M_');

        $smcreatorid_user_res = self::$db->pquery(
            'SELECT vtiger_users.* FROM vtiger_users INNER JOIN vtiger_crmentity ON vtiger_crmentity.creator_user_id = vtiger_users.id  WHERE  vtiger_crmentity.crmid = ?',
            [self::$focus->id]
        );
        $smcreatorid_user_row = self::$db->fetchByAssoc($smcreatorid_user_res);
        $this->replaceUserData($smcreatorid_user_row['id'], $smcreatorid_user_row, 'C_USER');
        $smcreatorid_user_focus = CRMEntity::getInstance('Users');
        $smcreatorid_user_focus->id = $smcreatorid_user_row['id'];
        $this->retrieve_entity_infoCustom($smcreatorid_user_focus, $smcreatorid_user_focus->id, 'Users');
        $this->replaceFieldsToContent('Users', $smcreatorid_user_focus, true, false, 'C_');

        $this->replaceContent();
    }

    protected function replaceTermsAndConditions()
    {
        $value = Core_Utils_Helper::getTermsAndConditions(self::$module);

        if (empty($value)) {
            $value = Core_Utils_Helper::getTermsAndConditions('Inventory');
        }

        self::$rep['$TERMS_AND_CONDITIONS$'] = nl2br($value);
    }

    private function replaceUserData($id, $data, $type)
    {
        $Fields = [
            'FIRSTNAME'   => 'first_name',
            'LASTNAME'    => 'last_name',
            'EMAIL'       => 'email1',
            'TITLE'       => 'title',
            'FAX'         => 'phone_fax',
            'DEPARTMENT'  => 'department',
            'OTHER_EMAIL' => 'email2',
            'PHONE'       => 'phone_work',
            'YAHOOID'     => 'yahoo_id',
            'MOBILE'      => 'phone_mobile',
            'HOME_PHONE'  => 'phone_home',
            'OTHER_PHONE' => 'phone_other',
            'SIGHNATURE'  => 'signature',
            'NOTES'       => 'description',
            'ADDRESS'     => 'address_street',
            'COUNTRY'     => 'address_country',
            'CITY'        => 'address_city',
            'ZIP'         => 'address_postalcode',
            'STATE'       => 'address_state'
        ];

        foreach ($Fields as $n => $v) {
            self::$rep['$' . $type . '_' . $n . '$'] = $this->getUserValue($v, $data);
        }

        $currency_id = $this->getUserValue('currency_id', $data);
        $currency_info = $this->getInventoryCurrencyInfoCustomArray('', '', $currency_id);

        if ($type == 'L_USER') {
            $type = 'R_USER';
        }

        self::$rep['$' . $type . 'S_IMAGENAME$'] = $this->getUserImage($id);
        self::$rep['$' . $type . 'S_CRMID$'] = $id;
        self::$rep['$' . $type . 'S_CURRENCY_NAME$'] = $currency_info['currency_name'];
        self::$rep['$' . $type . 'S_CURRENCY_CODE$'] = $currency_info['currency_code'];
        self::$rep['$' . $type . 'S_CURRENCY_SYMBOL$'] = $currency_info['currency_symbol'];

        $this->replaceContent();
    }

    private function replaceLabels()
    {
        $app_lang_array = Vtiger_Language_Handler::getModuleStringsFromFile(self::$language);
        $mod_lang_array = Vtiger_Language_Handler::getModuleStringsFromFile(self::$language, self::$module);
        $app_lang = $app_lang_array['languageStrings'];
        $mod_lang = $mod_lang_array['languageStrings'];

        self::$rep['%G_Qty%'] = $app_lang['Quantity'];
        self::$rep['%G_Subtotal%'] = $app_lang['Sub Total'];
        self::$rep['%M_LBL_VENDOR_NAME_TITLE%'] = $app_lang['Vendor Name'];
        $this->replaceContent();

        if (strpos(self::$content, '%G_') !== false) {
            foreach ($app_lang as $key => $value) {
                self::$rep['%G_' . $key . '%'] = $value;
            }

            $this->replaceContent();
        }

        if (strpos(self::$content, '%M_') !== false) {
            foreach ($mod_lang as $key => $value) {
                self::$rep['%M_' . $key . '%'] = $value;
            }

            $this->replaceContent();

            foreach ($app_lang as $key => $value) {
                self::$rep['%M_' . $key . '%'] = $value;
            }

            if (self::$module == 'SalesOrder') {
                self::$rep['%G_SO Number%'] = $mod_lang['SalesOrder No'];
            }

            if (self::$module == 'Invoice') {
                self::$rep['%G_Invoice No%'] = $mod_lang['Invoice No'];
            }

            self::$rep['%M_Grand Total%'] = vtranslate('Grand Total', self::$module);

            $this->replaceContent();
        }
    }

    public function getSettings($templateid)
    {
        return $this->getSettingsForId($templateid);
    }

    public function getSettingsForModule($module)
    {
        $db = PearDatabase::getInstance();
        $sql = "SELECT (margin_top * 10) AS margin_top,
                     (margin_bottom * 10) AS margin_bottom,
                     (margin_left * 10) AS margin_left,
                     (margin_right*10) AS margin_right,
                     format,
                     orientation,
                     encoding,
                     disp_header, disp_footer
              FROM vtiger_pdfmaker_settings INNER JOIN vtiger_pdfmaker ON  vtiger_pdfmaker.templateid = vtiger_pdfmaker_settings.templateid WHERE  vtiger_pdfmaker.module = ? AND vtiger_pdfmaker.module IN ('Invoice','Quotes','SalesOrder','PurchaseOrder') ";

        $result = $db->pquery($sql, [$module]);

        return $db->fetchByAssoc($result, 1);
    }

    public static function includeSimpleHtmlDom()
    {
        require_once 'vendor/simplehtmldom/simplehtmldom/simple_html_dom.php';
    }
}