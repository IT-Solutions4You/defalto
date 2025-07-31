<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$memory_limit = substr(ini_get('memory_limit'), 0, -1);

if ($memory_limit < 256) {
    ini_set('memory_limit', '256M');
}

class PDFMaker_PDFContent_Model extends PDFMaker_PDFContentUtils_Model
{
    public static $pagebreak;
    public static $bridge2mpdf = array();
    private static $is_inventory_module = false;
    private static $module;
    private static $language;
    private static $focus;
    private static $db;
    private static $mod_strings;
    private static $def_charset;
    private static $site_url;
    private static $decimal_point;
    private static $thousands_separator;
    private static $decimals;
    private static $rowbreak;
    private static $ignored_picklist_values = array();
    private static $header;
    private static $footer;
    private static $body;
    private static $content;
    private static $section_sep = '&#%ITS%%%@@@%%%ITS%#&';
    private static $rep;
    private static $execution_time_start;
    private static $inventory_table_array = array(
        'PurchaseOrder' => 'vtiger_purchaseorder',
        'SalesOrder' => 'vtiger_salesorder',
        'Quotes' => 'vtiger_quotes',
        'Invoice' => 'vtiger_invoice',
        'Issuecards' => 'vtiger_issuecards',
        'Receiptcards' => 'vtiger_receiptcards',
        'Creditnote' => 'vtiger_creditnote',
        'StornoInvoice' => 'vtiger_stornoinvoice'
    );
    private static $inventory_id_array = array(
        'PurchaseOrder' => 'purchaseorderid',
        'SalesOrder' => 'salesorderid',
        'Quotes' => 'quoteid',
        'Invoice' => 'invoiceid',
        'Issuecards' => 'issuecardid',
        'Receiptcards' => 'receiptcardid',
        'Creditnote' => 'creditnote_id',
        'StornoInvoice' => 'stornoinvoice_id'
    );
    private static $org_colsOLD = array(
        'organizationname' => 'NAME',
        'address' => 'ADDRESS',
        'city' => 'CITY',
        'state' => 'STATE',
        'code' => 'ZIP',
        'country' => 'COUNTRY',
        'phone' => 'PHONE',
        'fax' => 'FAX',
        'website' => 'WEBSITE',
        'logo' => 'LOGO'
    );

    protected array $vatBlock = [];

    function __construct($l_module, $l_focus, $l_language)
    {
        parent::__construct();

        if (!defined('LOGO_PATH')) {
            define('LOGO_PATH', 'test/logo/');
        }

        PDFMaker_Debugger_Model::GetInstance()->Init();
        $v = 'vtiger_current_version';
        $vcv = vglobal($v);
        $i = 'site_URL';
        $salt = vglobal($i);
        $d = 'default_charset';
        $dc = vglobal($d);

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
            array(self::$module, 'Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder')
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
        $result = self::$db->pquery('SELECT value FROM vtiger_pdfmaker_ignorepicklistvalues', array());

        while ($row = self::$db->fetchByAssoc($result)) {
            self::$ignored_picklist_values[] = $row['value'];
        }
    }

    public function getContent()
    {
        self::$execution_time_start = microtime(true);

        $v = 'vtiger_current_version';
        $vcv = vglobal($v);
        $ir = 'img_root_directory';
        $img_root = vglobal($ir);

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
        self::$rep["src='"] = "src='" . $img_root;
        self::$rep['$' . strtoupper(self::$module) . '_CRMID$'] = self::$focus->id;

        if ($vcv == '5.2.1') {
            $displayValueCreated = getDisplayDate(self::$focus->column_fields['createdtime']);
            $displayValueModified = getDisplayDate(self::$focus->column_fields['modifiedtime']);
        } else {
            $createdtime = new DateTimeField(self::$focus->column_fields['createdtime']);
            $displayValueCreated = $createdtime->getDisplayDateTimeValue();
            $modifiedtime = new DateTimeField(self::$focus->column_fields['modifiedtime']);
            $displayValueModified = $modifiedtime->getDisplayDateTimeValue();
        }

        self::$rep['$' . strtoupper(self::$module) . '_CREATEDTIME_DATETIME$'] = $displayValueCreated;
        self::$rep['$' . strtoupper(self::$module) . '_MODIFIEDTIME_DATETIME$'] = $displayValueModified;
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
        $this->convertInventoryModules();
        $this->retrieveAssignedUserId();
        $this->handleRowbreak();
        $this->replaceUserCompanyFields();

        $this->replaceLabels();

        self::$rep['%EXECUTIONTIME%'] = 'Total execution time in seconds: ' . (microtime(true) - self::$execution_time_start);
        $this->replaceContent();

        self::$content = $this->fixImg(self::$content);

        if (strtoupper(self::$def_charset) != 'UTF-8') {
            self::$content = iconv(self::$def_charset, 'UTF-8//TRANSLIT', self::$content);
        }

        $PDF_content = array();
        [$PDF_content['header'], $PDF_content['body'], $PDF_content['footer']] = explode(self::$section_sep, self::$content);

        return $PDF_content;
    }

