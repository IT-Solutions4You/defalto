<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

include_once('config.php');
require_once('include/logging.php');
require_once('include/utils/utils.php');

/** Class to populate the default required data during installation
 */
class DefaultDataPopulator extends CRMEntity
{

    var $new_schema = true;

    function DefaultDataPopulator()
    {
        self::__construct();
    }

    function addDefaultLeadMapping()
    {
        global $adb;

        $fieldMap = [
            ['company', 'accountname', null, 'potentialname', 0],
            ['industry', 'industry', null, null, 1],
            ['phone', 'phone', 'phone', null, 1],
            ['fax', 'fax', 'fax', null, 1],
            ['rating', 'rating', null, null, 1],
            ['email', 'email1', 'email', null, 0],
            ['website', 'website', null, null, 1],
            ['city', 'bill_city', 'mailingcity', null, 1],
            ['code', 'bill_code', 'mailingcode', null, 1],
            ['country_id', 'bill_country_id', 'mailingcountry_id', null, 1],
            ['state', 'bill_state', 'mailingstate', null, 1],
            ['lane', 'bill_street', 'mailingstreet', null, 1],
            ['pobox', 'bill_pobox', 'mailingpobox', null, 1],
            ['city', 'ship_city', null, null, 1],
            ['code', 'ship_code', null, null, 1],
            ['country_id', 'ship_country_id', null, null, 1],
            ['state', 'ship_state', null, null, 1],
            ['lane', 'ship_street', null, null, 1],
            ['pobox', 'ship_pobox', null, null, 1],
            ['description', 'description', 'description', 'description', 1],
            ['salutationtype', null, 'salutationtype', null, 1],
            ['firstname', null, 'firstname', null, 0],
            ['lastname', null, 'lastname', null, 0],
            ['mobile', null, 'mobile', null, 1],
            ['designation', null, 'title', null, 1],
            ['secondaryemail', null, 'secondaryemail', null, 1],
            ['leadsource', null, 'leadsource', 'leadsource', 1],
            ['leadstatus', null, null, null, 1],
            ['noofemployees', 'employees', null, null, 1],
            ['annualrevenue', 'annual_revenue', null, null, 1],
        ];

        $leadTab = getTabid('Leads');
        $accountTab = getTabid('Accounts');
        $contactTab = getTabid('Contacts');
        $potentialTab = getTabid('Potentials');
        $mapSql = "INSERT INTO vtiger_convertleadmapping(leadfid,accountfid,contactfid,potentialfid,editable) values(?,?,?,?,?)";

        foreach ($fieldMap as $values) {
            $leadfid = getFieldid($leadTab, $values[0]);
            $accountfid = getFieldid($accountTab, $values[1]);
            $contactfid = getFieldid($contactTab, $values[2]);
            $potentialfid = getFieldid($potentialTab, $values[3]);
            $editable = $values[4];
            $adb->pquery($mapSql, [$leadfid, $accountfid, $contactfid, $potentialfid, $editable]);
        }
    }

    /**
     * this function adds the entries for settings page
     * it assumes entries as were present on 10-12-208
     */
    function addEntriesForSettings()
    {
        global $adb;

        //icons for all fields
        $icons = [
            "ico-users.gif",
            "ico-roles.gif",
            "ico-profile.gif",
            "ico-groups.gif",
            "shareaccess.gif",
            "orgshar.gif",
            "set-IcoLoginHistory.gif",
            "vtlib_modmng.gif",
            "picklist.gif",
            "picklistdependency.gif",
            "menueditor.png",
            "notification.gif",
            "company.gif",
            "ogmailserver.gif",
            "currency.gif",
            "taxConfiguration.gif",
            "system.gif",
            "announ.gif",
            "set-IcoTwoTabConfig.gif",
            "terms.gif",
            "settingsInvNumber.gif",
            "mailScanner.gif",
            "settingsWorkflow.png",
            "migrate.gif",
            "Cron.png",
        ];

        //labels for blocks
        $blocks = [
            'LBL_USER_MANAGEMENT',
            'LBL_STUDIO',
            'LBL_COMMUNICATION_TEMPLATES',
            'LBL_OTHER_SETTINGS',
            'LBL_MODULE_MANAGER',
        ];

        //field names
        $names = [
            'LBL_USERS',
            'LBL_ROLES',
            'LBL_PROFILES',
            'USERGROUPLIST',
            'LBL_SHARING_ACCESS',
            'LBL_FIELDS_ACCESS',
            'LBL_LOGIN_HISTORY_DETAILS',
            'VTLIB_LBL_MODULE_MANAGER',
            'LBL_PICKLIST_EDITOR',
            'LBL_PICKLIST_DEPENDENCY_SETUP',
            'LBL_MENU_EDITOR',
            'NOTIFICATIONSCHEDULERS',
            'LBL_COMPANY_DETAILS',
            'LBL_MAIL_SERVER_SETTINGS',
            'LBL_CURRENCY_SETTINGS',
            'LBL_TAX_SETTINGS',
            'LBL_SYSTEM_INFO',
            'LBL_ANNOUNCEMENT',
            'LBL_DEFAULT_MODULE_VIEW',
            'INVENTORYTERMSANDCONDITIONS',
            'LBL_CUSTOMIZE_MODENT_NUMBER',
            'LBL_MAIL_SCANNER',
            'LBL_LIST_WORKFLOWS',
            'LBL_CONFIG_EDITOR',
            'Scheduler',
        ];


        $name_blocks = [
            'LBL_USERS' => 'LBL_USER_MANAGEMENT',
            'LBL_ROLES' => 'LBL_USER_MANAGEMENT',
            'LBL_PROFILES' => 'LBL_USER_MANAGEMENT',
            'USERGROUPLIST' => 'LBL_USER_MANAGEMENT',
            'LBL_SHARING_ACCESS' => 'LBL_USER_MANAGEMENT',
            'LBL_FIELDS_ACCESS' => 'LBL_USER_MANAGEMENT',
            'LBL_LOGIN_HISTORY_DETAILS' => 'LBL_USER_MANAGEMENT',
            'VTLIB_LBL_MODULE_MANAGER' => 'LBL_STUDIO',
            'LBL_PICKLIST_EDITOR' => 'LBL_STUDIO',
            'LBL_PICKLIST_DEPENDENCY_SETUP' => 'LBL_STUDIO',
            'LBL_MENU_EDITOR' => 'LBL_STUDIO',
            'NOTIFICATIONSCHEDULERS' => 'LBL_COMMUNICATION_TEMPLATES',
            'LBL_COMPANY_DETAILS' => 'LBL_COMMUNICATION_TEMPLATES',
            'LBL_MAIL_SERVER_SETTINGS' => 'LBL_OTHER_SETTINGS',
            'LBL_CURRENCY_SETTINGS' => 'LBL_OTHER_SETTINGS',
            'LBL_TAX_SETTINGS' => 'LBL_OTHER_SETTINGS',
            'LBL_SYSTEM_INFO' => 'LBL_OTHER_SETTINGS',
            'LBL_ANNOUNCEMENT' => 'LBL_OTHER_SETTINGS',
            'LBL_DEFAULT_MODULE_VIEW' => 'LBL_OTHER_SETTINGS',
            'INVENTORYTERMSANDCONDITIONS' => 'LBL_OTHER_SETTINGS',
            'LBL_CUSTOMIZE_MODENT_NUMBER' => 'LBL_OTHER_SETTINGS',
            'LBL_MAIL_SCANNER' => 'LBL_OTHER_SETTINGS',
            'LBL_LIST_WORKFLOWS' => 'LBL_OTHER_SETTINGS',
            'LBL_CONFIG_EDITOR' => 'LBL_OTHER_SETTINGS',
            'Scheduler' => 'LBL_OTHER_SETTINGS',
        ];


        //description for fields
        $description = [
            'LBL_USER_DESCRIPTION',
            'LBL_ROLE_DESCRIPTION',
            'LBL_PROFILE_DESCRIPTION',
            'LBL_GROUP_DESCRIPTION',
            'LBL_SHARING_ACCESS_DESCRIPTION',
            'LBL_SHARING_FIELDS_DESCRIPTION',
            'LBL_LOGIN_HISTORY_DESCRIPTION',
            'VTLIB_LBL_MODULE_MANAGER_DESCRIPTION',
            'LBL_PICKLIST_DESCRIPTION',
            'LBL_PICKLIST_DEPENDENCY_DESCRIPTION',
            'LBL_MENU_DESC',
            'LBL_NOTIF_SCHED_DESCRIPTION',
            'LBL_COMPANY_DESCRIPTION',
            'LBL_MAIL_SERVER_DESCRIPTION',
            'LBL_CURRENCY_DESCRIPTION',
            'LBL_TAX_DESCRIPTION',
            'LBL_SYSTEM_DESCRIPTION',
            'LBL_ANNOUNCEMENT_DESCRIPTION',
            'LBL_DEFAULT_MODULE_VIEW_DESC',
            'LBL_INV_TANDC_DESCRIPTION',
            'LBL_CUSTOMIZE_MODENT_NUMBER_DESCRIPTION',
            'LBL_MAIL_SCANNER_DESCRIPTION',
            'LBL_LIST_WORKFLOWS_DESCRIPTION',
            'LBL_CONFIG_EDITOR_DESCRIPTION',
            'Allows you to Configure Cron Task',
        ];

        $links = [
            'index.php?module=Users&parent=Settings&view=List',
            'index.php?module=Roles&parent=Settings&view=Index',
            'index.php?module=Profiles&parent=Settings&view=List',
            'index.php?module=Groups&parent=Settings&view=List',
            'index.php?module=SharingAccess&parent=Settings&view=Index',
            'index.php?module=FieldAccess&parent=Settings&view=Index',
            'index.php?module=LoginHistory&parent=Settings&view=List',
            'index.php?module=ModuleManager&parent=Settings&view=List',
            'index.php?parent=Settings&module=Picklist&view=Index',
            'index.php?parent=Settings&module=PickListDependency&view=List',
            'index.php?module=MenuEditor&parent=Settings&view=Index',
            'index.php?module=Settings&view=listnotificationschedulers&parenttab=Settings',
            'index.php?parent=Settings&module=Vtiger&view=CompanyDetails',
            'index.php?parent=Settings&module=Vtiger&view=OutgoingServerDetail',
            'index.php?parent=Settings&module=Currency&view=List',
            'index.php?module=Vtiger&parent=Settings&view=TaxIndex',
            'index.php?module=Settings&submodule=Server&view=ProxyConfig',
            'index.php?parent=Settings&module=Vtiger&view=AnnouncementEdit',
            'index.php?module=Settings&action=DefModuleView&parenttab=Settings',
            'index.php?parent=Settings&module=Vtiger&view=TermsAndConditionsEdit',
            'index.php?module=Vtiger&parent=Settings&view=CustomRecordNumbering',
            'index.php?parent=Settings&module=MailConverter&view=List',
            'index.php?module=Workflows&parent=Settings&view=List',
            'index.php?module=Vtiger&parent=Settings&view=ConfigEditorDetail',
            'index.php?module=CronTasks&parent=Settings&view=List',
        ];

        //insert settings blocks
        $count = php7_count($blocks);
        for ($i = 0; $i < $count; $i++) {
            $adb->query("insert into vtiger_settings_blocks values (" . $adb->getUniqueID('vtiger_settings_blocks') . ", '$blocks[$i]', $i+1)");
        }

        $count = php7_count($icons);
        //insert settings fields
        for ($i = 0, $seq = 1; $i < $count; $i++, $seq++) {
            if ($i == 8 || $i == 12 || $i == 18) {
                $seq = 1;
            }
            $adb->query("insert into vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence) values (" . $adb->getUniqueID('vtiger_settings_field') . ", " . getSettingsBlockId($name_blocks[$names[$i]]) . ", '$names[$i]', '$icons[$i]', '$description[$i]', '$links[$i]', $seq)");
        }

        // for Workflow in settings page of every module
        $module_manager_id = getSettingsBlockId('LBL_MODULE_MANAGER');
        $result = $adb->pquery("SELECT max(sequence) AS maxseq FROM vtiger_settings_field WHERE blockid = ?", [$module_manager_id]);
        $maxseq = $adb->query_result($result, 0, 'maxseq');
        if ($maxseq < 0 || $maxseq == null) {
            $maxseq = 1;
        }
        $adb->pquery("INSERT INTO vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence) VALUES (?,?,?,?,?,?,?)", [$adb->getUniqueID('vtiger_settings_field'), $module_manager_id, 'LBL_WORKFLOW_LIST', 'settingsWorkflow.png', 'LBL_AVAILABLE_WORKLIST_LIST', 'index.php?module=com_vtiger_workflow&action=workflowlist', $maxseq]);

        //hide the system details tab for now
        $adb->query("update vtiger_settings_field set active=1 where name='LBL_SYSTEM_INFO'");
    }

    function addInventoryRows($paramArray)
    {
        global $adb;

        $fieldCreateCount = 0;

        for ($index = 0; $index < php7_count($paramArray); ++$index) {
            $criteria = $paramArray[$index];

            $semodule = $criteria['semodule'];

            $modfocus = CRMEntity::getInstance($semodule);
            $modfocus->setModuleSeqNumber('configure', $semodule, $criteria['prefix'], $criteria['startid']);
        }
    }

    public function createBlocks($tabId, $blocks): array
    {
        $blockIds = [];
        $sequence = 0;

        foreach ($blocks as $blockLabel) {
            $blockId = $this->db->getUniqueID('vtiger_blocks');
            $sequence++;
            $blockIds[] = $blockId;
            $this->db->query(sprintf('INSERT INTO vtiger_blocks VALUES (%s,%s,\'%s\',%s,0,0,0,0,0,1,0)', $blockId, $tabId, $blockLabel, $sequence));
        }

        return $blockIds;
    }

    public function createFields($fields): array
    {
        $fieldIds = [];

        foreach ($fields as $field) {
            $fieldId = $this->db->getUniqueID('vtiger_field');
            $field[1] = $fieldId;
            $this->db->pquery('INSERT INTO vtiger_field VALUES (' . generateQuestionMarks($field) . ')', $field);

            $fieldIds[$field[0]][$field[2]] = $field[1];
        }

        return $fieldIds;
    }

    public function createTabs($tabs): array
    {
        $tabIds = [];

        foreach ($tabs as $tabInfo) {
            $tabIds[$tabInfo[1]] = $tabInfo[0];
            $this->db->pquery('INSERT INTO vtiger_tab(tabid,name,presence,tabsequence,tablabel,customized,ownedby,isentitytype,parent) VALUES (' . generateQuestionMarks($tabInfo) . ')', $tabInfo);
        }

        return $tabIds;
    }

