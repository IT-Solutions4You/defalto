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

            (new Vtiger_Block())->getBlockTable()->insertData([
                'blockid' =>  $blockId,
                'tabid' =>  $tabId,
                'blocklabel' =>  $blockLabel,
                'sequence' =>  $sequence,
                'show_title' => 0,
                'visible' => 0,
                'create_view' => 0,
                'edit_view' => 0,
                'detail_view' => 0,
                'display_status' => 1,
                'iscustom' => 0,
            ]);
        }

        return $blockIds;
    }

    public function createFields($fields): array
    {
        $fieldIds = [];

        foreach ($fields as $field) {
            $fieldId = $this->db->getUniqueID('vtiger_field');
            $field[1] = $fieldId;
            $this->db->pquery('INSERT INTO vtiger_field (tabid,fieldid,columnname,tablename,generatedtype,uitype,fieldname,fieldlabel,readonly,presence,defaultvalue,maximumlength,sequence,block,displaytype,typeofdata,quickcreate,quickcreatesequence,info_type,masseditable) VALUES (' . generateQuestionMarks($field) . ')', $field);

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
     * @throws AppException
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
        /** Default language */
        Install_Utils_Model::installDefaultLanguage();
        /** Users */
        $usersInstall = Core_Install_Model::getInstance(Vtiger_ModuleBasic::EVENT_MODULE_POSTINSTALL, 'Users')->installModule();
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
        (new Core_DatabaseData_Model())->getTable('vtiger_organizationdetails', null)->insertData([
            'organization_id' => $this->db->getUniqueID('vtiger_organizationdetails'),
            'organizationname' => 'IT-Solutions4You s.r.o.',
            'address' => 'IT-Solutions4You s.r.o.',
            'city' => 'Presov',
            'state' => '',
            'country_id' => 'SK',
            'code' => '08001',
            'phone' => '+421 773 23 70',
            'website' => 'it-solutions4you.com',
            'logoname' => '',
        ]);

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