    public function retrieveAssignedUserId()
    {
        if (self::$focus->column_fields['assigned_user_id'] == '') {
            $result = self::$db->pquery('SELECT assigned_user_id FROM vtiger_crmentity WHERE crmid = ?', array(self::$focus->id));

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

    private function replaceContent()
    {
        if (!empty(self::$rep)) {
            self::$content = str_replace(array_keys(self::$rep), self::$rep, self::$content);
            self::$rep = array();
        }
    }

    private function convertRelatedModule()
    {
        $v = 'vtiger_current_version';
        $vcv = vglobal($v);
        $field_inf = '_fieldinfo_cache';
        $fieldModRel = $this->GetFieldModuleRel();

        $module_tabid = getTabId(self::$module);
        $Query_Parr = array('3', '64', $module_tabid);
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

                        $result_delete = self::$db->pquery('SELECT deleted FROM ' . $control_sql . '? AND deleted=0', array($fk_record));

                        if (self::$db->num_rows($result_delete) > 0) {
                            $focus2->retrieve_entity_info($fk_record, $related_module);
                            $related_module_id = $focus2->id = $fk_record;

                            if ($vcv == '5.2.1') {
                                $displayValueCreated = getDisplayDate($focus2->column_fields['createdtime']);
                                $displayValueModified = getDisplayDate($focus2->column_fields['modifiedtime']);
                            } else {
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
                    $this->replaceInventoryDetailsBlock($related_module, $focus2, $columnname);

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
                            $this->replaceInventoryDetailsBlock($relMod, $tmpFocus, $columnname);

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
            $inventory_content = array();
        }

        $convEntity = $emodule;

        if ($is_related === false) {
            $related = '';
        } else {
            if ($is_related !== true) {
                $convEntity = $is_related;
            }
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
                    } else {
                        if ($fieldDataType == 'text') {
                            $FIELD_DISPLAY_VALUE = htmlspecialchars_decode($FIELD_MODEL->getDisplayValue($fieldvalue));
                        } else {
                            $FIELD_DISPLAY_VALUE = $FIELD_MODEL->getDisplayValue($fieldvalue);
                        }
                    }
                }

                self::$rep['%' . $related . strtoupper($convEntity . '_' . $fieldname) . '%'] = vtranslate($fieldlabel, $emodule);
                self::$rep['%M_' . $fieldlabel . '%'] = vtranslate($fieldlabel, $emodule);

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

    private function replaceInventoryDetailsBlock($module, $focus, $is_related = false)
    {
        if (!isset(self::$inventory_table_array[$module])) {
            $this->fillInventoryData($module, $focus);
        }

        if (!isset(self::$inventory_table_array[$module])) {
            return array();
        }

        $prefix = '';

        if ($is_related !== false) {
            $prefix = 'R_' . strtoupper($is_related) . '_';
        }

        self::$rep['$' . $prefix . 'SUBTOTAL$'] = $this->formatNumberToPDF($focus->column_fields['subtotal']);
        self::$rep['$' . $prefix . 'TOTAL$'] = $this->formatNumberToPDF($focus->column_fields['price_total']);

        $currencytype = $this->getInventoryCurrencyInfoCustom($module, $focus);
        $currencytype['currency_symbol'] = str_replace('€', '&euro;', $currencytype['currency_symbol']);
        $currencytype['currency_symbol'] = str_replace('£', '&pound;', $currencytype['currency_symbol']);

        self::$rep['$' . $prefix . 'CURRENCYNAME$'] = getTranslatedCurrencyString($currencytype['currency_name']);
        self::$rep['$' . $prefix . 'CURRENCYSYMBOL$'] = $currencytype['currency_symbol'];
        self::$rep['$' . $prefix . 'CURRENCYCODE$'] = $currencytype['currency_code'];
        self::$rep['$' . $prefix . 'ADJUSTMENT$'] = $this->formatNumberToPDF($focus->column_fields['adjustment']);

        $Products = $this->getInventoryProducts($module, $focus);

        self::$rep['$' . $prefix . 'TOTALWITHOUTVAT$'] = $Products['TOTAL']['TOTALWITHOUTVAT'];
        self::$rep['$' . $prefix . 'VAT$'] = $Products['TOTAL']['TAXTOTAL'];

        if ('individual' === $Products['TOTAL']['TAXTYPE']) {
            self::$rep['$' . $prefix . 'VATPERCENT$'] = '$VATPERCENT_INDIVIDUAL$';
        } else {
            self::$rep['$' . $prefix . 'VATPERCENT$'] = $Products['TOTAL']['TAXTOTALPERCENT'];
        }


        self::$rep['$' . $prefix . 'TOTALWITHVAT$'] = $Products['TOTAL']['TOTALWITHVAT'];
        self::$rep['$' . $prefix . 'SHTAXAMOUNT$'] = $Products['TOTAL']['SHTAXAMOUNT'];
        self::$rep['$' . $prefix . 'SHTAXTOTAL$'] = $Products['TOTAL']['SHTAXTOTAL'];
        self::$rep['$' . $prefix . 'TOTALDISCOUNT$'] = $Products['TOTAL']['FINALDISCOUNT'];
        self::$rep['$' . $prefix . 'TOTALDISCOUNTPERCENT$'] = $Products['TOTAL']['FINALDISCOUNTPERCENT'];
        self::$rep['$' . $prefix . 'TOTALAFTERDISCOUNT$'] = $Products['TOTAL']['TOTALAFTERDISCOUNT'];
        $this->replaceContent();

        if ($is_related === false) {
            $vattable = '';

            if (php7_count($Products['TOTAL']['VATBLOCK']) > 0) {
                $vattable = "<table border='1' style='border-collapse:collapse;' cellpadding='3'>";
                $vattable .= "<tr>
                                <td nowrap align='center'>" . vtranslate('Name') . "</td>
                                <td nowrap align='center'>" . self::$mod_strings['LBL_VATBLOCK_VAT_PERCENT'] . "</td>
                                <td nowrap align='center'>" . self::$mod_strings['LBL_VATBLOCK_SUM'] . " (" . $currencytype['currency_symbol'] . ")" . "</td>
                                <td nowrap align='center'>" . self::$mod_strings['LBL_VATBLOCK_VAT_VALUE'] . " (" . $currencytype['currency_symbol'] . ")" . "</td>
                              </tr>";

                foreach ($Products['TOTAL']['VATBLOCK'] as $keyW => $valueW) {
                    if ($valueW['netto'] != 0) {
                        $vattable .= "<tr>
                                        <td nowrap align='left' width='20%'>" . $valueW['label'] . "</td>
                        				<td nowrap align='right' width='25%'>" . $this->formatNumberToPDF($valueW['value']) . " %</td>
                                        <td nowrap align='right' width='30%'>" . $this->formatNumberToPDF($valueW['netto']) . "</td>
                                        <td nowrap align='right' width='25%'>" . $this->formatNumberToPDF($valueW['vat']) . "</td>
                                      </tr>";
                    }
                }

                $vattable .= '</table>';
            }

            self::$rep['$VATBLOCK$'] = $vattable;

            $this->replaceContent();

            $VProductParts = [];

            if (strpos(self::$content, '#VATBLOCK_START#') !== false && strpos(self::$content, '#VATBLOCK_END#') !== false) {
                self::$content = $this->convertVatBlock(self::$content);
                $VExplodedPdf = array();
                $VExploded = explode('#VATBLOCK_START#', self::$content);
                $VExplodedPdf[] = $VExploded[0];

                for ($iterator = 1; $iterator < php7_count($VExploded); $iterator++) {
                    $VSubExploded = explode('#VATBLOCK_END#', $VExploded[$iterator]);

                    foreach ($VSubExploded as $Vpart) {
                        $VExplodedPdf[] = $Vpart;
                    }

                    $Vhighestpartid = $iterator * 2 - 1;
                    $VProductParts[$Vhighestpartid] = $VExplodedPdf[$Vhighestpartid];
                    $VExplodedPdf[$Vhighestpartid] = '';
                }

                if (php7_count($Products['TOTAL']['VATBLOCK']) > 0) {
                    foreach ($Products['TOTAL']['VATBLOCK'] as $keyW => $valueW) {
                        foreach ($VProductParts as $productpartid => $productparttext) {
                            if ($valueW['netto'] != 0) {
                                foreach ($valueW as $vColl => $vVal) {
                                    if (is_numeric($vVal)) {
                                        $vVal = $this->formatNumberToPDF($vVal);
                                    }

                                    $productparttext = str_replace('$VATBLOCK_' . strtoupper($vColl) . '$', $vVal, $productparttext);
                                }

                                $VExplodedPdf[$productpartid] .= $productparttext;
                            }
                        }
                    }
                }

                self::$content = implode('', $VExplodedPdf);
            }
        }

        return $Products;
    }

    private function fillInventoryData($module, $focus)
    {
        if (!isset(self::$is_inventory_module[$module])) {
            self::$is_inventory_module[$module] = InventoryItem_Utils_Helper::usesInventoryItem($module);
        }

        if (self::$is_inventory_module[$module] || (isset($focus->column_fields['currency_id']) && isset($focus->column_fields['conversion_rate']) && isset($focus->column_fields['price_total']))) {
            self::$inventory_table_array[$module] = $focus->table_name;
            self::$inventory_id_array[$module] = $focus->table_index;
        }
    }

    private function getInventoryCurrencyInfoCustom($module, $focus)
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
     * @param float $taxValue
     * @param float $nett
     * @param float $vat
     * @return void
     */
    public function setVatBlock($taxKey, $taxLabel, $taxValue, $nett, $vat)
    {
        if (empty($this->vatBlock[$taxKey])) {
            $this->vatBlock[$taxKey] = [
                'netto' => 0,
                'vat' => 0,
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

    private function getInventoryProducts($module, $focus)
    {
        if (!empty($focus->id)) {
            $total_vatsum = $totalwithoutwat = $totalAfterDiscount_subtotal = $total_subtotal = $totalsum_subtotal = 0;

            [$images, $bacImgs] = $this->getInventoryImages($focus->id);

            $recordModel = Vtiger_Record_Model::getInstanceById($focus->id);
            $relatedProducts = $recordModel->getProducts();
            //##Final details convertion started
            $finalDetails = $relatedProducts[1]['final_details'];
            $taxtype = $finalDetails['taxtype'];

            $currencyFieldsList = array(
                'NETTOTAL' => 'subtotal',
                'TAXTOTAL' => 'tax_totalamount',
                'SHTAXTOTAL' => 'shtax_totalamount',
                'TOTALAFTERDISCOUNT' => 'preTaxTotal',
                'FINALDISCOUNT' => 'discountTotal_final',
                'SHTAXAMOUNT' => 'shipping_handling_charge',
            );

            foreach ($currencyFieldsList as $variableName => $fieldName) {
                $Details['TOTAL'][$variableName] = $this->formatNumberToPDF($finalDetails[$fieldName]);
            }

            $totalWithVat = $this->getTotalWithVat($finalDetails);
            $Details['TOTAL']['TOTALWITHVAT'] = $this->formatNumberToPDF($totalWithVat);
            $Details['TOTAL']['TAXTYPE'] = $taxtype;

            foreach ($relatedProducts as $i => $PData) {
                $Details['P'][$i] = array(
                    'TAXTYPE' => $taxtype,
                );
                $sequence = $i;
                $producttitle = $productname = $PData['productName' . $sequence];
                $entitytype = $PData['entityType' . $sequence];
                $productid = $psid = $PData['hdnProductId' . $sequence];
                $focus_p = CRMEntity::getInstance('Products');

                if ($entitytype == 'Products' && $psid != '') {
                    $focus_p->id = $psid;
                    $this->retrieve_entity_infoCustom($focus_p, $psid, 'Products');
                }

                $currencytype = $this->getInventoryCurrencyInfoCustom($module, $focus);
                $Array_P = $this->replaceFieldsToContent('Products', $focus_p, false, $currencytype);
                $Details['P'][$i] = array_merge($Array_P, $Details['P'][$i]);

                unset($focus_p);

                $focus_s = CRMEntity::getInstance('Services');

                if ($entitytype == 'Services' && $psid != '') {
                    $focus_s->id = $psid;
                    $this->retrieve_entity_infoCustom($focus_s, $psid, 'Services');
                }

                $Array_S = $this->replaceFieldsToContent('Services', $focus_s, false, $currencytype);
                $Details['P'][$i] = array_merge($Array_S, $Details['P'][$i]);

                unset($focus_s);

                $Details['P'][$i]['PRODUCTS_CRMID'] = $Details['P'][$i]['SERVICES_CRMID'] = $qty_per_unit = $usageunit = '';


                if ($entitytype == 'Products') {
                    $Details['P'][$i]['PRODUCTS_CRMID'] = $psid;
                    $qty_per_unit = $Details['P'][$i]['PRODUCTS_QTY_PER_UNIT'];
                    $usageunit = $Details['P'][$i]['PRODUCTS_USAGEUNIT'];
                } elseif ($entitytype == 'Services') {
                    $Details['P'][$i]['SERVICES_CRMID'] = $psid;
                    $qty_per_unit = $Details['P'][$i]['SERVICES_QTY_PER_UNIT'];
                    $usageunit = $Details['P'][$i]['SERVICES_SERVICE_USAGEUNIT'];
                }

                $psdescription = $Details['P'][$i][strtoupper($entitytype) . '_DESCRIPTION'];

                $Details['P'][$i]['RECORD_ID'] = $psid;
                $Details['P'][$i]['PS_CRMID'] = $psid;
                $Details['P'][$i]['PS_NO'] = $PData['hdnProductcode' . $sequence];

                if (php7_count($PData['subprod_qty_list' . $sequence]) > 0) {
                    foreach ($PData['subprod_qty_list' . $sequence] as $sid => $SData) {
                        $sname = $SData['name'];

                        if ($SData['qty'] > 0) {
                            $sname .= ' (' . $SData['qty'] . ')';
                        }

                        $productname .= "<br/><span style='color:#C0C0C0;font-style:italic;'>" . $sname . '</span>';
                    }
                }

                $comment = $PData['comment' . $sequence];

                if ($comment != '') {
                    if (strpos($comment, '&lt;br /&gt;') === false && strpos($comment, '&lt;br/&gt;') === false && strpos($comment, '&lt;br&gt;') === false) {
                        $comment = str_replace("\\n", '<br>', nl2br($comment));
                    }

                    $comment = html_entity_decode($comment, ENT_QUOTES, self::$def_charset);
                    $productname .= '<br /><small>' . $comment . '</small>';
                }

                $Details['P'][$i]['PRODUCTNAME'] = $productname;
                $Details['P'][$i]['PRODUCTTITLE'] = $producttitle;

                $inventory_prodrel_desc = $psdescription;

                if (strpos($psdescription, '&lt;br /&gt;') === false && strpos($psdescription, '&lt;br/&gt;') === false && strpos($psdescription, '&lt;br&gt;') === false) {
                    $psdescription = str_replace("\\n", '<br>', nl2br($psdescription));
                }

                $Details['P'][$i]['PRODUCTDESCRIPTION'] = html_entity_decode($psdescription, ENT_QUOTES, self::$def_charset);
                $Details['P'][$i]['PRODUCTEDITDESCRIPTION'] = $comment;

                if (strpos($inventory_prodrel_desc, '&lt;br /&gt;') === false && strpos($inventory_prodrel_desc, '&lt;br/&gt;') === false && strpos($inventory_prodrel_desc, '&lt;br&gt;') === false) {
                    $inventory_prodrel_desc = str_replace("\\n", '<br>', nl2br($inventory_prodrel_desc));
                }

                $Details['P'][$i]['CRMNOWPRODUCTDESCRIPTION'] = html_entity_decode($inventory_prodrel_desc, ENT_QUOTES, self::$def_charset);
                $Details['P'][$i]['PRODUCTLISTPRICE'] = $this->formatNumberToPDF($PData['listPrice' . $sequence]);
                $Details['P'][$i]['PRODUCTTOTAL'] = $this->formatNumberToPDF($PData['productTotal' . $sequence]);
                $Details['P'][$i]['PRODUCTQUANTITY'] = $this->formatNumberToPDF($PData['qty' . $sequence]);
                $Details['P'][$i]['PRODUCTQINSTOCK'] = $this->formatNumberToPDF($PData['qtyInStock' . $sequence]);
                $Details['P'][$i]['PRODUCTPRICE'] = $this->formatNumberToPDF($PData['unitPrice' . $sequence]);
                $Details['P'][$i]['PRODUCTPOSITION'] = $sequence;
                $Details['P'][$i]['PRODUCTQTYPERUNIT'] = $this->formatNumberToPDF($qty_per_unit);
                $value = $usageunit;

                if (!in_array(trim($value), self::$ignored_picklist_values)) {
                    $value = $this->getTranslatedStringCustom($value, 'Products/Services', self::$language);
                } else {
                    $value = '';
                }

                $Details['P'][$i]['PRODUCTUSAGEUNIT'] = $value;
                $Details['P'][$i]['PRODUCTDISCOUNT'] = $PData['discountTotal' . $sequence];
                $Details['P'][$i]['PRODUCTDISCOUNTPERCENT'] = $PData['discount_percent' . $sequence];
                $totalAfterDiscount = $PData['totalAfterDiscount' . $sequence];
                $Details['P'][$i]['PRODUCTSTOTALAFTERDISCOUNTSUM'] = $totalAfterDiscount;
                $Details['P'][$i]['PRODUCTSTOTALAFTERDISCOUNT'] = $this->formatNumberToPDF($PData['totalAfterDiscount' . $sequence]);
                $Details['P'][$i]['PRODUCTTOTALSUM'] = $this->formatNumberToPDF($PData['netPrice' . $sequence]);

                $totalAfterDiscount_subtotal += $totalAfterDiscount;
                $total_subtotal += $PData['productTotal' . $sequence];
                $totalsum_subtotal += $PData['netPrice' . $sequence];

                $Details['P'][$i]['PRODUCTSTOTALAFTERDISCOUNT_SUBTOTAL'] = $this->formatNumberToPDF($totalAfterDiscount_subtotal);
                $Details['P'][$i]['PRODUCTTOTAL_SUBTOTAL'] = $this->formatNumberToPDF($total_subtotal);
                $Details['P'][$i]['PRODUCTTOTALSUM_SUBTOTAL'] = $this->formatNumberToPDF($totalsum_subtotal);

                $mpdfSubtotalAble[$i]['$TOTALAFTERDISCOUNT_SUBTOTAL$'] = $Details['P'][$i]['PRODUCTSTOTALAFTERDISCOUNT_SUBTOTAL'];
                $mpdfSubtotalAble[$i]['$TOTAL_SUBTOTAL$'] = $Details['P'][$i]['PRODUCTTOTAL_SUBTOTAL'];
                $mpdfSubtotalAble[$i]['$TOTALSUM_SUBTOTAL$'] = $Details['P'][$i]['PRODUCTTOTALSUM_SUBTOTAL'];

                $Details['P'][$i]['PRODUCTSEQUENCE'] = $sequence;
                $Details['P'][$i]['PRODUCTS_IMAGENAME'] = '';

                if (isset($images[$productid . '_' . $sequence])) {
                    $width = $height = '';
                    if ($images[$productid . '_' . $sequence]['width'] > 0) {
                        $width = " width='" . $images[$productid . '_' . $sequence]['width'] . "' ";
                    }
                    if ($images[$productid . '_' . $sequence]['height'] > 0) {
                        $height = " height='" . $images[$productid . '_' . $sequence]['height'] . "' ";
                    }
                    $Details['P'][$i]['PRODUCTS_IMAGENAME'] = "<img src='" . self::$site_url . '/' . $images[$productid . '_' . $sequence]['src'] . "' " . $width . $height . "/>";
                } elseif (isset($bacImgs[$productid . '_' . $sequence])) {
                    $Details['P'][$i]['PRODUCTS_IMAGENAME'] = "<img src='" . self::$site_url . '/' . $bacImgs[$productid . '_' . $sequence]['src'] . "' width='83' />";
                }

                $taxtotal = $tax_avg_value = '0.00';

                if ($taxtype == 'individual') {
                    //$tax_info_message = $mod_strings['LBL_TOTAL_AFTER_DISCOUNT'] . ' = $totalAfterDiscount \\n';
                    $tax_details = getTaxDetailsForProduct($productid, 'all');
                    $Tax_Values = array();
                    $VatPercent = array();

                    for ($tax_count = 0; $tax_count < php7_count($tax_details); $tax_count++) {
                        $tax_name = $tax_details[$tax_count]['taxname'];
                        $tax_label = $tax_details[$tax_count]['taxlabel'];
                        $lineItemId = $this->getItemIdBySequence($i, $focus->id);
                        $tax_value = getInventoryProductTaxValue($focus->id, $productid, $tax_name);

                        $individual_taxamount = $totalAfterDiscount * $tax_value / 100;
                        $taxtotal = $taxtotal + $individual_taxamount;

                        if ($tax_name != '') {
                            $vatsum = round($individual_taxamount, self::$decimals);
                            $total_vatsum += $vatsum;
                            $this->setVatBlock($tax_name . '-' . $tax_value, $tax_label, $tax_value, $totalAfterDiscount, $vatsum);
                            $Tax_Values[] = $tax_value;
                            $VatPercent[] = $this->formatNumberToPDF($tax_value);
                        }
                    }

                    if (php7_count($Tax_Values) > 0) {
                        $tax_avg_value = array_sum($Tax_Values);
                    }

                    $VatPercentString = implode(', ', array_filter($VatPercent));
                    $Details['P'][$i]['VATPERCENT_INDIVIDUAL'] = !empty($VatPercentString) ? $VatPercentString : '0';
                    $Details['TOTAL']['VATPERCENT_INDIVIDUAL'][] = $VatPercentString;
                }

                $Details['P'][$i]['PRODUCTVATPERCENT'] = $this->formatNumberToPDF($tax_avg_value);
                $Details['P'][$i]['PRODUCTVATSUM'] = $this->formatNumberToPDF($taxtotal);

                $result1 = self::$db->pquery(
                    'SELECT * FROM vtiger_inventoryproductrel WHERE id=? AND sequence_no=?',
                    array(self::$focus->id, $sequence)
                );
                $row1 = self::$db->fetchByAssoc($result1, 0);

                $tabid = getTabid($module);
                $result2 = self::$db->pquery(
                    'SELECT fieldname, fieldlabel, columnname, uitype, typeofdata FROM vtiger_field WHERE tablename = ? AND tabid = ?',
                    array('vtiger_inventoryproductrel', $tabid)
                );

                while ($row2 = self::$db->fetchByAssoc($result2)) {
                    if (!isset($Details['P'][$i]['PRODUCT_' . strtoupper($row2['fieldname'])])) {
                        $UITypes = array();
                        $value = $row1[$row2['columnname']];

                        if ($value != '') {
                            $uitype_name = $this->getUITypeName($row2['uitype'], $row2['typeofdata']);

                            if ($uitype_name != '') {
                                $UITypes[$uitype_name][] = $row2['fieldname'];
                            }

                            $value = $this->getFieldValue($focus, $module, $row2['fieldname'], $value, $UITypes);
                        }

                        $Details['P'][$i]['PRODUCT_' . strtoupper($row2['fieldname'])] = $value;
                    }
                }
            }
        }

        $Details['TOTAL']['TOTALWITHOUTVAT'] = $this->formatNumberToPDF($totalAfterDiscount_subtotal);

        if ($taxtype == 'individual') {
            $Details['TOTAL']['TAXTOTAL'] = $this->formatNumberToPDF($total_vatsum);
        }

        $finalDiscountPercent = '';
        $total_vat_percent = 0;

        if ($taxtype !== 'individual') {
            if (php7_count($finalDetails['taxes']) > 0) {
                foreach ($finalDetails['taxes'] as $TAX) {
                    $this->setVatBlock($TAX['taxname'], $TAX['taxlabel'], $TAX['percentage'], $finalDetails['totalAfterDiscount'], $TAX['amount']);
                    $total_vat_percent += $TAX['percentage'];
                }
            }
        }

        $Details['TOTAL']['TAXTOTALPERCENT'] = $this->formatNumberToPDF($total_vat_percent);

        $hdnDiscountPercent = (float)$focus->column_fields['hdnDiscountPercent'];
        $hdnDiscountAmount = (float)$focus->column_fields['discount_amount'];

        if (!empty($hdnDiscountPercent)) {
            $finalDiscountPercent = $hdnDiscountPercent;
        }

        $Details['TOTAL']['FINALDISCOUNTPERCENT'] = $this->formatNumberToPDF($finalDiscountPercent);
        $Details['TOTAL']['VATBLOCK'] = $this->getVatBlock();

        return $Details;
    }

    private function retrieve_entity_infoCustom(&$focus, $record, $module)
    {
        $result = array();

        foreach ($focus->tab_name_index as $table_name => $index) {
            $result[$table_name] = self::$db->pquery('SELECT * FROM ' . $table_name . ' WHERE ' . $index . '=?', array($record));
        }

        $tabid = getTabid($module);
        $result1 = self::$db->pquery('SELECT fieldname, fieldid, fieldlabel, columnname, tablename, uitype, typeofdata, presence FROM vtiger_field WHERE tabid=?', array($tabid));
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

    /**
     * @throws Exception
     */
    public function getItemIdBySequence($sequence, $record)
    {
        $result = self::$db->pquery('SELECT lineitem_id FROM vtiger_inventoryproductrel WHERE id=? AND sequence_no=?', [$record, $sequence]);

        return (int)self::$db->query_result($result, 0, 'lineitem_id');
    }

    private function getFieldValue($efocus, $emodule, $fieldname, $value, $UITypes, $inventory_currency = false)
    {
        return $this->getFieldValueUtils($efocus, $emodule, $fieldname, $value, $UITypes, $inventory_currency, self::$ignored_picklist_values, self::$def_charset, self::$decimals, self::$decimal_point, self::$thousands_separator, self::$language, self::$focus->id);
    }

    private function convertInventoryModules()
    {
        $ProductParts = array();
        $result = self::$db->pquery('select * from vtiger_inventoryproductrel where id=?', array(self::$focus->id));
        $num_rows = self::$db->num_rows($result);

        if ($num_rows > 0) {
            $Products = $this->replaceInventoryDetailsBlock(self::$module, self::$focus);
            $Blocks = array('', 'PRODUCTS_', 'SERVICES_');

            foreach ($Blocks as $block_type) {
                if (strpos(self::$content, '#PRODUCTBLOC_' . $block_type . 'START#') !== false && strpos(self::$content, '#PRODUCTBLOC_' . $block_type . 'END#') !== false) {
                    $tableTag = $this->convertProductBlock($block_type);
                    $breaklines_array = $this->getInventoryBreaklines(self::$focus->id);

                    $breaklines = $breaklines_array['products'];
                    $show_header = $breaklines_array['show_header'];
                    $show_subtotal = $breaklines_array['show_subtotal'];

                    $breakline_type = '';

                    if (php7_count($breaklines)) {
                        if ($tableTag !== false) {
                            $breakline_type = '</table>' . self::$pagebreak . $tableTag['tag'];

                            if ($show_header == 1) {
                                $breakline_type .= $tableTag['header'];
                            }

                            if ($show_subtotal == 1) {
                                $breakline_type = $tableTag['subtotal'] . $breakline_type;
                            } else {
                                $breakline_type = $tableTag['footer'] . $breakline_type;
                            }
                        } else {
                            $breakline_type = self::$pagebreak;
                        }
                    }


                    $ExplodedPdf = array();
                    $Exploded = explode('#PRODUCTBLOC_' . $block_type . 'START#', self::$content);
                    $ExplodedPdf[] = $Exploded[0];

                    for ($iterator = 1; $iterator < php7_count($Exploded); $iterator++) {
                        $SubExploded = explode('#PRODUCTBLOC_' . $block_type . 'END#', $Exploded[$iterator]);

                        foreach ($SubExploded as $part) {
                            $ExplodedPdf[] = $part;
                        }

                        $highestpartid = $iterator * 2 - 1;
                        $ProductParts[$highestpartid] = $ExplodedPdf[$highestpartid];
                        $ExplodedPdf[$highestpartid] = '';
                    }

                    if ($Products['P']) {
                        foreach ($Products['P'] as $Product_Details) {
                            if (($block_type == 'PRODUCTS_' && empty($Product_Details['PRODUCTS_CRMID'])) || ($block_type == 'SERVICES_' && empty($Product_Details['SERVICES_CRMID']))) {
                                continue;
                            }

                            foreach ($ProductParts as $productpartid => $productparttext) {
                                $breakline = '';
                                $breakLineId = $Product_Details['RECORD_ID'] . '_' . $Product_Details['PRODUCTSEQUENCE'];

                                if (!empty($breakline_type) && isset($breaklines[$breakLineId])) {
                                    $breakline = $breakline_type;
                                }

                                $productparttext .= $breakline;

                                foreach ($Product_Details as $coll => $value) {
                                    $productparttext = str_replace('$' . strtoupper($coll) . '$', $value, $productparttext);
                                }

                                $ExplodedPdf[$productpartid] .= $productparttext;
                            }
                        }
                    }
                    
                    self::$content = implode('', $ExplodedPdf);
                }
            }

            self::$rep['$VATPERCENT_INDIVIDUAL$'] = implode(', ', array_filter((array)$Products['TOTAL']['VATPERCENT_INDIVIDUAL']));
            $this->replaceContent();
        }
    }

    private function convertProductBlock($block_type = '')
    {
        PDFMaker_PDFContent_Model::includeSimpleHtmlDom();
        $html = str_get_html(self::$content);
        $tableDOM = false;

        if (is_array($html->find('td'))) {
            foreach ($html->find('td') as $td) {
                if (trim($td->plaintext) == '#PRODUCTBLOC_' . $block_type . 'START#') {
                    $td->parent->outertext = '#PRODUCTBLOC_' . $block_type . 'START#';
                    $oParent = $td->parent;

                    while ($oParent->tag != 'table') {
                        $oParent = $oParent->parent;
                    }

                    [$tag] = explode('>', $oParent->outertext, 2);

                    $header = $oParent->first_child();

                    if ($header->tag != 'tr') {
                        $header = $header->children(0);
                    }

                    $header_style = '';

                    if (is_object($td->parent->prev_sibling()->children[0])) {
                        $header_style = $td->parent->prev_sibling()->children[0]->getAttribute('style');
                    }

                    $footer_tag = '<tr>';

                    if (isset($header_style)) {
                        $StyleHeader = explode(';', $header_style);

                        if (isset($StyleHeader)) {
                            foreach ($StyleHeader as $style_header_tag) {
                                if (strpos($style_header_tag, 'border-top') == true) {
                                    $footer_tag .= "<td colspan='" . $td->getAttribute('colspan') . "' style='" . $style_header_tag . "'>&nbsp;</td>";
                                }
                            }
                        }
                    } else {
                        $footer_tag .= "<td colspan='" . $td->getAttribute('colspan') . "' style='border-top:1px solid #000000;'>&nbsp;</td>";
                    }

                    $footer_tag .= '</tr>';
                    $var = $td->parent->next_sibling()->last_child()->plaintext;

                    $subtotal_tr = '';

                    if (strpos($var, 'TOTAL') !== false) {
                        if (is_object($td)) {
                            $style_subtotal = $td->getAttribute('style');
                        }

                        $style_subtotal_tag = $style_subtotal_endtag = '';

                        if (isset($td->innertext)) {
                            [$style_subtotal_tag, $style_subtotal_endtag] = explode('#PRODUCTBLOC_' . $block_type . 'START#', $td->innertext);
                        }

                        if (isset($style_subtotal)) {
                            $StyleSubtotal = explode(';', $style_subtotal);
                            if (isset($StyleSubtotal)) {
                                foreach ($StyleSubtotal as $style_tag) {
                                    if (strpos($style_tag, 'border-top') == true) {
                                        $tag .= " style='" . $style_tag . "'";
                                        break;
                                    }
                                }
                            }
                        } else {
                            $style_subtotal = '';
                        }

                        $subtotal_tr = '<tr>';

                        $preg_cond = '/\$([A-Z]*)\$/';

                        preg_match($preg_cond, $var, $var_array);
                        $var_text = $var_array[1];

                        $var_split = preg_split($preg_cond, $var);


                        $subtotal_tr .= "<td colspan='" . ($td->getAttribute('colspan') - 1) . "' style='" . $style_subtotal . ";border-right:none'>" . $style_subtotal_tag . '%G_Subtotal%' . $style_subtotal_endtag . '</td>';
                        $subtotal_tr .= "<td align='right' nowrap='nowrap' style='" . $style_subtotal . "'>" . $style_subtotal_tag . $var_split[0] . '$' . $var_text . '_SUBTOTAL$' . $var_split[1] . $style_subtotal_endtag . '</td>';
                        $subtotal_tr .= '</tr>';
                    }

                    $tag .= '>';
                    $tableDOM['tag'] = $tag;
                    $tableDOM['header'] = $header->outertext;
                    $tableDOM['footer'] = $footer_tag;
                    $tableDOM['subtotal'] = $subtotal_tr;
                }

                if (trim($td->plaintext) == '#PRODUCTBLOC_' . $block_type . 'END#') {
                    $td->parent->outertext = '#PRODUCTBLOC_' . $block_type . 'END#';
                }
            }

            self::$content = $html->save();
        }

        return $tableDOM;
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
                        $prev_sibling_styles = array();

                        while ($prev_sibling != null) {
                            $prev_sibling_styles[] = $this->getDOMElementAtts($prev_sibling);
                            $prev_sibling = $prev_sibling->prev_sibling();
                        }

                        $next_sibling = $tmpPb->next_sibling();
                        $next_sibling_styles = array();

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

    public static function getSiteUrl() {
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
            $user_res = self::$db->pquery('SELECT * FROM vtiger_users WHERE id = ?', array(self::$focus->column_fields['assigned_user_id']));
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
            array(self::$focus->id)
        );
        $modifiedby_user_row = self::$db->fetchByAssoc($modifiedby_user_res);
        $this->replaceUserData($modifiedby_user_row['id'], $modifiedby_user_row, 'M_USER');
        $modifiedby_user_focus = CRMEntity::getInstance('Users');
        $modifiedby_user_focus->id = $modifiedby_user_row['id'];
        $this->retrieve_entity_infoCustom($modifiedby_user_focus, $modifiedby_user_focus->id, 'Users');
        $this->replaceFieldsToContent('Users', $modifiedby_user_focus, true, false, 'M_');

        $smcreatorid_user_res = self::$db->pquery(
            'SELECT vtiger_users.* FROM vtiger_users INNER JOIN vtiger_crmentity ON vtiger_crmentity.creator_user_id = vtiger_users.id  WHERE  vtiger_crmentity.crmid = ?',
            array(self::$focus->id)
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
        $Fields = array(
            'FIRSTNAME' => 'first_name',
            'LASTNAME' => 'last_name',
            'EMAIL' => 'email1',
            'TITLE' => 'title',
            'FAX' => 'phone_fax',
            'DEPARTMENT' => 'department',
            'OTHER_EMAIL' => 'email2',
            'PHONE' => 'phone_work',
            'YAHOOID' => 'yahoo_id',
            'MOBILE' => 'phone_mobile',
            'HOME_PHONE' => 'phone_home',
            'OTHER_PHONE' => 'phone_other',
            'SIGHNATURE' => 'signature',
            'NOTES' => 'description',
            'ADDRESS' => 'address_street',
            'COUNTRY' => 'address_country',
            'CITY' => 'address_city',
            'ZIP' => 'address_postalcode',
            'STATE' => 'address_state'
        );

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

        $result = $db->pquery($sql, array($module));

        return $db->fetchByAssoc($result, 1);
    }

    private function getInventoryTaxTypeCustom($module, $focus)
    {
        if (!empty($focus->id)) {
            $res = self::$db->pquery('SELECT taxtype FROM ' . self::$inventory_table_array[$module] . ' WHERE ' . self::$inventory_id_array[$module] . '=?', array($focus->id));
            return self::$db->query_result($res, 0, 'taxtype');
        }

        return '';
    }

    public static function includeSimpleHtmlDom()
    {
        require_once 'vendor/simplehtmldom/simplehtmldom/simple_html_dom.php';
    }
}