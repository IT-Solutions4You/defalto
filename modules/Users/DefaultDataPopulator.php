<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

include_once('config.php');
require_once('include/logging.php');
require_once('include/utils/utils.php');

/** Class to populate the default required data during installation
 */
class DefaultDataPopulator extends CRMEntity
{
    var $new_schema = true;

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
                'blockid'        => $blockId,
                'tabid'          => $tabId,
                'blocklabel'     => $blockLabel,
                'sequence'       => $sequence,
                'show_title'     => 0,
                'visible'        => 0,
                'create_view'    => 0,
                'edit_view'      => 0,
                'detail_view'    => 0,
                'display_status' => 1,
                'iscustom'       => 0,
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
            $this->db->pquery(
                'INSERT INTO vtiger_field (tabid,fieldid,columnname,tablename,generatedtype,uitype,fieldname,fieldlabel,readonly,presence,defaultvalue,maximumlength,sequence,block,displaytype,typeofdata,quickcreate,quickcreatesequence,info_type,masseditable) VALUES (' . generateQuestionMarks(
                    $field
                ) . ')',
                $field
            );

            $fieldIds[$field[0]][$field[2]] = $field[1];
        }

        return $fieldIds;
    }

    public function createTabs($tabs): array
    {
        $tabIds = [];

        foreach ($tabs as $tabInfo) {
            $tabIds[$tabInfo[1]] = $tabInfo[0];
            $this->db->pquery(
                'INSERT INTO vtiger_tab(tabid,name,presence,tabsequence,tablabel,customized,ownedby,isentitytype,parent) VALUES (' . generateQuestionMarks($tabInfo) . ')',
                $tabInfo
            );
        }

        return $tabIds;
    }

    /** Function to populate the default required data during installation
     * @throws Exception
     */
    function create_tables()
    {
        global $app_strings;

        $tabs = [
            [1, 'Dashboard', 0, 12, 'Dashboards', 0, 1, 0, 'Analytics'],
            [2, 'Potentials', 0, 7, 'Potentials', 0, 0, 1, 'Sales'],
            [3, 'Home', 0, 1, 'Home', 0, 1, 0, null],
            [4, 'Contacts', 0, 6, 'Contacts', 0, 0, 1, 'Sales'],
            [6, 'Accounts', 0, 5, 'Accounts', 0, 0, 1, 'Sales'],
            [7, 'Leads', 0, 4, 'Leads', 0, 0, 1, 'Sales'],
            [8, 'Documents', 0, 9, 'Documents', 0, 0, 1, 'Tools'],
            [13, 'HelpDesk', 0, 11, 'HelpDesk', 0, 0, 1, 'Support'],
            [14, 'Products', 0, 8, 'Products', 0, 0, 1, 'Inventory'],
            [15, 'Faq', 0, -1, 'Faq', 0, 1, 1, 'Support'],
            [18, 'Vendors', 0, -1, 'Vendors', 0, 1, 1, 'Inventory'],
            [19, 'PriceBooks', 0, -1, 'PriceBooks', 0, 1, 1, 'Inventory'],
            [20, 'Quotes', 0, -1, 'Quotes', 0, 0, 1, 'Sales'],
            [21, 'PurchaseOrder', 0, -1, 'PurchaseOrder', 0, 0, 1, 'Inventory'],
            [22, 'SalesOrder', 0, -1, 'SalesOrder', 0, 0, 1, 'Sales'],
            [23, 'Invoice', 0, -1, 'Invoice', 0, 0, 1, 'Sales'],
            [24, 'Rss', 0, -1, 'Rss', 0, 1, 0, null],
            [26, 'Campaigns', 0, -1, 'Campaigns', 0, 0, 1, 'Marketing'],
            [27, 'Portal', 0, -1, 'Portal', 0, 1, 0, null],
            [29, 'Users', 0, -1, 'Users', 0, 1, 0, null],
            [30, 'ModTracker', 0, -1, 'ModTracker', 1, 1, 0, ''],
            [31, 'ModComments', 0, -1, 'Comments', 1, 0, 1, 'Settings'],
            [32, 'Import', 0, -1, 'Import', 1, 1, 0, ''],
            [33, 'MailManager', 0, -1, 'MailManager', 1, 1, 0, 'Tools'],
            [34, 'Google', 0, -1, 'Google', 1, 1, 0, ''],
            [35, 'CustomerPortal', 0, -1, 'CustomerPortal', 0, 1, 0, ''],
            [36, 'Webforms', 0, -1, 'Webforms', 0, 1, 0, ''],
            [37, 'RecycleBin', 0, -1, 'Recycle Bin', 0, 1, 0, 'TOOLS'],
            [38, 'PBXManager', 0, -1, 'PBXManager', 1, 0, 1, 'Tools'],
            [39, 'ServiceContracts', 0, -1, 'ServiceContracts', 0, 0, 1, 'SUPPORT'],
            [40, 'Services', 0, -1, 'Services', 0, 0, 1, 'INVENTORY'],
            [41, 'WSAPP', 0, -1, 'WSAPP', 1, 1, 0, ''],
            [42, 'Assets', 0, -1, 'Assets', 0, 0, 1, 'INVENTORY'],
            [43, 'Project', 0, -1, 'Project', 0, 0, 1, 'PROJECT'],
            [44, 'ProjectMilestone', 0, -1, 'ProjectMilestone', 0, 0, 1, 'PROJECT'],
            [45, 'ProjectTask', 0, -1, 'ProjectTask', 0, 0, 1, 'PROJECT'],
            [46, 'SMSNotifier', 0, -1, 'SMSNotifier', 0, 0, 1, ''],
            [47, 'Appointments', 0, -1, 'Appointments', 1, 0, 1, 'HOME'],
            [48, 'ITS4YouEmails', 0, -1, 'Emails', 1, 0, 1, 'Tools'],
            [49, 'EMAILMaker', 0, -1, 'EMAILMaker', 1, 1, 0, 'TOOLS'],
            [50, 'PDFMaker', 0, -1, 'PDFMaker', 1, 1, 0, 'TOOLS'],
            [51, 'Reporting', 0, -1, 'Reporting', 1, 0, 1, 'ANALYTICS'],
            [52, 'Installer', 0, -1, 'Installer', 1, 1, 0, 'Tools'],
            [53, 'Tour', 0, -1, 'Tour', 1, 1, 0, 'TOOLS'],
            [54, 'InventoryItem', 0, -1, 'InventoryItem', 1, 0, 1, ''],
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

        //inserting actions for get_attachments
        $folderid = $this->db->getUniqueID("vtiger_attachmentsfolder");
        $this->db->query("insert into vtiger_attachmentsfolder values(" . $folderid . ",'Default','This is a Default Folder',1,1)");

//insert into inventory terms and conditions table

        $inv_tandc_text = '
 - Unless otherwise agreed in writing by the supplier all invoices are payable within thirty (30) days of the date of invoice, in the currency of the invoice, drawn on a bank based in India or by such other method as is agreed in advance by the Supplier.

 - All prices are not inclusive of VAT which shall be payable in addition by the Customer at the applicable rate.';

        $this->db->query(
            "insert into vtiger_inventory_tandc(id,type,tandc) values (" . $this->db->getUniqueID("vtiger_inventory_tandc") . ", 'Inventory', '" . $inv_tandc_text . "')"
        );

        //Insert into vtiger_organizationdetails vtiger_table
        (new Core_DatabaseData_Model())->getTable('vtiger_organizationdetails', null)->insertData([
            'organization_id'  => $this->db->getUniqueID('vtiger_organizationdetails'),
            'organizationname' => 'IT-Solutions4You s.r.o.',
            'address'          => 'IT-Solutions4You s.r.o.',
            'city'             => 'Presov',
            'state'            => '',
            'country_id'       => 'SK',
            'code'             => '08001',
            'phone'            => '+421 773 23 70',
            'website'          => 'it-solutions4you.com',
            'logoname'         => '',
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
        include 'version.php';
        $this->db->query("insert into vtiger_version values(" . $this->db->getUniqueID('vtiger_version') . ",'" . $defalto_current_version . "','" . $defalto_current_version . "')");

        //Register default language English
        require_once('vtlib/Vtiger/Language.php');
        $vtlanguage = new Vtiger_Language();
        $vtlanguage->register('en_us', 'US English', 'English', true, true, true);

        $this->initWebservices();

        /**
         * Setup module sequence numbering.
         */
        $modseq = [
            'Leads'         => 'LEA',
            'Accounts'      => 'ACC',
            'Campaigns'     => 'CAM',
            'Contacts'      => 'CON',
            'Potentials'    => 'POT',
            'HelpDesk'      => 'TT',
            'Quotes'        => 'QUO',
            'SalesOrder'    => 'SO',
            'PurchaseOrder' => 'PO',
            'Invoice'       => 'INV',
            'Products'      => 'PRO',
            'Vendors'       => 'VEN',
            'PriceBooks'    => 'PB',
            'Faq'           => 'FAQ',
            'Documents'     => 'DOC',
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
            'file'  => 'include/Webservices/VtigerModuleOperation.php',
            'class' => 'VtigerModuleOperation',
        ];

        foreach ($names as $tab) {
            if (in_array($tab, ['Rss', 'Recyclebin'])) {
                continue;
            }
            $entityId = $this->db->getUniqueID("vtiger_ws_entity");
            $this->db->pquery(
                'insert into vtiger_ws_entity(id,name,handler_path,handler_class,ismodule) values (?,?,?,?,?)',
                [$entityId, $tab, $moduleHandler['file'], $moduleHandler['class'], 1]
            );
        }

        $entityId = $this->db->getUniqueID("vtiger_ws_entity");
        $this->db->pquery(
            'insert into vtiger_ws_entity(id,name,handler_path,handler_class,ismodule) values (?,?,?,?,?)',
            [$entityId, 'Events', $moduleHandler['file'], $moduleHandler['class'], 1]
        );

        $entityId = $this->db->getUniqueID("vtiger_ws_entity");
        $this->db->pquery(
            'insert into vtiger_ws_entity(id,name,handler_path,handler_class,ismodule) values (?,?,?,?,?)',
            [$entityId, 'Users', $moduleHandler['file'], $moduleHandler['class'], 1]
        );

        vtws_addDefaultActorTypeEntity('Groups', [
            'fieldNames' => 'groupname',
            'indexField' => 'groupid',
            'tableName'  => 'vtiger_groups',
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
            'tableName'  => 'vtiger_currency_info',
        ]);

        $webserviceObject = VtigerWebserviceObject::fromName($this->db, 'Currency');
        $this->db->pquery("insert into vtiger_ws_entity_tables(webservice_entity_id,table_name) values (?,?)", [$webserviceObject->getEntityId(), 'vtiger_currency_info']);

        vtws_addDefaultActorTypeEntity('DocumentFolders', [
            'fieldNames' => 'foldername',
            'indexField' => 'folderid',
            'tableName'  => 'vtiger_attachmentsfolder',
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
            'picklist'      => [15, 16],
            'text'          => [19, 20, 21, 24],
            'autogenerated' => [3],
            'phone'         => [11],
            'multipicklist' => [33],
            'url'           => [17],
            'skype'         => [85],
            'boolean'       => [56, 156],
            'owner'         => [53],
            'file'          => [61, 28],
            'email'         => [13],
            'currency'      => [71, 72],
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
            "login"          => [
                "include"  => [
                    "include/Webservices/Login.php",
                ],
                "handler"  => "vtws_login",
                "params"   => [
                    "username"  => "String",
                    "accessKey" => "String",
                ],
                "prelogin" => 1,
                "type"     => "POST",
            ],
            "retrieve"       => [
                "include"  => [
                    "include/Webservices/Retrieve.php",
                ],
                "handler"  => "vtws_retrieve",
                "params"   => [
                    "id" => "String",
                ],
                "prelogin" => 0,
                "type"     => "GET",
            ],
            "create"         => [
                "include"  => [
                    "include/Webservices/Create.php",
                ],
                "handler"  => "vtws_create",
                "params"   => [
                    "elementType" => "String",
                    "element"     => "encoded",
                ],
                "prelogin" => 0,
                "type"     => "POST",
            ],
            "update"         => [
                "include"  => [
                    "include/Webservices/Update.php",
                ],
                "handler"  => "vtws_update",
                "params"   => [
                    "element" => "encoded",
                ],
                "prelogin" => 0,
                "type"     => "POST",
            ],
            "delete"         => [
                "include"  => [
                    "include/Webservices/Delete.php",
                ],
                "handler"  => "vtws_delete",
                "params"   => [
                    "id" => "String",
                ],
                "prelogin" => 0,
                "type"     => "POST",
            ],
            "sync"           => [
                "include"  => [
                    "include/Webservices/GetUpdates.php",
                ],
                "handler"  => "vtws_sync",
                "params"   => [
                    "modifiedTime" => "DateTime",
                    "elementType"  => "String",
                ],
                "prelogin" => 0,
                "type"     => "GET",
            ],
            "query"          => [
                "include"  => [
                    "include/Webservices/Query.php",
                ],
                "handler"  => "vtws_query",
                "params"   => [
                    "query" => "String",
                ],
                "prelogin" => 0,
                "type"     => "GET",
            ],
            "logout"         => [
                "include"  => [
                    "include/Webservices/Logout.php",
                ],
                "handler"  => "vtws_logout",
                "params"   => [
                    "sessionName" => "String",
                ],
                "prelogin" => 0,
                "type"     => "POST",
            ],
            "listtypes"      => [
                "include"  => [
                    "include/Webservices/ModuleTypes.php",
                ],
                "handler"  => "vtws_listtypes",
                "params"   => [
                    "fieldTypeList" => "encoded",
                ],
                "prelogin" => 0,
                "type"     => "GET",
            ],
            "getchallenge"   => [
                "include"  => [
                    "include/Webservices/AuthToken.php",
                ],
                "handler"  => "vtws_getchallenge",
                "params"   => [
                    "username" => "String",
                ],
                "prelogin" => 1,
                "type"     => "GET",
            ],
            "describe"       => [
                "include"  => [
                    "include/Webservices/DescribeObject.php",
                ],
                "handler"  => "vtws_describe",
                "params"   => [
                    "elementType" => "String",
                ],
                "prelogin" => 0,
                "type"     => "GET",
            ],
            "extendsession"  => [
                "include"  => [
                    "include/Webservices/ExtendSession.php",
                ],
                "handler"  => "vtws_extendSession",
                'params'   => [],
                "prelogin" => 1,
                "type"     => "POST",
            ],
            'convertlead'    => [
                "include"  => [
                    "include/Webservices/ConvertLead.php",
                ],
                "handler"  => "vtws_convertlead",
                "prelogin" => 0,
                "type"     => "POST",
                'params'   => [
                    'leadId'         => 'String',
                    'assignedTo'     => 'String',
                    'accountName'    => 'String',
                    'avoidPotential' => 'Boolean',
                    'potential'      => 'Encoded',
                ],
            ],
            "revise"         => [
                "include"  => [
                    "include/Webservices/Revise.php",
                ],
                "handler"  => "vtws_revise",
                "params"   => [
                    "element" => "Encoded",
                ],
                "prelogin" => 0,
                "type"     => "POST",
            ],
            "changePassword" => [
                "include"  => [
                    "include/Webservices/ChangePassword.php",
                ],
                "handler"  => "vtws_changePassword",
                "params"   => [
                    "id"              => "String",
                    "oldPassword"     => "String",
                    "newPassword"     => "String",
                    'confirmPassword' => 'String',
                ],
                "prelogin" => 0,
                "type"     => "POST",
            ],
            "deleteUser"     => [
                "include"  => [
                    "include/Webservices/DeleteUser.php",
                ],
                "handler"  => "vtws_deleteUser",
                "params"   => [
                    "id"         => "String",
                    "newOwnerId" => "String",
                ],
                "prelogin" => 0,
                "type"     => "POST",
            ],
        ];

        foreach ($operationMeta as $operationName => $operationDetails) {
            $operationId = vtws_addWebserviceOperation(
                $operationName,
                $operationDetails['include'],
                $operationDetails['handler'],
                $operationDetails['type'],
                $operationDetails['prelogin']
            );
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
            "50"  => ["Accounts"],
            "51"  => ["Accounts"],
            "57"  => ["Contacts"],
            "58"  => ["Campaigns"],
            "73"  => ["Accounts"],
            "75"  => ["Vendors"],
            "76"  => ["Potentials"],
            "78"  => ["Quotes"],
            "80"  => ["SalesOrder"],
            "81"  => ["Vendors"],
            "101" => ["Users"],
            "52"  => ["Users"],
            "357" => ["Contacts", "Accounts", "Leads", "Users", "Vendors"],
            "59"  => ["Products"],
            "66"  => ["Leads", "Accounts", "Potentials", "HelpDesk", "Campaigns"],
            "77"  => ["Users"],
            "68"  => ["Contacts", "Accounts"],
            "117" => ['Currency'],
            '26'  => ['DocumentFolders'],
            '10'  => [],
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
        $result = $this->db->pquery(
            "insert into vtiger_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) values(?,?,?,?);",
            [$fieldTypeId, 'vtiger_attachmentsfolder', 'createdby', "reference"]
        );
        if (!is_object($result)) {
            echo "failed fo init<br>";
            $success = false;
        }
        $fieldTypeId = $this->db->getUniqueID('vtiger_ws_entity_fieldtype');
        $result = $this->db->pquery(
            'INSERT INTO vtiger_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) VALUES (?,?,?,?);',
            [$fieldTypeId, 'vtiger_organizationdetails', 'logoname', 'file']
        );
        if (!is_object($result)) {
            echo "failed fo init<br>";
            $success = false;
        }
        $fieldTypeId = $this->db->getUniqueID('vtiger_ws_entity_fieldtype');
        $result = $this->db->pquery(
            'INSERT INTO vtiger_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) VALUES (?,?,?,?);',
            [$fieldTypeId, 'vtiger_organizationdetails', 'phone', 'phone']
        );
        if (!is_object($result)) {
            echo "failed fo init<br>";
            $success = false;
        }
        $fieldTypeId = $this->db->getUniqueID('vtiger_ws_entity_fieldtype');
        $result = $this->db->pquery(
            'INSERT INTO vtiger_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) VALUES (?,?,?,?);',
            [$fieldTypeId, 'vtiger_organizationdetails', 'fax', 'phone']
        );
        if (!is_object($result)) {
            echo "failed fo init<br>";
            $success = false;
        }
        $fieldTypeId = $this->db->getUniqueID('vtiger_ws_entity_fieldtype');
        $result = $this->db->pquery(
            'INSERT INTO vtiger_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) VALUES (?,?,?,?);',
            [$fieldTypeId, 'vtiger_organizationdetails', 'website', 'url']
        );
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
        $this->log = Vtiger_Logger_Helper::getLogger('DefaultDataPopulator');
        $this->db = PearDatabase::getInstance();
    }
}