    /** Function to populate the default required data during installation
     */
    function create_tables()
    {
        global $app_strings;

        $tabs = [
            [3, 'Home', 0, 1, 'Home', 0, 1, 0, null],
            [7, 'Leads', 0, 4, 'Leads', 0, 0, 1, 'Sales'],
            [6, 'Accounts', 0, 5, 'Accounts', 0, 0, 1, 'Sales'],
            [4, 'Contacts', 0, 6, 'Contacts', 0, 0, 1, 'Sales'],
            [2, 'Potentials', 0, 7, 'Potentials', 0, 0, 1, 'Sales'],
            [8, 'Documents', 0, 9, 'Documents', 0, 0, 1, 'Tools'],
            [13, 'HelpDesk', 0, 11, 'HelpDesk', 0, 0, 1, 'Support'],
            [14, 'Products', 0, 8, 'Products', 0, 0, 1, 'Inventory'],
            [1, 'Dashboard', 0, 12, 'Dashboards', 0, 1, 0, 'Analytics'],
            [15, 'Faq', 0, -1, 'Faq', 0, 1, 1, 'Support'],
            [18, 'Vendors', 0, -1, 'Vendors', 0, 1, 1, 'Inventory'],
            [19, 'PriceBooks', 0, -1, 'PriceBooks', 0, 1, 1, 'Inventory'],
            [20, 'Quotes', 0, -1, 'Quotes', 0, 0, 1, 'Sales'],
            [21, 'PurchaseOrder', 0, -1, 'PurchaseOrder', 0, 0, 1, 'Inventory'],
            [22, 'SalesOrder', 0, -1, 'SalesOrder', 0, 0, 1, 'Sales'],
            [23, 'Invoice', 0, -1, 'Invoice', 0, 0, 1, 'Sales'],
            [24, 'Rss', 0, -1, 'Rss', 0, 1, 0, 'Tools'],
            [26, 'Campaigns', 0, -1, 'Campaigns', 0, 0, 1, 'Marketing'],
            [27, 'Portal', 0, -1, 'Portal', 0, 1, 0, 'Tools'],
            [29, 'Users', 0, -1, 'Users', 0, 1, 0, null],
        ];
        $tabIds = $this->createTabs($tabs);

        // Populate the vtiger_blocks vtiger_table
        /** Potential */
        [$potentialInformation, $potentialCustom, $potentialDescription] = $this->createBlocks(2, ['LBL_OPPORTUNITY_INFORMATION', 'LBL_CUSTOM_INFORMATION', 'LBL_DESCRIPTION_INFORMATION']);
        /** Contacts */
        [$contactInformation, $contactCustom, $contactPortal, $contactAddress, $contactDescription, $contactImage] = $this->createBlocks(4, ['LBL_CONTACT_INFORMATION', 'LBL_CUSTOM_INFORMATION', 'LBL_CUSTOMER_PORTAL_INFORMATION', 'LBL_ADDRESS_INFORMATION', 'LBL_DESCRIPTION_INFORMATION', 'LBL_IMAGE_INFORMATION']);
        /** Accounts */
        [$accountInformation, $accountCustom, $accountAddress, $accountDescription] = $this->createBlocks(6, ['LBL_ACCOUNT_INFORMATION', 'LBL_CUSTOM_INFORMATION', 'LBL_ADDRESS_INFORMATION', 'LBL_DESCRIPTION_INFORMATION']);
        /** Leads */
        [$leadInformation, $leadCustom, $leadAddress, $leadDescription] = $this->createBlocks(7, ['LBL_LEAD_INFORMATION', 'LBL_CUSTOM_INFORMATION', 'LBL_ADDRESS_INFORMATION', 'LBL_DESCRIPTION_INFORMATION',]);
        /** Documents */
        [$documentInformation, $documentFile, $documentDescription] = $this->createBlocks(8, ['LBL_NOTE_INFORMATION', 'LBL_FILE_INFORMATION', 'LBL_DESCRIPTION']);
        /** HelpDesk */
        [$helpDeskInformation, $helpDeskCustom, $helpDeskDescription, $helpDeskResolution, $helpDeskComment] = $this->createBlocks(13, ['LBL_TICKET_INFORMATION', 'LBL_CUSTOM_INFORMATION', 'LBL_DESCRIPTION_INFORMATION', 'LBL_TICKET_RESOLUTION', 'LBL_COMMENTS',]);
        /** Products */
        [$productInformation, $productPricing, $productStock, $productCustom, $productImage, $productDescription] = $this->createBlocks(14, ['LBL_PRODUCT_INFORMATION', 'LBL_PRICING_INFORMATION', 'LBL_STOCK_INFORMATION', 'LBL_CUSTOM_INFORMATION', 'LBL_IMAGE_INFORMATION', 'LBL_DESCRIPTION_INFORMATION']);
        /** Faq */
        [$faqInformation, $faqComment] = $this->createBlocks(15, ['LBL_FAQ_INFORMATION', 'LBL_COMMENT_INFORMATION']);
        /** Vendors */
        [$vendorInformation, $vendorCustom, $vendorAddress, $vendorDescription] = $this->createBlocks(18, ['LBL_VENDOR_INFORMATION', 'LBL_CUSTOM_INFORMATION', 'LBL_VENDOR_ADDRESS_INFORMATION', 'LBL_DESCRIPTION_INFORMATION']);
        /** PriceBooks */
        [$priceBookInformation, $priceBookCustom, $priceBookDescription] = $this->createBlocks(19, ['LBL_PRICEBOOK_INFORMATION', 'LBL_CUSTOM_INFORMATION', 'LBL_DESCRIPTION_INFORMATION']);
        /** Quotes */
        [$quoteInformation, $quoteCustom, $quoteAddress, $quoteRelated, $quoteTerms, $quoteDescription] = $this->createBlocks(20, ['LBL_QUOTE_INFORMATION', 'LBL_CUSTOM_INFORMATION', 'LBL_ADDRESS_INFORMATION', 'LBL_RELATED_PRODUCTS', 'LBL_TERMS_INFORMATION', 'LBL_DESCRIPTION_INFORMATION']);
        /** PurchaseOrder */
        [$purchaseOrderInformation, $purchaseOrderCustom, $purchaseOrderAddress, $purchaseOrderRelated, $purchaseOrderTerms, $purchaseOrderDescription] = $this->createBlocks(21, ['LBL_PO_INFORMATION', 'LBL_CUSTOM_INFORMATION', 'LBL_ADDRESS_INFORMATION', 'LBL_RELATED_PRODUCTS', 'LBL_TERMS_INFORMATION', 'LBL_DESCRIPTION_INFORMATION']);
        /** SalesOrder */
        [$salesOrderInformation, $salesOrderCustom, $salesOrderAddress, $salesOrderRelated, $salesOrderTerms, $salesOrderDescription, $salesOrderRecurring] = $this->createBlocks(22, ['LBL_SO_INFORMATION', 'LBL_CUSTOM_INFORMATION', 'LBL_ADDRESS_INFORMATION', 'LBL_RELATED_PRODUCTS', 'LBL_TERMS_INFORMATION', 'LBL_DESCRIPTION_INFORMATION', 'Recurring Invoice Information']);
        /** Invoice */
        [$invoiceInformation, $invoiceCustom, $invoiceAddress, $invoiceRelated, $invoiceTerms, $invoiceDescription] = $this->createBlocks(23, ['LBL_INVOICE_INFORMATION', 'LBL_CUSTOM_INFORMATION', 'LBL_ADDRESS_INFORMATION', 'LBL_RELATED_PRODUCTS', 'LBL_TERMS_INFORMATION', 'LBL_DESCRIPTION_INFORMATION']);
        /** Campaigns */
        [$campaignInformation, $campaignCustom, $campaignExpectation, $campaignDescription] = $this->createBlocks(26, ['LBL_CAMPAIGN_INFORMATION', 'LBL_CUSTOM_INFORMATION', 'LBL_EXPECTATIONS_AND_ACTUALS', 'LBL_DESCRIPTION_INFORMATION']);
        /** Users */
        [$userLogin, $userCurrency, $userMore, $userAddress, $userImage, $userAdvance] = $this->createBlocks(29, ['LBL_USERLOGIN_ROLE', 'LBL_CURRENCY_CONFIGURATION', 'LBL_MORE_INFORMATION', 'LBL_ADDRESS_INFORMATION', 'LBL_USER_IMAGE_INFORMATION', 'LBL_USER_ADV_OPTIONS']);


        $fields = [
            //Account Details -- START
            //Block9
            [6, null, 'accountname', 'vtiger_account', 1, '2', 'accountname', 'Account Name', 1, 0, '', 100, 1, $accountInformation, 1, 'V~M', 0, 1, 'BAS', 0],
            [6, null, 'account_no', 'vtiger_account', 1, '4', 'account_no', 'Account No', 1, 0, '', 100, 2, $accountInformation, 1, 'V~O', 3, null, 'BAS', 0],
            [6, null, 'phone', 'vtiger_account', 1, '11', 'phone', 'Phone', 1, 2, '', 100, 4, $accountInformation, 1, 'V~O', 2, 2, 'BAS', 1],
            [6, null, 'website', 'vtiger_account', 1, '17', 'website', 'Website', 1, 2, '', 100, 3, $accountInformation, 1, 'V~O', 2, 3, 'BAS', 1],
            [6, null, 'fax', 'vtiger_account', 1, '1', 'fax', 'Fax', 1, 2, '', 100, 6, $accountInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [6, null, 'tickersymbol', 'vtiger_account', 1, '1', 'tickersymbol', 'Ticker Symbol', 1, 2, '', 100, 5, $accountInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [6, null, 'otherphone', 'vtiger_account', 1, '11', 'otherphone', 'Other Phone', 1, 2, '', 100, 8, $accountInformation, 1, 'V~O', 1, null, 'ADV', 1],
            [6, null, 'parentid', 'vtiger_account', 1, '51', 'account_id', 'Member Of', 1, 2, '', 100, 7, $accountInformation, 1, 'I~O', 1, null, 'BAS', 0],
            [6, null, 'email1', 'vtiger_account', 1, '13', 'email1', 'Email', 1, 2, '', 100, 10, $accountInformation, 1, 'E~O', 1, null, 'BAS', 1],
            [6, null, 'employees', 'vtiger_account', 1, '7', 'employees', 'Employees', 1, 2, '', 100, 9, $accountInformation, 1, 'I~O', 1, null, 'ADV', 1],
            [6, null, 'email2', 'vtiger_account', 1, '13', 'email2', 'Other Email', 1, 2, '', 100, 11, $accountInformation, 1, 'E~O', 1, null, 'ADV', 1],
            [6, null, 'ownership', 'vtiger_account', 1, '1', 'ownership', 'Ownership', 1, 2, '', 100, 12, $accountInformation, 1, 'V~O', 1, null, 'ADV', 1],
            [6, null, 'rating', 'vtiger_account', 1, '15', 'rating', 'Rating', 1, 2, '', 100, 14, $accountInformation, 1, 'V~O', 1, null, 'ADV', 1],
            [6, null, 'industry', 'vtiger_account', 1, '15', 'industry', 'industry', 1, 2, '', 100, 13, $accountInformation, 1, 'V~O', 1, null, 'ADV', 1],
            [6, null, 'siccode', 'vtiger_account', 1, '1', 'siccode', 'SIC Code', 1, 2, '', 100, 16, $accountInformation, 1, 'V~O', 1, null, 'ADV', 1],
            [6, null, 'account_type', 'vtiger_account', 1, '15', 'accounttype', 'Type', 1, 2, '', 100, 15, $accountInformation, 1, 'V~O', 1, null, 'ADV', 1],
            [6, null, 'annualrevenue', 'vtiger_account', 1, '71', 'annual_revenue', 'Annual Revenue', 1, 2, '', 100, 18, $accountInformation, 1, 'I~O', 1, null, 'ADV', 1],
            [6, null, 'emailoptout', 'vtiger_account', 1, '56', 'emailoptout', 'Email Opt Out', 1, 2, '', 100, 17, $accountInformation, 1, 'C~O', 1, null, 'ADV', 1],
            [6, null, 'notify_owner', 'vtiger_account', 1, 56, 'notify_owner', 'Notify Owner', 1, 2, '', 10, 20, $accountInformation, 1, 'C~O', 1, null, 'ADV', 1],
            [6, null, 'smownerid', 'vtiger_crmentity', 1, '53', 'assigned_user_id', 'Assigned To', 1, 0, '', 100, 19, $accountInformation, 1, 'V~M', 0, 4, 'BAS', 1],
            [6, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 22, $accountInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [6, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 21, $accountInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [6, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 23, $accountInformation, 3, 'V~O', 3, null, 'BAS', 0],
            //Block 11
            [6, null, 'bill_street', 'vtiger_accountbillads', 1, '21', 'bill_street', 'Billing Address', 1, 2, '', 100, 1, $accountAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [6, null, 'ship_street', 'vtiger_accountshipads', 1, '21', 'ship_street', 'Shipping Address', 1, 2, '', 100, 2, $accountAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [6, null, 'bill_city', 'vtiger_accountbillads', 1, '1', 'bill_city', 'Billing City', 1, 2, '', 100, 5, $accountAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [6, null, 'ship_city', 'vtiger_accountshipads', 1, '1', 'ship_city', 'Shipping City', 1, 2, '', 100, 6, $accountAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [6, null, 'bill_state', 'vtiger_accountbillads', 1, '1', 'bill_state', 'Billing State', 1, 2, '', 100, 7, $accountAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [6, null, 'ship_state', 'vtiger_accountshipads', 1, '1', 'ship_state', 'Shipping State', 1, 2, '', 100, 8, $accountAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [6, null, 'bill_code', 'vtiger_accountbillads', 1, '1', 'bill_code', 'Billing Code', 1, 2, '', 100, 9, $accountAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [6, null, 'ship_code', 'vtiger_accountshipads', 1, '1', 'ship_code', 'Shipping Code', 1, 2, '', 100, 10, $accountAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [6, null, 'bill_country', 'vtiger_accountbillads', 1, '1', 'bill_country', 'Billing Country', 1, 2, '', 100, 11, $accountAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [6, null, 'ship_country', 'vtiger_accountshipads', 1, '1', 'ship_country', 'Shipping Country', 1, 2, '', 100, 12, $accountAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [6, null, 'bill_pobox', 'vtiger_accountbillads', 1, '1', 'bill_pobox', 'Billing Po Box', 1, 2, '', 100, 3, $accountAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [6, null, 'ship_pobox', 'vtiger_accountshipads', 1, '1', 'ship_pobox', 'Shipping Po Box', 1, 2, '', 100, 4, $accountAddress, 1, 'V~O', 1, null, 'BAS', 1],
            //Block12
            [6, null, 'description', 'vtiger_crmentity', 1, '19', 'description', 'Description', 1, 2, '', 100, 1, $accountDescription, 1, 'V~O', 1, null, 'BAS', 1],
            //Account Details -- END

            //Lead Details --- START
            //Block13 -- Start
            [7, null, 'salutation', 'vtiger_leaddetails', 1, '55', 'salutationtype', 'Salutation', 1, 0, '', 100, 1, $leadInformation, 3, 'V~O', 1, null, 'BAS', 1],
            [7, null, 'firstname', 'vtiger_leaddetails', 1, '55', 'firstname', 'First Name', 1, 0, '', 100, 2, $leadInformation, 1, 'V~O', 2, 1, 'BAS', 1],
            [7, null, 'lead_no', 'vtiger_leaddetails', 1, '4', 'lead_no', 'Lead No', 1, 0, '', 100, 3, $leadInformation, 1, 'V~O', 3, null, 'BAS', 0],
            [7, null, 'phone', 'vtiger_leadaddress', 1, '11', 'phone', 'Phone', 1, 2, '', 100, 5, $leadInformation, 1, 'V~O', 2, 4, 'BAS', 1],
            [7, null, 'lastname', 'vtiger_leaddetails', 1, '255', 'lastname', 'Last Name', 1, 0, '', 100, 4, $leadInformation, 1, 'V~M', 0, 2, 'BAS', 1],
            [7, null, 'mobile', 'vtiger_leadaddress', 1, '1', 'mobile', 'Mobile', 1, 2, '', 100, 7, $leadInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [7, null, 'company', 'vtiger_leaddetails', 1, '2', 'company', 'Company', 1, 2, '', 100, 6, $leadInformation, 1, 'V~M', 2, 3, 'BAS', 1],
            [7, null, 'fax', 'vtiger_leadaddress', 1, '1', 'fax', 'Fax', 1, 2, '', 100, 9, $leadInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [7, null, 'designation', 'vtiger_leaddetails', 1, '1', 'designation', 'Designation', 1, 2, '', 100, 8, $leadInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [7, null, 'email', 'vtiger_leaddetails', 1, '13', 'email', 'Email', 1, 2, '', 100, 11, $leadInformation, 1, 'E~O', 2, 5, 'BAS', 1],
            [7, null, 'leadsource', 'vtiger_leaddetails', 1, '15', 'leadsource', 'Lead Source', 1, 2, '', 100, 10, $leadInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [7, null, 'website', 'vtiger_leadsubdetails', 1, '17', 'website', 'Website', 1, 2, '', 100, 13, $leadInformation, 1, 'V~O', 1, null, 'ADV', 1],
            [7, null, 'industry', 'vtiger_leaddetails', 1, '15', 'industry', 'Industry', 1, 2, '', 100, 12, $leadInformation, 1, 'V~O', 1, null, 'ADV', 1],
            [7, null, 'leadstatus', 'vtiger_leaddetails', 1, '15', 'leadstatus', 'Lead Status', 1, 2, '', 100, 15, $leadInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [7, null, 'annualrevenue', 'vtiger_leaddetails', 1, '71', 'annualrevenue', 'Annual Revenue', 1, 2, '', 100, 14, $leadInformation, 1, 'I~O', 1, null, 'ADV', 1],
            [7, null, 'rating', 'vtiger_leaddetails', 1, '15', 'rating', 'Rating', 1, 2, '', 100, 17, $leadInformation, 1, 'V~O', 1, null, 'ADV', 1],
            [7, null, 'smownerid', 'vtiger_crmentity', 1, '53', 'assigned_user_id', 'Assigned To', 1, 0, '', 100, 19, $leadInformation, 1, 'V~M', 0, 6, 'BAS', 1],
            [7, null, 'secondaryemail', 'vtiger_leaddetails', 1, '13', 'secondaryemail', 'Secondary Email', 1, 2, '', 100, 18, $leadInformation, 1, 'E~O', 1, null, 'ADV', 1],
            [7, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 21, $leadInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [7, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 20, $leadInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [7, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 23, $leadInformation, 3, 'V~O', 3, null, 'BAS', 0],
            //Block13 -- End
            //Block15 -- Start
            [7, null, 'lane', 'vtiger_leadaddress', 1, '21', 'lane', 'Street', 1, 2, '', 100, 1, $leadAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [7, null, 'code', 'vtiger_leadaddress', 1, '1', 'code', 'Postal Code', 1, 2, '', 100, 3, $leadAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [7, null, 'city', 'vtiger_leadaddress', 1, '1', 'city', 'City', 1, 2, '', 100, 4, $leadAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [7, null, 'country', 'vtiger_leadaddress', 1, '1', 'country', 'Country', 1, 2, '', 100, 5, $leadAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [7, null, 'state', 'vtiger_leadaddress', 1, '1', 'state', 'State', 1, 2, '', 100, 6, $leadAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [7, null, 'pobox', 'vtiger_leadaddress', 1, '1', 'pobox', 'Po Box', 1, 2, '', 100, 2, $leadAddress, 1, 'V~O', 1, null, 'BAS', 1],
            //Block15 --End
            //Block16 -- Start
            [7, null, 'description', 'vtiger_crmentity', 1, '19', 'description', 'Description', 1, 2, '', 100, 1, $leadDescription, 1, 'V~O', 1, null, 'BAS', 1],
            //Block16 -- End
            //Lead Details -- END

            //Contact Details -- START
            //Block4 -- Start
            [4, null, 'salutation', 'vtiger_contactdetails', 1, '55', 'salutationtype', 'Salutation', 1, 0, '', 100, 1, $contactInformation, 3, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'firstname', 'vtiger_contactdetails', 1, '55', 'firstname', 'First Name', 1, 0, '', 100, 2, $contactInformation, 1, 'V~O', 2, 1, 'BAS', 1],
            [4, null, 'contact_no', 'vtiger_contactdetails', 1, '4', 'contact_no', 'Contact Id', 1, 0, '', 100, 3, $contactInformation, 1, 'V~O', 3, null, 'BAS', 0],
            [4, null, 'phone', 'vtiger_contactdetails', 1, '11', 'phone', 'Office Phone', 1, 2, '', 100, 5, $contactInformation, 1, 'V~O', 2, 4, 'BAS', 1],
            [4, null, 'lastname', 'vtiger_contactdetails', 1, '255', 'lastname', 'Last Name', 1, 0, '', 100, 4, $contactInformation, 1, 'V~M', 0, 2, 'BAS', 1],
            [4, null, 'mobile', 'vtiger_contactdetails', 1, '1', 'mobile', 'Mobile', 1, 2, '', 100, 7, $contactInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'accountid', 'vtiger_contactdetails', 1, '51', 'account_id', 'Account Name', 1, 0, '', 100, 6, $contactInformation, 1, 'I~O', 2, 3, 'BAS', 1],
            [4, null, 'homephone', 'vtiger_contactsubdetails', 1, '11', 'homephone', 'Home Phone', 1, 2, '', 100, 9, $contactInformation, 1, 'V~O', 1, null, 'ADV', 1],
            [4, null, 'leadsource', 'vtiger_contactsubdetails', 1, '15', 'leadsource', 'Lead Source', 1, 2, '', 100, 8, $contactInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'otherphone', 'vtiger_contactsubdetails', 1, '11', 'otherphone', 'Other Phone', 1, 2, '', 100, 11, $contactInformation, 1, 'V~O', 1, null, 'ADV', 1],
            [4, null, 'title', 'vtiger_contactdetails', 1, '1', 'title', 'Title', 1, 2, '', 100, 10, $contactInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'fax', 'vtiger_contactdetails', 1, '1', 'fax', 'Fax', 1, 2, '', 100, 13, $contactInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'department', 'vtiger_contactdetails', 1, '1', 'department', 'Department', 1, 2, '', 100, 12, $contactInformation, 1, 'V~O', 1, null, 'ADV', 1],
            [4, null, 'birthday', 'vtiger_contactsubdetails', 1, '5', 'birthday', 'Birthdate', 1, 2, '', 100, 16, $contactInformation, 1, 'D~O', 1, null, 'ADV', 1],
            [4, null, 'email', 'vtiger_contactdetails', 1, '13', 'email', 'Email', 1, 2, '', 100, 15, $contactInformation, 1, 'E~O', 2, 5, 'BAS', 1],
            [4, null, 'reportsto', 'vtiger_contactdetails', 1, '57', 'contact_id', 'Reports To', 1, 2, '', 100, 18, $contactInformation, 1, 'V~O', 1, null, 'ADV', 0],
            [4, null, 'assistant', 'vtiger_contactsubdetails', 1, '1', 'assistant', 'Assistant', 1, 2, '', 100, 17, $contactInformation, 1, 'V~O', 1, null, 'ADV', 1],
            [4, null, 'secondaryemail', 'vtiger_contactdetails', 1, '13', 'secondaryemail', 'Secondary Email', 1, 2, '', 100, 20, $contactInformation, 1, 'E~O', 1, null, 'ADV', 1],
            [4, null, 'assistantphone', 'vtiger_contactsubdetails', 1, '11', 'assistantphone', 'Assistant Phone', 1, 2, '', 100, 19, $contactInformation, 1, 'V~O', 1, null, 'ADV', 1],
            [4, null, 'donotcall', 'vtiger_contactdetails', 1, '56', 'donotcall', 'Do Not Call', 1, 2, '', 100, 22, $contactInformation, 1, 'C~O', 1, null, 'ADV', 1],
            [4, null, 'emailoptout', 'vtiger_contactdetails', 1, '56', 'emailoptout', 'Email Opt Out', 1, 2, '', 100, 21, $contactInformation, 1, 'C~O', 1, null, 'ADV', 1],
            [4, null, 'smownerid', 'vtiger_crmentity', 1, '53', 'assigned_user_id', 'Assigned To', 1, 0, '', 100, 24, $contactInformation, 1, 'V~M', 0, 6, 'BAS', 1],
            [4, null, 'reference', 'vtiger_contactdetails', 1, '56', 'reference', 'Reference', 1, 2, '', 10, 23, $contactInformation, 1, 'C~O', 1, null, 'ADV', 1],
            [4, null, 'notify_owner', 'vtiger_contactdetails', 1, '56', 'notify_owner', 'Notify Owner', 1, 2, '', 10, 26, $contactInformation, 1, 'C~O', 1, null, 'ADV', 1],
            [4, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 25, $contactInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [4, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 27, $contactInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [4, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 28, $contactInformation, 3, 'V~O', 3, null, 'BAS', 0],
            //Block4 -- End
            //Block6 - Begin Customer Portal
            [4, null, 'portal', 'vtiger_customerdetails', 1, '56', 'portal', 'Portal User', 1, 2, '', 100, 1, $contactPortal, 1, 'C~O', 1, null, 'ADV', 0],
            [4, null, 'support_start_date', 'vtiger_customerdetails', 1, '5', 'support_start_date', 'Support Start Date', 1, 2, '', 100, 2, $contactPortal, 1, 'D~O', 1, null, 'ADV', 1],
            [4, null, 'support_end_date', 'vtiger_customerdetails', 1, '5', 'support_end_date', 'Support End Date', 1, 2, '', 100, 3, $contactPortal, 1, 'D~O~OTH~GE~support_start_date~Support Start Date', 1, null, 'ADV', 1],
            //Block6 - End Customer Portal
            //Block 7 -- Start
            [4, null, 'mailingstreet', 'vtiger_contactaddress', 1, '21', 'mailingstreet', 'Mailing Street', 1, 2, '', 100, 1, $contactAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'otherstreet', 'vtiger_contactaddress', 1, '21', 'otherstreet', 'Other Street', 1, 2, '', 100, 2, $contactAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'mailingcity', 'vtiger_contactaddress', 1, '1', 'mailingcity', 'Mailing City', 1, 2, '', 100, 5, $contactAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'othercity', 'vtiger_contactaddress', 1, '1', 'othercity', 'Other City', 1, 2, '', 100, 6, $contactAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'mailingstate', 'vtiger_contactaddress', 1, '1', 'mailingstate', 'Mailing State', 1, 2, '', 100, 7, $contactAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'otherstate', 'vtiger_contactaddress', 1, '1', 'otherstate', 'Other State', 1, 2, '', 100, 8, $contactAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'mailingzip', 'vtiger_contactaddress', 1, '1', 'mailingzip', 'Mailing Zip', 1, 2, '', 100, 9, $contactAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'otherzip', 'vtiger_contactaddress', 1, '1', 'otherzip', 'Other Zip', 1, 2, '', 100, 10, $contactAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'mailingcountry', 'vtiger_contactaddress', 1, '1', 'mailingcountry', 'Mailing Country', 1, 2, '', 100, 11, $contactAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'othercountry', 'vtiger_contactaddress', 1, '1', 'othercountry', 'Other Country', 1, 2, '', 100, 12, $contactAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'mailingpobox', 'vtiger_contactaddress', 1, '1', 'mailingpobox', 'Mailing Po Box', 1, 2, '', 100, 3, $contactAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [4, null, 'otherpobox', 'vtiger_contactaddress', 1, '1', 'otherpobox', 'Other Po Box', 1, 2, '', 100, 4, $contactAddress, 1, 'V~O', 1, null, 'BAS', 1],
            //Block7 -- End
            //ContactImageInformation
            [4, null, 'imagename', 'vtiger_contactdetails', 1, '69', 'imagename', 'Contact Image', 1, 2, '', 100, 1, $contactImage, 1, 'V~O', 3, null, 'ADV', 0],
            //Block8 -- Start
            [4, null, 'description', 'vtiger_crmentity', 1, '19', 'description', 'Description', 1, 2, '', 100, 1, $contactDescription, 1, 'V~O', 1, null, 'BAS', 1],
            //Block8 -- End
            //Contact Details -- END

            //Potential Details -- START
            //Block1 -- Start
            [2, null, 'potentialname', 'vtiger_potential', 1, '2', 'potentialname', 'Potential Name', 1, 0, '', 100, 1, $potentialInformation, 1, 'V~M', 0, 1, 'BAS', 1],
            [2, null, 'potential_no', 'vtiger_potential', 1, '4', 'potential_no', 'Potential No', 1, 0, '', 100, 2, $potentialInformation, 1, 'V~O', 3, null, 'BAS', 0],
            [2, null, 'amount', 'vtiger_potential', 1, 71, 'amount', 'Amount', 1, 2, '', 100, 4, $potentialInformation, 1, 'N~O', 2, 5, 'BAS', 1],
            //changed for b2c model
            [2, null, 'related_to', 'vtiger_potential', 1, '10', 'related_to', 'Related To', 1, 0, '', 100, 3, $potentialInformation, 1, 'V~M', 0, 2, 'BAS', 1],
            //b2c model changes end
            [2, null, 'closingdate', 'vtiger_potential', 1, '23', 'closingdate', 'Expected Close Date', 1, 2, '', 100, 7, $potentialInformation, 1, 'D~M', 2, 3, 'BAS', 1],
            [2, null, 'potentialtype', 'vtiger_potential', 1, '15', 'opportunity_type', 'Type', 1, 2, '', 100, 6, $potentialInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [2, null, 'nextstep', 'vtiger_potential', 1, '1', 'nextstep', 'Next Step', 1, 2, '', 100, 9, $potentialInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [2, null, 'leadsource', 'vtiger_potential', 1, '15', 'leadsource', 'Lead Source', 1, 2, '', 100, 8, $potentialInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [2, null, 'sales_stage', 'vtiger_potential', 1, '15', 'sales_stage', 'Sales Stage', 1, 2, '', 100, 11, $potentialInformation, 1, 'V~M', 2, 4, 'BAS', 1],
            [2, null, 'smownerid', 'vtiger_crmentity', 1, '53', 'assigned_user_id', 'Assigned To', 1, 2, '', 100, 10, $potentialInformation, 1, 'V~M', 0, 6, 'BAS', 1],
            [2, null, 'probability', 'vtiger_potential', 1, '9', 'probability', 'Probability', 1, 2, '', 100, 13, $potentialInformation, 1, 'N~O', 1, null, 'BAS', 1],
            [2, null, 'campaignid', 'vtiger_potential', 1, '58', 'campaignid', 'Campaign Source', 1, 2, '', 100, 12, $potentialInformation, 1, 'N~O', 1, null, 'BAS', 1],
            [2, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 15, $potentialInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [2, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 14, $potentialInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [2, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 16, $potentialInformation, 3, 'V~O', 3, null, 'BAS', 0],
            //Block1 -- End
            //Block3 -- Start
            [2, null, 'description', 'vtiger_crmentity', 1, '19', 'description', 'Description', 1, 2, '', 100, 1, $potentialDescription, 1, 'V~O', 1, null, 'BAS', 1],
            //Block3 -- End
            //Potential Details -- END

            //campaign entries being added
            [26, null, 'campaignname', 'vtiger_campaign', 1, '2', 'campaignname', 'Campaign Name', 1, 0, '', 100, 1, $campaignInformation, 1, 'V~M', 0, 1, 'BAS', 1],
            [26, null, 'campaign_no', 'vtiger_campaign', 1, '4', 'campaign_no', 'Campaign No', 1, 0, '', 100, 2, $campaignInformation, 1, 'V~O', 3, null, 'BAS', 0],
            [26, null, 'campaigntype', 'vtiger_campaign', 1, 15, 'campaigntype', 'Campaign Type', 1, 2, '', 100, 5, $campaignInformation, 1, 'V~O', 2, 3, 'BAS', 1],
            [26, null, 'product_id', 'vtiger_campaign', 1, 59, 'product_id', 'Product', 1, 2, '', 100, 6, $campaignInformation, 1, 'I~O', 2, 5, 'BAS', 1],
            [26, null, 'campaignstatus', 'vtiger_campaign', 1, 15, 'campaignstatus', 'Campaign Status', 1, 2, '', 100, 4, $campaignInformation, 1, 'V~O', 2, 6, 'BAS', 1],
            [26, null, 'closingdate', 'vtiger_campaign', 1, '23', 'closingdate', 'Expected Close Date', 1, 2, '', 100, 8, $campaignInformation, 1, 'D~M', 2, 2, 'BAS', 1],
            [26, null, 'smownerid', 'vtiger_crmentity', 1, '53', 'assigned_user_id', 'Assigned To', 1, 0, '', 100, 3, $campaignInformation, 1, 'V~M', 0, 7, 'BAS', 1],
            [26, null, 'numsent', 'vtiger_campaign', 1, '9', 'numsent', 'Num Sent', 1, 2, '', 100, 12, $campaignInformation, 1, 'N~O', 1, null, 'BAS', 1],
            [26, null, 'sponsor', 'vtiger_campaign', 1, '1', 'sponsor', 'Sponsor', 1, 2, '', 100, 9, $campaignInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [26, null, 'targetaudience', 'vtiger_campaign', 1, '1', 'targetaudience', 'Target Audience', 1, 2, '', 100, 7, $campaignInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [26, null, 'targetsize', 'vtiger_campaign', 1, '1', 'targetsize', 'TargetSize', 1, 2, '', 100, 10, $campaignInformation, 1, 'I~O', 1, null, 'BAS', 1],
            [26, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 11, $campaignInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [26, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 13, $campaignInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [26, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 16, $campaignInformation, 3, 'V~O', 3, null, 'BAS', 0],
            [26, null, 'expectedresponse', 'vtiger_campaign', 1, '15', 'expectedresponse', 'Expected Response', 1, 2, '', 100, 3, $campaignExpectation, 1, 'V~O', 2, 4, 'BAS', 1],
            [26, null, 'expectedrevenue', 'vtiger_campaign', 1, '71', 'expectedrevenue', 'Expected Revenue', 1, 2, '', 100, 4, $campaignExpectation, 1, 'N~O', 1, null, 'BAS', 1],
            [26, null, 'budgetcost', 'vtiger_campaign', 1, '71', 'budgetcost', 'Budget Cost', 1, 2, '', 100, 1, $campaignExpectation, 1, 'N~O', 1, null, 'BAS', 1],
            [26, null, 'actualcost', 'vtiger_campaign', 1, '71', 'actualcost', 'Actual Cost', 1, 2, '', 100, 2, $campaignExpectation, 1, 'N~O', 1, null, 'BAS', 1],
            [26, null, 'expectedresponsecount', 'vtiger_campaign', 1, '1', 'expectedresponsecount', 'Expected Response Count', 1, 2, '', 100, 7, $campaignExpectation, 1, 'I~O', 1, null, 'BAS', 1],
            [26, null, 'expectedsalescount', 'vtiger_campaign', 1, '1', 'expectedsalescount', 'Expected Sales Count', 1, 2, '', 100, 5, $campaignExpectation, 1, 'I~O', 1, null, 'BAS', 1],
            [26, null, 'expectedroi', 'vtiger_campaign', 1, '71', 'expectedroi', 'Expected ROI', 1, 2, '', 100, 9, $campaignExpectation, 1, 'N~O', 1, null, 'BAS', 1],
            [26, null, 'actualresponsecount', 'vtiger_campaign', 1, '1', 'actualresponsecount', 'Actual Response Count', 1, 2, '', 100, 8, $campaignExpectation, 1, 'I~O', 1, null, 'BAS', 1],
            [26, null, 'actualsalescount', 'vtiger_campaign', 1, '1', 'actualsalescount', 'Actual Sales Count', 1, 2, '', 100, 6, $campaignExpectation, 1, 'I~O', 1, null, 'BAS', 1],
            [26, null, 'actualroi', 'vtiger_campaign', 1, '71', 'actualroi', 'Actual ROI', 1, 2, '', 100, 10, $campaignExpectation, 1, 'N~O', 1, null, 'BAS', 1],
            [26, null, 'description', 'vtiger_crmentity', 1, '19', 'description', 'Description', 1, 2, '', 100, 1, $campaignDescription, 1, 'V~O', 1, null, 'BAS', 1],
            //Campaign entries end

            //Ticket Details -- START
            //Block25 -- Start
            [13, null, 'ticket_no', 'vtiger_troubletickets', 1, '4', 'ticket_no', 'Ticket No', 1, 0, '', 100, 13, $helpDeskInformation, 1, 'V~O', 3, null, 'BAS', 0],
            [13, null, 'smownerid', 'vtiger_crmentity', 1, '53', 'assigned_user_id', 'Assigned To', 1, 0, '', 100, 4, $helpDeskInformation, 1, 'V~M', 0, 4, 'BAS', 1],
            [13, null, 'parent_id', 'vtiger_troubletickets', 1, '68', 'parent_id', 'Related To', 1, 0, '', 100, 2, $helpDeskInformation, 1, 'I~O', 1, null, 'BAS', 1],
            [13, null, 'priority', 'vtiger_troubletickets', 1, '15', 'ticketpriorities', 'Priority', 1, 2, '', 100, 6, $helpDeskInformation, 1, 'V~O', 2, 3, 'BAS', 1],
            [13, null, 'product_id', 'vtiger_troubletickets', 1, '59', 'product_id', 'Product Name', 1, 2, '', 100, 5, $helpDeskInformation, 1, 'I~O', 1, null, 'BAS', 1],
            [13, null, 'severity', 'vtiger_troubletickets', 1, '15', 'ticketseverities', 'Severity', 1, 2, '', 100, 8, $helpDeskInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [13, null, 'status', 'vtiger_troubletickets', 1, '15', 'ticketstatus', 'Status', 1, 2, '', 100, 7, $helpDeskInformation, 1, 'V~M', 1, 2, 'BAS', 1],
            [13, null, 'category', 'vtiger_troubletickets', 1, '15', 'ticketcategories', 'Category', 1, 2, '', 100, 10, $helpDeskInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [13, null, 'hours', 'vtiger_troubletickets', 1, '1', 'hours', 'Hours', 1, 2, '', 100, 9, $helpDeskInformation, 1, 'I~O', 1, null, 'BAS', 1],
            [13, null, 'days', 'vtiger_troubletickets', 1, '1', 'days', 'Days', 1, 2, '', 100, 10, $helpDeskInformation, 1, 'I~O', 1, null, 'BAS', 1],
            [13, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 9, $helpDeskInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [13, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 12, $helpDeskInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [13, null, 'from_portal', 'vtiger_ticketcf', 1, '56', 'from_portal', 'From Portal', 1, 0, '', 100, 13, $helpDeskInformation, 3, 'C~O', 3, null, 'BAS', 0],
            [13, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 16, $helpDeskInformation, 3, 'V~O', 3, null, 'BAS', 0],
            [13, null, 'title', 'vtiger_troubletickets', 1, '22', 'ticket_title', 'Title', 1, 0, '', 100, 1, $helpDeskInformation, 1, 'V~M', 0, 1, 'BAS', 1],
            [13, null, 'description', 'vtiger_crmentity', 1, '19', 'description', 'Description', 1, 2, '', 100, 1, $helpDeskDescription, 1, 'V~O', 2, 4, 'BAS', 1],
            [13, null, 'solution', 'vtiger_troubletickets', 1, '19', 'solution', 'Solution', 1, 0, '', 100, 1, $helpDeskResolution, 1, 'V~O', 3, null, 'BAS', 0],
            [13, null, 'comments', 'vtiger_ticketcomments', 1, '19', 'comments', 'Add Comment', 1, 0, '', 100, 1, $helpDeskComment, 1, 'V~O', 3, null, 'BAS', 0],
            //Block25-30 -- End
            //Ticket Details -- END

            //Product Details -- START
            //Block31-36 -- Start
            [14, null, 'productname', 'vtiger_products', 1, '2', 'productname', 'Product Name', 1, 0, '', 100, 1, $productInformation, 1, 'V~M', 0, 1, 'BAS', 1],
            [14, null, 'product_no', 'vtiger_products', 1, '4', 'product_no', 'Product No', 1, 0, '', 100, 2, $productInformation, 1, 'V~O', 3, null, 'BAS', 0],
            [14, null, 'productcode', 'vtiger_products', 1, '1', 'productcode', 'Part Number', 1, 2, '', 100, 4, $productInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [14, null, 'discontinued', 'vtiger_products', 1, '56', 'discontinued', 'Product Active', 1, 2, '', 100, 3, $productInformation, 1, 'V~O', 2, 2, 'BAS', 1],
            [14, null, 'manufacturer', 'vtiger_products', 1, '15', 'manufacturer', 'Manufacturer', 1, 2, '', 100, 6, $productInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [14, null, 'productcategory', 'vtiger_products', 1, '15', 'productcategory', 'Product Category', 1, 2, '', 100, 6, $productInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [14, null, 'sales_start_date', 'vtiger_products', 1, '5', 'sales_start_date', 'Sales Start Date', 1, 2, '', 100, 5, $productInformation, 1, 'D~O', 1, null, 'BAS', 1],
            [14, null, 'sales_end_date', 'vtiger_products', 1, '5', 'sales_end_date', 'Sales End Date', 1, 2, '', 100, 8, $productInformation, 1, 'D~O~OTH~GE~sales_start_date~Sales Start Date', 1, null, 'BAS', 1],
            [14, null, 'start_date', 'vtiger_products', 1, '5', 'start_date', 'Support Start Date', 1, 2, '', 100, 7, $productInformation, 1, 'D~O', 1, null, 'BAS', 1],
            [14, null, 'expiry_date', 'vtiger_products', 1, '5', 'expiry_date', 'Support Expiry Date', 1, 2, '', 100, 10, $productInformation, 1, 'D~O~OTH~GE~start_date~Start Date', 1, null, 'BAS', 1],
            [14, null, 'website', 'vtiger_products', 1, '17', 'website', 'Website', 1, 2, '', 100, 14, $productInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [14, null, 'vendor_id', 'vtiger_products', 1, '75', 'vendor_id', 'Vendor Name', 1, 2, '', 100, 13, $productInformation, 1, 'I~O', 1, null, 'BAS', 1],
            [14, null, 'mfr_part_no', 'vtiger_products', 1, '1', 'mfr_part_no', 'Mfr PartNo', 1, 2, '', 100, 16, $productInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [14, null, 'vendor_part_no', 'vtiger_products', 1, '1', 'vendor_part_no', 'Vendor PartNo', 1, 2, '', 100, 15, $productInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [14, null, 'serialno', 'vtiger_products', 1, '1', 'serial_no', 'Serial No', 1, 2, '', 100, 18, $productInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [14, null, 'productsheet', 'vtiger_products', 1, '1', 'productsheet', 'Product Sheet', 1, 2, '', 100, 17, $productInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [14, null, 'glacct', 'vtiger_products', 1, '15', 'glacct', 'GL Account', 1, 2, '', 100, 20, $productInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [14, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 19, $productInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [14, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 21, $productInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [14, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 22, $productInformation, 3, 'V~O', 3, null, 'BAS', 0],
            //Block32 Pricing Information
            [14, null, 'unit_price', 'vtiger_products', 1, '72', 'unit_price', 'Unit Price', 1, 2, '', 100, 1, $productPricing, 1, 'N~O', 2, 3, 'BAS', 0],
            [14, null, 'commissionrate', 'vtiger_products', 1, '9', 'commissionrate', 'Commission Rate', 1, 2, '', 100, 2, $productPricing, 1, 'N~O', 1, null, 'BAS', 1],
            [14, null, 'taxclass', 'vtiger_products', 1, '83', 'taxclass', 'Tax Class', 1, 2, '', 100, 4, $productPricing, 1, 'V~O', 3, null, 'BAS', 1],
            //Block 33 stock info
            [14, null, 'usageunit', 'vtiger_products', 1, '15', 'usageunit', 'Usage Unit', 1, 2, '', 100, 1, $productStock, 1, 'V~O', 1, null, 'ADV', 1],
            [14, null, 'qty_per_unit', 'vtiger_products', 1, '1', 'qty_per_unit', 'Qty/Unit', 1, 2, '', 100, 2, $productStock, 1, 'N~O', 1, null, 'ADV', 1],
            [14, null, 'qtyinstock', 'vtiger_products', 1, '1', 'qtyinstock', 'Qty In Stock', 1, 2, '', 100, 3, $productStock, 1, 'NN~O', 0, 4, 'ADV', 1],
            [14, null, 'reorderlevel', 'vtiger_products', 1, '1', 'reorderlevel', 'Reorder Level', 1, 2, '', 100, 4, $productStock, 1, 'I~O', 1, null, 'ADV', 1],
            [14, null, 'smownerid', 'vtiger_crmentity', 1, '53', 'assigned_user_id', 'Handler', 1, 0, '', 100, 5, $productStock, 1, 'V~M', 0, 5, 'BAS', 1],
            [14, null, 'qtyindemand', 'vtiger_products', 1, '1', 'qtyindemand', 'Qty In Demand', 1, 2, '', 100, 6, $productStock, 1, 'I~O', 1, null, 'ADV', 1],
            //ProductImageInformation
            [14, null, 'imagename', 'vtiger_products', 1, '69', 'imagename', 'Product Image', 1, 2, '', 100, 1, $productImage, 1, 'V~O', 3, null, 'ADV', 1],
            //Block 36 Description Info
            [14, null, 'description', 'vtiger_crmentity', 1, '19', 'description', 'Description', 1, 2, '', 100, 1, $productDescription, 1, 'V~O', 1, null, 'BAS', 1],
            //Product Details -- END

            //Documents Details -- START
            //Block17 -- Start
            [8, null, 'title', 'vtiger_notes', 1, '2', 'notes_title', 'Title', 1, 0, '', 100, 1, $documentInformation, 1, 'V~M', 0, 1, 'BAS', 1],
            [8, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 5, $documentInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [8, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 6, $documentInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [8, null, 'smownerid', 'vtiger_crmentity', 1, '53', 'assigned_user_id', 'Assigned To', 1, 0, '', 100, 4, $documentInformation, 1, 'V~M', 0, 3, 'BAS', 1],
            [8, null, 'filename', 'vtiger_notes', 1, '28', 'filename', 'File Name', 1, 2, '', 100, 3, $documentFile, 1, 'V~O', 3, null, 'BAS', 0],
            [8, null, 'notecontent', 'vtiger_notes', 1, '19', 'notecontent', 'Note', 1, 2, '', 100, 1, $documentDescription, 1, 'V~O', 1, null, 'BAS', 0],
            [8, null, 'filetype', 'vtiger_notes', 1, 1, 'filetype', 'File Type', 1, 2, null, 100, 5, $documentFile, 2, 'V~O', 3, null, 'BAS', 0],
            [8, null, 'filesize', 'vtiger_notes', 1, 1, 'filesize', 'File Size', 1, 2, null, 100, 4, $documentFile, 2, 'I~O', 3, null, 'BAS', 0],
            [8, null, 'filelocationtype', 'vtiger_notes', 1, 27, 'filelocationtype', 'Download Type', 1, 0, null, 100, 1, $documentFile, 1, 'V~O', 3, null, 'BAS', 0],
            [8, null, 'fileversion', 'vtiger_notes', 1, 1, 'fileversion', 'Version', 1, 2, null, 100, 6, $documentFile, 1, 'V~O', 1, null, 'BAS', 1],
            [8, null, 'filestatus', 'vtiger_notes', 1, 56, 'filestatus', 'Active', 1, 2, null, 100, 2, $documentFile, 1, 'V~O', 1, null, 'BAS', 1],
            [8, null, 'filedownloadcount', 'vtiger_notes', 1, 1, 'filedownloadcount', 'Download Count', 1, 2, null, 100, 7, $documentFile, 2, 'I~O', 3, null, 'BAS', 0],
            [8, null, 'folderid', 'vtiger_notes', 1, 26, 'folderid', 'Folder Name', 1, 2, '', 100, 2, $documentInformation, 1, 'V~O', 2, 2, 'BAS', 0],
            [8, null, 'note_no', 'vtiger_notes', 1, '4', 'note_no', 'Document No', 1, 0, '', 100, 3, $documentInformation, 1, 'V~O', 3, null, 'BAS', 0],
            [8, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 12, $documentInformation, 3, 'V~O', 3, null, 'BAS', 0],
            //Block17 -- End
            //Documents Details -- END

            //Faq Details -- START
            //Block37-40 -- Start
            [15, null, 'product_id', 'vtiger_faq', 1, '59', 'product_id', 'Product Name', 1, 2, '', 100, 1, $faqInformation, 1, 'I~O', 3, null, 'BAS', 1],
            [15, null, 'faq_no', 'vtiger_faq', 1, '4', 'faq_no', 'Faq No', 1, 0, '', 100, 2, $faqInformation, 1, 'V~O', 3, null, 'BAS', 0],
            [15, null, 'category', 'vtiger_faq', 1, '15', 'faqcategories', 'Category', 1, 2, '', 100, 4, $faqInformation, 1, 'V~O', 3, null, 'BAS', 1],
            [15, null, 'status', 'vtiger_faq', 1, '15', 'faqstatus', 'Status', 1, 2, '', 100, 3, $faqInformation, 1, 'V~M', 3, null, 'BAS', 1],
            [15, null, 'question', 'vtiger_faq', 1, '20', 'question', 'Question', 1, 2, '', 100, 7, $faqInformation, 1, 'V~M', 3, null, 'BAS', 1],
            [15, null, 'answer', 'vtiger_faq', 1, '20', 'faq_answer', 'Answer', 1, 2, '', 100, 8, $faqInformation, 1, 'V~M', 3, null, 'BAS', 1],
            [15, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 5, $faqInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [15, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 6, $faqInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [15, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 7, $faqInformation, 3, 'V~O', 3, null, 'BAS', 0],
            [15, null, 'comments', 'vtiger_faqcomments', 1, '19', 'comments', 'Add Comment', 1, 0, '', 100, 1, $faqComment, 1, 'V~O', 3, null, 'BAS', 0],
            //Block37-40 -- End

            //Vendor Details --START
            //Block44-47
            [18, null, 'vendorname', 'vtiger_vendor', 1, '2', 'vendorname', 'Vendor Name', 1, 0, '', 100, 1, $vendorInformation, 1, 'V~M', 0, 1, 'BAS', 1],
            [18, null, 'vendor_no', 'vtiger_vendor', 1, '4', 'vendor_no', 'Vendor No', 1, 0, '', 100, 2, $vendorInformation, 1, 'V~O', 3, null, 'BAS', 0],
            [18, null, 'phone', 'vtiger_vendor', 1, '1', 'phone', 'Phone', 1, 2, '', 100, 4, $vendorInformation, 1, 'V~O', 2, 2, 'BAS', 1],
            [18, null, 'email', 'vtiger_vendor', 1, '13', 'email', 'Email', 1, 2, '', 100, 3, $vendorInformation, 1, 'E~O', 2, 3, 'BAS', 1],
            [18, null, 'website', 'vtiger_vendor', 1, '17', 'website', 'Website', 1, 2, '', 100, 6, $vendorInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [18, null, 'glacct', 'vtiger_vendor', 1, '15', 'glacct', 'GL Account', 1, 2, '', 100, 5, $vendorInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [18, null, 'category', 'vtiger_vendor', 1, '1', 'category', 'Category', 1, 2, '', 100, 8, $vendorInformation, 1, 'V~O', 1, null, 'BAS', 1],
            [18, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 7, $vendorInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [18, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 9, $vendorInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [18, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 12, $vendorInformation, 3, 'V~O', 3, null, 'BAS', 0],
            //Block 46
            [18, null, 'street', 'vtiger_vendor', 1, '21', 'street', 'Street', 1, 2, '', 100, 1, $vendorAddress, 1, 'V~O', 1, null, 'ADV', 1],
            [18, null, 'pobox', 'vtiger_vendor', 1, '1', 'pobox', 'Po Box', 1, 2, '', 100, 2, $vendorAddress, 1, 'V~O', 1, null, 'ADV', 1],
            [18, null, 'city', 'vtiger_vendor', 1, '1', 'city', 'City', 1, 2, '', 100, 3, $vendorAddress, 1, 'V~O', 1, null, 'ADV', 1],
            [18, null, 'state', 'vtiger_vendor', 1, '1', 'state', 'State', 1, 2, '', 100, 4, $vendorAddress, 1, 'V~O', 1, null, 'ADV', 1],
            [18, null, 'postalcode', 'vtiger_vendor', 1, '1', 'postalcode', 'Postal Code', 1, 2, '', 100, 5, $vendorAddress, 1, 'V~O', 1, null, 'ADV', 1],
            [18, null, 'country', 'vtiger_vendor', 1, '1', 'country', 'Country', 1, 2, '', 100, 6, $vendorAddress, 1, 'V~O', 1, null, 'ADV', 1],
            //Block 47
            [18, null, 'description', 'vtiger_crmentity', 1, '19', 'description', 'Description', 1, 2, '', 100, 1, $vendorDescription, 1, 'V~O', 1, null, 'ADV', 1],
            //Vendor Details -- END

            //PriceBook Details Start
            //Block48
            [19, null, 'bookname', 'vtiger_pricebook', 1, '2', 'bookname', 'Price Book Name', 1, 0, '', 100, 1, $priceBookInformation, 1, 'V~M', 0, 1, 'BAS', 1],
            [19, null, 'pricebook_no', 'vtiger_pricebook', 1, '4', 'pricebook_no', 'PriceBook No', 1, 0, '', 100, 3, $priceBookInformation, 1, 'V~O', 3, null, 'BAS', 0],
            [19, null, 'active', 'vtiger_pricebook', 1, '56', 'active', 'Active', 1, 2, '', 100, 2, $priceBookInformation, 1, 'C~O', 2, 2, 'BAS', 1],
            [19, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 4, $priceBookInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [19, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 5, $priceBookInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [19, null, 'currency_id', 'vtiger_pricebook', 1, '117', 'currency_id', 'Currency', 1, 0, '', 100, 5, $priceBookInformation, 1, 'I~M', 0, 3, 'BAS', 0],
            [19, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 7, $priceBookInformation, 3, 'V~O', 3, null, 'BAS', 0],
            //Block50
            [19, null, 'description', 'vtiger_crmentity', 1, '19', 'description', 'Description', 1, 2, '', 100, 1, $priceBookDescription, 1, 'V~O', 1, null, 'BAS', 1],
            //PriceBook Details End

            //Quote Details -- START
            //Block51
            [20, null, 'quote_no', 'vtiger_quotes', 1, '4', 'quote_no', 'Quote No', 1, 0, '', 100, 3, $quoteInformation, 1, 'V~O', 3, null, 'BAS', 0],
            [20, null, 'subject', 'vtiger_quotes', 1, '2', 'subject', 'Subject', 1, 0, '', 100, 1, $quoteInformation, 1, 'V~M', 1, null, 'BAS', 1],
            [20, null, 'potentialid', 'vtiger_quotes', 1, '76', 'potential_id', 'Potential Name', 1, 2, '', 100, 2, $quoteInformation, 1, 'I~O', 3, null, 'BAS', 1],
            [20, null, 'quotestage', 'vtiger_quotes', 1, '15', 'quotestage', 'Quote Stage', 1, 2, '', 100, 4, $quoteInformation, 1, 'V~M', 3, null, 'BAS', 1],
            [20, null, 'validtill', 'vtiger_quotes', 1, '5', 'validtill', 'Valid Till', 1, 2, '', 100, 5, $quoteInformation, 1, 'D~O', 3, null, 'BAS', 1],
            [20, null, 'contactid', 'vtiger_quotes', 1, '57', 'contact_id', 'Contact Name', 1, 2, '', 100, 6, $quoteInformation, 1, 'V~O', 3, null, 'BAS', 1],
            [20, null, 'carrier', 'vtiger_quotes', 1, '15', 'carrier', 'Carrier', 1, 2, '', 100, 8, $quoteInformation, 1, 'V~O', 3, null, 'BAS', 1],
            [20, null, 'subtotal', 'vtiger_quotes', 1, '72', 'hdnSubTotal', 'Sub Total', 1, 2, '', 100, 9, $quoteInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [20, null, 'shipping', 'vtiger_quotes', 1, '1', 'shipping', 'Shipping', 1, 2, '', 100, 10, $quoteInformation, 1, 'V~O', 3, null, 'BAS', 1],
            [20, null, 'inventorymanager', 'vtiger_quotes', 1, '77', 'assigned_user_id1', 'Inventory Manager', 1, 2, '', 100, 11, $quoteInformation, 1, 'I~O', 3, null, 'BAS', 1],
            [20, null, 'adjustment', 'vtiger_quotes', 1, '72', 'txtAdjustment', 'Adjustment', 1, 2, '', 100, 20, $quoteInformation, 3, 'NN~O', 3, null, 'BAS', 1],
            [20, null, 'total', 'vtiger_quotes', 1, '72', 'hdnGrandTotal', 'Total', 1, 2, '', 100, 14, $quoteInformation, 3, 'N~O', 3, null, 'BAS', 1],
            //Added fields taxtype, discount percent, discount amount and S&H amount for Tax process
            [20, null, 'taxtype', 'vtiger_quotes', 1, '16', 'hdnTaxType', 'Tax Type', 1, 2, '', 100, 14, $quoteInformation, 3, 'V~O', 3, null, 'BAS', 1],
            [20, null, 'discount_percent', 'vtiger_quotes', 1, '1', 'hdnDiscountPercent', 'Discount Percent', 1, 2, '', 100, 14, $quoteInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [20, null, 'discount_amount', 'vtiger_quotes', 1, '72', 'hdnDiscountAmount', 'Discount Amount', 1, 2, '', 100, 14, $quoteInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [20, null, 's_h_amount', 'vtiger_quotes', 1, '72', 'hdnS_H_Amount', 'S&H Amount', 1, 2, '', 100, 14, $quoteInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [20, null, 'accountid', 'vtiger_quotes', 1, '73', 'account_id', 'Account Name', 1, 2, '', 100, 16, $quoteInformation, 1, 'I~M', 3, null, 'BAS', 1],
            [20, null, 'smownerid', 'vtiger_crmentity', 1, '53', 'assigned_user_id', 'Assigned To', 1, 0, '', 100, 17, $quoteInformation, 1, 'V~M', 3, null, 'BAS', 1],
            [20, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 18, $quoteInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [20, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 19, $quoteInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [20, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 22, $quoteInformation, 3, 'V~O', 3, null, 'BAS', 0],
            [20, null, 'currency_id', 'vtiger_quotes', 1, '117', 'currency_id', 'Currency', 1, 2, 1, 100, 20, $quoteInformation, 3, 'I~O', 3, null, 'BAS', 1],
            [20, null, 'conversion_rate', 'vtiger_quotes', 1, '1', 'conversion_rate', 'Conversion Rate', 1, 2, 1, 100, 21, $quoteInformation, 3, 'N~O', 3, null, 'BAS', 1],
            //Block 53
            [20, null, 'bill_street', 'vtiger_quotesbillads', 1, '24', 'bill_street', 'Billing Address', 1, 2, '', 100, 1, $quoteAddress, 1, 'V~M', 3, null, 'BAS', 1],
            [20, null, 'ship_street', 'vtiger_quotesshipads', 1, '24', 'ship_street', 'Shipping Address', 1, 2, '', 100, 2, $quoteAddress, 1, 'V~M', 3, null, 'BAS', 1],
            [20, null, 'bill_city', 'vtiger_quotesbillads', 1, '1', 'bill_city', 'Billing City', 1, 2, '', 100, 5, $quoteAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [20, null, 'ship_city', 'vtiger_quotesshipads', 1, '1', 'ship_city', 'Shipping City', 1, 2, '', 100, 6, $quoteAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [20, null, 'bill_state', 'vtiger_quotesbillads', 1, '1', 'bill_state', 'Billing State', 1, 2, '', 100, 7, $quoteAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [20, null, 'ship_state', 'vtiger_quotesshipads', 1, '1', 'ship_state', 'Shipping State', 1, 2, '', 100, 8, $quoteAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [20, null, 'bill_code', 'vtiger_quotesbillads', 1, '1', 'bill_code', 'Billing Code', 1, 2, '', 100, 9, $quoteAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [20, null, 'ship_code', 'vtiger_quotesshipads', 1, '1', 'ship_code', 'Shipping Code', 1, 2, '', 100, 10, $quoteAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [20, null, 'bill_country', 'vtiger_quotesbillads', 1, '1', 'bill_country', 'Billing Country', 1, 2, '', 100, 11, $quoteAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [20, null, 'ship_country', 'vtiger_quotesshipads', 1, '1', 'ship_country', 'Shipping Country', 1, 2, '', 100, 12, $quoteAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [20, null, 'bill_pobox', 'vtiger_quotesbillads', 1, '1', 'bill_pobox', 'Billing Po Box', 1, 2, '', 100, 3, $quoteAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [20, null, 'ship_pobox', 'vtiger_quotesshipads', 1, '1', 'ship_pobox', 'Shipping Po Box', 1, 2, '', 100, 4, $quoteAddress, 1, 'V~O', 3, null, 'BAS', 1],
            //Block55
            [20, null, 'description', 'vtiger_crmentity', 1, '19', 'description', 'Description', 1, 2, '', 100, 1, $quoteDescription, 1, 'V~O', 3, null, 'ADV', 1],
            //Block 56
            [20, null, 'terms_conditions', 'vtiger_quotes', 1, '19', 'terms_conditions', 'Terms & Conditions', 1, 2, '', 100, 1, $quoteTerms, 1, 'V~O', 3, null, 'ADV', 1],
            //Quote Details -- END

            //Purchase Order Details -- START
            //Block57
            [21, null, 'purchaseorder_no', 'vtiger_purchaseorder', 1, '4', 'purchaseorder_no', 'PurchaseOrder No', 1, 0, '', 100, 2, $purchaseOrderInformation, 1, 'V~O', 3, null, 'BAS', 0],
            [21, null, 'subject', 'vtiger_purchaseorder', 1, '2', 'subject', 'Subject', 1, 0, '', 100, 1, $purchaseOrderInformation, 1, 'V~M', 3, null, 'BAS', 1],
            [21, null, 'vendorid', 'vtiger_purchaseorder', 1, '81', 'vendor_id', 'Vendor Name', 1, 0, '', 100, 3, $purchaseOrderInformation, 1, 'I~M', 3, null, 'BAS', 1],
            [21, null, 'requisition_no', 'vtiger_purchaseorder', 1, '1', 'requisition_no', 'Requisition No', 1, 2, '', 100, 4, $purchaseOrderInformation, 1, 'V~O', 3, null, 'BAS', 1],
            [21, null, 'tracking_no', 'vtiger_purchaseorder', 1, '1', 'tracking_no', 'Tracking Number', 1, 2, '', 100, 5, $purchaseOrderInformation, 1, 'V~O', 3, null, 'BAS', 1],
            [21, null, 'contactid', 'vtiger_purchaseorder', 1, '57', 'contact_id', 'Contact Name', 1, 2, '', 100, 6, $purchaseOrderInformation, 1, 'I~O', 3, null, 'BAS', 1],
            [21, null, 'duedate', 'vtiger_purchaseorder', 1, '5', 'duedate', 'Due Date', 1, 2, '', 100, 7, $purchaseOrderInformation, 1, 'D~O', 3, null, 'BAS', 1],
            [21, null, 'carrier', 'vtiger_purchaseorder', 1, '15', 'carrier', 'Carrier', 1, 2, '', 100, 8, $purchaseOrderInformation, 1, 'V~O', 3, null, 'BAS', 1],
            [21, null, 'adjustment', 'vtiger_purchaseorder', 1, '72', 'txtAdjustment', 'Adjustment', 1, 2, '', 100, 10, $purchaseOrderInformation, 3, 'NN~O', 3, null, 'BAS', 1],
            [21, null, 'salescommission', 'vtiger_purchaseorder', 1, '1', 'salescommission', 'Sales Commission', 1, 2, '', 100, 11, $purchaseOrderInformation, 1, 'N~O', 3, null, 'BAS', 1],
            [21, null, 'exciseduty', 'vtiger_purchaseorder', 1, '1', 'exciseduty', 'Excise Duty', 1, 2, '', 100, 12, $purchaseOrderInformation, 1, 'N~O', 3, null, 'BAS', 1],
            [21, null, 'total', 'vtiger_purchaseorder', 1, '72', 'hdnGrandTotal', 'Total', 1, 2, '', 100, 13, $purchaseOrderInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [21, null, 'subtotal', 'vtiger_purchaseorder', 1, '72', 'hdnSubTotal', 'Sub Total', 1, 2, '', 100, 14, $purchaseOrderInformation, 3, 'N~O', 3, null, 'BAS', 1],
            //Added fields taxtype, discount percent, discount amount and S&H amount for Tax process
            [21, null, 'taxtype', 'vtiger_purchaseorder', 1, '16', 'hdnTaxType', 'Tax Type', 1, 2, '', 100, 14, $purchaseOrderInformation, 3, 'V~O', 3, null, 'BAS', 1],
            [21, null, 'discount_percent', 'vtiger_purchaseorder', 1, '1', 'hdnDiscountPercent', 'Discount Percent', 1, 2, '', 100, 14, $purchaseOrderInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [21, null, 'discount_amount', 'vtiger_purchaseorder', 1, '72', 'hdnDiscountAmount', 'Discount Amount', 1, 0, '', 100, 14, $purchaseOrderInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [21, null, 's_h_amount', 'vtiger_purchaseorder', 1, '72', 'hdnS_H_Amount', 'S&H Amount', 1, 2, '', 100, 14, $purchaseOrderInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [21, null, 'postatus', 'vtiger_purchaseorder', 1, '15', 'postatus', 'Status', 1, 2, '', 100, 15, $purchaseOrderInformation, 1, 'V~M', 3, null, 'BAS', 1],
            [21, null, 'smownerid', 'vtiger_crmentity', 1, '53', 'assigned_user_id', 'Assigned To', 1, 0, '', 100, 16, $purchaseOrderInformation, 1, 'V~M', 3, null, 'BAS', 1],
            [21, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 17, $purchaseOrderInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [21, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 18, $purchaseOrderInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [21, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 22, $purchaseOrderInformation, 3, 'V~O', 3, null, 'BAS', 0],
            [21, null, 'currency_id', 'vtiger_purchaseorder', 1, '117', 'currency_id', 'Currency', 1, 2, 1, 100, 19, $purchaseOrderInformation, 3, 'I~O', 3, null, 'BAS', 1],
            [21, null, 'conversion_rate', 'vtiger_purchaseorder', 1, '1', 'conversion_rate', 'Conversion Rate', 1, 2, 1, 100, 20, $purchaseOrderInformation, 3, 'N~O', 3, null, 'BAS', 1],
            //Block 59
            [21, null, 'bill_street', 'vtiger_pobillads', 1, '24', 'bill_street', 'Billing Address', 1, 2, '', 100, 1, $purchaseOrderAddress, 1, 'V~M', 3, null, 'BAS', 1],
            [21, null, 'ship_street', 'vtiger_poshipads', 1, '24', 'ship_street', 'Shipping Address', 1, 2, '', 100, 2, $purchaseOrderAddress, 1, 'V~M', 3, null, 'BAS', 1],
            [21, null, 'bill_city', 'vtiger_pobillads', 1, '1', 'bill_city', 'Billing City', 1, 2, '', 100, 5, $purchaseOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [21, null, 'ship_city', 'vtiger_poshipads', 1, '1', 'ship_city', 'Shipping City', 1, 2, '', 100, 6, $purchaseOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [21, null, 'bill_state', 'vtiger_pobillads', 1, '1', 'bill_state', 'Billing State', 1, 2, '', 100, 7, $purchaseOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [21, null, 'ship_state', 'vtiger_poshipads', 1, '1', 'ship_state', 'Shipping State', 1, 2, '', 100, 8, $purchaseOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [21, null, 'bill_code', 'vtiger_pobillads', 1, '1', 'bill_code', 'Billing Code', 1, 2, '', 100, 9, $purchaseOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [21, null, 'ship_code', 'vtiger_poshipads', 1, '1', 'ship_code', 'Shipping Code', 1, 2, '', 100, 10, $purchaseOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [21, null, 'bill_country', 'vtiger_pobillads', 1, '1', 'bill_country', 'Billing Country', 1, 2, '', 100, 11, $purchaseOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [21, null, 'ship_country', 'vtiger_poshipads', 1, '1', 'ship_country', 'Shipping Country', 1, 2, '', 100, 12, $purchaseOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [21, null, 'bill_pobox', 'vtiger_pobillads', 1, '1', 'bill_pobox', 'Billing Po Box', 1, 2, '', 100, 3, $purchaseOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [21, null, 'ship_pobox', 'vtiger_poshipads', 1, '1', 'ship_pobox', 'Shipping Po Box', 1, 2, '', 100, 4, $purchaseOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            //Block61
            [21, null, 'description', 'vtiger_crmentity', 1, '19', 'description', 'Description', 1, 2, '', 100, 1, $purchaseOrderDescription, 1, 'V~O', 3, null, 'ADV', 1],
            //Block62
            [21, null, 'terms_conditions', 'vtiger_purchaseorder', 1, '19', 'terms_conditions', 'Terms & Conditions', 1, 2, '', 100, 1, $purchaseOrderTerms, 1, 'V~O', 3, null, 'ADV', 1],
            //Purchase Order Details -- END

            //Sales Order Details -- START
            //Block63
            [22, null, 'salesorder_no', 'vtiger_salesorder', 1, '4', 'salesorder_no', 'SalesOrder No', 1, 0, '', 100, 4, $salesOrderInformation, 1, 'V~O', 3, null, 'BAS', 0],
            [22, null, 'subject', 'vtiger_salesorder', 1, '2', 'subject', 'Subject', 1, 0, '', 100, 1, $salesOrderInformation, 1, 'V~M', 3, null, 'BAS', 1],
            [22, null, 'potentialid', 'vtiger_salesorder', 1, '76', 'potential_id', 'Potential Name', 1, 2, '', 100, 2, $salesOrderInformation, 1, 'I~O', 3, null, 'BAS', 1],
            [22, null, 'customerno', 'vtiger_salesorder', 1, '1', 'customerno', 'Customer No', 1, 2, '', 100, 3, $salesOrderInformation, 1, 'V~O', 3, null, 'BAS', 1],
            [22, null, 'quoteid', 'vtiger_salesorder', 1, '78', 'quote_id', 'Quote Name', 1, 2, '', 100, 5, $salesOrderInformation, 1, 'I~O', 3, null, 'BAS', 1],
            [22, null, 'purchaseorder', 'vtiger_salesorder', 1, '1', 'vtiger_purchaseorder', 'Purchase Order', 1, 2, '', 100, 5, $salesOrderInformation, 1, 'V~O', 3, null, 'BAS', 1],
            [22, null, 'contactid', 'vtiger_salesorder', 1, '57', 'contact_id', 'Contact Name', 1, 2, '', 100, 6, $salesOrderInformation, 1, 'I~O', 3, null, 'BAS', 1],
            [22, null, 'duedate', 'vtiger_salesorder', 1, '5', 'duedate', 'Due Date', 1, 2, '', 100, 8, $salesOrderInformation, 1, 'D~O', 3, null, 'BAS', 1],
            [22, null, 'carrier', 'vtiger_salesorder', 1, '15', 'carrier', 'Carrier', 1, 2, '', 100, 9, $salesOrderInformation, 1, 'V~O', 3, null, 'BAS', 1],
            [22, null, 'pending', 'vtiger_salesorder', 1, '1', 'pending', 'Pending', 1, 2, '', 100, 10, $salesOrderInformation, 1, 'V~O', 3, null, 'BAS', 1],
            [22, null, 'sostatus', 'vtiger_salesorder', 1, '15', 'sostatus', 'Status', 1, 2, '', 100, 11, $salesOrderInformation, 1, 'V~M', 3, null, 'BAS', 1],
            [22, null, 'adjustment', 'vtiger_salesorder', 1, '72', 'txtAdjustment', 'Adjustment', 1, 2, '', 100, 12, $salesOrderInformation, 3, 'NN~O', 3, null, 'BAS', 1],
            [22, null, 'salescommission', 'vtiger_salesorder', 1, '1', 'salescommission', 'Sales Commission', 1, 2, '', 100, 13, $salesOrderInformation, 1, 'N~O', 3, null, 'BAS', 1],
            [22, null, 'exciseduty', 'vtiger_salesorder', 1, '1', 'exciseduty', 'Excise Duty', 1, 2, '', 100, 13, $salesOrderInformation, 1, 'N~O', 3, null, 'BAS', 1],
            [22, null, 'total', 'vtiger_salesorder', 1, '72', 'hdnGrandTotal', 'Total', 1, 2, '', 100, 14, $salesOrderInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [22, null, 'subtotal', 'vtiger_salesorder', 1, '72', 'hdnSubTotal', 'Sub Total', 1, 2, '', 100, 15, $salesOrderInformation, 3, 'N~O', 3, null, 'BAS', 1],
            //Added fields taxtype, discount percent, discount amount and S&H amount for Tax process
            [22, null, 'taxtype', 'vtiger_salesorder', 1, '16', 'hdnTaxType', 'Tax Type', 1, 2, '', 100, 15, $salesOrderInformation, 3, 'V~O', 3, null, 'BAS', 1],
            [22, null, 'discount_percent', 'vtiger_salesorder', 1, '1', 'hdnDiscountPercent', 'Discount Percent', 1, 2, '', 100, 15, $salesOrderInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [22, null, 'discount_amount', 'vtiger_salesorder', 1, '72', 'hdnDiscountAmount', 'Discount Amount', 1, 0, '', 100, 15, $salesOrderInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [22, null, 's_h_amount', 'vtiger_salesorder', 1, '72', 'hdnS_H_Amount', 'S&H Amount', 1, 2, '', 100, 15, $salesOrderInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [22, null, 'accountid', 'vtiger_salesorder', 1, '73', 'account_id', 'Account Name', 1, 2, '', 100, 16, $salesOrderInformation, 1, 'I~M', 3, null, 'BAS', 1],
            [22, null, 'smownerid', 'vtiger_crmentity', 1, '53', 'assigned_user_id', 'Assigned To', 1, 0, '', 100, 17, $salesOrderInformation, 1, 'V~M', 3, null, 'BAS', 1],
            [22, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 18, $salesOrderInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [22, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 19, $salesOrderInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [22, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 22, $salesOrderInformation, 3, 'V~O', 3, null, 'BAS', 0],
            [22, null, 'currency_id', 'vtiger_salesorder', 1, '117', 'currency_id', 'Currency', 1, 2, 1, 100, 20, $salesOrderInformation, 3, 'I~O', 3, null, 'BAS', 1],
            [22, null, 'conversion_rate', 'vtiger_salesorder', 1, '1', 'conversion_rate', 'Conversion Rate', 1, 2, 1, 100, 21, $salesOrderInformation, 3, 'N~O', 3, null, 'BAS', 1],
            //Block 65
            [22, null, 'bill_street', 'vtiger_sobillads', 1, '24', 'bill_street', 'Billing Address', 1, 2, '', 100, 1, $salesOrderAddress, 1, 'V~M', 3, null, 'BAS', 1],
            [22, null, 'ship_street', 'vtiger_soshipads', 1, '24', 'ship_street', 'Shipping Address', 1, 2, '', 100, 2, $salesOrderAddress, 1, 'V~M', 3, null, 'BAS', 1],
            [22, null, 'bill_city', 'vtiger_sobillads', 1, '1', 'bill_city', 'Billing City', 1, 2, '', 100, 5, $salesOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [22, null, 'ship_city', 'vtiger_soshipads', 1, '1', 'ship_city', 'Shipping City', 1, 2, '', 100, 6, $salesOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [22, null, 'bill_state', 'vtiger_sobillads', 1, '1', 'bill_state', 'Billing State', 1, 2, '', 100, 7, $salesOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [22, null, 'ship_state', 'vtiger_soshipads', 1, '1', 'ship_state', 'Shipping State', 1, 2, '', 100, 8, $salesOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [22, null, 'bill_code', 'vtiger_sobillads', 1, '1', 'bill_code', 'Billing Code', 1, 2, '', 100, 9, $salesOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [22, null, 'ship_code', 'vtiger_soshipads', 1, '1', 'ship_code', 'Shipping Code', 1, 2, '', 100, 10, $salesOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [22, null, 'bill_country', 'vtiger_sobillads', 1, '1', 'bill_country', 'Billing Country', 1, 2, '', 100, 11, $salesOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [22, null, 'ship_country', 'vtiger_soshipads', 1, '1', 'ship_country', 'Shipping Country', 1, 2, '', 100, 12, $salesOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [22, null, 'bill_pobox', 'vtiger_sobillads', 1, '1', 'bill_pobox', 'Billing Po Box', 1, 2, '', 100, 3, $salesOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [22, null, 'ship_pobox', 'vtiger_soshipads', 1, '1', 'ship_pobox', 'Shipping Po Box', 1, 2, '', 100, 4, $salesOrderAddress, 1, 'V~O', 3, null, 'BAS', 1],
            //Block67
            [22, null, 'description', 'vtiger_crmentity', 1, '19', 'description', 'Description', 1, 2, '', 100, 1, $salesOrderDescription, 1, 'V~O', 3, null, 'ADV', 1],
            //Block68
            [22, null, 'terms_conditions', 'vtiger_salesorder', 1, '19', 'terms_conditions', 'Terms & Conditions', 1, 2, '', 100, 1, $salesOrderTerms, 1, 'V~O', 3, null, 'ADV', 1],
            // Add fields for the Recurring Information block - Block 86
            [22, null, 'enable_recurring', 'vtiger_salesorder', 1, '56', 'enable_recurring', 'Enable Recurring', 1, 0, '', 100, 1, $salesOrderRecurring, 1, 'C~O', 3, null, 'BAS', 0],
            [22, null, 'recurring_frequency', 'vtiger_invoice_recurring_info', 1, '16', 'recurring_frequency', 'Frequency', 1, 0, '', 100, 2, $salesOrderRecurring, 1, 'V~O', 3, null, 'BAS', 0],
            [22, null, 'start_period', 'vtiger_invoice_recurring_info', 1, '5', 'start_period', 'Start Period', 1, 0, '', 100, 3, $salesOrderRecurring, 1, 'D~O', 3, null, 'BAS', 0],
            [22, null, 'end_period', 'vtiger_invoice_recurring_info', 1, '5', 'end_period', 'End Period', 1, 0, '', 100, 4, $salesOrderRecurring, 1, 'D~O~OTH~G~start_period~Start Period', 3, null, 'BAS', 0],
            [22, null, 'payment_duration', 'vtiger_invoice_recurring_info', 1, '16', 'payment_duration', 'Payment Duration', 1, 0, '', 100, 5, $salesOrderRecurring, 1, 'V~O', 3, null, 'BAS', 0],
            [22, null, 'invoice_status', 'vtiger_invoice_recurring_info', 1, '15', 'invoicestatus', 'Invoice Status', 1, 0, '', 100, 6, $salesOrderRecurring, 1, 'V~M', 3, null, 'BAS', 0],
            //Sales Order Details -- END

            //Invoice Details -- START
            //Block69
            [23, null, 'subject', 'vtiger_invoice', 1, '2', 'subject', 'Subject', 1, 0, '', 100, 1, $invoiceInformation, 1, 'V~M', 3, null, 'BAS', 1],
            [23, null, 'salesorderid', 'vtiger_invoice', 1, '80', 'salesorder_id', 'Sales Order', 1, 2, '', 100, 2, $invoiceInformation, 1, 'I~O', 3, null, 'BAS', 1],
            [23, null, 'customerno', 'vtiger_invoice', 1, '1', 'customerno', 'Customer No', 1, 2, '', 100, 3, $invoiceInformation, 1, 'V~O', 3, null, 'BAS', 1],
            //to include contact name vtiger_field in Invoice-start
            [23, null, 'contactid', 'vtiger_invoice', 1, '57', 'contact_id', 'Contact Name', 1, 2, '', 100, 4, $invoiceInformation, 1, 'I~O', 3, null, 'BAS', 1],
            //end
            [23, null, 'invoicedate', 'vtiger_invoice', 1, '5', 'invoicedate', 'Invoice Date', 1, 2, '', 100, 5, $invoiceInformation, 1, 'D~O', 3, null, 'BAS', 1],
            [23, null, 'duedate', 'vtiger_invoice', 1, '5', 'duedate', 'Due Date', 1, 2, '', 100, 6, $invoiceInformation, 1, 'D~O', 3, null, 'BAS', 1],
            [23, null, 'purchaseorder', 'vtiger_invoice', 1, '1', 'vtiger_purchaseorder', 'Purchase Order', 1, 2, '', 100, 8, $invoiceInformation, 1, 'V~O', 3, null, 'BAS', 1],
            [23, null, 'adjustment', 'vtiger_invoice', 1, '72', 'txtAdjustment', 'Adjustment', 1, 2, '', 100, 9, $invoiceInformation, 3, 'NN~O', 3, null, 'BAS', 1],
            [23, null, 'salescommission', 'vtiger_invoice', 1, '1', 'salescommission', 'Sales Commission', 1, 2, '', 10, 13, $invoiceInformation, 1, 'N~O', 3, null, 'BAS', 1],
            [23, null, 'exciseduty', 'vtiger_invoice', 1, '1', 'exciseduty', 'Excise Duty', 1, 2, '', 100, 11, $invoiceInformation, 1, 'N~O', 3, null, 'BAS', 1],
            [23, null, 'subtotal', 'vtiger_invoice', 1, '72', 'hdnSubTotal', 'Sub Total', 1, 2, '', 100, 12, $invoiceInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [23, null, 'total', 'vtiger_invoice', 1, '72', 'hdnGrandTotal', 'Total', 1, 2, '', 100, 13, $invoiceInformation, 3, 'N~O', 3, null, 'BAS', 1],
            //Added fields taxtype, discount percent, discount amount and S&H amount for Tax process
            [23, null, 'taxtype', 'vtiger_invoice', 1, '16', 'hdnTaxType', 'Tax Type', 1, 2, '', 100, 13, $invoiceInformation, 3, 'V~O', 3, null, 'BAS', 1],
            [23, null, 'discount_percent', 'vtiger_invoice', 1, '1', 'hdnDiscountPercent', 'Discount Percent', 1, 2, '', 100, 13, $invoiceInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [23, null, 'discount_amount', 'vtiger_invoice', 1, '72', 'hdnDiscountAmount', 'Discount Amount', 1, 2, '', 100, 13, $invoiceInformation, 3, 'N~O', 3, null, 'BAS', 1],
            [23, null, 's_h_amount', 'vtiger_invoice', 1, '72', 'hdnS_H_Amount', 'S&H Amount', 1, 2, '', 100, 14, 57, 3, 'N~O', 3, null, 'BAS', 1],
            [23, null, 'accountid', 'vtiger_invoice', 1, '73', 'account_id', 'Account Name', 1, 2, '', 100, 14, $invoiceInformation, 1, 'I~M', 3, null, 'BAS', 1],
            [23, null, 'invoicestatus', 'vtiger_invoice', 1, '15', 'invoicestatus', 'Status', 1, 2, '', 100, 15, $invoiceInformation, 1, 'V~O', 3, null, 'BAS', 1],
            [23, null, 'smownerid', 'vtiger_crmentity', 1, '53', 'assigned_user_id', 'Assigned To', 1, 0, '', 100, 16, $invoiceInformation, 1, 'V~M', 3, null, 'BAS', 1],
            [23, null, 'createdtime', 'vtiger_crmentity', 1, '70', 'createdtime', 'Created Time', 1, 0, '', 100, 17, $invoiceInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [23, null, 'modifiedtime', 'vtiger_crmentity', 1, '70', 'modifiedtime', 'Modified Time', 1, 0, '', 100, 18, $invoiceInformation, 2, 'DT~O', 3, null, 'BAS', 0],
            [23, null, 'modifiedby', 'vtiger_crmentity', 1, '52', 'modifiedby', 'Last Modified By', 1, 0, '', 100, 22, $invoiceInformation, 3, 'V~O', 3, null, 'BAS', 0],
            [23, null, 'currency_id', 'vtiger_invoice', 1, '117', 'currency_id', 'Currency', 1, 2, 1, 100, 19, $invoiceInformation, 3, 'I~O', 3, null, 'BAS', 1],
            [23, null, 'conversion_rate', 'vtiger_invoice', 1, '1', 'conversion_rate', 'Conversion Rate', 1, 2, 1, 100, 20, $invoiceInformation, 3, 'N~O', 3, null, 'BAS', 1],
            //Block 71
            [23, null, 'bill_street', 'vtiger_invoicebillads', 1, '24', 'bill_street', 'Billing Address', 1, 2, '', 100, 1, $invoiceAddress, 1, 'V~M', 3, null, 'BAS', 1],
            [23, null, 'ship_street', 'vtiger_invoiceshipads', 1, '24', 'ship_street', 'Shipping Address', 1, 2, '', 100, 2, $invoiceAddress, 1, 'V~M', 3, null, 'BAS', 1],
            [23, null, 'bill_city', 'vtiger_invoicebillads', 1, '1', 'bill_city', 'Billing City', 1, 2, '', 100, 5, $invoiceAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [23, null, 'ship_city', 'vtiger_invoiceshipads', 1, '1', 'ship_city', 'Shipping City', 1, 2, '', 100, 6, $invoiceAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [23, null, 'bill_state', 'vtiger_invoicebillads', 1, '1', 'bill_state', 'Billing State', 1, 2, '', 100, 7, $invoiceAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [23, null, 'ship_state', 'vtiger_invoiceshipads', 1, '1', 'ship_state', 'Shipping State', 1, 2, '', 100, 8, $invoiceAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [23, null, 'bill_code', 'vtiger_invoicebillads', 1, '1', 'bill_code', 'Billing Code', 1, 2, '', 100, 9, $invoiceAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [23, null, 'ship_code', 'vtiger_invoiceshipads', 1, '1', 'ship_code', 'Shipping Code', 1, 2, '', 100, 10, $invoiceAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [23, null, 'bill_country', 'vtiger_invoicebillads', 1, '1', 'bill_country', 'Billing Country', 1, 2, '', 100, 11, $invoiceAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [23, null, 'ship_country', 'vtiger_invoiceshipads', 1, '1', 'ship_country', 'Shipping Country', 1, 2, '', 100, 12, $invoiceAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [23, null, 'bill_pobox', 'vtiger_invoicebillads', 1, '1', 'bill_pobox', 'Billing Po Box', 1, 2, '', 100, 3, $invoiceAddress, 1, 'V~O', 3, null, 'BAS', 1],
            [23, null, 'ship_pobox', 'vtiger_invoiceshipads', 1, '1', 'ship_pobox', 'Shipping Po Box', 1, 2, '', 100, 4, $invoiceAddress, 1, 'V~O', 3, null, 'BAS', 1],
            //Block73
            [23, null, 'description', 'vtiger_crmentity', 1, '19', 'description', 'Description', 1, 2, '', 100, 1, $invoiceDescription, 1, 'V~O', 3, null, 'ADV', 1],
            //Block74
            [23, null, 'terms_conditions', 'vtiger_invoice', 1, '19', 'terms_conditions', 'Terms & Conditions', 1, 2, '', 100, 1, $invoicetermsblock, 1, 'V~O', 3, null, 'ADV', 1],
            //Added for Custom invoice Number
            [23, null, 'invoice_no', 'vtiger_invoice', 1, '4', 'invoice_no', 'Invoice No', 1, 0, '', 100, 3, $invoiceInformation, 1, 'V~O', 3, null, 'BAS', 0],
            //Invoice Details -- END

            //users Details Starts Block 79,80,81
            [29, null, 'user_name', 'vtiger_users', 1, '106', 'user_name', 'User Name', 1, 0, '', 11, 1, $userLogin, 1, 'V~M', 1, null, 'BAS', 1],
            [29, null, 'is_admin', 'vtiger_users', 1, '156', 'is_admin', 'Admin', 1, 0, '', 3, 2, $userLogin, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'user_password', 'vtiger_users', 1, '99', 'user_password', 'Password', 1, 0, '', 30, 3, $userLogin, 4, 'P~M', 1, null, 'BAS', 1],
            [29, null, 'confirm_password', 'vtiger_users', 1, '99', 'confirm_password', 'Confirm Password', 1, 0, '', 30, 5, $userLogin, 4, 'P~M', 1, null, 'BAS', 1],
            [29, null, 'first_name', 'vtiger_users', 1, '1', 'first_name', 'First Name', 1, 0, '', 30, 7, $userLogin, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'last_name', 'vtiger_users', 1, '2', 'last_name', 'Last Name', 1, 0, '', 30, 9, $userLogin, 1, 'V~M', 1, null, 'BAS', 1],
            [29, null, 'roleid', 'vtiger_user2role', 1, '98', 'roleid', 'Role', 1, 0, '', 200, 11, $userLogin, 1, 'V~M', 1, null, 'BAS', 1],
            [29, null, 'email1', 'vtiger_users', 1, '104', 'email1', 'Email', 1, 0, '', 100, 4, $userLogin, 1, 'E~M', 1, null, 'BAS', 1],
            [29, null, 'status', 'vtiger_users', 1, '115', 'status', 'Status', 1, 0, '', 100, 6, $userLogin, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'activity_view', 'vtiger_users', 1, '16', 'activity_view', 'Default Activity View', 1, 0, '', 100, 12, $userLogin, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'lead_view', 'vtiger_users', 1, '16', 'lead_view', 'Default Lead View', 1, 0, '', 100, 10, $userLogin, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'hour_format', 'vtiger_users', 1, '116', 'hour_format', 'Calendar Hour Format', 1, 0, '', 100, 13, $userLogin, 3, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'end_hour', 'vtiger_users', 1, '116', 'end_hour', 'Day ends at', 1, 0, '', 100, 15, $userLogin, 3, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'start_hour', 'vtiger_users', 1, '116', 'start_hour', 'Day starts at', 1, 0, '', 100, 14, $userLogin, 3, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'is_owner', 'vtiger_users', 1, '1', 'is_owner', 'Account Owner', 0, 2, 0, 100, 12, $userLogin, 5, 'V~O', 0, 1, 'BAS', 0],
            [29, null, 'title', 'vtiger_users', 1, '1', 'title', 'Title', 1, 0, '', 50, 1, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'phone_work', 'vtiger_users', 1, '11', 'phone_work', 'Office Phone', 1, 0, '', 50, 5, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'department', 'vtiger_users', 1, '1', 'department', 'Department', 1, 0, '', 50, 3, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'phone_mobile', 'vtiger_users', 1, '11', 'phone_mobile', 'Mobile', 1, 0, '', 50, 7, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'reports_to_id', 'vtiger_users', 1, '101', 'reports_to_id', 'Reports To', 1, 0, '', 50, 8, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'phone_other', 'vtiger_users', 1, '11', 'phone_other', 'Other Phone', 1, 0, '', 50, 11, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'email2', 'vtiger_users', 1, '13', 'email2', 'Other Email', 1, 0, '', 100, 4, $userMore, 1, 'E~O', 1, null, 'BAS', 1],
            [29, null, 'phone_fax', 'vtiger_users', 1, '11', 'phone_fax', 'Fax', 1, 0, '', 50, 2, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'secondaryemail', 'vtiger_users', 1, '13', 'secondaryemail', 'Secondary Email', 1, 0, '', 100, 6, $userMore, 1, 'E~O', 1, null, 'BAS', 1],
            [29, null, 'phone_home', 'vtiger_users', 1, '11', 'phone_home', 'Home Phone', 1, 0, '', 50, 9, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'date_format', 'vtiger_users', 1, '16', 'date_format', 'Date Format', 1, 0, '', 30, 12, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'signature', 'vtiger_users', 1, '21', 'signature', 'Signature', 1, 0, '', 250, 13, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'description', 'vtiger_users', 1, '21', 'description', 'Documents', 1, 0, '', 250, 14, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'address_street', 'vtiger_users', 1, '21', 'address_street', 'Street Address', 1, 0, '', 250, 1, $userAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'address_city', 'vtiger_users', 1, '1', 'address_city', 'City', 1, 0, '', 100, 3, $userAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'address_state', 'vtiger_users', 1, '1', 'address_state', 'State', 1, 0, '', 100, 5, $userAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'address_postalcode', 'vtiger_users', 1, '1', 'address_postalcode', 'Postal Code', 1, 0, '', 100, 4, $userAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'address_country', 'vtiger_users', 1, '1', 'address_country', 'Country', 1, 0, '', 100, 2, $userAddress, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'accesskey', 'vtiger_users', 1, 3, 'accesskey', 'Webservice Access Key', 1, 0, '', 100, 2, $userAdvance, 2, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'time_zone', 'vtiger_users', 1, '16', 'time_zone', 'Time Zone', 1, 0, '', 200, 15, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'currency_id', 'vtiger_users', 1, '117', 'currency_id', 'Currency', 1, 0, '', 100, 1, $userCurrency, 1, 'I~O', 1, null, 'BAS', 1],
            [29, null, 'currency_grouping_pattern', 'vtiger_users', 1, '16', 'currency_grouping_pattern', 'Digit Grouping Pattern', 1, 0, '', 100, 2, $userCurrency, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'currency_decimal_separator', 'vtiger_users', 1, '16', 'currency_decimal_separator', 'Decimal Separator', 1, 0, '', 2, 3, $userCurrency, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'currency_grouping_separator', 'vtiger_users', 1, '16', 'currency_grouping_separator', 'Digit Grouping Separator', 1, 0, '', 2, 4, $userCurrency, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'currency_symbol_placement', 'vtiger_users', 1, '16', 'currency_symbol_placement', 'Symbol Placement', 1, 0, '', 20, 5, $userCurrency, 1, 'V~O', 1, null, 'BAS', 1],

            //User Image Information
            [29, null, 'imagename', 'vtiger_users', 1, '105', 'imagename', 'User Image', 1, 0, '', 250, 10, $userImage, 1, 'V~O', 1, null, 'BAS', 1],
            //added for internl_mailer
            [29, null, 'internal_mailer', 'vtiger_users', 1, '56', 'internal_mailer', 'INTERNAL_MAIL_COMPOSER', 1, 0, '', 50, 15, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'theme', 'vtiger_users', 1, '31', 'theme', 'Theme', 1, 0, '', 100, 16, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'language', 'vtiger_users', 1, '32', 'language', 'Language', 1, 0, '', 100, 17, $userMore, 1, 'V~O', 1, null, 'BAS', 1],
            [29, null, 'reminder_interval', 'vtiger_users', 1, '16', 'reminder_interval', 'Reminder Interval', 1, 0, '', 100, 1, $userAdvance, 1, 'V~O', 1, null, 'BAS', 1],
            //user Details End
        ];
        $fieldIds = $this->createFields($fields);

        $potentialRelatedTo = $fieldIds[2]['related_to'];
        $this->db->query("insert into vtiger_fieldmodulerel (fieldid, module, relmodule, status, sequence) values ($potentialRelatedTo, 'Potentials', 'Accounts', NULL, 0), ($potentialRelatedTo, 'Potentials', 'Contacts', NULL, 1)");

        //entry to vtiger_field to maintain account,contact,lead relationships

        $this->db->query("INSERT INTO vtiger_field(tabid, fieldid, columnname, tablename, generatedtype, uitype, fieldname, fieldlabel, readonly, presence, defaultvalue, maximumlength, sequence, block, displaytype, typeofdata, quickcreate, quickcreatesequence, info_type, masseditable) VALUES (" . getTabid('Contacts') . "," . $this->db->getUniqueID('vtiger_field') . ", 'campaignrelstatus', 'vtiger_campaignrelstatus', 1, '16', 'campaignrelstatus', 'Status', 1, 0, 0, 100, 1, NULL, 1, 'V~O', 1, NULL, 'BAS', 0)");
        $this->db->query("INSERT INTO vtiger_field(tabid, fieldid, columnname, tablename, generatedtype, uitype, fieldname, fieldlabel, readonly, presence, defaultvalue, maximumlength, sequence, block, displaytype, typeofdata, quickcreate, quickcreatesequence, info_type, masseditable) VALUES (" . getTabid('Accounts') . "," . $this->db->getUniqueID('vtiger_field') . ", 'campaignrelstatus', 'vtiger_campaignrelstatus', 1, '16', 'campaignrelstatus', 'Status', 1, 0, 0, 100, 1, NULL, 1, 'V~O', 1, NULL, 'BAS', 0)");
        $this->db->query("INSERT INTO vtiger_field(tabid, fieldid, columnname, tablename, generatedtype, uitype, fieldname, fieldlabel, readonly, presence, defaultvalue, maximumlength, sequence, block, displaytype, typeofdata, quickcreate, quickcreatesequence, info_type, masseditable) VALUES (" . getTabid('Leads') . "," . $this->db->getUniqueID('vtiger_field') . ", 'campaignrelstatus', 'vtiger_campaignrelstatus', 1, '16', 'campaignrelstatus', 'Status', 1, 0, 0, 100, 1, NULL, 1, 'V~O', 1, NULL, 'BAS', 0)");
        $this->db->query("INSERT INTO vtiger_field(tabid, fieldid, columnname, tablename, generatedtype, uitype, fieldname, fieldlabel, readonly, presence, defaultvalue, maximumlength, sequence, block, displaytype, typeofdata, quickcreate, quickcreatesequence, info_type, masseditable) VALUES (" . getTabid('Campaigns') . "," . $this->db->getUniqueID('vtiger_field') . ", 'campaignrelstatus', 'vtiger_campaignrelstatus', 1, '16', 'campaignrelstatus', 'Status', 1, 0, 0, 100, 1, NULL, 1, 'V~O', 1, NULL, 'BAS', 0)");

        // Updated Phone field uitype
        $this->db->query("update vtiger_field set uitype='11' where fieldname='mobile' and tabid=" . getTabid('Leads'));
        $this->db->query("update vtiger_field set uitype='11' where fieldname='mobile' and tabid=" . getTabid('Contacts'));
        $this->db->query("update vtiger_field set uitype='11' where fieldname='fax' and tabid=" . getTabid('Leads'));
        $this->db->query("update vtiger_field set uitype='11' where fieldname='fax' and tabid=" . getTabid('Contacts'));
        $this->db->query("update vtiger_field set uitype='11' where fieldname='fax' and tabid=" . getTabid('Accounts'));

        $tab_field_array = [
            'Accounts' => ['accountname'],
            'Contacts' => ['imagename'],
            'Products' => ['imagename', 'product_id'],
            'Invoice' => ['invoice_no', 'salesorder_id'],
            'SalesOrder' => ['quote_id', 'salesorder_no'],
            'PurchaseOrder' => ['purchaseorder_no'],
            'Quotes' => ['quote_no'],
            'HelpDesk' => ['filename'],
        ];
        foreach ($tab_field_array as $index => $value) {
            $tabid = getTabid($index);
            $this->db->pquery("UPDATE vtiger_field SET masseditable=0 WHERE tabid=? AND fieldname IN (" . generateQuestionMarks($value) . ")", [$tabid, $value]);
        }

        //The Entity Name for the modules are maintained in this table
        $this->db->query("insert into vtiger_entityname values(7,'Leads','vtiger_leaddetails','firstname,lastname','leadid','leadid')");
        $this->db->query("insert into vtiger_entityname values(6,'Accounts','vtiger_account','accountname','accountid','account_id')");
        $this->db->query("insert into vtiger_entityname values(4,'Contacts','vtiger_contactdetails','firstname,lastname','contactid','contact_id')");
        $this->db->query("insert into vtiger_entityname values(2,'Potentials','vtiger_potential','potentialname','potentialid','potential_id')");
        $this->db->query("insert into vtiger_entityname values(8,'Documents','vtiger_notes','title','notesid','notesid')");
        $this->db->query("insert into vtiger_entityname values(13,'HelpDesk','vtiger_troubletickets','title','ticketid','ticketid')");
        $this->db->query("insert into vtiger_entityname values(14,'Products','vtiger_products','productname','productid','product_id')");
        $this->db->query("insert into vtiger_entityname values(29,'Users','vtiger_users','first_name,last_name','id','id')");
        $this->db->query("insert into vtiger_entityname values(23,'Invoice','vtiger_invoice','subject','invoiceid','invoiceid')");
        $this->db->query("insert into vtiger_entityname values(20,'Quotes','vtiger_quotes','subject','quoteid','quote_id')");
        $this->db->query("insert into vtiger_entityname values(21,'PurchaseOrder','vtiger_purchaseorder','subject','purchaseorderid','purchaseorderid')");
        $this->db->query("insert into vtiger_entityname values(22,'SalesOrder','vtiger_salesorder','subject','salesorderid','salesorder_id')");
        $this->db->query("insert into vtiger_entityname values(18,'Vendors','vtiger_vendor','vendorname','vendorid','vendor_id')");
        $this->db->query("insert into vtiger_entityname values(19,'PriceBooks','vtiger_pricebook','bookname','pricebookid','pricebookid')");
        $this->db->query("insert into vtiger_entityname values(26,'Campaigns','vtiger_campaign','campaignname','campaignid','campaignid')");
        $this->db->query("insert into vtiger_entityname values(15,'Faq','vtiger_faq','question','id','id')");
        // Insert End
        //Inserting values into org share action mapping
        $this->db->query("insert into vtiger_org_share_action_mapping values(0,'Public: Read Only')");
        $this->db->query("insert into vtiger_org_share_action_mapping values(1,'Public: Read, Create/Edit')");
        $this->db->query("insert into vtiger_org_share_action_mapping values(2,'Public: Read, Create/Edit, Delete')");
        $this->db->query("insert into vtiger_org_share_action_mapping values(3,'Private')");

        $this->db->query("insert into vtiger_org_share_action_mapping values(4,'Hide Details')");
        $this->db->query("insert into vtiger_org_share_action_mapping values(5,'Hide Details and Add Events')");
        $this->db->query("insert into vtiger_org_share_action_mapping values(6,'Show Details')");
        $this->db->query("insert into vtiger_org_share_action_mapping values(7,'Show Details and Add Events')");

        //Inserting for all vtiger_tabs
        $def_org_tabid = [2, 4, 6, 7, 8, 13, 14, 20, 21, 22, 23, 26];

        foreach ($def_org_tabid as $def_tabid) {
            $this->db->query("insert into vtiger_org_share_action2tab values(0," . $def_tabid . ")");
            $this->db->query("insert into vtiger_org_share_action2tab values(1," . $def_tabid . ")");
            $this->db->query("insert into vtiger_org_share_action2tab values(2," . $def_tabid . ")");
            $this->db->query("insert into vtiger_org_share_action2tab values(3," . $def_tabid . ")");
        }

        //Insert into default_org_sharingrule
        $this->db->query("insert into vtiger_def_org_share values (" . $this->db->getUniqueID('vtiger_def_org_share') . ",2,2,0)");

        $this->db->query("insert into vtiger_def_org_share values (" . $this->db->getUniqueID('vtiger_def_org_share') . ",4,2,2)");

        $this->db->query("insert into vtiger_def_org_share values (" . $this->db->getUniqueID('vtiger_def_org_share') . ",6,2,0)");

        $this->db->query("insert into vtiger_def_org_share values (" . $this->db->getUniqueID('vtiger_def_org_share') . ",7,2,0)");

        $this->db->query("insert into vtiger_def_org_share values (" . $this->db->getUniqueID('vtiger_def_org_share') . ",13,2,0)");
        $this->db->query("insert into vtiger_def_org_share values (" . $this->db->getUniqueID('vtiger_def_org_share') . ",20,2,0)");
        $this->db->query("insert into vtiger_def_org_share values (" . $this->db->getUniqueID('vtiger_def_org_share') . ",21,2,0)");
        $this->db->query("insert into vtiger_def_org_share values (" . $this->db->getUniqueID('vtiger_def_org_share') . ",22,2,0)");
        $this->db->query("insert into vtiger_def_org_share values (" . $this->db->getUniqueID('vtiger_def_org_share') . ",23,2,0)");
        $this->db->query("insert into vtiger_def_org_share values (" . $this->db->getUniqueID('vtiger_def_org_share') . ",26,2,0)");
        $this->db->query("insert into vtiger_def_org_share values (" . $this->db->getUniqueID('vtiger_def_org_share') . ",8,2,0)");
        $this->db->query("insert into vtiger_def_org_share values (" . $this->db->getUniqueID('vtiger_def_org_share') . ",14,2,0)");

        //Populating the DataShare Related Modules
        //Lead Related Module
        //Account Related Module
        $this->db->query("insert into vtiger_datashare_relatedmodules values (" . $this->db->getUniqueID('vtiger_datashare_relatedmodules') . ",6,2)");
        $this->db->query("insert into vtiger_datashare_relatedmodules values (" . $this->db->getUniqueID('vtiger_datashare_relatedmodules') . ",6,13)");
        $this->db->query("insert into vtiger_datashare_relatedmodules values (" . $this->db->getUniqueID('vtiger_datashare_relatedmodules') . ",6,20)");
        $this->db->query("insert into vtiger_datashare_relatedmodules values (" . $this->db->getUniqueID('vtiger_datashare_relatedmodules') . ",6,22)");
        $this->db->query("insert into vtiger_datashare_relatedmodules values (" . $this->db->getUniqueID('vtiger_datashare_relatedmodules') . ",6,23)");

        //Potential Related Module
        $this->db->query("insert into vtiger_datashare_relatedmodules values (" . $this->db->getUniqueID('vtiger_datashare_relatedmodules') . ",2,20)");
        $this->db->query("insert into vtiger_datashare_relatedmodules values (" . $this->db->getUniqueID('vtiger_datashare_relatedmodules') . ",2,22)");

        //Quote Related Module
        $this->db->query("insert into vtiger_datashare_relatedmodules values (" . $this->db->getUniqueID('vtiger_datashare_relatedmodules') . ",20,22)");

        //SO Related Module
        $this->db->query("insert into vtiger_datashare_relatedmodules values (" . $this->db->getUniqueID('vtiger_datashare_relatedmodules') . ",22,23)");


        // New Secutity End
        //insert into the vtiger_notificationscheduler vtiger_table
        //insert into related list vtiger_table

        //Inserting for vtiger_account related lists
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Accounts") . "," . getTabid("Contacts") . ",'get_contacts',1,'Contacts',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Accounts") . "," . getTabid("Potentials") . ",'get_opportunities',2,'Potentials',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Accounts") . "," . getTabid("Quotes") . ",'get_quotes',3,'Quotes',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Accounts") . "," . getTabid("SalesOrder") . ",'get_salesorder',4,'Sales Order',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Accounts") . "," . getTabid("Invoice") . ",'get_invoices',5,'Invoice',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Accounts") . "," . getTabid("Documents") . ",'get_attachments',9,'Documents',0,'add,select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Accounts") . "," . getTabid("HelpDesk") . ",'get_tickets',10,'HelpDesk',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Accounts") . "," . getTabid("Products") . ",'get_products',11,'Products',0,'select',null,'','')");

        //Inserting Lead Related Lists
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Leads") . "," . getTabid("Documents") . ",'get_attachments',4,'Documents',0,'add,select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Leads") . "," . getTabid("Products") . ",'get_products',5,'Products',0,'select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Leads") . "," . getTabid("Campaigns") . ",'get_campaigns',6,'Campaigns',0,'select',null,'','')");

        //Inserting for contact related lists
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Contacts") . "," . getTabid("Potentials") . ",'get_opportunities',1,'Potentials',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Contacts") . "," . getTabid("HelpDesk") . ",'get_tickets',4,'HelpDesk',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Contacts") . "," . getTabid("Quotes") . ",'get_quotes',5,'Quotes',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Contacts") . "," . getTabid("PurchaseOrder") . ",'get_purchase_orders',6,'Purchase Order',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Contacts") . "," . getTabid("SalesOrder") . ",'get_salesorder',7,'Sales Order',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Contacts") . "," . getTabid("Products") . ",'get_products',8,'Products',0,'select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Contacts") . "," . getTabid("Documents") . ",'get_attachments',10,'Documents',0,'add,select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Contacts") . "," . getTabid("Campaigns") . ",'get_campaigns',11,'Campaigns',0,'select',null,'','')");
        $this->db->query("INSERT INTO vtiger_relatedlists VALUES(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid('Contacts') . "," . getTabid('Invoice') . ",'get_invoices',12,'Invoice',0, 'add',null,'','')");

        //Inserting Potential Related Lists
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Potentials") . "," . getTabid("Contacts") . ",'get_contacts',2,'Contacts',0,'select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Potentials") . "," . getTabid("Products") . ",'get_products',3,'Products',0,'select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Potentials") . "," . getTabid("Documents") . ",'get_attachments',5,'Documents',0,'add,select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Potentials") . "," . getTabid("Quotes") . ",'get_Quotes',6,'Quotes',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Potentials") . "," . getTabid("SalesOrder") . ",'get_salesorder',7,'Sales Order',0,'add',null,'','')");

        //Inserting Product Related Lists
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Products") . "," . getTabid("HelpDesk") . ",'get_tickets',1,'HelpDesk',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Products") . "," . getTabid("Documents") . ",'get_attachments',3,'Documents',0,'add,select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Products") . "," . getTabid("Quotes") . ",'get_quotes',4,'Quotes',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Products") . "," . getTabid("PurchaseOrder") . ",'get_purchase_orders',5,'Purchase Order',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Products") . "," . getTabid("SalesOrder") . ",'get_salesorder',6,'Sales Order',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Products") . "," . getTabid("Invoice") . ",'get_invoices',7,'Invoice',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Products") . "," . getTabid("PriceBooks") . ",'get_product_pricebooks',8,'PriceBooks',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Products") . "," . getTabid("Leads") . ",'get_leads',9,'Leads',0,'select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Products") . "," . getTabid("Accounts") . ",'get_accounts',10,'Accounts',0,'select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Products") . "," . getTabid("Contacts") . ",'get_contacts',11,'Contacts',0,'select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Products") . "," . getTabid("Potentials") . ",'get_opportunities',12,'Potentials',0,'select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Products") . "," . getTabid("Products") . ",'get_products',13,'Product Bundles',0,'add,select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Products") . "," . getTabid("Products") . ",'get_parent_products',14,'Parent Product',0,'',null,'','')");

        //Inserting HelpDesk Related Lists
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("HelpDesk") . "," . getTabid("Documents") . ",'get_attachments',2,'Documents',0,'add,select',null,'','')");

        //Inserting PriceBook Related Lists
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("PriceBooks") . ",14,'get_pricebook_products',2,'Products',0,'select',null,'','')");

        // Inserting Vendor Related Lists
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Vendors") . ",14,'get_products',1,'Products',0,'add,select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Vendors") . ",21,'get_purchase_orders',2,'Purchase Order',0,'add',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Vendors") . ",4,'get_contacts',3,'Contacts',0,'select',null,'','')");

        // Inserting Quotes Related Lists
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Quotes") . "," . getTabid("SalesOrder") . ",'get_salesorder',1,'Sales Order',0,'',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Quotes") . "," . getTabid("Documents") . ",'get_attachments',3,'Documents',0,'add,select',null,'','')");

        // Inserting Purchase order Related Lists
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("PurchaseOrder") . "," . getTabid("Documents") . ",'get_attachments',2,'Documents',0,'add,select',null,'','')");

        // Inserting Sales order Related Lists
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("SalesOrder") . "," . getTabid("Documents") . ",'get_attachments',2,'Documents',0,'add,select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("SalesOrder") . "," . getTabid("Invoice") . ",'get_invoices',3,'Invoice',0,'',null,'','')");

        // Inserting Invoice Related Lists
        $this->db->query("insert into vtiger_relatedlists values(" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Invoice") . "," . getTabid("Documents") . ",'get_attachments',2,'Documents',0,'add,select',null,'','')");

        // Inserting Campaigns Related Lists
        $this->db->query("insert into vtiger_relatedlists values (" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Campaigns") . "," . getTabid("Contacts") . ",'get_contacts',1,'Contacts',0,'add,select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values (" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Campaigns") . "," . getTabid("Leads") . ",'get_leads',2,'Leads',0,'add,select',null,'','')");
        $this->db->query("insert into vtiger_relatedlists values (" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Campaigns") . "," . getTabid("Potentials") . ",'get_opportunities',3,'Potentials',0,'add',null,'','')");
        $this->db->query("INSERT INTO vtiger_relatedlists VALUES (" . $this->db->getUniqueID('vtiger_relatedlists') . ", " . getTabid("Accounts") . ", " . getTabid("Campaigns") . ", 'get_campaigns', 13, 'Campaigns', 0, 'select',null,'','')");
        $this->db->query("INSERT INTO vtiger_relatedlists VALUES (" . $this->db->getUniqueID('vtiger_relatedlists') . ", " . getTabid("Campaigns") . ", " . getTabid("Accounts") . ", 'get_accounts', 5, 'Accounts', 0, 'add,select',null,'','')");

        // Inserting Faq's Related Lists
        $this->db->query("insert into vtiger_relatedlists values (" . $this->db->getUniqueID('vtiger_relatedlists') . "," . getTabid("Faq") . "," . getTabid("Documents") . ",'get_attachments',1,'Documents',0,'add,select',null,'','')");

        $this->db->query("insert into vtiger_notificationscheduler(schedulednotificationid,schedulednotificationname,active,notificationsubject,notificationbody,label) values (" . $this->db->getUniqueID("vtiger_notificationscheduler") . ",'LBL_TASK_NOTIFICATION_DESCRITPION',1,'Task Delay Notification','Tasks delayed beyond 24 hrs ','LBL_TASK_NOTIFICATION')");
        $this->db->query("insert into vtiger_notificationscheduler(schedulednotificationid,schedulednotificationname,active,notificationsubject,notificationbody,label) values (" . $this->db->getUniqueID("vtiger_notificationscheduler") . ",'LBL_BIG_DEAL_DESCRIPTION' ,1,'Big Deal notification','Success! A big deal has been won! ','LBL_BIG_DEAL')");
        $this->db->query("insert into vtiger_notificationscheduler(schedulednotificationid,schedulednotificationname,active,notificationsubject,notificationbody,label) values (" . $this->db->getUniqueID("vtiger_notificationscheduler") . ",'LBL_TICKETS_DESCRIPTION',1,'Pending Tickets notification','Ticket pending please ','LBL_PENDING_TICKETS')");
        $this->db->query("insert into vtiger_notificationscheduler(schedulednotificationid,schedulednotificationname,active,notificationsubject,notificationbody,label) values (" . $this->db->getUniqueID("vtiger_notificationscheduler") . ",'LBL_MANY_TICKETS_DESCRIPTION',1,'Too many tickets Notification','Too many tickets pending against this entity ','LBL_MANY_TICKETS')");
        $this->db->query("insert into vtiger_notificationscheduler(schedulednotificationid,schedulednotificationname,active,notificationsubject,notificationbody,label,type) values (" . $this->db->getUniqueID("vtiger_notificationscheduler") . ",'LBL_START_DESCRIPTION' ,1,'Support Start Notification','10','LBL_START_NOTIFICATION','select')");
        $this->db->query("insert into vtiger_notificationscheduler(schedulednotificationid,schedulednotificationname,active,notificationsubject,notificationbody,label,type) values (" . $this->db->getUniqueID("vtiger_notificationscheduler") . ",'LBL_SUPPORT_DESCRIPTION',1,'Support ending please','11','LBL_SUPPORT_NOTICIATION','select')");
        $this->db->query("insert into vtiger_notificationscheduler(schedulednotificationid,schedulednotificationname,active,notificationsubject,notificationbody,label,type) values (" . $this->db->getUniqueID("vtiger_notificationscheduler") . ",'LBL_SUPPORT_DESCRIPTION_MONTH',1,'Support ending please','12','LBL_SUPPORT_NOTICIATION_MONTH','select')");
        $this->db->query("insert into vtiger_notificationscheduler(schedulednotificationid,schedulednotificationname,active,notificationsubject,notificationbody,label) values (" . $this->db->getUniqueID("vtiger_notificationscheduler") . ",'LBL_ACTIVITY_REMINDER_DESCRIPTION' ,1,'Activity Reminder Notification','This is a reminder notification for the Activity','LBL_ACTIVITY_NOTIFICATION')");

        //inserting actions for get_attachments
        $folderid = $this->db->getUniqueID("vtiger_attachmentsfolder");
        $this->db->query("insert into vtiger_attachmentsfolder values(" . $folderid . ",'Default','This is a Default Folder',1,1)");

//insert into inventory terms and conditions table

        $inv_tandc_text = '
 - Unless otherwise agreed in writing by the supplier all invoices are payable within thirty (30) days of the date of invoice, in the currency of the invoice, drawn on a bank based in India or by such other method as is agreed in advance by the Supplier.

 - All prices are not inclusive of VAT which shall be payable in addition by the Customer at the applicable rate.';

        $this->db->query("insert into vtiger_inventory_tandc(id,type,tandc) values (" . $this->db->getUniqueID("vtiger_inventory_tandc") . ", 'Inventory', '" . $inv_tandc_text . "')");


        //Insert into vtiger_organizationdetails vtiger_table
        $organizationId = $this->db->getUniqueID('vtiger_organizationdetails');
        $this->db->query(
            "insert into vtiger_organizationdetails(organization_id,organizationname,address,city,state,country,code,phone,fax,website,logoname)
								values ($organizationId,'IT-Solutions4You s.r.o.','Slovenska 69','Presov',
										'','Slovakia','08001','+421 773 23 70','+421 773 23 70','it-solutions4you.com','')"
        );


        $this->db->query("insert into vtiger_actionmapping values(0,'Save',0)");
        $this->db->query("insert into vtiger_actionmapping values(1,'EditView',0)");
        $this->db->query("insert into vtiger_actionmapping values(2,'Delete',0)");
        $this->db->query("insert into vtiger_actionmapping values(3,'index',0)");
        $this->db->query("insert into vtiger_actionmapping values(4,'DetailView',0)");
        $this->db->query("insert into vtiger_actionmapping values(5,'Import',0)");
        $this->db->query("insert into vtiger_actionmapping values(6,'Export',0)");
        //$this->db->query("insert into vtiger_actionmapping values(7,'AddBusinessCard',0)");
        $this->db->query("insert into vtiger_actionmapping values(7,'CreateView',0)");
        $this->db->query("insert into vtiger_actionmapping values(8,'Merge',0)");
        $this->db->query("insert into vtiger_actionmapping values(1,'VendorEditView',1)");
        $this->db->query("insert into vtiger_actionmapping values(4,'VendorDetailView',1)");
        $this->db->query("insert into vtiger_actionmapping values(0,'SaveVendor',1)");
        $this->db->query("insert into vtiger_actionmapping values(2,'DeleteVendor',1)");
        $this->db->query("insert into vtiger_actionmapping values(1,'PriceBookEditView',1)");
        $this->db->query("insert into vtiger_actionmapping values(4,'PriceBookDetailView',1)");
        $this->db->query("insert into vtiger_actionmapping values(0,'SavePriceBook',1)");
        $this->db->query("insert into vtiger_actionmapping values(2,'DeletePriceBook',1)");
        $this->db->query("insert into vtiger_actionmapping values(9,'ConvertLead',0)");
        $this->db->query("insert into vtiger_actionmapping values(1,'DetailViewAjax',1)");
        $this->db->query("insert into vtiger_actionmapping values(4,'TagCloud',1)");
        $this->db->query("insert into vtiger_actionmapping values(1,'QuickCreate',1)");
        $this->db->query("insert into vtiger_actionmapping values(3,'Popup',1)");
        $this->db->query("insert into vtiger_actionmapping values(10,'DuplicatesHandling',0)");

        //added by jeri for category view from db
        $this->db->query("insert into vtiger_parenttab values (1,'My Home Page',1,0)");
        $this->db->query("insert into vtiger_parenttab values (2,'Marketing',2,0)");
        $this->db->query("insert into vtiger_parenttab values (3,'Sales',3,0)");
        $this->db->query("insert into vtiger_parenttab values (4,'Support',4,0)");
        $this->db->query("insert into vtiger_parenttab values (5,'Analytics',5,0)");
        $this->db->query("insert into vtiger_parenttab values (6,'Inventory',6,0)");
        $this->db->query("insert into vtiger_parenttab values (7,'Tools',7,0)");
        $this->db->query("insert into vtiger_parenttab values (8,'Settings',8,0)");

        $this->db->query("insert into vtiger_parenttabrel values (1,3,1)");
        $this->db->query("insert into vtiger_parenttabrel values (3,7,1)");
        $this->db->query("insert into vtiger_parenttabrel values (3,6,2)");
        $this->db->query("insert into vtiger_parenttabrel values (3,4,3)");
        $this->db->query("insert into vtiger_parenttabrel values (3,2,4)");
        $this->db->query("insert into vtiger_parenttabrel values (3,20,5)");
        $this->db->query("insert into vtiger_parenttabrel values (3,22,6)");
        $this->db->query("insert into vtiger_parenttabrel values (3,23,7)");
        $this->db->query("insert into vtiger_parenttabrel values (3,19,8)");
        $this->db->query("insert into vtiger_parenttabrel values (3,8,9)");
        $this->db->query("insert into vtiger_parenttabrel values (4,13,1)");
        $this->db->query("insert into vtiger_parenttabrel values (4,15,2)");
        $this->db->query("insert into vtiger_parenttabrel values (4,6,3)");
        $this->db->query("insert into vtiger_parenttabrel values (4,4,4)");
        $this->db->query("insert into vtiger_parenttabrel values (4,8,5)");
        $this->db->query("insert into vtiger_parenttabrel values (6,14,1)");
        $this->db->query("insert into vtiger_parenttabrel values (6,18,2)");
        $this->db->query("insert into vtiger_parenttabrel values (6,19,3)");
        $this->db->query("insert into vtiger_parenttabrel values (6,21,4)");
        $this->db->query("insert into vtiger_parenttabrel values (6,22,5)");
        $this->db->query("insert into vtiger_parenttabrel values (6,20,6)");
        $this->db->query("insert into vtiger_parenttabrel values (6,23,7)");
        $this->db->query("insert into vtiger_parenttabrel values (7,24,1)");
        $this->db->query("insert into vtiger_parenttabrel values (7,27,2)");
        $this->db->query("insert into vtiger_parenttabrel values (7,8,3)");
        $this->db->query("insert into vtiger_parenttabrel values (2,26,1)");
        $this->db->query("insert into vtiger_parenttabrel values (2,6,2)");
        $this->db->query("insert into vtiger_parenttabrel values (2,4,3)");
        $this->db->query("insert into vtiger_parenttabrel values (2,7,5)");
        $this->db->query("insert into vtiger_parenttabrel values (2,8,8)");

        //add settings page to database starts
        $this->addEntriesForSettings();
        //add settings page to database end
        //Added to populate the default inventory tax informations
        $vatid = $this->db->getUniqueID("vtiger_inventorytaxinfo");
        $salesid = $this->db->getUniqueID("vtiger_inventorytaxinfo");
        $serviceid = $this->db->getUniqueID("vtiger_inventorytaxinfo");
        $this->db->query("insert into vtiger_inventorytaxinfo values($vatid,'tax" . $vatid . "','VAT','4.50','0')");
        $this->db->query("insert into vtiger_inventorytaxinfo values($salesid,'tax" . $salesid . "','Sales','10.00','0')");
        $this->db->query("insert into vtiger_inventorytaxinfo values($serviceid,'tax" . $serviceid . "','Service','12.50','0')");
        //After added these taxes we should add these taxes as columns in vtiger_inventoryproductrel table
        $this->db->query("alter table vtiger_inventoryproductrel add column tax$vatid decimal(7,3) default NULL");
        $this->db->query("alter table vtiger_inventoryproductrel add column tax$salesid decimal(7,3) default NULL");
        $this->db->query("alter table vtiger_inventoryproductrel add column tax$serviceid decimal(7,3) default NULL");

        //Added to handle picklist uniqueid for the picklist values
        //$this->db->query("insert into vtiger_picklistvalues_seq values(1)");
        //Added to populate the default Shipping & Hanlding tax informations
        $shvatid = $this->db->getUniqueID("vtiger_shippingtaxinfo");
        $shsalesid = $this->db->getUniqueID("vtiger_shippingtaxinfo");
        $shserviceid = $this->db->getUniqueID("vtiger_shippingtaxinfo");
        $this->db->query("insert into vtiger_shippingtaxinfo values($shvatid,'shtax" . $shvatid . "','VAT','4.50','0')");
        $this->db->query("insert into vtiger_shippingtaxinfo values($shsalesid,'shtax" . $shsalesid . "','Sales','10.00','0')");
        $this->db->query("insert into vtiger_shippingtaxinfo values($shserviceid,'shtax" . $shserviceid . "','Service','12.50','0')");
        //After added these taxes we should add these taxes as columns in vtiger_inventoryshippingrel table
        $this->db->query("alter table vtiger_inventoryshippingrel add column shtax$shvatid decimal(7,3) default NULL");
        $this->db->query("alter table vtiger_inventoryshippingrel add column shtax$shsalesid decimal(7,3) default NULL");
        $this->db->query("alter table vtiger_inventoryshippingrel add column shtax$shserviceid decimal(7,3) default NULL");

        //version file is included here because without including this file version cannot be get
        include('vtigerversion.php');
        $this->db->query("insert into vtiger_version values(" . $this->db->getUniqueID('vtiger_version') . ",'" . $vtiger_current_version . "','" . $vtiger_current_version . "')");

        //Register default language English
        require_once('vtlib/Vtiger/Language.php');
        $vtlanguage = new Vtiger_Language();
        $vtlanguage->register('en_us', 'US English', 'English', true, true, true);

        $this->initWebservices();

        /**
         * Setup module sequence numbering.
         */
        $modseq = [
            'Leads' => 'LEA',
            'Accounts' => 'ACC',
            'Campaigns' => 'CAM',
            'Contacts' => 'CON',
            'Potentials' => 'POT',
            'HelpDesk' => 'TT',
            'Quotes' => 'QUO',
            'SalesOrder' => 'SO',
            'PurchaseOrder' => 'PO',
            'Invoice' => 'INV',
            'Products' => 'PRO',
            'Vendors' => 'VEN',
            'PriceBooks' => 'PB',
            'Faq' => 'FAQ',
            'Documents' => 'DOC',
        ];
        foreach ($modseq as $modname => $prefix) {
            $this->addInventoryRows(
                [
                    ['semodule' => $modname, 'active' => '1', 'prefix' => $prefix, 'startid' => '1', 'curid' => '1'],
                ]
            );
        }

        require('modules/Utilities/Currencies.php');
        foreach ($currencies as $key => $value) {
            $this->db->query("insert into vtiger_currencies values(" . $this->db->getUniqueID("vtiger_currencies") . ",'$key','" . $value[0] . "','" . $value[1] . "')");
        }

        $this->addDefaultLeadMapping();
    }

    function initWebservices()
    {
        $this->vtws_addEntityInfo();
        $this->vtws_addOperationInfo();
        $this->vtws_addFieldTypeInformation();
        $this->vtws_addFieldInfo();
    }

    function vtws_addEntityInfo()
    {
        require_once 'include/Webservices/Utils.php';
        $names = vtws_getModuleNameList();
        $moduleHandler = [
            'file' => 'include/Webservices/VtigerModuleOperation.php',
            'class' => 'VtigerModuleOperation',
        ];

        foreach ($names as $tab) {
            if (in_array($tab, ['Rss', 'Recyclebin'])) {
                continue;
            }
            $entityId = $this->db->getUniqueID("vtiger_ws_entity");
            $this->db->pquery('insert into vtiger_ws_entity(id,name,handler_path,handler_class,ismodule) values (?,?,?,?,?)', [$entityId, $tab, $moduleHandler['file'], $moduleHandler['class'], 1]);
        }

        $entityId = $this->db->getUniqueID("vtiger_ws_entity");
        $this->db->pquery('insert into vtiger_ws_entity(id,name,handler_path,handler_class,ismodule) values (?,?,?,?,?)', [$entityId, 'Events', $moduleHandler['file'], $moduleHandler['class'], 1]);


        $entityId = $this->db->getUniqueID("vtiger_ws_entity");
        $this->db->pquery('insert into vtiger_ws_entity(id,name,handler_path,handler_class,ismodule) values (?,?,?,?,?)', [$entityId, 'Users', $moduleHandler['file'], $moduleHandler['class'], 1]);

        vtws_addDefaultActorTypeEntity('Groups', [
            'fieldNames' => 'groupname',
            'indexField' => 'groupid',
            'tableName' => 'vtiger_groups',
        ]);

        require_once("include/Webservices/WebServiceError.php");
        require_once 'include/Webservices/VtigerWebserviceObject.php';
        $webserviceObject = VtigerWebserviceObject::fromName($this->db, 'Groups');
        $this->db->pquery(
            "insert into vtiger_ws_entity_tables(webservice_entity_id,table_name) values
			(?,?)",
            [$webserviceObject->getEntityId(), 'vtiger_groups']
        );

        vtws_addDefaultActorTypeEntity('Currency', [
            'fieldNames' => 'currency_name',
            'indexField' => 'id',
            'tableName' => 'vtiger_currency_info',
        ]);

        $webserviceObject = VtigerWebserviceObject::fromName($this->db, 'Currency');
        $this->db->pquery("insert into vtiger_ws_entity_tables(webservice_entity_id,table_name) values (?,?)", [$webserviceObject->getEntityId(), 'vtiger_currency_info']);

        vtws_addDefaultActorTypeEntity('DocumentFolders', [
            'fieldNames' => 'foldername',
            'indexField' => 'folderid',
            'tableName' => 'vtiger_attachmentsfolder',
        ]);
        $webserviceObject = VtigerWebserviceObject::fromName($this->db, 'DocumentFolders');
        $this->db->pquery("insert into vtiger_ws_entity_tables(webservice_entity_id,table_name) values (?,?)", [$webserviceObject->getEntityId(), 'vtiger_attachmentsfolder']);

        vtws_addActorTypeWebserviceEntityWithName(
            'CompanyDetails',
            'include/Webservices/VtigerCompanyDetails.php',
            'VtigerCompanyDetails',
            ['fieldNames' => 'organizationname', 'indexField' => 'groupid', 'tableName' => 'vtiger_organizationdetails']
        );
        $webserviceObject = VtigerWebserviceObject::fromName($this->db, 'CompanyDetails');
        $this->db->pquery('INSERT INTO vtiger_ws_entity_tables(webservice_entity_id,table_name) VALUES (?,?)', [$webserviceObject->getEntityId(), 'vtiger_organizationdetails']);
    }

    function vtws_addFieldInfo()
    {
        $this->db->pquery('INSERT INTO vtiger_ws_fieldinfo(id,property_name,property_value) VALUES (?,?,?)', ['vtiger_organizationdetails.organization_id', 'upload.path', '1']);
    }

    function vtws_addFieldTypeInformation()
    {
        $fieldTypeInfo = [
            'picklist' => [15, 16],
            'text' => [19, 20, 21, 24],
            'autogenerated' => [3],
            'phone' => [11],
            'multipicklist' => [33],
            'url' => [17],
            'skype' => [85],
            'boolean' => [56, 156],
            'owner' => [53],
            'file' => [61, 28],
            'email' => [13],
            'currency' => [71, 72],
        ];

        foreach ($fieldTypeInfo as $type => $uitypes) {
            foreach ($uitypes as $uitype) {
                $result = $this->db->pquery("insert into vtiger_ws_fieldtype(uitype,fieldtype) values(?,?)", [$uitype, $type]);
                if (!is_object($result)) {
                    echo "Query for fieldtype details($uitype:uitype,$type:fieldtype)";
                }
            }
        }

        $this->vtws_addReferenceTypeInformation();
    }

    function vtws_addOperationInfo()
    {
        $operationMeta = [
            "login" => [
                "include" => [
                    "include/Webservices/Login.php",
                ],
                "handler" => "vtws_login",
                "params" => [
                    "username" => "String",
                    "accessKey" => "String",
                ],
                "prelogin" => 1,
                "type" => "POST",
            ],
            "retrieve" => [
                "include" => [
                    "include/Webservices/Retrieve.php",
                ],
                "handler" => "vtws_retrieve",
                "params" => [
                    "id" => "String",
                ],
                "prelogin" => 0,
                "type" => "GET",
            ],
            "create" => [
                "include" => [
                    "include/Webservices/Create.php",
                ],
                "handler" => "vtws_create",
                "params" => [
                    "elementType" => "String",
                    "element" => "encoded",
                ],
                "prelogin" => 0,
                "type" => "POST",
            ],
            "update" => [
                "include" => [
                    "include/Webservices/Update.php",
                ],
                "handler" => "vtws_update",
                "params" => [
                    "element" => "encoded",
                ],
                "prelogin" => 0,
                "type" => "POST",
            ],
            "delete" => [
                "include" => [
                    "include/Webservices/Delete.php",
                ],
                "handler" => "vtws_delete",
                "params" => [
                    "id" => "String",
                ],
                "prelogin" => 0,
                "type" => "POST",
            ],
            "sync" => [
                "include" => [
                    "include/Webservices/GetUpdates.php",
                ],
                "handler" => "vtws_sync",
                "params" => [
                    "modifiedTime" => "DateTime",
                    "elementType" => "String",
                ],
                "prelogin" => 0,
                "type" => "GET",
            ],
            "query" => [
                "include" => [
                    "include/Webservices/Query.php",
                ],
                "handler" => "vtws_query",
                "params" => [
                    "query" => "String",
                ],
                "prelogin" => 0,
                "type" => "GET",
            ],
            "logout" => [
                "include" => [
                    "include/Webservices/Logout.php",
                ],
                "handler" => "vtws_logout",
                "params" => [
                    "sessionName" => "String",
                ],
                "prelogin" => 0,
                "type" => "POST",
            ],
            "listtypes" => [
                "include" => [
                    "include/Webservices/ModuleTypes.php",
                ],
                "handler" => "vtws_listtypes",
                "params" => [
                    "fieldTypeList" => "encoded",
                ],
                "prelogin" => 0,
                "type" => "GET",
            ],
            "getchallenge" => [
                "include" => [
                    "include/Webservices/AuthToken.php",
                ],
                "handler" => "vtws_getchallenge",
                "params" => [
                    "username" => "String",
                ],
                "prelogin" => 1,
                "type" => "GET",
            ],
            "describe" => [
                "include" => [
                    "include/Webservices/DescribeObject.php",
                ],
                "handler" => "vtws_describe",
                "params" => [
                    "elementType" => "String",
                ],
                "prelogin" => 0,
                "type" => "GET",
            ],
            "extendsession" => [
                "include" => [
                    "include/Webservices/ExtendSession.php",
                ],
                "handler" => "vtws_extendSession",
                'params' => [],
                "prelogin" => 1,
                "type" => "POST",
            ],
            'convertlead' => [
                "include" => [
                    "include/Webservices/ConvertLead.php",
                ],
                "handler" => "vtws_convertlead",
                "prelogin" => 0,
                "type" => "POST",
                'params' => [
                    'leadId' => 'String',
                    'assignedTo' => 'String',
                    'accountName' => 'String',
                    'avoidPotential' => 'Boolean',
                    'potential' => 'Encoded',
                ],
            ],
            "revise" => [
                "include" => [
                    "include/Webservices/Revise.php",
                ],
                "handler" => "vtws_revise",
                "params" => [
                    "element" => "Encoded",
                ],
                "prelogin" => 0,
                "type" => "POST",
            ],
            "changePassword" => [
                "include" => [
                    "include/Webservices/ChangePassword.php",
                ],
                "handler" => "vtws_changePassword",
                "params" => [
                    "id" => "String",
                    "oldPassword" => "String",
                    "newPassword" => "String",
                    'confirmPassword' => 'String',
                ],
                "prelogin" => 0,
                "type" => "POST",
            ],
            "deleteUser" => [
                "include" => [
                    "include/Webservices/DeleteUser.php",
                ],
                "handler" => "vtws_deleteUser",
                "params" => [
                    "id" => "String",
                    "newOwnerId" => "String",
                ],
                "prelogin" => 0,
                "type" => "POST",
            ],
        ];

        foreach ($operationMeta as $operationName => $operationDetails) {
            $operationId = vtws_addWebserviceOperation($operationName, $operationDetails['include'], $operationDetails['handler'], $operationDetails['type'], $operationDetails['prelogin']);
            $params = $operationDetails['params'];
            $sequence = 1;
            foreach ($params as $paramName => $paramType) {
                vtws_addWebserviceOperationParam($operationId, $paramName, $paramType, $sequence++);
            }
        }
    }

    function vtws_addReferenceTypeInformation()
    {
        $referenceMapping = [
            "50" => ["Accounts"],
            "51" => ["Accounts"],
            "57" => ["Contacts"],
            "58" => ["Campaigns"],
            "73" => ["Accounts"],
            "75" => ["Vendors"],
            "76" => ["Potentials"],
            "78" => ["Quotes"],
            "80" => ["SalesOrder"],
            "81" => ["Vendors"],
            "101" => ["Users"],
            "52" => ["Users"],
            "357" => ["Contacts", "Accounts", "Leads", "Users", "Vendors"],
            "59" => ["Products"],
            "66" => ["Leads", "Accounts", "Potentials", "HelpDesk", "Campaigns"],
            "77" => ["Users"],
            "68" => ["Contacts", "Accounts"],
            "117" => ['Currency'],
            '26' => ['DocumentFolders'],
            '10' => [],
        ];

        foreach ($referenceMapping as $uitype => $referenceArray) {
            $success = true;
            $result = $this->db->pquery("insert into vtiger_ws_fieldtype(uitype,fieldtype) values(?,?)", [$uitype, "reference"]);
            if (!is_object($result)) {
                $success = false;
            }
            $result = $this->db->pquery("select * from vtiger_ws_fieldtype where uitype=?", [$uitype]);
            $rowCount = $this->db->num_rows($result);
            for ($i = 0; $i < $rowCount; $i++) {
                $fieldTypeId = $this->db->query_result($result, $i, "fieldtypeid");
                foreach ($referenceArray as $index => $referenceType) {
                    $result = $this->db->pquery("insert into vtiger_ws_referencetype(fieldtypeid,type) values(?,?)", [$fieldTypeId, $referenceType]);
                    if (!is_object($result)) {
                        echo "failed for: $referenceType, uitype: $fieldTypeId";
                        $success = false;
                    }
                }
            }
            if (!$success) {
                echo "Migration Query Failed";
            }
        }

        $success = true;
        $fieldTypeId = $this->db->getUniqueID("vtiger_ws_entity_fieldtype");
        $result = $this->db->pquery("insert into vtiger_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) values(?,?,?,?);", [$fieldTypeId, 'vtiger_attachmentsfolder', 'createdby', "reference"]);
        if (!is_object($result)) {
            echo "failed fo init<br>";
            $success = false;
        }
        $fieldTypeId = $this->db->getUniqueID('vtiger_ws_entity_fieldtype');
        $result = $this->db->pquery('INSERT INTO vtiger_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) VALUES (?,?,?,?);', [$fieldTypeId, 'vtiger_organizationdetails', 'logoname', 'file']);
        if (!is_object($result)) {
            echo "failed fo init<br>";
            $success = false;
        }
        $fieldTypeId = $this->db->getUniqueID('vtiger_ws_entity_fieldtype');
        $result = $this->db->pquery('INSERT INTO vtiger_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) VALUES (?,?,?,?);', [$fieldTypeId, 'vtiger_organizationdetails', 'phone', 'phone']);
        if (!is_object($result)) {
            echo "failed fo init<br>";
            $success = false;
        }
        $fieldTypeId = $this->db->getUniqueID('vtiger_ws_entity_fieldtype');
        $result = $this->db->pquery('INSERT INTO vtiger_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) VALUES (?,?,?,?);', [$fieldTypeId, 'vtiger_organizationdetails', 'fax', 'phone']);
        if (!is_object($result)) {
            echo "failed fo init<br>";
            $success = false;
        }
        $fieldTypeId = $this->db->getUniqueID('vtiger_ws_entity_fieldtype');
        $result = $this->db->pquery('INSERT INTO vtiger_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) VALUES (?,?,?,?);', [$fieldTypeId, 'vtiger_organizationdetails', 'website', 'url']);
        if (!is_object($result)) {
            echo "failed fo init<br>";
            $success = false;
        }

        $result = $this->db->pquery("insert into vtiger_ws_entity_referencetype(fieldtypeid,type) values(?,?)", [$fieldTypeId, 'Users']);
        if (!is_object($result)) {
            echo "failed for: Users, fieldtypeid: $fieldTypeId";
            $success = false;
        }
        if (!$success) {
            echo "Migration Query Failed";
        }
    }

    function __construct()
    {
        $this->log = Logger::getLogger('DefaultDataPopulator');
        $this->db = PearDatabase::getInstance();
    }

}