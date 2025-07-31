<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_EMAILContent_Model extends EMAILMaker_EMAILContentUtils_Model
{
    private static $is_inventory_module = false;
    private static $Email_Images = array();
    private static $templateid;
    private static $recipientid;
    private static $recipientmodule;
    private static $module;
    private static $recordId;
    private static $recordModel;
    private static $language;

    /** @var CRMEntity */
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
    private static $subject;
    private static $body;
    private static $preview;
    private static $content;
    private static $templatename;
    private static $type;
    private static $section_sep = "&#%ITS%%%@@@%%%ITS%#&";
    private static $rep = [];
    private static $inventory_table_array = array();
    private static $inventory_id_array = array();
    private static $org_colsOLD = array();
    private static $relBlockModules = array();
    private static $load_related_documents = false;
    private static $folders_related_documents = [];

    public $EMAILMaker = false;

    private $vatBlock = [];

    public function __construct()
    {
        if (!defined('LOGO_PATH')) {
            define("LOGO_PATH", '/test/logo/');
        }

        self::$db = PearDatabase::getInstance();
        self::$def_charset = vglobal('default_charset');
        $mod_strings_array = Vtiger_Language_Handler::getModuleStringsFromFile(self::$language, self::$module);
        self::$mod_strings = $mod_strings_array['languageStrings'];

        $this->EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $this->getIgnoredPicklistValues();

        self::$rowbreak = "<rowbreak />";
        self::$site_url = trim(vglobal('site_URL'), "/");
        self::$inventory_table_array = $this->getInventoryTableArray();
        self::$inventory_id_array = $this->getInventoryIdArray();

        if(empty(self::$is_inventory_module)) {
            self::$is_inventory_module = [];
        }

        self::$is_inventory_module[self::$module] = InventoryItem_Utils_Helper::usesInventoryItem(self::$module);
        self::$org_colsOLD = $this->getOrgOldCols();
    }

    private function getIgnoredPicklistValues()
    {
        $result = self::$db->pquery("SELECT value FROM vtiger_emakertemplates_ignorepicklistvalues", array());
        while ($row = self::$db->fetchByAssoc($result)) {
            self::$ignored_picklist_values[] = $row["value"];
        }
    }

    /**
     * @param int $templateId
     * @param string $language
     * @param string|null $moduleName
     * @param int|null $recordId
     * @param int|null $recipientRecordId
     * @param string|null $recipientModuleName
     * @return EMAILMaker_EMAILContent_Model
     */
    public static function getInstanceById(int $templateId, string $language, string $moduleName = null, int $recordId = null, int $recipientRecordId = null, string $recipientModuleName = null): self
    {
        $self = self::getInstance($moduleName, $recordId, $language, $recipientRecordId, $recipientModuleName);

        if (!empty($recordId)) {
            self::$focus->retrieve_entity_info($recordId, $moduleName);
            self::$focus->id = $recordId;
        }

        if (!empty($templateId)) {
            self::$templateid = $templateId;
            $self->getTemplateData();
        }

        return $self;
    }

    private function getTemplateData()
    {
        $result = self::$db->pquery('SELECT *  FROM vtiger_emakertemplates WHERE templateid=?', [self::$templateid]);
        $data = self::$db->fetch_array($result);
        $body = $data['body'];

        if (vtlib_isModuleActive('ITS4YouStyles')) {
            $body = (new ITS4YouStyles_Module_Model())->addStyles($body, self::$templateid, "EMAILMaker");
        }

        $this->setBody($body);
        $this->setSubject($data['subject']);
        self::$templatename = $data['templatename'];
        self::$load_related_documents = !empty($data['load_related_documents']);
        self::$folders_related_documents = array_filter(explode(',', $data['folders_related_documents']));
    }

    private function getDecimalData()
    {
        $result2 = self::$db->pquery("SELECT * FROM vtiger_emakertemplates_settings", array());
        $data = self::$db->fetch_array($result2);

        self::$decimal_point = html_entity_decode($data["decimal_point"], ENT_QUOTES);
        self::$thousands_separator = html_entity_decode(($data["thousands_separator"] != "sp" ? $data["thousands_separator"] : " "), ENT_QUOTES);
        self::$decimals = $data["decimals"];
    }

    /**
     * @param string $moduleName
     * @param int $recordId
     * @param string $language
     * @param int|null $recipientRecordId
     * @param string|null $recipientModuleName
     * @return self
     * @throws Exception
     */
    public static function getInstance(string $moduleName, int $recordId, string $language, int $recipientRecordId = null, string $recipientModuleName = null): self
    {
        self::$language = $language;
        self::$module = $moduleName;

        if (!empty($moduleName)) {
            self::$focus = CRMEntity::getInstance($moduleName);

            if (!empty($recordId)) {
                self::$focus->retrieve_entity_info($recordId, $moduleName);
                self::$focus->id = $recordId;
            }
        }

        self::$recipientid = $recipientRecordId;
        self::$recipientmodule = $recipientModuleName;

        $self = new self();
        $self->getDecimalData();

        return $self;
    }

    public function replaceGeneralFields()
    {
        foreach (EMAILMaker_Fields_Model::getGeneralFieldsOptions() as $variable => $label) {
            self::$rep['$' . $variable . '$'] = $this->getGeneralFieldsValue($variable);
        }
    }

    public function retrieveRecordId()
    {
        if (self::$focus && !empty(self::$focus->id)) {
            self::$recordId = self::$focus->id;
        }
    }

    public function retrieveRecordModel()
    {
        if (!self::$recordModel && !empty(self::$recordId)) {
            self::$recordModel = Vtiger_Record_Model::getInstanceById(self::$recordId, self::$module);
        }
    }

    public function getRecordModel()
    {
        $this->retrieveRecordId();
        $this->retrieveRecordModel();

        return self::$recordModel;
    }

    /**
     * @param string $fieldName
     * @return string
     */
    public function getGeneralFieldsValue($fieldName)
    {
        global $site_URL, $PORTAL_URL, $current_user, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID;

        $siteUrl = rtrim($site_URL, '/') . '/';
        $portalUrl = rtrim($PORTAL_URL, '/') . '/';
        $recordModel = $this->getRecordModel();
        $moduleName = self::$module;
        $recordId = null;

        if ($recordModel) {
            $recordId = $recordModel->getId();
        }

        switch ($fieldName) {
            case 'crmdetailviewurl':
                return $recordModel ? $siteUrl . $recordModel->getDetailViewUrl() : '';
            case 'portaldetailviewurl':
                $recordIdNames = array(
                    'HelpDesk' => 'ticketid',
                    'Faq' => 'faqid',
                    'Products' => 'productid',
                );
                $recordIdName = isset($recordIdNames[$moduleName]) ? $recordIdNames[$moduleName] : 'id';

                return $recordId ? $portalUrl . 'index.php?module=' . $moduleName . '&action=index&' . $recordIdName . '=' . $recordId . '&status=true' : '';
            case 'dbtimezone':
                return DateTimeField::getDBTimeZone();
            case 'siteurl':
                return $siteUrl;
            case 'portalurl':
                return $portalUrl;
            case 'support_name':
                return $HELPDESK_SUPPORT_NAME;
            case 'support_email_id':
                return $HELPDESK_SUPPORT_EMAIL_ID;
            case 'portalpdfurl':
                return $recordId ? $portalUrl . 'index.php?module=' . $moduleName . '&action=index&id=' . $recordId . '&downloadfile=true' : '';
            default:
                return '';
        }
    }

    public function replaceCurrentDate() {
        self::$rep['##DD-MM-YYYY##'] = date('d-m-Y');
        self::$rep['##DD.MM.YYYY##'] = date('d.m.Y');
        self::$rep['##MM-DD-YYYY##'] = date('m-d-Y');
        self::$rep['##YYYY-MM-DD##'] = date('Y-m-d');
        self::$rep['##HH:II:SS##'] = date('h:i:s');
        self::$rep['##HH:II##'] = date('h:i');
    }

    /**
     * @throws Exception
     */
    public function getContent($convert_recipient = true, $convert_source = true, $fixImg = false)
    {
        self::$content = self::$subject . self::$section_sep;
        self::$content .= self::$body;

        $this->replaceGeneralFields();
        $this->replaceCurrentDate();

        self::$rep["&nbsp;"] = " ";
        self::$rep["##PAGE##"] = "{PAGENO}";
        self::$rep["##PAGES##"] = "{nb}";
        self::$rep["src='"] = "src='" . vglobal('img_root_directory');

        $moduleToLower = strtolower(self::$module);

        if ($convert_source) {
            self::$rep['$s-' . $moduleToLower . '-crmid$'] = self::$focus->id;
            self::$rep['$s-' . $moduleToLower . '_crmid$'] = self::$focus->id;

            $createdTime = new DateTimeField(self::$focus->column_fields['createdtime']);
            $displayValueCreated = $createdTime->getDisplayDateTimeValue();
            $modifiedTime = new DateTimeField(self::$focus->column_fields['modifiedtime']);
            $displayValueModified = $modifiedTime->getDisplayDateTimeValue();
        }

        self::$rep['$s-' . $moduleToLower . '-createdtime-datetime$'] = $displayValueCreated;
        self::$rep['$s-' . $moduleToLower . '-modifiedtime-datetime$'] = $displayValueModified;
        self::$rep['$s-' . $moduleToLower . '_createdtime_datetime$'] = $displayValueCreated;
        self::$rep['$s-' . $moduleToLower . '_modifiedtime_datetime$'] = $displayValueModified;

        if ($convert_source) {
            $this->convertEntityImages();
        }
        $this->replaceContent();
        self::$content = html_entity_decode(self::$content, ENT_QUOTES, self::$def_charset);  //because of encrypting it is here

        $recipient_id = self::$recipientid;
        $recipient_module = self::$recipientmodule;

        if ($convert_recipient && $recipient_id != "" && $recipient_module != "") {
            $recipient_module = self::$recipientmodule;
            $focus_recipient = CRMEntity::getInstance($recipient_module);
            $focus_recipient->id = $recipient_id;

            $this->retrieve_entity_infoCustom($focus_recipient, $focus_recipient->id, $recipient_module);
            self::$rep["$" . strtolower($recipient_module) . "-crmid$"] = $focus_recipient->id;
            self::$rep["$" . strtolower($recipient_module) . "_crmid$"] = $focus_recipient->id;
            $this->replaceContent();
            $this->replaceFieldsToContent($recipient_module, $focus_recipient, false, false, true);
            $this->replaceContent();
        }
        if ($convert_source) {
            $this->convertRelatedModule();
            $this->convertRelatedBlocks();
            $this->replaceFieldsToContent(self::$module, self::$focus);
            $this->convertInventoryModules();

            if ($this->focus->column_fields["assigned_user_id"] == "" && $this->focus->id != "") {
                $result = self::$db->pquery("SELECT assigned_user_id FROM vtiger_crmentity WHERE crmid=?", array(self::$focus->id));
                $this->focus->column_fields["assigned_user_id"] = self::$db->query_result($result, 0, "assigned_user_id");
            }

            self::$content = $this->convertListViewBlock(self::$content);
        }
        $this->handleRowbreak();
        $this->replaceUserCompanyFields($convert_source);
        $this->replaceLabels();
        $this->convertHideTR();

        if (strtoupper(self::$def_charset) != "UTF-8") {
            self::$content = iconv(self::$def_charset, "UTF-8//TRANSLIT", self::$content);
        }

        $this->replaceCustomFunctions();
        $EMAIL_content = array();
        if ($convert_recipient) {
            $Clear_Modules = array("Accounts", "Contacts", "Vendors", "Leads", "Users");
            foreach ($Clear_Modules as $clear_module) {
                if ($clear_module != $recipient_module) {
                    $tabid1 = getTabId($clear_module);
                    $field_inf = "_fieldinfo_cache";
                    $temp = &VTCacheUtils::$$field_inf;
                    unset($temp[$tabid1]);
                    $focus1 = CRMEntity::getInstance($clear_module);
                    self::$rep["$" . strtolower($clear_module) . "-crmid$"] = "";
                    self::$rep["$" . "s-" . strtolower($clear_module) . "-crmid$"] = "";
                    self::$rep["$" . strtolower($clear_module) . "_crmid$"] = "";
                    self::$rep["$" . "s-" . strtolower($clear_module) . "_crmid$"] = "";
                    $this->replaceFieldsToContent($clear_module, $focus1, false, false, true);
                    $this->replaceFieldsToContent($clear_module, $focus1);
                    unset($focus1);
                }
                $this->replaceContent();
            }
            $this->replaceCustomFunctions("_after");
            [$EMAIL_content["pre_subject"], $EMAIL_content["pre_body"]] = explode(self::$section_sep, self::$content);
        }
        if ($convert_recipient || $fixImg) {
            $this->fixImg();
        }
        [$EMAIL_content["subject"], $EMAIL_content["body"]] = explode(self::$section_sep, self::$content);

        $this->setSubject($EMAIL_content["subject"]);
        $this->setBody($EMAIL_content["body"]);
        $this->setPreview($EMAIL_content);
    }

    /**
     * @param string $type
     */
    protected function convertHideTR(string $type = ''): void
    {
        $regex = '/<tr\b[^<]*>[^<]*(?:<(?!tr\b)[^<]*)*#' . $type . 'HIDETR#[^<]*(?:<(?!\/tr>)[^<]*)*<\/tr>/';

        self::$content = preg_replace($regex, '', self::$content);
    }

    //private function replaceFieldsToContent($emodule, $efocus, $is_related = false, $inventory_currency = false, $is_recipient = false, $is_l_user = false)

    public function setSubject($subject)
    {
        self::$subject = $subject;
    }

    public function setBody($body)
    {
        self::$body = $body;
    }

    public function setPreview($EMAIL_content)
    {
        if (isset($EMAIL_content["pre_body"])) {
            self::$preview = $EMAIL_content["pre_body"];
        } else {
            self::$preview = $EMAIL_content["body"];
        }
    }

    private function convertEntityImages()
    {
        $recordData = self::$focus->column_fields;

        self::$rep['$s-users-imagename$'] = $this->getUserImage($recordData['assigned_user_id'], self::$site_url);
        self::$rep['$c-users-imagename$'] = $this->getUserImage($recordData['creator'] ? $recordData['creator'] : $recordData['creator_user_id'], self::$site_url);
        self::$rep['$m-users-imagename$'] = $this->getUserImage($recordData['modifiedby'], self::$site_url);
        self::$rep['$l-users-imagename$'] = $this->getUserImage($_SESSION['authenticated_user_id'], self::$site_url);

        switch (self::$module) {
            case "Contacts":
                self::$rep['$s-contacts-imagename$'] = $this->getContactImage(self::$focus->id, self::$site_url);
                break;
            case "Products":
                self::$rep['$s-products-imagename$'] = $this->getProductImage(self::$focus->id, self::$site_url);
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

    private function retrieve_entity_infoCustom(&$focus, $record, $module)
    {
        $result = array();
        foreach ($focus->tab_name_index as $table_name => $index) {
            $result[$table_name] = self::$db->pquery("SELECT * FROM " . $table_name . " WHERE " . $index . "=?", array($record));
        }
        $tabid = getTabid($module);
        $result1 = self::$db->pquery("SELECT fieldname, fieldid, fieldlabel, columnname, tablename, uitype, typeofdata, presence FROM vtiger_field WHERE tabid=?", array($tabid));
        $noofrows = self::$db->num_rows($result1);

        if ($noofrows) {
            while ($resultrow = self::$db->fetch_array($result1)) {
                $fieldcolname = $resultrow["columnname"];
                $tablename = $resultrow["tablename"];
                $fieldname = $resultrow["fieldname"];

                $fld_value = "";
                if (isset($result[$tablename])) {
                    $fld_value = self::$db->query_result($result[$tablename], 0, $fieldcolname);
                }
                $focus->column_fields[$fieldname] = $fld_value;
            }
        }
        $focus->column_fields["record_id"] = $record;
        $focus->column_fields["record_module"] = $module;
    }

    /**
     * @param string $module
     * @param CRMEntity $focus
     * @param bool|string $isRelated
     * @param bool|string $inventoryCurrency
     * @param bool $isRecipient
     * @param string $related
     * @return array|bool
     * @throws Exception
     */
    private function replaceFieldsToContent($module, $focus, $isRelated = false, $inventoryCurrency = false, $isRecipient = false, $related = 'r-')
    {
        $convEntity = $module;
        $record = !empty($focus->id) ? $focus->id : null;
        $recordModel = $record ? Vtiger_Record_Model::getInstanceById($record, $module) : false;

        if ($inventoryCurrency) {
            $inventory_content = array();
        }

        if (false === $isRelated) {
            $related = $isRecipient ? '' : 's-';
        } elseif (true !== $isRelated) {
            $convEntity = $isRelated . '-' . $convEntity;
        }

        $structureValues = $this->getRecordStructureValues($module, $record);

        foreach ($structureValues as $blockFields) {
            foreach ($blockFields as $fieldModel) {
                $fieldName = $fieldModel->get('name');
                $fieldLabel = $fieldModel->get('label');
                $fieldDisplayValue = '';

                if (!empty($focus->id)) {
                    $fieldDisplayValue = $this->getFieldDisplayValue($fieldModel, $inventoryCurrency);
                }

                $label = self::getTranslate($fieldLabel, $module);

                self::$rep['%' . $related . strtolower($convEntity . '-' . $fieldName) . '%'] = $label;
                self::$rep['%' . strtolower($convEntity . '-' . $fieldName) . '%'] = $label;
                self::$rep['%M_' . $fieldLabel . '%'] = $label;

                if ($inventoryCurrency) {
                    $inventory_content[strtoupper($module . '-' . $fieldName)] = $fieldDisplayValue;
                    $inventory_content[strtoupper($module . '_' . $fieldName)] = $fieldDisplayValue;
                } else {
                    self::$rep['$' . $related . strtolower($convEntity . '-' . $fieldName) . '$'] = $fieldDisplayValue;
                }
            }
        }

        if ($inventoryCurrency) {

            return $inventory_content;
        } else {
            $this->replaceContent();
        }

        return true;
    }

    /**
     * @param array $values
     * @param string $module
     * @param null|int $record
     * @return array
     */
    public function updateStructureValues(&$values, $module, $record = null)
    {
        $structureValues = $this->getRecordStructureValues($module, $record);

        foreach ($structureValues as $blockLabel => $blockFields) {
            foreach ($blockFields as $fieldName => $field) {
                $values[$blockLabel][$fieldName] = $field;
            }
        }

        return $values;
    }

    /**
     * @param string $module
     * @param null|int $record
     * @return array
     */
    public function getRecordStructureValues($module, $record = null)
    {
        return $this->getRecordStructure($module, $record)->getStructure();
    }

    /**
     * @param $module
     * @param null|int $record
     * @return Vtiger_RecordStructure_Model
     */
    public function getRecordStructure($module, $record = null)
    {
        if ($record) {
            $detailViewModel = Vtiger_DetailView_Model::getInstance($module, $record);
            $recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($detailViewModel->getRecord(), '');
        } else {
            $moduleModel = Vtiger_Module_Model::getInstance($module);
            $recordStructure = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel, '');
        }

        return $recordStructure;
    }

    public function getFieldDisplayValue($field, $inventoryCurrency)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $fieldValue = $field->get('fieldvalue');
        $fieldDataType = $field->getFieldDataType();

        switch ($fieldDataType) {
            case 'reference':
            case 'owner':
            case 'email':
                $fieldDisplayValue = $field->getEditViewDisplayValue($fieldValue);
                break;
            case 'double':
            case 'currency':
            case 'percentage':
                $fieldDisplayValue = $this->formatNumberToEMAIL($fieldValue);
                break;
            case 'text':
                $fieldDisplayValue = decode_html($field->getDisplayValue($fieldValue));
                break;
            default:
                if (self::$focus && !empty(self::$focus->id)) {
                    $fieldDisplayValue = $field->getDisplayValue($fieldValue, self::$focus->id);
                } else {
                    $fieldDisplayValue = $field->getDisplayValue($fieldValue);
                }
                break;
        }

        return $fieldDisplayValue;
    }

    private function formatNumberToEMAIL($value)
    {
        $number = "";
        if (is_numeric($value)) {
            $number = number_format($value, self::$decimals, self::$decimal_point, self::$thousands_separator);
        }
        return $number;
    }

    private function convertRelatedModule()
    {
        $field_inf = "_fieldinfo_cache";
        $fieldModRel = $this->GetFieldModuleRel();
        $module_tabid = getTabId(self::$module);
        $Query_Parr = array('3', '64', $module_tabid);
        $sql = 'SELECT fieldid, fieldname, uitype, columnname FROM vtiger_field WHERE (displaytype != ? OR fieldid = ?) AND tabid = ?';
        $result = self::$db->pquery($sql, $Query_Parr);
        $num_rows = self::$db->num_rows($result);

        if ($num_rows > 0) {
            while ($row = self::$db->fetch_array($result)) {
                $columnname = $row["columnname"];
                $fk_record = self::$focus->column_fields[$row["fieldname"]];
                $related_module = $this->getUITypeRelatedModule($row["uitype"], $fk_record);

                if (!empty($related_module) && vtlib_isModuleActive($related_module)) {
                    $tabid = getTabId($related_module);
                    $temp = &VTCacheUtils::$$field_inf;
                    unset($temp[$tabid]);
                    $focus2 = CRMEntity::getInstance($related_module);
                    if ($fk_record != "" && $fk_record != "0") {
                        $result_delete = self::$db->pquery("SELECT deleted FROM vtiger_crmentity WHERE crmid=? AND deleted=?", array($fk_record, "0"));
                        if (self::$db->num_rows($result_delete) > 0) {
                            $focus2->retrieve_entity_info($fk_record, $related_module);
                            $focus2->id = $fk_record;
                        }
                    }
                    self::$rep["$" . "r-" . strtolower($related_module) . "-crmid$"] = $focus2->id;
                    self::$rep["$" . "r-" . strtolower($columnname) . "-crmid$"] = $focus2->id;
                    self::$rep["$" . "r-" . strtolower($related_module) . "_crmid$"] = $focus2->id;
                    self::$rep["$" . "r-" . strtolower($columnname) . "_crmid$"] = $focus2->id;

                    $createdTime = new DateTimeField($focus2->column_fields['createdtime']);
                    $displayValueCreated = $createdTime->getDisplayDateTimeValue();
                    $modifiedTime = new DateTimeField($focus2->column_fields['modifiedtime']);
                    $displayValueModified = $modifiedTime->getDisplayDateTimeValue();

                    self::$rep["$" . "r-" . strtolower($related_module) . "-createdtime-datetime$"] = $displayValueCreated;
                    self::$rep["$" . "r-" . strtolower($columnname) . "-createdtime-datetime$"] = $displayValueCreated;
                    self::$rep["$" . "r-" . strtolower($related_module) . "-modifiedtime-datetime$"] = $displayValueModified;
                    self::$rep["$" . "r-" . strtolower($columnname) . "-modifiedtime-datetime$"] = $displayValueModified;
                    self::$rep["$" . "r-" . strtolower($related_module) . "_createdtime_datetime$"] = $displayValueCreated;
                    self::$rep["$" . "r-" . strtolower($columnname) . "_createdtime_datetime$"] = $displayValueCreated;
                    self::$rep["$" . "r-" . strtolower($related_module) . "_modifiedtime_datetime$"] = $displayValueModified;
                    self::$rep["$" . "r-" . strtolower($columnname) . "_modifiedtime_datetime$"] = $displayValueModified;

                    if (isset($related_module)) {
                        $entityImg = "";
                        switch ($related_module) {
                            case "Contacts":
                                $entityImg = $this->getContactImage($focus2->id, self::$site_url);
                                break;
                            case "Products":
                                $entityImg = $this->getProductImage($focus2->id, self::$site_url);
                                break;
                        }
                        self::$rep['$r-' . strtolower($related_module) . '-imagename$'] = $entityImg;
                        self::$rep['$r-' . strtolower($columnname) . '-imagename$'] = $entityImg;
                        self::$rep['$r-' . strtolower($columnname) . '-' . strtolower($related_module) . '-imagename$'] = $entityImg;
                    }
                    $this->replaceContent();
                    $this->replaceFieldsToContent($related_module, $focus2, true);
                    $this->replaceFieldsToContent($related_module, $focus2, $columnname);
                    $this->replaceInventoryDetailsBlock($related_module, $focus2, $columnname);
                    unset($focus2);
                }
                if ($row["uitype"] == "68") {
                    $fieldModRel[$row["fieldid"]][] = "Contacts";
                    $fieldModRel[$row["fieldid"]][] = "Accounts";
                }
                if (isset($fieldModRel[$row["fieldid"]])) {
                    foreach ($fieldModRel[$row["fieldid"]] as $idx => $relMod) {
                        if ($relMod == $related_module) {
                            continue;
                        }
                        $tmpTabId = getTabId($relMod);
                        $temp = &VTCacheUtils::$$field_inf;
                        unset($temp[$tmpTabId]);
                        if (!empty($tmpTabId) && vtlib_isModuleActive($relMod) && file_exists('modules/' . $relMod . '/' . $relMod . '.php')) {
                            $tmpFocus = CRMEntity::getInstance($relMod);
                            self::$rep["$" . "r-" . strtolower($relMod) . "-crmid$"] = $tmpFocus->id;
                            self::$rep["$" . "r-" . strtolower($columnname) . "-crmid$"] = $tmpFocus->id;
                            self::$rep["$" . "r-" . strtolower($relMod) . "_crmid$"] = $tmpFocus->id;
                            self::$rep["$" . "r-" . strtolower($columnname) . "_crmid$"] = $tmpFocus->id;

                            $createdTime = new DateTimeField($tmpFocus->column_fields['createdtime']);
                            $displayValueCreated = $createdTime->getDisplayDateTimeValue();
                            $modifiedTime = new DateTimeField($tmpFocus->column_fields['modifiedtime']);
                            $displayValueModified = $modifiedTime->getDisplayDateTimeValue();

                            self::$rep["$" . "r-" . strtolower($relMod) . "-createdtime-datetime$"] = $displayValueCreated;
                            self::$rep["$" . "r-" . strtolower($columnname) . "-createdtime-datetime$"] = $displayValueCreated;
                            self::$rep["$" . "r-" . strtolower($relMod) . "-modifiedtime-datetime$"] = $displayValueModified;
                            self::$rep["$" . "r-" . strtolower($columnname) . "-modifiedtime-datetime$"] = $displayValueModified;
                            self::$rep["$" . "r-" . strtolower($relMod) . "_createdtime_datetime$"] = $displayValueCreated;
                            self::$rep["$" . "r-" . strtolower($columnname) . "_createdtime_datetime$"] = $displayValueCreated;
                            self::$rep["$" . "r-" . strtolower($relMod) . "_modifiedtime_datetime$"] = $displayValueModified;
                            self::$rep["$" . "r-" . strtolower($columnname) . "_modifiedtime_datetime$"] = $displayValueModified;

                            $this->replaceFieldsToContent($relMod, $tmpFocus, true);
                            $this->replaceFieldsToContent($relMod, $tmpFocus, $columnname);
                            $this->replaceInventoryDetailsBlock($relMod, $tmpFocus, $columnname);
                            unset($tmpFocus);
                        }
                    }
                }
            }
        }
    }

    private function replaceInventoryDetailsBlock($module, $focus, $is_related = false)
    {
        if (!isset(self::$inventory_table_array[$module])) {
            $this->fillInventoryData($module, $focus);
        }
        if (!isset(self::$inventory_table_array[$module])) {
            return array();
        }
        $prefix = "";

        $IReplacements = array();
        $IReplacements["SUBTOTAL"] = $this->formatNumberToEMAIL($focus->column_fields["subtotal"]);
        $IReplacements["TOTAL"] = $this->formatNumberToEMAIL($focus->column_fields["price_total"]);

        $currencytype = $this->getInventoryCurrencyInfoCustom($module, $focus);
        $currencytype["currency_symbol"] = str_replace("€", "&euro;", $currencytype["currency_symbol"]);
        $currencytype["currency_symbol"] = str_replace("£", "&pound;", $currencytype["currency_symbol"]);

        $IReplacements["CURRENCYNAME"] = getTranslatedCurrencyString($currencytype["currency_name"]);
        $IReplacements["CURRENCYSYMBOL"] = $currencytype["currency_symbol"];
        $IReplacements["CURRENCYCODE"] = $currencytype["currency_code"];
        $IReplacements["ADJUSTMENT"] = $this->formatNumberToEMAIL($focus->column_fields["adjustment"]);

        $Products = $this->getInventoryProducts($module, $focus);

        $IReplacements["TOTALWITHOUTVAT"] = $Products["TOTAL"]["TOTALWITHOUTVAT"];
        $IReplacements["VAT"] = $Products["TOTAL"]["TAXTOTAL"];
        $IReplacements["VATPERCENT"] = $Products["TOTAL"]["TAXTOTALPERCENT"];
        $IReplacements["TOTALWITHVAT"] = $Products["TOTAL"]["TOTALWITHVAT"];
        $IReplacements["SHTAXAMOUNT"] = $Products["TOTAL"]["SHTAXAMOUNT"];
        $IReplacements["SHTAXTOTAL"] = $Products["TOTAL"]["SHTAXTOTAL"];
        $IReplacements["DEDUCTEDTAXESTOTAL"] = $Products["TOTAL"]["DEDUCTEDTAXESTOTAL"];
        $IReplacements["TOTALDISCOUNT"] = $Products["TOTAL"]["FINALDISCOUNT"];
        $IReplacements["TOTALDISCOUNTPERCENT"] = $Products["TOTAL"]["FINALDISCOUNTPERCENT"];
        $IReplacements["TOTALAFTERDISCOUNT"] = $Products["TOTAL"]["TOTALAFTERDISCOUNT"];

        foreach ($IReplacements as $r_key => $r_value) {
            if ($is_related !== false) {
                $prefix = "r-" . strtoupper($is_related) . "";
                self::$rep["$" . strtolower($prefix . "-" . $r_key) . "$"] = $r_value;
                self::$rep["$" . strtolower($prefix . "_" . $r_key) . "$"] = $r_value;
                self::$rep["$" . strtolower($prefix . "-" . $module . "-" . $r_key) . "$"] = $r_value;
                self::$rep["$" . strtolower($prefix . "-" . $module . "_" . $r_key) . "$"] = $r_value;
            } else {
                self::$rep["$" . $r_key . "$"] = $r_value;
                self::$rep["$" . "s-" . strtolower($r_key) . "$"] = $r_value;
            }
        }

        $this->replaceContent();

        if ($is_related === false) {
            //in order to handle old format of VATBLOCK

            $blockTypes = ['VATBLOCK', 'DEDUCTEDTAXESBLOCK', 'CHARGESBLOCK'];
            foreach ($blockTypes as $blockType) {
                $vattable = '';

                foreach ((array)$Products["TOTAL"][$blockType] as $keyW => $valueW) {
                    if ((empty($valueW['netto']) && $blockType != 'CHARGESBLOCK') || (empty($valueW['value']) && $blockType == 'CHARGESBLOCK')) {
                        unset($Products["TOTAL"][$blockType][$keyW]);
                    }
                }

                if (count((array)$Products["TOTAL"][$blockType])) {
                    $vattable .= "<table border='1' style='border-collapse:collapse;' cellpadding='3'>";
                    $vattable .= '<tr>';

                    if ($blockType == 'CHARGESBLOCK') {
                        $vattable .= '<td></td><td nowrap align="right">' . self::getTranslate('LBL_CHARGESBLOCK_SUM') . '</td>';
                    } else {
                        $vattable .= '<td nowrap align="center">' . self::getTranslate('Name') . '</td>
                                          <td nowrap align="center">' . self::getTranslate('LBL_VATBLOCK_VAT_PERCENT') . '</td>
                                          <td nowrap align="center">' . self::getTranslate('LBL_VATBLOCK_SUM') . ' (' . $currencytype['currency_symbol'] . ')</td>
                                          <td nowrap align="center">' . self::getTranslate('LBL_VATBLOCK_VAT_VALUE') . ' (' . $currencytype['currency_symbol'] . ')</td>';
                    }
                    $vattable .= '</tr>';
                    foreach ($Products["TOTAL"][$blockType] as $keyW => $valueW) {
                        $vattable .= '<tr>';
                        if ($blockType == 'CHARGESBLOCK') {
                            $vattable .= '<td nowrap align="right" width="75%">' . $valueW['label'] . '</td>
                                          <td nowrap align="right" width="25%">' . $this->formatNumberToEMAIL($valueW['value']) . '</td>';
                        } else {
                            $vattable .= '<td nowrap align="left" width="20%">' . $valueW['label'] . '</td>
                                          <td nowrap align="right" width="25%">' . $this->formatNumberToEMAIL($valueW['value']) . ' %</td>
                                          <td nowrap align="right" width="30%">' . $this->formatNumberToEMAIL($valueW['netto']) . '</td>
                                          <td nowrap align="right" width="25%">' . $this->formatNumberToEMAIL($valueW['vat']) . '</td>';
                        }
                        $vattable .= '</tr>';
                    }
                    $vattable .= '</table>';
                }
                self::$rep['$' . $blockType . '$'] = $vattable;
                self::$rep['$s-' . strtolower($blockType) . '$'] = $vattable;
            }
            $this->replaceContent();

            foreach (['VAT', 'CHARGES'] as $blockType) {
                if (strpos(self::$content, '#' . $blockType . 'BLOCK_START#') !== false && strpos(self::$content, '#' . $blockType . 'BLOCK_END#') !== false) {
                    self::$content = $this->convertBlock($blockType, self::$content);
                    $VExplodedEMAIL = [];
                    $VExploded = explode('#' . $blockType . 'BLOCK_START#', self::$content);
                    $VExplodedEMAIL[] = $VExploded[0];
                    for ($iterator = 1; $iterator < count($VExploded); $iterator++) {
                        $VSubExploded = explode('#' . $blockType . 'BLOCK_END#', $VExploded[$iterator]);
                        foreach ($VSubExploded as $Vpart) {
                            $VExplodedEMAIL[] = $Vpart;
                        }

                        $Vhighestpartid = $iterator * 2 - 1;
                        $VProductParts[$Vhighestpartid] = $VExplodedEMAIL[$Vhighestpartid];
                        $VExplodedEMAIL[$Vhighestpartid] = '';
                    }

                    if (count($Products['TOTAL'][$blockType . 'BLOCK']) > 0) {
                        foreach ($Products['TOTAL'][$blockType . 'BLOCK'] as $keyW => $valueW) {
                            foreach ($VProductParts as $productpartid => $productparttext) {
                                foreach ($valueW as $vColl => $vVal) {
                                    if (is_numeric($vVal)) {
                                        $vVal = $this->formatNumberToEMAIL($vVal);
                                    }
                                    $productparttext = str_replace('$' . $blockType . 'BLOCK_' . strtoupper($vColl) . '$', $vVal, $productparttext);
                                }
                                $VExplodedEMAIL[$productpartid] .= $productparttext;
                            }
                        }
                    }
                    self::$content = implode('', $VExplodedEMAIL);
                }
            }
        }
        return $Products;
    }

    private function fillInventoryData($module, $focus)
    {
        if (isset($focus->column_fields["currency_id"]) && isset($focus->column_fields["conversion_rate"]) && isset($focus->column_fields["price_total"])) {
            self::$inventory_table_array[$module] = $focus->table_name;
            self::$inventory_id_array[$module] = $focus->table_index;
        }
    }

    private function getInventoryCurrencyInfoCustom($module, $focus)
    {
        $record_id = "";
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
            $totalVatSum = $totalwithoutwat = $totalAfterDiscount_subtotal = $total_subtotal = $totalsum_subtotal = 0;
            [$images, $bacImgs] = $this->getInventoryImages($focus->id);

            $recordModel = Vtiger_Record_Model::getInstanceById($focus->id);
            $relatedProducts = $recordModel->getProducts();
            //##Final details convertion started
            $finalDetails = $relatedProducts[1]['final_details'];
            $taxtype = $finalDetails['taxtype'];

            $chargesAndItsTaxes = $finalDetails['chargesAndItsTaxes'];

            $currencyFieldsList = array(
                'NETTOTAL' => 'subtotal',
                'TAXTOTAL' => 'tax_totalamount',
                'SHTAXTOTAL' => 'shtax_totalamount',
                'TOTALAFTERDISCOUNT' => 'preTaxTotal',
                'FINALDISCOUNT' => 'discountTotal_final',
                'SHTAXAMOUNT' => 'shipping_handling_charge',
                'DEDUCTEDTAXESTOTAL' => 'deductTaxesTotalAmount',
            );

            foreach ($currencyFieldsList as $variableName => $fieldName) {
                $Details["TOTAL"][$variableName] = $this->formatNumberToEMAIL($finalDetails[$fieldName]);
            }

            $totalWithVat = $this->getTotalWithVat($finalDetails);
            $Details['TOTAL']['TOTALWITHVAT'] = $this->formatNumberToEMAIL($totalWithVat);

            foreach ($relatedProducts as $i => $PData) {
                $Details["P"][$i] = array();

                $sequence = $i;
                $producttitle = $productname = $PData["productName" . $sequence];
                $entitytype = $PData["entityType" . $sequence];
                $productid = $psid = $PData["hdnProductId" . $sequence];

                $focus_p = CRMEntity::getInstance("Products");
                if ($entitytype == "Products" && $psid != "") {
                    $focus_p->id = $psid;
                    $this->retrieve_entity_infoCustom($focus_p, $psid, "Products");
                }
                $currencytype = $this->getInventoryCurrencyInfoCustom($module, $focus);
                $Array_P = $this->replaceFieldsToContent("Products", $focus_p, false, $currencytype);
                $Details["P"][$i] = array_merge($Array_P, $Details["P"][$i]);

                unset($focus_p);


                $focus_s = CRMEntity::getInstance("Services");
                if ($entitytype == "Services" && $psid != "") {
                    $focus_s->id = $psid;
                    $this->retrieve_entity_infoCustom($focus_s, $psid, "Services");
                }
                $Array_S = $this->replaceFieldsToContent("Services", $focus_s, false, $currencytype);
                $Details["P"][$i] = array_merge($Array_S, $Details["P"][$i]);
                unset($focus_s);

                $Details["P"][$i]["PRODUCTS_CRMID"] = $Details["P"][$i]["SERVICES_CRMID"] = $qty_per_unit = $usageunit = "";

                if ($entitytype == "Products") {
                    $Details["P"][$i]["PRODUCTS_CRMID"] = $psid;
                    $qty_per_unit = $Details["P"][$i]["PRODUCTS_QTY_PER_UNIT"];
                    $usageunit = $Details["P"][$i]["PRODUCTS_USAGEUNIT"];
                } elseif ($entitytype == "Services") {
                    $Details["P"][$i]["SERVICES_CRMID"] = $psid;
                    $qty_per_unit = $Details["P"][$i]["SERVICES_QTY_PER_UNIT"];
                    $usageunit = $Details["P"][$i]["SERVICES_SERVICE_USAGEUNIT"];
                }
                $psdescription = $Details["P"][$i][strtoupper($entitytype) . "_DESCRIPTION"];
                $Details["P"][$i]["PS_CRMID"] = $psid;
                $Details["P"][$i]["PS_NO"] = $PData["hdnProductcode" . $sequence];

                if (Core_Utils_Helper::count($PData['subprod_qty_list' . $sequence]) > 0) {
                    foreach ($PData['subprod_qty_list' . $sequence] as $sid => $SData) {
                        $sname = $SData["name"];
                        if ($SData["qty"] > 0) {
                            $sname .= " (" . $SData["qty"] . ")";
                        }
                        $productname .= "<br/><span style='color:#C0C0C0;font-style:italic;'>" . $sname . "</span>";
                    }
                }

                $comment = $PData["comment" . $sequence];

                if ($comment != "") {
                    if (strpos($comment, '&lt;br /&gt;') === false && strpos($comment, '&lt;br/&gt;') === false && strpos($comment, '&lt;br&gt;') === false) {
                        $comment = str_replace("\\n", "<br>", nl2br($comment));
                    }
                    $comment = html_entity_decode($comment, ENT_QUOTES, self::$def_charset);
                    $productname .= "<br /><small>" . $comment . "</small>";
                }

                $Details["P"][$i]["PRODUCTNAME"] = $productname;
                $Details["P"][$i]["PRODUCTTITLE"] = $producttitle;

                $inventory_prodrel_desc = $psdescription;
                if (strpos($psdescription, '&lt;br /&gt;') === false && strpos($psdescription, '&lt;br/&gt;') === false && strpos($psdescription, '&lt;br&gt;') === false) {
                    $psdescription = str_replace("\\n", "<br>", nl2br($psdescription));
                }
                $Details["P"][$i]["PRODUCTDESCRIPTION"] = html_entity_decode($psdescription, ENT_QUOTES, self::$def_charset);
                $Details["P"][$i]["PRODUCTEDITDESCRIPTION"] = $comment;
                if (strpos($inventory_prodrel_desc, '&lt;br /&gt;') === false && strpos($inventory_prodrel_desc, '&lt;br/&gt;') === false && strpos($inventory_prodrel_desc, '&lt;br&gt;') === false) {
                    $inventory_prodrel_desc = str_replace("\\n", "<br>", nl2br($inventory_prodrel_desc));
                }
                $Details["P"][$i]["CRMNOWPRODUCTDESCRIPTION"] = html_entity_decode($inventory_prodrel_desc, ENT_QUOTES, self::$def_charset);
                $Details["P"][$i]["PRODUCTLISTPRICE"] = $this->formatNumberToEMAIL($PData["listPrice" . $sequence]);
                $Details["P"][$i]["PRODUCTTOTAL"] = $this->formatNumberToEMAIL($PData["productTotal" . $sequence]);
                $Details["P"][$i]["PRODUCTQUANTITY"] = $this->formatNumberToEMAIL($PData["qty" . $sequence]);
                $Details["P"][$i]["PRODUCTQINSTOCK"] = $this->formatNumberToEMAIL($PData["qtyInStock" . $sequence]);
                $Details["P"][$i]["PRODUCTPRICE"] = $this->formatNumberToEMAIL($PData["unitPrice" . $sequence]);
                $Details["P"][$i]["PRODUCTPOSITION"] = $sequence;
                $Details["P"][$i]["PRODUCTQTYPERUNIT"] = $this->formatNumberToEMAIL($qty_per_unit);
                $value = $usageunit;
                if (!in_array(trim($value), self::$ignored_picklist_values)) {
                    $value = $this->getTranslatedStringCustom($value, "Products/Services", self::$language);
                } else {
                    $value = "";
                }
                $Details["P"][$i]["PRODUCTUSAGEUNIT"] = $value;
                $Details["P"][$i]["PRODUCTDISCOUNT"] = $PData["discountTotal" . $sequence];
                $Details["P"][$i]["PRODUCTDISCOUNTPERCENT"] = $PData["discount_percent" . $sequence];
                $totalAfterDiscount = $PData["totalAfterDiscount" . $sequence];
                $Details["P"][$i]["PRODUCTSTOTALAFTERDISCOUNTSUM"] = $totalAfterDiscount;
                $Details["P"][$i]["PRODUCTSTOTALAFTERDISCOUNT"] = $this->formatNumberToEMAIL($PData["totalAfterDiscount" . $sequence]);
                $Details["P"][$i]["PRODUCTTOTALSUM"] = $this->formatNumberToEMAIL($PData["netPrice" . $sequence]);

                $totalAfterDiscount_subtotal += $totalAfterDiscount;
                $total_subtotal += $PData["productTotal" . $sequence];
                $totalsum_subtotal += $PData["netPrice" . $sequence];

                $Details["P"][$i]["PRODUCTSTOTALAFTERDISCOUNT_SUBTOTAL"] = $this->formatNumberToEMAIL($totalAfterDiscount_subtotal);
                $Details["P"][$i]["PRODUCTTOTAL_SUBTOTAL"] = $this->formatNumberToEMAIL($total_subtotal);
                $Details["P"][$i]["PRODUCTTOTALSUM_SUBTOTAL"] = $this->formatNumberToEMAIL($totalsum_subtotal);

                $mpdfSubtotalAble[$i]["$" . "TOTALAFTERDISCOUNT_SUBTOTAL$"] = $Details["P"][$i]["PRODUCTSTOTALAFTERDISCOUNT_SUBTOTAL"];
                $mpdfSubtotalAble[$i]["$" . "TOTAL_SUBTOTAL$"] = $Details["P"][$i]["PRODUCTTOTAL_SUBTOTAL"];
                $mpdfSubtotalAble[$i]["$" . "TOTALSUM_SUBTOTAL$"] = $Details["P"][$i]["PRODUCTTOTALSUM_SUBTOTAL"];

                $Details["P"][$i]["PRODUCTSEQUENCE"] = $sequence;
                $Details["P"][$i]["PRODUCTS_IMAGENAME"] = "";
                if (isset($images[$productid . "_" . $sequence])) {
                    $width = $height = "";
                    if ($images[$productid . "_" . $sequence]["width"] > 0) {
                        $width = " width='" . $images[$productid . "_" . $sequence]["width"] . "' ";
                    }
                    if ($images[$productid . "_" . $sequence]["height"] > 0) {
                        $height = " height='" . $images[$productid . "_" . $sequence]["height"] . "' ";
                    }
                    $Details["P"][$i]["PRODUCTS_IMAGENAME"] = "<img src='" . self::$site_url . "/" . $images[$productid . "_" . $sequence]["src"] . "' " . $width . $height . "/>";
                } elseif (isset($bacImgs[$productid . "_" . $sequence])) {
                    $Details["P"][$i]["PRODUCTS_IMAGENAME"] = "<img src='" . self::$site_url . "/" . $bacImgs[$productid . "_" . $sequence]["src"] . "' width='83' />";
                }

                $taxAverageValue = 0;
                $taxTotal = 0;

                if ('individual' === $taxtype) {
                    $taxDetails = getTaxDetailsForProduct($productid, "all");
                    $Tax_Values = [];
                    for ($taxCount = 0; $taxCount < count($taxDetails); $taxCount++) {
                        $taxName = $taxDetails[$taxCount]['taxname'];
                        $taxLabel = $taxDetails[$taxCount]['taxlabel'];
                        $taxValue = getInventoryProductTaxValue($focus->id, $productid, $taxName);
                        $individualTaxAmount = $totalAfterDiscount * $taxValue / 100;
                        $taxTotal += $individualTaxAmount;

                        if (!empty($taxName)) {
                            $taxVatSum = round($individualTaxAmount, self::$decimals);
                            $taxNameWithValue = $taxName . '-' . $taxValue;
                            $this->setVatBlock($taxNameWithValue, $taxLabel, $taxValue, $totalAfterDiscount, $taxVatSum);

                            $totalVatSum += $taxVatSum;
                            $Tax_Values[] = $taxValue;
                        }
                    }

                    if (count($Tax_Values) > 0) {
                        $taxAverageValue = array_sum($Tax_Values);
                    }
                }

                $Details["P"][$i]["PRODUCTVATPERCENT"] = $this->formatNumberToEMAIL($taxAverageValue);
                $Details["P"][$i]["PRODUCTVATSUM"] = $this->formatNumberToEMAIL($taxTotal);

                $result1 = self::$db->pquery("SELECT * FROM vtiger_inventoryproductrel WHERE id=? AND sequence_no=?", array(self::$focus->id, $sequence));
                $row1 = self::$db->fetchByAssoc($result1, 0);

                $tabid = getTabid($module);
                $result2 = self::$db->pquery("SELECT fieldname, fieldlabel, columnname, uitype, typeofdata FROM vtiger_field WHERE tablename = ? AND tabid = ?", array("vtiger_inventoryproductrel", $tabid));
                while ($row2 = self::$db->fetchByAssoc($result2)) {
                    if (!isset($Details["P"][$i]["PRODUCT_" . strtoupper($row2["fieldname"])])) {
                        $UITypes = array();
                        $value = $row1[$row2["columnname"]];
                        if ($value != "") {
                            $uitype_name = $this->getUITypeName($row2['uitype'], $row2["typeofdata"]);
                            if ($uitype_name != "") {
                                $UITypes[$uitype_name][] = $row2["fieldname"];
                            }

                            $value = $this->getFieldValue($focus, $module, $row2["fieldname"], $value, $UITypes);
                        }
                        $Details["P"][$i]["PRODUCT_" . strtoupper($row2["fieldname"])] = $value;
                    }
                }
            }
        }

        $Details["TOTAL"]["TOTALWITHOUTVAT"] = $this->formatNumberToEMAIL($totalAfterDiscount_subtotal);
        if ($taxtype == "individual") {
            $Details["TOTAL"]["TAXTOTAL"] = $this->formatNumberToEMAIL($totalVatSum);
        }
        $finalDiscountPercent = "";
        $totalVatPercent = 0;

        foreach ((array)$finalDetails['taxes'] as $TAX) {
            $this->setVatBlock($TAX['taxname'], $TAX['taxlabel'], $TAX['percentage'], $finalDetails['totalAfterDiscount'], $TAX['amount']);
            $totalVatPercent += $TAX['percentage'];
        }

        $Details["TOTAL"]["TAXTOTALPERCENT"] = $this->formatNumberToEMAIL($totalVatPercent);

        $hdnDiscountPercent = (float)$focus->column_fields['hdnDiscountPercent'];
        $hdnDiscountAmount = (float)$focus->column_fields['discount_amount'];

        if (!empty($hdnDiscountPercent)) {
            $finalDiscountPercent = $hdnDiscountPercent;
        }

        $Details["TOTAL"]["FINALDISCOUNTPERCENT"] = $this->formatNumberToEMAIL($finalDiscountPercent);
        $Details["TOTAL"]["VATBLOCK"] = $this->getVatBlock();

        $Charges_Block = array();

        if (!empty($chargesAndItsTaxes)) {
            $allCharges = getAllCharges();

            foreach ($chargesAndItsTaxes as $chargeId => $chargeData) {
                $name = $allCharges[$chargeId]['name'];
                $Charges_Block[] = array('label' => $name, 'value' => $chargeData['value']);
            }
        }

        $Details["TOTAL"]["CHARGESBLOCK"] = $Charges_Block;

        return $Details;
    }

    private function getFieldValue($efocus, $emodule, $fieldname, $value, $UITypes, $inventory_currency = false)
    {
        return $this->getFieldValueUtils($efocus, $emodule, $fieldname, $value, $UITypes, $inventory_currency, self::$ignored_picklist_values, self::$def_charset, self::$decimals, self::$decimal_point, self::$thousands_separator, self::$language);
    }

    /**
     * @throws Exception
     */
    private function convertRelatedBlocks(): void
    {
        if (!str_contains(self::$content, '#RELATED_BLOCK_')) {
            return;
        }

        Core_RelatedBlock_Model::$numberUserConfig = Core_RelatedBlock_Model::$currencyUserConfig = [
            'currency_grouping_separator' => self::$thousands_separator,
            'currency_decimal_separator' => self::$decimal_point,
            'truncate_trailing_zeros' => false,
            'no_of_currency_decimals' => self::$decimals,
        ];

        self::$content = Core_RelatedBlock_Model::replaceAll(self::$recordModel, self::$content);
    }

    private function convertInventoryModules()
    {
        $result = self::$db->pquery("select * from vtiger_inventoryproductrel where id=?", array(self::$focus->id));
        $num_rows = self::$db->num_rows($result);
        if ($num_rows > 0) {
            $Products = $this->replaceInventoryDetailsBlock(self::$module, self::$focus);
            $var_array = array();
            $Blocks = array("", "PRODUCTS_", "SERVICES_");
            foreach ($Blocks as $block_type) {
                if (strpos(self::$content, "#PRODUCTBLOC_" . $block_type . "START#") !== false && strpos(self::$content, "#PRODUCTBLOC_" . $block_type . "END#") !== false) {
                    $tableTag = $this->convertProductBlock($block_type);
                    $ExplodedEMAIL = array();
                    $Exploded = explode("#PRODUCTBLOC_" . $block_type . "START#", self::$content);
                    $ExplodedEMAIL[] = $Exploded[0];
                    for ($iterator = 1; $iterator < count($Exploded); $iterator++) {
                        $SubExploded = explode("#PRODUCTBLOC_" . $block_type . "END#", $Exploded[$iterator]);
                        foreach ($SubExploded as $part) {
                            $ExplodedEMAIL[] = $part;
                        }
                        $highestpartid = $iterator * 2 - 1;
                        $ProductParts[$highestpartid] = $ExplodedEMAIL[$highestpartid];
                        $ExplodedEMAIL[$highestpartid] = '';
                    }
                    if ($Products["P"]) {
                        foreach ($Products["P"] as $Product_Details) {
                            if (($block_type == "PRODUCTS_" && !empty($Product_Details["SERVICES_RECORD_ID"])) || ($block_type == "SERVICES_" && !empty($Product_Details["PRODUCTS_RECORD_ID"]))) {
                                continue;
                            }
                            foreach ($ProductParts as $productpartid => $productparttext) {
                                foreach ($Product_Details as $coll => $value) {
                                    $productparttext = str_replace("$" . strtoupper($coll) . "$", $value, $productparttext);
                                }
                                $ExplodedEMAIL[$productpartid] .= $productparttext;
                            }
                        }
                    }
                    self::$content = implode('', $ExplodedEMAIL);
                }
            }
        }
    }

    private function convertProductBlock($block_type = '')
    {
        EMAILMaker_EMAILMaker_Model::getSimpleHtmlDomFile();
        $html = str_get_html(self::$content);
        $tableDOM = false;
        if (is_array($html->find("td"))) {
            foreach ($html->find("td") as $td) {
                if (trim($td->plaintext) == "#PRODUCTBLOC_" . $block_type . "START#") {
                    $td->parent->outertext = "#PRODUCTBLOC_" . $block_type . "START#";
                    $oParent = $td->parent;
                    while ($oParent->tag != "table") {
                        $oParent = $oParent->parent;
                    }
                    [$tag] = explode(">", $oParent->outertext, 2);
                    $header = $oParent->first_child();
                    if ($header->tag != "tr") {
                        $header = $header->children(0);
                    }
                    $header_style = '';
                    if (is_object($td->parent->prev_sibling()->children[0])) {
                        $header_style = $td->parent->prev_sibling()->children[0]->getAttribute("style");
                    }
                    $footer_tag = "<tr>";
                    if (isset($header_style)) {
                        $StyleHeader = explode(";", $header_style);
                        if (isset($StyleHeader)) {
                            foreach ($StyleHeader as $style_header_tag) {
                                if (strpos($style_header_tag, "border-top") == true) {
                                    $footer_tag .= "<td colspan='" . $td->getAttribute("colspan") . "' style='" . $style_header_tag . "'>&nbsp;</td>";
                                }
                            }
                        }
                    } else {
                        $footer_tag .= "<td colspan='" . $td->getAttribute("colspan") . "' style='border-top:1px solid #000000;'>&nbsp;</td>";
                    }
                    $footer_tag .= "</tr>";
                    $var = $td->parent->next_sibling()->last_child()->plaintext;
                    $subtotal_tr = "";
                    if (strpos($var, "TOTAL") !== false) {
                        if (is_object($td)) {
                            $style_subtotal = $td->getAttribute("style");
                        }
                        $style_subtotal_tag = $style_subtotal_endtag = "";
                        if (isset($td->innertext)) {
                            [$style_subtotal_tag, $style_subtotal_endtag] = explode("#PRODUCTBLOC_" . $block_type . "START#", $td->innertext);
                        }
                        if (isset($style_subtotal)) {
                            $StyleSubtotal = explode(";", $style_subtotal);
                            if (isset($StyleSubtotal)) {
                                foreach ($StyleSubtotal as $style_tag) {
                                    if (strpos($style_tag, "border-top") == true) {
                                        $tag .= " style='" . $style_tag . "'";
                                        break;
                                    }
                                }
                            }
                        } else {
                            $style_subtotal = "";
                        }
                        $subtotal_tr = "<tr>";
                        $subtotal_tr .= "<td colspan='" . ($td->getAttribute("colspan") - 1) . "' style='" . $style_subtotal . ";border-right:none'>" . $style_subtotal_tag . "%G_Subtotal%" . $style_subtotal_endtag . "</td>";
                        $subtotal_tr .= "<td align='right' nowrap='nowrap' style='" . $style_subtotal . "'>" . $style_subtotal_tag . "" . rtrim($var, "$") . "_SUBTOTAL$" . $style_subtotal_endtag . "</td>";
                        $subtotal_tr .= "</tr>";
                    }
                    $tag .= ">";
                    $tableDOM["tag"] = $tag;
                    $tableDOM["header"] = $header->outertext;
                    $tableDOM["footer"] = $footer_tag;
                    $tableDOM["subtotal"] = $subtotal_tr;
                }
                if (trim($td->plaintext) == "#PRODUCTBLOC_" . $block_type . "END#") {
                    $td->parent->outertext = "#PRODUCTBLOC_" . $block_type . "END#";
                }
            }
            self::$content = $html->save();
        }
        return $tableDOM;
    }

    private function handleRowbreak()
    {
        EMAILMaker_EMAILMaker_Model::getSimpleHtmlDomFile();
        $html = str_get_html(self::$content);
        $toSkip = 0;
        if (is_array($html->find("rowbreak"))) {
            foreach ($html->find("rowbreak") as $pb) {
                if ($pb->outertext == self::$rowbreak) {
                    $tmpPb = $pb;
                    while ($tmpPb != null && $tmpPb->tag != "td") {
                        $tmpPb = $tmpPb->parent();
                    }
                    if ($tmpPb->tag == "td") {
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
                        for ($i = 0; $i < (count($partsArr) - 1); $i++) {
                            $tmpPb->innertext = $partsArr[$i];
                            $addition = '<tr>';
                            for ($j = 0; $j < count($prev_sibling_styles); $j++) {
                                $addition .= '<td ' . $prev_sibling_styles[$j] . '>&nbsp;</td>';
                            }
                            $addition .= '<td style="' . $tmpPb->getAttribute("style") . '">' . $partsArr[$i + 1] . '</td>';
                            for ($j = 0; $j < count($next_sibling_styles); $j++) {
                                $addition .= '<td ' . $next_sibling_styles[$j] . '>&nbsp;</td>';
                            }
                            $addition .= '</tr>';
                            $tmpPb->parent()->outertext = $tmpPb->parent()->outertext . $addition;
                        }
                        $toSkip = count($partsArr) - 2;
                    }
                }
            }
            self::$content = $html->save();
        }
    }

    protected function replaceTermsAndConditions()
    {
        $value = Core_Utils_Helper::getTermsAndConditions(self::$module);

        if (empty($value)) {
            $value = Core_Utils_Helper::getTermsAndConditions('Inventory');
        }

        self::$rep['$TERMS_AND_CONDITIONS$'] = nl2br($value);
    }

    /**
     * @throws Exception
     */
    public function replaceCompanyFields(): void
    {
        $userId = intval(self::$focus->column_fields['assigned_user_id'] ?? Users_Record_Model::getCurrentUserModel()->getId());
        $fields = Core_TemplateContent_Helper::getCompanyFields($userId, self::$language);

        self::$rep = array_merge(self::$rep, $fields);
    }

    /**
     * @throws Exception
     */
    private function replaceUserCompanyFields($convert_source): void
    {
        $this->replaceCompanyFields();
        $this->replaceTermsAndConditions();

        $current_user = Users_Record_Model::getCurrentUserModel();

        if ($convert_source) {
            //assigned user fields
            $user_row = array();
            $assigned_user_id = "";
            if (self::$focus->column_fields["assigned_user_id"] != "") {
                $user_res = self::$db->pquery("SELECT * FROM vtiger_users WHERE id = ?", array(self::$focus->column_fields["assigned_user_id"]));
                $num_user_rows = self::$db->num_rows($user_res);
                if ($num_user_rows > 0) {
                    $user_row = self::$db->fetchByAssoc($user_res);
                    $assigned_user_id = self::$focus->column_fields["assigned_user_id"];
                }
            }

            self::$rep["$" . "s-user_crmid$"] = $assigned_user_id;

            $this->replaceContent();
            $this->replaceUserData($assigned_user_id, $user_row, "s");
            $focus_user = CRMEntity::getInstance("Users");

            if (!empty($assigned_user_id)) {
                $focus_user->id = $assigned_user_id;
                $this->retrieve_entity_infoCustom($focus_user, $focus_user->id, "Users");
            }
            $this->replaceFieldsToContent("Users", $focus_user, false);
        }

        $luserid = $this->get("luserid");
        if (!$luserid) {
            $luserid = $current_user->id;
        }
        self::$rep["$" . "l-user_crmid$"] = $luserid;
        if ($luserid == $current_user->id) {
            $this->replaceUserData($current_user->id, $current_user->column_fields, "l");
        }
        $curr_user_focus = CRMEntity::getInstance("Users");
        $curr_user_focus->id = $luserid;
        $this->retrieve_entity_infoCustom($curr_user_focus, $curr_user_focus->id, "Users");
        if ($luserid != $current_user->id) {
            $this->replaceUserData($current_user->id, $curr_user_focus->column_fields, "l");
        }
        $this->replaceFieldsToContent("Users", $curr_user_focus, true, false, false, "l-");
        self::$rep["$" . "l-users_crmid$"] = $curr_user_focus->id;

        $muserid = $this->get("muserid");
        if ($muserid) {
            $modifiedby_user_res_sql = "WHERE vtiger_users.id = ?";
            $modifiedby_user_res_data = array($muserid);
        } else {
            $modifiedby_user_res_sql = "INNER JOIN vtiger_crmentity ON vtiger_crmentity.modifiedby = vtiger_users.id WHERE vtiger_crmentity.crmid = ?";
            $modifiedby_user_res_data = array(self::$focus->id);
        }
        $modifiedby_user_res = self::$db->pquery("SELECT vtiger_users.* FROM vtiger_users " . $modifiedby_user_res_sql, $modifiedby_user_res_data);
        $modifiedby_user_row = self::$db->fetchByAssoc($modifiedby_user_res);

        $this->replaceUserData($modifiedby_user_row["id"], $modifiedby_user_row, "m");
        $modifiedby_user_focus = CRMEntity::getInstance("Users");
        $modifiedby_user_focus->id = $modifiedby_user_row["id"];
        $this->retrieve_entity_infoCustom($modifiedby_user_focus, $modifiedby_user_focus->id, "Users");
        $this->replaceFieldsToContent("Users", $modifiedby_user_focus, true, false, false, 'm-');
        self::$rep["$" . "m-users_crmid$"] = $modifiedby_user_focus->id;

        $smcreatorid_user_res = self::$db->pquery("SELECT vtiger_users.* FROM vtiger_users INNER JOIN vtiger_crmentity ON vtiger_crmentity.creator_user_id = vtiger_users.id  WHERE  vtiger_crmentity.crmid = ?", array(self::$focus->id));
        $smcreatorid_user_row = self::$db->fetchByAssoc($smcreatorid_user_res);
        $this->replaceUserData($smcreatorid_user_row["id"], $smcreatorid_user_row, "c");
        $smcreatorid_user_focus = CRMEntity::getInstance("Users");
        $smcreatorid_user_focus->id = $smcreatorid_user_row["id"];
        $this->retrieve_entity_infoCustom($smcreatorid_user_focus, $smcreatorid_user_focus->id, "Users");
        $this->replaceFieldsToContent("Users", $smcreatorid_user_focus, true, false, false, 'c-');
        self::$rep["$" . "c-users_crmid$"] = $smcreatorid_user_focus->id;

        $this->replaceContent();
    }

    private function replaceUserData($id, $data, $type)
    {
        $Fields = $this->getUserFieldsForPDF();
        foreach ($Fields as $n => $v) {
            $val = $this->getUserValue($v, $data);
            self::$rep["$" . $type . "-user_" . $n . "$"] = $val;
            self::$rep["$" . $type . "-users_" . $n . "$"] = $val;
        }
        $currency_id = $this->getUserValue("currency_id", $data);
        $currency_info = $this->getInventoryCurrencyInfoCustomArray('', '', $currency_id);
        self::$rep["$" . $type . "-users-currency_name$"] = $currency_info["currency_name"];
        self::$rep["$" . $type . "-users-currency_code$"] = $currency_info["currency_code"];
        self::$rep["$" . $type . "-users-currency_symbol$"] = $currency_info["currency_symbol"];
        $this->replaceContent();
    }

    private function replaceLabels()
    {
        $app_lang_array = Vtiger_Language_Handler::getModuleStringsFromFile(self::$language);
        $mod_lang_array = Vtiger_Language_Handler::getModuleStringsFromFile(self::$language, self::$module);
        $app_lang = $app_lang_array["languageStrings"];
        $mod_lang = $mod_lang_array["languageStrings"];

        [$custom_lang, $languages] = $this->EMAILMaker->GetCustomLabels();
        $currLangId = "";
        foreach ($languages as $langId => $langVal) {
            if ($langVal["prefix"] == self::$language) {
                $currLangId = $langId;
                break;
            }
        }


        self::$rep["%G_Qty%"] = $app_lang["Quantity"];
        self::$rep["%G_Subtotal%"] = $app_lang["Sub Total"];
        self::$rep["%M_LBL_VENDOR_NAME_TITLE%"] = $app_lang["Vendor Name"];
        $this->replaceContent();

        if (strpos(self::$content, "%G_") !== false) {
            foreach ($app_lang as $key => $value) {
                self::$rep["%G_" . $key . "%"] = $value;
            }
            $this->replaceContent();
        }
        if (strpos(self::$content, "%M_") !== false) {
            foreach ($mod_lang as $key => $value) {
                self::$rep["%M_" . $key . "%"] = $value;
            }
            $this->replaceContent();
            foreach ($app_lang as $key => $value) {
                self::$rep["%M_" . $key . "%"] = $value;
            }
            if (self::$module == "SalesOrder") {
                self::$rep["%G_SO Number%"] = $mod_lang["SalesOrder No"];
            }
            if (self::$module == "Invoice") {
                self::$rep["%G_Invoice No%"] = $mod_lang["Invoice No"];
            }
            self::$rep["%M_Grand Total%"] = vtranslate('Grand Total', self::$module);
            $this->replaceContent();
        }
        if (strpos(self::$content, "%C_") !== false) {
            foreach ($custom_lang as $key => $value) {
                self::$rep["%" . $value->GetKey() . "%"] = $value->GetLangValue($currLangId);
            }
            $this->replaceContent();
        }
        if (count(self::$relBlockModules) > 0) {
            $services_lang = return_specified_module_language(self::$language, "Services");
            $contacts_lang = return_specified_module_language(self::$language, "Contacts");
            foreach (self::$relBlockModules as $relBlockModule) {
                if ($relBlockModule != "") {
                    $relMod_lang = return_specified_module_language(self::$language, $relBlockModule);
                    $r_rbm_upper = "%r-" . strtolower($relBlockModule);
                    self::$rep[$r_rbm_upper . "_Service Name%"] = $services_lang["Service Name"];
                    self::$rep[$r_rbm_upper . "_Secondary Email%"] = $contacts_lang["Secondary Email"];

                    $LD = $this->getRelBlockLabels();
                    foreach ($LD as $lkey => $llabel) {
                        self::$rep[$r_rbm_upper . "_" . $lkey . "%"] = $app_lang[$llabel];
                    }
                    $rl_res = self::$db->pquery("SELECT vtiger_field.fieldlabel FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid WHERE vtiger_tab.name = ?", array($relBlockModule));
                    while ($rl_row = self::$db->fetchByAssoc($rl_res)) {
                        $key = $rl_row["fieldlabel"];

                        if ($relMod_lang[$key]) {
                            $value = $relMod_lang[$key];
                        } elseif ($app_lang[$key]) {
                            $value = $app_lang[$key];
                        } else {
                            $value = $key;
                        }
                        self::$rep[$r_rbm_upper . "_" . htmlentities($key, ENT_QUOTES, self::$def_charset) . "%"] = $value;
                        self::$rep["%R_" . strtoupper($relBlockModule) . "_" . htmlentities($key, ENT_QUOTES, self::$def_charset) . "%"] = $value;
                    }
                    if ($relBlockModule == "Products") {
                        self::$rep[$r_rbm_upper . "_LBL_LIST_PRICE%"] = $app_lang["LBL_LIST_PRICE"];
                    }
                    $this->replaceContent();
                }
            }
        }
    }

    private function replaceCustomFunctions($after = "")
    {
        if (is_numeric(strpos(self::$content, '[CUSTOMFUNCTION' . strtoupper($after) . '|'))) {
            vglobal('its4you_main_focus', self::$focus);
            $focus = self::$focus;
            $customFunctions = new EMAILMaker_AllowedFunctions_Helper();
            $Allowed_Functions = $customFunctions->getAllowedFunctions();
            vglobal('PDFMaker_template_id', 'email');

            foreach (glob('modules/EMAILMaker/resources/functions/*.php') as $file) {
                include_once $file;
            }

            $startFunctions = explode('[CUSTOMFUNCTION' . strtoupper($after) . '|', self::$content);
            $content = $startFunctions[0];

            foreach ($startFunctions as $function) {
                $endFunction = explode('|CUSTOMFUNCTION' . strtoupper($after) . ']', $function);
                $html = $endFunction[0];

                if (!empty($html)) {
                    $Params = $this->getCustomfunctionParams($html);
                    $func = $Params[0];
                    unset($Params[0]);

                    if (in_array($func, $Allowed_Functions)) {
                        $content .= call_user_func_array($func, $Params);
                    }
                }

                $content .= $endFunction[1];
            }

            self::$content = $content;
        }
    }

    private function fixImg()
    {
        EMAILMaker_EMAILMaker_Model::getSimpleHtmlDomFile();
        $html = str_get_html(self::$content);
        $surl = self::$site_url;

        if ($surl[strlen($surl) - 1] != "/") {
            $surl = $surl . "/";
        }
        $i = 1;
        if (is_array($html->find("img"))) {
            foreach ($html->find("img") as $img) {
                if (strpos($img->src . "/", $surl) === 0) {
                    $newPath = str_replace($surl . "/", "", $img->src);
                } elseif (strpos($img->src, $surl) === 0) {
                    $newPath = str_replace($surl, "", $img->src);
                } else {
                    $newPath = $img->src;
                }
                if (file_exists($newPath)) {
                    $img->src = "cid:image" . $i;
                    $Parts = explode(".", $newPath);
                    $img_type = $Parts[count($Parts) - 1];
                    self::$Email_Images["image" . $i] = array("name" => "image" . $i . "." . $img_type, "path" => $newPath);
                    $i++;
                }
            }
        }
        if (is_array($html->find("[background]"))) {
            foreach ($html->find('[background]') as $img) {
                if (strpos($img->background, $surl) === 0) {
                    $newPath = str_replace($surl, "", $img->background);
                    if (strpos($img->src . "/", $surl) === 0) {
                        $newPath = str_replace($surl . "/", "", $img->background);
                    } elseif (strpos($img->src, $surl) === 0) {
                        $newPath = str_replace($surl, "", $img->background);
                    } else {
                        $newPath = $img->background;
                    }

                    if (file_exists($newPath)) {
                        $img->background = "cid:image" . $i;
                        $Parts = explode(".", $newPath);
                        $img_type = $Parts[count($Parts) - 1];
                        self::$Email_Images["image" . $i] = array("name" => "image" . $i . "." . $img_type, "path" => $newPath);
                        $i++;
                    }
                }
            }
        }
        if ($i > 1) {
            self::$content = $html->save();
        }
    }

    public function getSubject()
    {
        return self::$subject;
    }

    public function getBody()
    {
        return self::$body;
    }

    public function getBodyWithStyles()
    {
        $templateBody = $this->getBody();
        $templateId = self::$templateid;

        if (!empty($templateId) && vtlib_isModuleActive('ITS4YouStyles') && class_exists('ITS4YouStyles_Module_Model')) {
            $templateBody = (new ITS4YouStyles_Module_Model())->addStyles($templateBody, $templateId, 'EMAILMaker');
        }

        return $templateBody;
    }

    public function getPreview()
    {
        return self::$preview;
    }

    public function getAttachments()
    {
        $templateAttachments = $this->getAttachmentsForId(self::$templateid);
        $recordsAttachments = $this->getAttachmentsForRecord();
        $commentAttachments = $this->getAttachmentsForContent();

        return array_merge($templateAttachments, $recordsAttachments, $commentAttachments);
    }

    public function getAttachmentsForContent(): array
    {
        if (empty(self::$recordId)) {
            return [];
        }

        $body = $this->getBody();
        $variables = ModComments_Module_Model::$commentAttachmentVariables;
        $attachments = false;
        $limit = 0;

        foreach ($variables as $variable) {
            if (str_contains($body, $variable)) {
                $attachments = true;
                $limit = ModComments_Module_Model::$commentFieldsLimit[trim($variable, '$')];
                break;
            }
        }

        return $attachments ? ModComments_Record_Model::getCommentsAttachmentsByRecord(self::$recordId, $limit) : [];
    }

    public function getAttachmentsForRecord()
    {
        if (empty(self::$recordId) || !self::$load_related_documents || !class_exists('ITS4YouEmails_Attachment_Model')) {
            return [];
        }

        $folderIds = self::$folders_related_documents;
        $attachments = [];
        $documents = ITS4YouEmails_Attachment_Model::getParentRecords(self::$recordId);

        /** @var Documents_Record_Model $documentRecord */
        foreach ($documents as $document) {
            if (!empty($folderIds)) {
                if (in_array($document['folderid'], $folderIds)) {
                    $attachments[] = $document['crmid'];
                }
            } else {
                $attachments[] = $document['crmid'];
            }
        }

        return $attachments;
    }

    public function getEmailImages($convert_recipient = true)
    {
        return self::$Email_Images;
    }

    private function getInventoryTaxTypeCustom($module, $focus)
    {
        if (!empty($focus->id)) {
            $res = self::$db->pquery("SELECT taxtype FROM " . self::$inventory_table_array[$module] . " WHERE " . self::$inventory_id_array[$module] . "=?", array($focus->id));
            return self::$db->query_result($res, 0, 'taxtype');
        }
        return "";
    }

    private function itsmd($val)
    {
        return md5($val);
    }

    public static function getTranslate($label, $module = 'EMAILMaker')
    {
        return Vtiger_Language_Handler::getTranslatedString($label, $module, self::$language);
    }
}