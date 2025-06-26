<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

if (!defined('VTIGER_UPGRADE')) die('Invalid entry point');

vimport('~~include/utils/utils.php');
vimport('~~modules/com_vtiger_workflow/include.inc');
vimport('~~modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc');
vimport('~~modules/com_vtiger_workflow/VTEntityMethodManager.inc');
vimport('~~include/Webservices/Utils.php');
vimport('~~modules/Users/Users.php');
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
require_once 'modules/com_vtiger_workflow/VTWorkflowManager.inc';
require_once 'modules/com_vtiger_workflow/VTTaskManager.inc';

if(!defined('INSTALLATION_MODE')) {
	Migration_Index_View::ExecuteQuery('ALTER TABLE com_vtiger_workflows ADD COLUMN filtersavedinnew int(1)', array());
}

Migration_Index_View::ExecuteQuery('UPDATE com_vtiger_workflows SET filtersavedinnew = 5', array());

// Core workflow schema dependecy introduced in 6.1.0
$adb = PearDatabase::getInstance();
$columns = [
    'schtypeid' => 'INT(10)',
    'schtime' => 'TIME',
    'schdayofmonth' => 'VARCHAR(100)',
    'schdayofweek' => 'VARCHAR(100)',
    'schannualdates' => 'VARCHAR(100)',
    'nexttrigger_time' => 'DATETIME',
];

foreach ($columns as $column => $type) {
    if (!columnExists($column, 'com_vtiger_workflows')) {
        $adb->pquery(sprintf('ALTER TABLE com_vtiger_workflows ADD %s %s', $column, $type));
    }
}

if(!defined('INSTALLATION_MODE')) {
	Migration_Index_View::ExecuteQuery("CREATE TABLE IF NOT EXISTS com_vtiger_workflow_tasktypes (
					id int(11) NOT NULL,
					tasktypename varchar(255) NOT NULL,
					label varchar(255),
					classname varchar(255),
					classpath varchar(255),
					templatepath varchar(255),
					modules text(500),
					sourcemodule varchar(255)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8", array());

    $taskTypes = [];
    $defaultModules = ['include' => [], 'exclude' => []];
    $createToDoModules = [
        'include' => ["Leads", "Accounts", "Potentials", "Contacts", "HelpDesk", "Campaigns", "Quotes", "PurchaseOrder", "SalesOrder", "Invoice"],
        'exclude' => ["FAQ"]
    ];
    $createEventModules = ['include' => ["Leads", "Accounts", "Potentials", "Contacts", "HelpDesk", "Campaigns"], 'exclude' => ["FAQ"]];

    $taskTypes[] = [
        'name'         => 'VTEmailTask',
        'label'        => 'Send Mail',
        'classname'    => 'VTEmailTask',
        'classpath'    => 'modules/com_vtiger_workflow/tasks/VTEmailTask.inc',
        'templatepath' => 'com_vtiger_workflow/taskforms/VTEmailTask.tpl',
        'modules'      => $defaultModules,
        'sourcemodule' => ''
    ];
    $taskTypes[] = [
        'name'         => 'VTEntityMethodTask',
        'label'        => 'Invoke Custom Function',
        'classname'    => 'VTEntityMethodTask',
        'classpath'    => 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc',
        'templatepath' => 'com_vtiger_workflow/taskforms/VTEntityMethodTask.tpl',
        'modules'      => $defaultModules,
        'sourcemodule' => ''
    ];
    $taskTypes[] = [
        'name'         => 'VTUpdateFieldsTask',
        'label'        => 'Update Fields',
        'classname'    => 'VTUpdateFieldsTask',
        'classpath'    => 'modules/com_vtiger_workflow/tasks/VTUpdateFieldsTask.inc',
        'templatepath' => 'com_vtiger_workflow/taskforms/VTUpdateFieldsTask.tpl',
        'modules'      => $defaultModules,
        'sourcemodule' => ''
    ];
    $taskTypes[] = [
        'name'         => 'VTCreateEntityTask',
        'label'        => 'Create Entity',
        'classname'    => 'VTCreateEntityTask',
        'classpath'    => 'modules/com_vtiger_workflow/tasks/VTCreateEntityTask.inc',
        'templatepath' => 'com_vtiger_workflow/taskforms/VTCreateEntityTask.tpl',
        'modules'      => $defaultModules,
        'sourcemodule' => ''
    ];
    $taskTypes[] = [
        'name'         => 'VTSMSTask',
        'label'        => 'SMS Task',
        'classname'    => 'VTSMSTask',
        'classpath'    => 'modules/com_vtiger_workflow/tasks/VTSMSTask.inc',
        'templatepath' => 'com_vtiger_workflow/taskforms/VTSMSTask.tpl',
        'modules'      => $defaultModules,
        'sourcemodule' => 'SMSNotifier'
    ];

    foreach ($taskTypes as $taskType) {
		VTTaskType::registerTaskType($taskType);
	}
}

Migration_Index_View::ExecuteQuery("CREATE TABLE IF NOT EXISTS vtiger_shorturls (
					id int(11) NOT NULL AUTO_INCREMENT,
					uid varchar(50) DEFAULT NULL,
					handler_path varchar(400) DEFAULT NULL,
					handler_class varchar(100) DEFAULT NULL,
					handler_function varchar(100) DEFAULT NULL,
					handler_data varchar(255) DEFAULT NULL,
					PRIMARY KEY (id),
					KEY uid (uid)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8", array());

global $adb;

// Change default Sales Man rolename to Sales Person
Migration_Index_View::ExecuteQuery("UPDATE vtiger_role SET rolename=? WHERE rolename=? and roleid=?", array('Sales Person', 'Sales Man', 'H5'));

if(!defined('INSTALLATION_MODE')) {
	$picklistResult = $adb->pquery('SELECT distinct fieldname FROM vtiger_field WHERE uitype IN (15,33)', array());
	$numRows = $adb->num_rows($picklistResult);
	for($i=0; $i<$numRows; $i++) {
		$fieldName = $adb->query_result($picklistResult,$i,'fieldname');
		$query = 'ALTER TABLE vtiger_'.$fieldName.' ADD COLUMN sortorderid INT(1)';
		Migration_Index_View::ExecuteQuery($query, array());
	}
}

Migration_Index_View::ExecuteQuery("ALTER TABLE vtiger_currency_info MODIFY COLUMN conversion_rate decimal(12,5)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE vtiger_productcurrencyrel MODIFY COLUMN actual_price decimal(28,5)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE vtiger_productcurrencyrel MODIFY COLUMN converted_price decimal(28,5)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE vtiger_pricebookproductrel MODIFY COLUMN listprice decimal(27,5)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE vtiger_inventoryproductrel MODIFY COLUMN listprice decimal(27,5)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE vtiger_inventoryproductrel MODIFY COLUMN discount_amount decimal(27,5)", array());

$currencyField = new CurrencyField(null);
$result = $adb->pquery("SELECT fieldname,tablename,columnname FROM vtiger_field WHERE uitype IN (?,?)",array('71','72'));
$count = $adb->num_rows($result);
for($i=0;$i<$count;$i++) {
	$fieldName = $adb->query_result($result,$i,'fieldname');
	$tableName = $adb->query_result($result,$i,'tablename');
	$columnName = $adb->query_result($result,$i,'columnname');

	$tableAndColumnSize = array();
	$tableInfo = $adb->database->MetaColumns($tableName);
	foreach ($tableInfo as $column) {
		$max_length = $column->max_length;
		$scale = $column->scale;

		$tableAndColumnSize[$tableName][$column->name]['max_length'] = $max_length;
		$tableAndColumnSize[$tableName][$column->name]['scale'] = $scale;
	}
	if(!empty($tableAndColumnSize[$tableName][$columnName]['scale'])) {
		$decimalsToChange = $currencyField->maxNumberOfDecimals - $tableAndColumnSize[$tableName][$columnName]['scale'];
		if($decimalsToChange != 0) {
			$maxlength = $tableAndColumnSize[$tableName][$columnName]['max_length'] + $decimalsToChange;
			$decimalDigits = $tableAndColumnSize[$tableName][$columnName]['scale'] + $decimalsToChange;

            Migration_Index_View::ExecuteQuery('ALTER TABLE ' . $tableName . ' CHANGE ' . $columnName . ' ' . $columnName . ' decimal(' . (int)$maxlength . ',' . (int)$decimalDigits . ')', null);
		}
	}
}

$inventoryModules = array('Invoice','SalesOrder','PurchaseOrder','Quotes');
$actions = array('Import','Export');

for($i = 0; $i < php7_count($inventoryModules); $i++) {
	$moduleName = $inventoryModules[$i];
	$moduleInstance = Vtiger_Module::getInstance($moduleName);

	$blockInstance = new Vtiger_Block();

	$blockInstance->label = 'LBL_ITEM_DETAILS';
	$blockInstance->sequence = '5';
	$blockInstance->showtitle = '0';

	$moduleInstance->addBlock($blockInstance);

	foreach ($actions as $actionName) {
		Vtiger_Access::updateTool($moduleInstance, $actionName, true, '');
	}
}

$itemFieldsName = array('productid','quantity','listprice','comment','discount_amount','discount_percent','tax1','tax2','tax3');
$itemFieldsLabel = array('Item Name','Quantity','List Price','Item Comment','Item Discount Amount','Item Discount Percent','Tax1','Tax2','Tax3');
$itemFieldsTypeOfData = array('V~M','V~M','V~M','V~O','V~O','V~O','V~O','V~O','V~O');
$itemFieldsDisplayType = array('10','7','19','19','7','7','83','83','83');

for($i=0; $i<php7_count($inventoryModules); $i++) {
	$moduleName = $inventoryModules[$i];
	$moduleInstance = Vtiger_Module::getInstance($moduleName);
	$blockInstance = Vtiger_Block::getInstance('LBL_ITEM_DETAILS',$moduleInstance);

	$relatedmodules = array('Products','Services');

	for($j=0;$j<php7_count($itemFieldsName);$j++) {
		$field = new Vtiger_Field();

		$field->name = $itemFieldsName[$j];
		$field->label = $itemFieldsLabel[$j];
		$field->column = $itemFieldsName[$j];
		$field->table = 'vtiger_inventoryproductrel';
		$field->uitype = $itemFieldsDisplayType[$j];
		$field->typeofdata = $itemFieldsTypeOfData[$j];
		$field->readonly = '0';
		$field->displaytype = '5';
		$field->masseditable = '0';

		$blockInstance->addField($field);

		if($itemFieldsName[$j] == 'productid') {
			$field->setRelatedModules($relatedmodules);
		}
	}
}

// Register a new actor type for LineItem API
vtws_addActorTypeWebserviceEntityWithoutName('LineItem', 'include/Webservices/LineItem/VtigerLineItemOperation.php', 'VtigerLineItemOperation', array());

$webserviceObject = VtigerWebserviceObject::fromName($adb,'LineItem');
Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_tables(webservice_entity_id,table_name) VALUES (?,?)", array($webserviceObject->getEntityId(), 'vtiger_inventoryproductrel'));

$fieldTypeId = $adb->getUniqueID("vtiger_ws_entity_fieldtype");
Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_fieldtype(fieldtypeid,table_name, field_name,fieldtype) VALUES (?,?,?,?);", array($fieldTypeId, 'vtiger_inventoryproductrel', 'productid',"reference"));
Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_referencetype(fieldtypeid,type) VALUES (?,?)",array($fieldTypeId,'Products'));

$fieldTypeId = $adb->getUniqueID("vtiger_ws_entity_fieldtype");
Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_fieldtype(fieldtypeid,table_name, field_name,fieldtype) VALUES (?,?,?,?);", array($fieldTypeId, 'vtiger_inventoryproductrel', 'id',"reference"));
Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_referencetype(fieldtypeid,type) VALUES (?,?)",array($fieldTypeId,'Invoice'));
Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_referencetype(fieldtypeid,type) VALUES (?,?)",array($fieldTypeId,'SalesOrder'));
Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_referencetype(fieldtypeid,type) VALUES (?,?)",array($fieldTypeId,'PurchaseOrder'));
Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_referencetype(fieldtypeid,type) VALUES (?,?)",array($fieldTypeId,'Quotes'));

$fieldTypeId = $adb->getUniqueID("vtiger_ws_entity_fieldtype");
Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) VALUES (?,?,?,?);", array($fieldTypeId,'vtiger_inventoryproductrel', 'incrementondel',"autogenerated"));

$adb->getUniqueID("vtiger_inventoryproductrel");
Migration_Index_View::ExecuteQuery("UPDATE vtiger_inventoryproductrel_seq SET id=coalesce((select max(lineitem_id) from vtiger_inventoryproductrel),0);",array());
Migration_Index_View::ExecuteQuery("UPDATE vtiger_ws_entity SET handler_path='include/Webservices/LineItem/VtigerInventoryOperation.php',handler_class='VtigerInventoryOperation' where name in ('Invoice','Quotes','PurchaseOrder','SalesOrder');",array());

$purchaseOrderTabId = getTabid("PurchaseOrder");


vtws_addActorTypeWebserviceEntityWithName('Tax',
		'include/Webservices/LineItem/VtigerTaxOperation.php',
		'VtigerTaxOperation', array('fieldNames'=>'taxlabel', 'indexField'=>'taxid', 'tableName'=>'vtiger_inventorytaxinfo'), true);

$webserviceObject = VtigerWebserviceObject::fromName($adb,'Tax');
Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_tables(webservice_entity_id,table_name) VALUES (?,?)",array($webserviceObject->getEntityId(),'vtiger_inventorytaxinfo'));

vtws_addActorTypeWebserviceEntityWithoutName('ProductTaxes',
		'include/Webservices/LineItem/VtigerProductTaxesOperation.php',
		'VtigerProductTaxesOperation', array());

$webserviceObject = VtigerWebserviceObject::fromName($adb,'ProductTaxes');
Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_tables(webservice_entity_id,table_name) VALUES (?,?)",array($webserviceObject->getEntityId(),'vtiger_producttaxrel'));

$fieldTypeId = $adb->getUniqueID("vtiger_ws_entity_fieldtype");

Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) VALUES (?,?,?,?);", array($fieldTypeId,'vtiger_producttaxrel', 'productid',"reference"));
Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_referencetype(fieldtypeid,type) VALUES (?,?)",array($fieldTypeId,'Products'));

$fieldTypeId = $adb->getUniqueID("vtiger_ws_entity_fieldtype");
Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) VALUES (?,?,?,?);", array($fieldTypeId,'vtiger_producttaxrel', 'taxid',"reference"));
Migration_Index_View::ExecuteQuery("INSERT INTO vtiger_ws_entity_referencetype(fieldtypeid,type) VALUES (?,?)",array($fieldTypeId,'Tax'));

//Changed the Currency Symbol of Moroccan, Dirham to DH
Migration_Index_View::ExecuteQuery("UPDATE vtiger_currencies SET currency_symbol=? WHERE currency_name=? AND currency_code=?", array('DH', 'Moroccan, Dirham', 'MAD'));

//Changing picklist values for sales stage of opportunities
Migration_Index_View::ExecuteQuery("UPDATE vtiger_sales_stage SET sales_stage=? WHERE sales_stage=?", array('Proposal or Price Quote', 'Proposal/Price Quote'));
Migration_Index_View::ExecuteQuery("UPDATE vtiger_sales_stage SET sales_stage=? WHERE sales_stage=?", array('Negotiation or Review', 'Negotiation/Review'));

//Updating the new picklist values of sales stage in opportunities for migration instances
Migration_Index_View::ExecuteQuery("UPDATE vtiger_potential SET sales_stage=? WHERE sales_stage=?", array('Proposal or Price Quote', 'Proposal/Price Quote'));
Migration_Index_View::ExecuteQuery("UPDATE vtiger_potential SET sales_stage=? WHERE sales_stage=?", array('Negotiation or Review', 'Negotiation/Review'));

//Updating the sales stage picklist values of opportunities in picklist dependency setup for migration instances
Migration_Index_View::ExecuteQuery("UPDATE vtiger_picklist_dependency SET sourcevalue=? WHERE sourcevalue=?", array('Proposal or Price Quote', 'Proposal/Price Quote'));
Migration_Index_View::ExecuteQuery("UPDATE vtiger_picklist_dependency SET sourcevalue=? WHERE sourcevalue=?", array('Negotiation or Review', 'Negotiation/Review'));

//Internationalized the description for webforms
Migration_Index_View::ExecuteQuery("UPDATE vtiger_settings_field SET description=? WHERE description=?", array('LBL_WEBFORMS_DESCRIPTION', 'Allows you to manage Webforms'));

Migration_Index_View::ExecuteQuery('CREATE TABLE IF NOT EXISTS vtiger_crmsetup(userid INT(11) NOT NULL, setup_status INT(2))', array());
if (!defined('INSTALLATION_MODE')) {
	Migration_Index_View::ExecuteQuery('INSERT INTO vtiger_crmsetup(userid, setup_status) SELECT id, 1 FROM vtiger_users', array());
}

Migration_Index_View::ExecuteQuery('INSERT INTO vtiger_ws_referencetype VALUES (?,?)', array(31,'Campaigns'));

Migration_Index_View::ExecuteQuery("ALTER TABLE vtiger_productcurrencyrel MODIFY COLUMN actual_price decimal(28,8)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE vtiger_productcurrencyrel MODIFY COLUMN converted_price decimal(28,8)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE vtiger_pricebookproductrel MODIFY COLUMN listprice decimal(27,8)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE vtiger_inventoryproductrel MODIFY COLUMN listprice decimal(27,8)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE vtiger_inventoryproductrel MODIFY COLUMN discount_amount decimal(27,8)", array());

$currencyField = new CurrencyField(null);
$result = Migration_Index_View::ExecuteQuery("SELECT tablename,columnname FROM vtiger_field WHERE uitype IN (?,?)",array('71','72'));
$count = $adb->num_rows($result);
for($i=0;$i<$count;$i++) {
	$tableName = $adb->query_result($result,$i,'tablename');
	$columnName = $adb->query_result($result,$i,'columnname');
    Migration_Index_View::ExecuteQuery('ALTER TABLE ' . $tableName . ' CHANGE ' . $columnName . ' ' . $columnName . " decimal(25,8)", null);
}

Migration_Index_View::ExecuteQuery('DELETE FROM vtiger_no_of_currency_decimals WHERE no_of_currency_decimalsid=?', array(1));

//deleting default workflows
Migration_Index_View::ExecuteQuery("delete from com_vtiger_workflowtasks where task_id=?", array(11));
Migration_Index_View::ExecuteQuery("delete from com_vtiger_workflowtasks where task_id=?", array(12));

// Creating Default workflows
$workflowManager = new VTWorkflowManager($adb);
$taskManager = new VTTaskManager($adb);

global $current_user;
$adb = PearDatabase::getInstance();
$user = new Users();
$current_user = $user->retrieveCurrentUserInfoFromFile(Users::getActiveAdminId());

$allTabIdResult = Migration_Index_View::ExecuteQuery('SELECT tabid, name FROM vtiger_tab', array());
$noOfTabs = $adb->num_rows($allTabIdResult);
$allTabIds = array();
for($i=0; $i<$noOfTabs; ++$i) {
	$tabId = $adb->query_result($allTabIdResult, $i, 'tabid');
	$tabName = $adb->query_result($allTabIdResult, $i, 'name');
	$allTabIds[$tabName] = $tabId;
}

//Dashboard schema changes
Vtiger_Utils::CreateTable('vtiger_module_dashboard_widgets', '(id INT(19) NOT NULL AUTO_INCREMENT, linkid INT(19), userid INT(19), filterid INT(19),
				title VARCHAR(100), data VARCHAR(500) DEFAULT "[]", PRIMARY KEY(id))');
$potentials = Vtiger_Module::getInstance('Potentials');
$potentials->addLink('DASHBOARDWIDGET', 'History', 'index.php?module=Potentials&view=ShowWidget&name=History','', '1');
$potentials->addLink('DASHBOARDWIDGET', 'Funnel', 'index.php?module=Potentials&view=ShowWidget&name=GroupedBySalesStage','', '3');
$potentials->addLink('DASHBOARDWIDGET', 'Potentials by Stage', 'index.php?module=Potentials&view=ShowWidget&name=GroupedBySalesPerson','', '4');
$potentials->addLink('DASHBOARDWIDGET', 'Pipelined Amount', 'index.php?module=Potentials&view=ShowWidget&name=PipelinedAmountPerSalesPerson','', '5');
$potentials->addLink('DASHBOARDWIDGET', 'Total Revenue', 'index.php?module=Potentials&view=ShowWidget&name=TotalRevenuePerSalesPerson','', '6');
$potentials->addLink('DASHBOARDWIDGET', 'Top Potentials', 'index.php?module=Potentials&view=ShowWidget&name=TopPotentials','', '7');
//$potentials->addLink('DASHBOARDWIDGET', 'Forecast', 'index.php?module=Potentials&view=ShowWidget&name=Forecast','', '8');

$accounts = Vtiger_Module::getInstance('Accounts');
$accounts->addLink('DASHBOARDWIDGET', 'History', 'index.php?module=Accounts&view=ShowWidget&name=History','', '1');

$contacts = Vtiger_Module::getInstance('Contacts');
$contacts->addLink('DASHBOARDWIDGET', 'History', 'index.php?module=Contacts&view=ShowWidget&name=History','', '1');

$leads = Vtiger_Module::getInstance('Leads');
$leads->addLink('DASHBOARDWIDGET', 'History', 'index.php?module=Leads&view=ShowWidget&name=History','', '1');
//$leads->addLink('DASHBOARDWIDGET', 'Leads Created', 'index.php?module=Leads&view=ShowWidget&name=LeadsCreated','', '3');
$leads->addLink('DASHBOARDWIDGET', 'Leads by Status', 'index.php?module=Leads&view=ShowWidget&name=LeadsByStatus','', '4');
$leads->addLink('DASHBOARDWIDGET', 'Leads by Source', 'index.php?module=Leads&view=ShowWidget&name=LeadsBySource','', '5');
$leads->addLink('DASHBOARDWIDGET', 'Leads by Industry', 'index.php?module=Leads&view=ShowWidget&name=LeadsByIndustry','', '6');

$helpDesk = Vtiger_Module::getInstance('HelpDesk');
$helpDesk->addLink('DASHBOARDWIDGET', 'Tickets by Status', 'index.php?module=HelpDesk&view=ShowWidget&name=TicketsByStatus','', '1');
$helpDesk->addLink('DASHBOARDWIDGET', 'Open Tickets', 'index.php?module=HelpDesk&view=ShowWidget&name=OpenTickets','', '2');

$home = Vtiger_Module::getInstance('Home');
$home->addLink('DASHBOARDWIDGET', 'History', 'index.php?module=Home&view=ShowWidget&name=History','', '1');
$home->addLink('DASHBOARDWIDGET', 'Funnel', 'index.php?module=Potentials&view=ShowWidget&name=GroupedBySalesStage','', '3');
$home->addLink('DASHBOARDWIDGET', 'Potentials by Stage', 'index.php?module=Potentials&view=ShowWidget&name=GroupedBySalesPerson','', '4');
$home->addLink('DASHBOARDWIDGET', 'Pipelined Amount', 'index.php?module=Potentials&view=ShowWidget&name=PipelinedAmountPerSalesPerson','', '5');
$home->addLink('DASHBOARDWIDGET', 'Total Revenue', 'index.php?module=Potentials&view=ShowWidget&name=TotalRevenuePerSalesPerson','', '6');
$home->addLink('DASHBOARDWIDGET', 'Top Potentials', 'index.php?module=Potentials&view=ShowWidget&name=TopPotentials','', '7');
//$home->addLink('DASHBOARDWIDGET', 'Forecast', 'index.php?module=Potentials&view=ShowWidget&name=Forecast','', '8');

//$home->addLink('DASHBOARDWIDGET', 'Leads Created', 'index.php?module=Leads&view=ShowWidget&name=LeadsCreated','', '9');
$home->addLink('DASHBOARDWIDGET', 'Leads by Status', 'index.php?module=Leads&view=ShowWidget&name=LeadsByStatus','', '10');
$home->addLink('DASHBOARDWIDGET', 'Leads by Source', 'index.php?module=Leads&view=ShowWidget&name=LeadsBySource','', '11');
$home->addLink('DASHBOARDWIDGET', 'Leads by Industry', 'index.php?module=Leads&view=ShowWidget&name=LeadsByIndustry','', '12');

$home->addLink('DASHBOARDWIDGET', 'Tickets by Status', 'index.php?module=HelpDesk&view=ShowWidget&name=TicketsByStatus','', '13');
$home->addLink('DASHBOARDWIDGET', 'Open Tickets', 'index.php?module=HelpDesk&view=ShowWidget&name=OpenTickets','', '14');

$projectTabId = getTabid('Project');
$projectTaskTabId = getTabid('ProjectTask');
$projectMilestoneTabId = getTabid('ProjectMilestone');
$contactsTabId = getTabid('Contacts');
$accountsTabId = getTabid('Accounts');

Migration_Index_View::ExecuteQuery('UPDATE vtiger_relatedlists SET actions=? WHERE tabid in(?, ?) and related_tabid in (?)',
        array('add', $contactsTabId, $accountsTabId, $projectTabId));

Migration_Index_View::ExecuteQuery('UPDATE vtiger_users SET truncate_trailing_zeros = ?', array(1));

//deleted the id column from the All filter (exclude custom modules)
Migration_Index_View::ExecuteQuery("DELETE FROM vtiger_cvcolumnlist WHERE cvid IN
			(SELECT cvid FROM vtiger_customview INNER JOIN vtiger_tab ON vtiger_tab.name=vtiger_customview.entitytype
				WHERE vtiger_tab.customized=0 AND viewname='All' AND entitytype NOT IN
				('ModComments','ProjectMilestone','Project','SMSNotifier','PBXManager'))
			AND columnindex = 0", array());

Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_links MODIFY column linktype VARCHAR(50)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_links MODIFY column linklabel VARCHAR(50)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_links MODIFY column handler_class VARCHAR(50)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_links MODIFY column handler VARCHAR(50)', array());

require_once 'modules/ModComments/ModComments.php';
ModComments::addWidgetTo(array("HelpDesk", "Faq"));
global $current_user, $VTIGER_BULK_SAVE_MODE;
$VTIGER_BULK_SAVE_MODE = true;

$customerPortalSettings = new Settings_CustomerPortal_Module_Model();
$portal_user_id = $customerPortalSettings->getCurrentPortalUser();

$stopLoop = false;
$pageCount = 0;
do {
	$ticketComments = Migration_Index_View::ExecuteQuery(sprintf('SELECT * FROM vtiger_ticketcomments ORDER BY commentid ASC LIMIT %s,%s', $pageCount*1000, 1000),  array());
	$rows = $adb->num_rows($ticketComments);
	if (empty($rows)) {
		$stopLoop = true;
		break;
	}
	for($i=0; $i<$rows; $i++) {
		$modComments = CRMEntity::getInstance('ModComments');
		$modComments->column_fields['commentcontent'] = decode_html($adb->query_result($ticketComments, $i, 'comments'));
		$modComments->column_fields['createdtime'] = $adb->query_result($ticketComments, $i, 'createdtime');
		$modComments->column_fields['modifiedtime'] = $adb->query_result($ticketComments, $i, 'createdtime');
		$modComments->column_fields['related_to'] = $adb->query_result($ticketComments, $i, 'ticketid');
		
		// Contact linked comments should be carried over (http://code.vtiger.com/vtiger/vtigercrm/issues/130)
		$ownerId = $adb->query_result($ticketComments, $i, 'ownerid');
		$ownerType = $adb->query_result($ticketComments, $i, 'ownertype');
		if ($ownerType == 'customer') {
			$modComments->column_fields['customer'] = $ownerId;
			$current_user->id = $ownerId = $portal_user_id; // Owner of record marked to PortalUser, reference marked to Contact.
		} else {
			$current_user->id = $ownerId;
		}
		$modComments->column_fields['assigned_user_id'] = $modComments->column_fields['creator'] = $ownerId;
		
		$modComments->save('ModComments');
		Migration_Index_View::ExecuteQuery('UPDATE vtiger_crmentity SET modifiedtime = ?, smcreatorid = ?, modifiedby = ? WHERE crmid = ?',
			array($modComments->column_fields['createdtime'], $ownerId, $ownerId, $modComments->id));
	}
	++$pageCount;
} while (!$stopLoop);

// Restore the UserId
$current_user->id = Users::getActiveAdminId();

$stopLoop = false;
$pageCount = 0;
do {
	$faqComments = Migration_Index_View::ExecuteQuery(sprintf('SELECT * FROM vtiger_faqcomments ORDER BY commentid ASC LIMIT %s, %s', $pageCount*1000, 1000), array());
	$rows = $adb->num_rows($faqComments);
	if (empty($rows)) {
		$stopLoop = true;
		break;
	}
	for($i=0; $i<$rows; $i++) {
		$modComments = CRMEntity::getInstance('ModComments');
		$modComments->column_fields['commentcontent'] = decode_html($adb->query_result($faqComments, $i, 'comments'));
		$modComments->column_fields['assigned_user_id'] = $modComments->column_fields['creator'] = Users::getActiveAdminId();
		$modComments->column_fields['createdtime'] = $adb->query_result($faqComments, $i, 'createdtime');
		$modComments->column_fields['modifiedtime'] = $adb->query_result($faqComments, $i, 'createdtime');
		$modComments->column_fields['related_to'] = $adb->query_result($faqComments, $i, 'faqid');
		$modComments->save('ModComments');
		Migration_Index_View::ExecuteQuery('UPDATE vtiger_crmentity SET modifiedtime = ?, smcreatorid = ?, modifiedby = ? WHERE crmid = ?',
			array($modComments->column_fields['createdtime'], $current_user->id, $current_user->id, $modComments->id));
	}
	++$pageCount;
} while (!$stopLoop);

$VTIGER_BULK_SAVE_MODE = false;

// Added label column in vtiger_crmentity table for easier lookup - Also added Event handler to update the label on save of a record
Migration_Index_View::ExecuteQuery("ALTER TABLE vtiger_crmentity ADD COLUMN label varchar(255)", array());

// To avoid infinite-loop if we not able fix label for non-entity/special modules.
$lastMaxCRMId = 0;
do {
	$rs = $adb->pquery("SELECT crmid,setype FROM vtiger_crmentity INNER JOIN vtiger_tab ON vtiger_tab.name=vtiger_crmentity.setype WHERE label IS NULL AND crmid > ? LIMIT 500", array($lastMaxCRMId));
	if (!$adb->num_rows($rs)) {
		break;
	}
	while ($row = $adb->fetch_array($rs)) {
		/**
		 * TODO: Optimize underlying API to cache re-usable data, for speedy data.
		 */
		$labelInfo = getEntityName($row['setype'], array(intval($row['crmid'])), true);

		if ($labelInfo) {
			$label = decode_html($labelInfo[$row['crmid']]);
			Migration_Index_View::ExecuteQuery('UPDATE vtiger_crmentity SET label=? WHERE crmid=? AND setype=?',
						array($label, $row['crmid'], $row['setype']));
		}

		if (intval($row['crmid']) > $lastMaxCRMId) {
			$lastMaxCRMId = intval($row['crmid']);
		}
	}
	$rs = null;
	unset($rs);
} while(true);

Migration_Index_View::ExecuteQuery('CREATE INDEX vtiger_crmentity_labelidx ON vtiger_crmentity(label)', array());

$homeModule = Vtiger_Module::getInstance('Home');
Vtiger_Event::register($homeModule, 'vtiger.entity.aftersave', 'Vtiger_RecordLabelUpdater_Handler', 'modules/Vtiger/RecordLabelUpdater.php');



$moduleInstance = Vtiger_Module::getInstance('Potentials');
$filter = Vtiger_Filter::getInstance('All', $moduleInstance);
$fieldInstance = Vtiger_Field::getInstance('amount', $moduleInstance);
$filter->addField($fieldInstance,6);


if(file_exists('modules/ModTracker/ModTrackerUtils.php')) {
	require_once 'modules/ModTracker/ModTrackerUtils.php';
	$modules = $adb->pquery('SELECT * FROM vtiger_tab WHERE isentitytype = 1', array());
	$rows = $adb->num_rows($modules);
	for($i=0; $i<$rows; $i++) {
		$tabid=$adb->query_result($modules, $i, 'tabid');
		ModTrackerUtils::modTrac_changeModuleVisibility($tabid, 'module_enable');
	}
}

$operationId = vtws_addWebserviceOperation('retrieve_inventory', 'include/Webservices/LineItem/RetrieveInventory.php', 'vtws_retrieve_inventory', 'GET');
vtws_addWebserviceOperationParam($operationId, 'id', 'String', 1);

// Update/Increment the sequence for the succeeding blocks of Users module, with starting sequence 2
$moduleInstance = Vtiger_Module::getInstance('Users');
$tabId = getTabid('Users');
Migration_Index_View::ExecuteQuery('UPDATE vtiger_blocks SET sequence = sequence+1 WHERE tabid=? AND sequence >= 2', array($tabId));

//update hour_format value in existing customers
Migration_Index_View::ExecuteQuery('UPDATE vtiger_users SET hour_format = ? WHERE hour_format = ? OR hour_format = ?', array(12, 'am/pm', ''));
//add user default values
Migration_Index_View::ExecuteQuery('UPDATE vtiger_users SET dayoftheweek = ?, callduration = ?, othereventduration = ?, start_hour = ? ', array('Monday', 5, 5, '00:00'));

Migration_Index_View::ExecuteQuery('UPDATE vtiger_users SET default_record_view = ?', array('Summary'));

// END 2012.12.02

// //////////////////////////////////////////////
$inventoryModules = array(
    'Invoice' => array('LBL_INVOICE_INFORMATION', 'vtiger_invoice', 'invoiceid'),
    'SalesOrder' => array('LBL_SO_INFORMATION', 'vtiger_salesorder', 'salesorderid'),
    'PurchaseOrder' => array('LBL_PO_INFORMATION', 'vtiger_purchaseorder', 'purchaseorderid'),
    'Quotes' => array('LBL_QUOTE_INFORMATION', 'vtiger_quotes', 'quoteid')
);

foreach ($inventoryModules as $module => $details) {
    $tableName = $details[1];
    $moduleInstance = Vtiger_Module::getInstance($module);
    $tableId = $details[2];

    $result = $adb->pquery("SELECT $tableId, subtotal, s_h_amount, discount_percent, discount_amount FROM $tableName", array());
    $numOfRows = $adb->num_rows($result);

    for ($i = 0; $i < $numOfRows; $i++) {
        $id = $adb->query_result($result, $i, $tableId);
        $subTotal = (float) $adb->query_result($result, $i, "subtotal");
        $shAmount = (float) $adb->query_result($result, $i, "s_h_amount");
        $discountAmount = (float) $adb->query_result($result, $i, "discount_amount");
        $discountPercent = (float) $adb->query_result($result, $i, "discount_percent");

        if ($discountPercent != '0') {
            $discountAmount = ($subTotal * $discountPercent) / 100;
        }

        $preTaxTotalValue = $subTotal + $shAmount - $discountAmount;

        Migration_Index_View::ExecuteQuery("UPDATE $tableName set pre_tax_total = ? WHERE $tableId = ?", array($preTaxTotalValue, $id));
    }
}

// Add Key Metrics widget.
$homeModule = Vtiger_Module::getInstance('Home');
$homeModule->addLink('DASHBOARDWIDGET', 'Key Metrics', 'index.php?module=Home&view=ShowWidget&name=KeyMetrics');
$homeModule = Vtiger_Module::getInstance('Home');
$homeModule->addLink('DASHBOARDWIDGET', 'Mini List', 'index.php?module=Home&view=ShowWidget&name=MiniList');

$InvoiceInstance = Vtiger_Module::getInstance('Invoice');
Vtiger_Event::register($InvoiceInstance, 'vtiger.entity.aftersave', 'InvoiceHandler', 'modules/Invoice/InvoiceHandler.php');

$POInstance = Vtiger_Module::getInstance('PurchaseOrder');
Vtiger_Event::register($POInstance, 'vtiger.entity.aftersave', 'PurchaseOrderHandler', 'modules/PurchaseOrder/PurchaseOrderHandler.php');

$sqltimelogTable = "CREATE TABLE vtiger_sqltimelog ( id integer, type VARCHAR(10),
					data text, started decimal(18,2), ended decimal(18,2), loggedon datetime)";

Migration_Index_View::ExecuteQuery($sqltimelogTable, array());


$moduleName = 'PurchaseOrder';
$emm = new VTEntityMethodManager($adb);
$emm->addEntityMethod($moduleName,"UpdateInventory","include/InventoryHandler.php","handleInventoryProductRel");

$vtWorkFlow = new VTWorkflowManager($adb);
$poWorkFlow = $vtWorkFlow->newWorkFlow($moduleName);
$poWorkFlow->description = "Update Inventory Products On Every Save";
$poWorkFlow->defaultworkflow = 1;
$poWorkFlow->executionCondition = 3;
$vtWorkFlow->save($poWorkFlow);

$tm = new VTTaskManager($adb);
$task = $tm->createTask('VTEntityMethodTask', $poWorkFlow->id);
$task->active = true;
$task->summary = "Update Inventory Products";
$task->methodName = "UpdateInventory";
$tm->saveTask($task);

// Add Tag Cloud widget.
$homeModule = Vtiger_Module::getInstance('Home');
$homeModule->addLink('DASHBOARDWIDGET', 'Tag Cloud', 'index.php?module=Home&view=ShowWidget&name=TagCloud');

// Schema changed for capturing Dashboard widget positions
Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_module_dashboard_widgets ADD COLUMN position VARCHAR(50)',array());

$moduleInstance = Vtiger_Module::getInstance('Contacts');
if($moduleInstance) {
	$moduleInstance->addLink('LISTVIEWSIDEBARWIDGET','Google Contacts',
		'module=Google&view=List&sourcemodule=Contacts', '','', '');
}

Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_cvadvfilter MODIFY comparator VARCHAR(20)', array());
Migration_Index_View::ExecuteQuery('UPDATE vtiger_cvadvfilter SET comparator = ? WHERE comparator = ?', array('next120days', 'next120day'));
Migration_Index_View::ExecuteQuery('UPDATE vtiger_cvadvfilter SET comparator = ? WHERE comparator = ?', array('last120days', 'last120day'));

Migration_Index_View::ExecuteQuery("UPDATE vtiger_relatedlists SET actions = ? WHERE tabid = ? AND related_tabid IN (?, ?)",
	array('ADD', getTabid('Project'), getTabid('ProjectTask'), getTabid('ProjectMilestone')));

if(Vtiger_Utils::CheckTable('vtiger_cron_task')) {
	Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_cron_task MODIFY COLUMN laststart INT(11) UNSIGNED',Array());
	Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_cron_task MODIFY COLUMN lastend INT(11) UNSIGNED',Array());
}

if(Vtiger_Utils::CheckTable('vtiger_cron_log')) {
	Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_cron_log MODIFY COLUMN start INT(11) UNSIGNED',Array());
   	Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_cron_log MODIFY COLUMN end INT(11) UNSIGNED',Array());
}
// END 2013.02.18

// Start 2013.03.19
// Mail Converter schema changes
Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_mailscanner ADD COLUMN timezone VARCHAR(10) default NULL', array());
Migration_Index_View::ExecuteQuery('UPDATE vtiger_mailscanner SET time_zone=? WHERE server LIKE ? AND time_zone IS NULL', ['-8:00', '%.gmail.com']);

Migration_Index_View::ExecuteQuery("ALTER TABLE vtiger_cvadvfilter MODIFY value VARCHAR(512)", array());
// End 2013.03.19

// Start 2013.04.23
Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_sqltimelog MODIFY started DECIMAL(20,6)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_sqltimelog MODIFY ended DECIMAL(20,6)', array());

//added Assests tab in contact
$assetsModuleInstance = Vtiger_Module::getInstance('Assets');
$contactModule = Vtiger_Module::getInstance('Contacts');
$contactModule->setRelatedList($assetsModuleInstance, '', false, 'get_dependents_list');
// End 2013.04.23

//Adding column to store the state of short cut settings fields
Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_settings_field ADD COLUMN pinned int(1) DEFAULT 0',array());

$defaultPinnedFields = array('LBL_USERS','LBL_LIST_WORKFLOWS','VTLIB_LBL_MODULE_MANAGER','LBL_PICKLIST_EDITOR');
$defaultPinnedSettingFieldQuery = 'UPDATE vtiger_settings_field SET pinned=1 WHERE name IN ('.generateQuestionMarks($defaultPinnedFields).')';
Migration_Index_View::ExecuteQuery($defaultPinnedSettingFieldQuery,$defaultPinnedFields);

Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_profile ADD COLUMN directly_related_to_role int(1) DEFAULT 0',array());

$blockId = getSettingsBlockId('LBL_STUDIO');
$result = $adb->pquery('SELECT max(sequence) as maxSequence FROM vtiger_settings_field WHERE blockid=?', array($blockId));
$sequence = 0;
if($adb->num_rows($result) > 0 ) {
	$sequence = $adb->query_result($result,0,'maxSequence');
}

$fieldId = $adb->getUniqueID('vtiger_settings_field');
$query = "INSERT INTO vtiger_settings_field (fieldid, blockid, name, iconpath, description, " .
		"linkto, sequence) VALUES (?,?,?,?,?,?,?)";
$layoutEditoLink = 'index.php?module=LayoutEditor&parent=Settings&view=Index';
$params = array($fieldId, $blockId, 'LBL_EDIT_FIELDS', '', 'LBL_LAYOUT_EDITOR_DESCRIPTION', $layoutEditoLink, $sequence);
Migration_Index_View::ExecuteQuery($query, $params);

Migration_Index_View::ExecuteQuery('UPDATE vtiger_role SET rolename = ? WHERE rolename = ? AND depth = ?', array('Organization', 'Organisation', 0));


//Create a new table to support custom fields in Documents module
$adb->query("CREATE TABLE IF NOT EXISTS vtiger_notescf (notesid INT(19), FOREIGN KEY fk_1_vtiger_notescf(notesid) REFERENCES vtiger_notes(notesid) ON DELETE CASCADE);");

if(!defined('INSTALLATION_MODE')) {
	Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_salutationtype ADD COLUMN sortorderid INT(1)', array());
}

// Adding users field into all the available profiles, this is used in email templates
// when non-admin sends an email with users field in the template
$module = 'Users';
$user = new $module();
$activeAdmin = Users::getActiveAdminId();
$user->retrieve_entity_info($activeAdmin, $module);
$handler = vtws_getModuleHandlerFromName($module, $user);
$meta = $handler->getMeta();
$moduleFields = $meta->getModuleFields();

$userAccessbleFields = array();
$skipFields = array(98,115,116,31,32);
foreach ($moduleFields as $fieldName => $webserviceField) {
	if($webserviceField->getFieldDataType() == 'string' || $webserviceField->getFieldDataType() == 'email' || $webserviceField->getFieldDataType() == 'phone') {
		if(!in_array($webserviceField->getUitype(), $skipFields) && $fieldName != 'asterisk_extension'){
			if (isset($userAccessbleFields[$webserviceField->getFieldId()])) {
				$userAccessbleFields[$webserviceField->getFieldId()] .= $fieldName;
			}
		}
	}
}

$tabId = getTabid($module);
$query = 'SELECT profileid FROM vtiger_profile';
$result = $adb->pquery($query, array());

for($i=0; $i<$adb->num_rows($result); $i++) {
	$profileId = $adb->query_result($result, $i, 'profileid');
	$sql = 'SELECT fieldid FROM vtiger_profile2field WHERE profileid = ? AND tabid = ?';
	$fieldsResult = $adb->pquery($sql, array($profileId, $tabId));
	$profile2Fields = array();
	$rows = $adb->num_rows($fieldsResult);
	for($j=0; $j<$rows; $j++) {
		array_push($profile2Fields, $adb->query_result($fieldsResult, $j, 'fieldid'));
	}
	foreach ($userAccessbleFields as $fieldId => $fieldName) {
		if(!in_array($fieldId, $profile2Fields)){
			$insertQuery = 'INSERT INTO vtiger_profile2field(profileid,tabid,fieldid,visible,readonly) VALUES(?,?,?,?,?)';
			Migration_Index_View::ExecuteQuery($insertQuery, array($profileId,$tabId,$fieldId,0,0));
		}
	}
}

//need to recreate user_privileges files as lot of user fields are added in this script and user_priviliges files are not updated
require_once('modules/Users/CreateUserPrivilegeFile.php');
createUserPrivilegesfile('1');

//Remove '--None--'/'None' from all the picklist values.
$sql = 'SELECT fieldname FROM vtiger_field WHERE uitype IN(?,?,?,?)';
$result = $adb->pquery($sql, array(15,16,33,55));
$num_rows = $adb->num_rows($result);
for($i=0; $i<$num_rows; $i++){
	$fieldName = $adb->query_result($result, $i, 'fieldname');
	$checkTable = $adb->pquery('SHOW TABLES LIKE "vtiger_'.$fieldName.'"', array());
	if($adb->num_rows($checkTable) > 0) {
		$query = "DELETE FROM vtiger_$fieldName WHERE $fieldName = ? OR $fieldName = ?";
		Migration_Index_View::ExecuteQuery($query, array('--None--', 'None'));
	}
}

$potentials = Vtiger_Module::getInstance('Potentials');
$potentials->addLink('DASHBOARDWIDGET', 'Funnel Amount', 'index.php?module=Potentials&view=ShowWidget&name=FunnelAmount','', '10');
$home = Vtiger_Module::getInstance('Home');
$home->addLink('DASHBOARDWIDGET', 'Funnel Amount', 'index.php?module=Potentials&view=ShowWidget&name=FunnelAmount','', '10');

// Enable Sharing-Access for Vendors
$vendorInstance = Vtiger_Module::getInstance('Vendors');
// Allow Sharing access and role-based security for Vendors
Vtiger_Access::deleteSharing($vendorInstance);
Vtiger_Access::initSharing($vendorInstance);
Vtiger_Access::allowSharing($vendorInstance);
Vtiger_Access::setDefaultSharing($vendorInstance);

Vtiger_Module::syncfile();

// Add Email Opt-out for Leads
Migration_Index_View::ExecuteQuery('UPDATE vtiger_leaddetails SET emailoptout=0 WHERE emailoptout IS NULL', array());

$module = Vtiger_Module::getInstance('Home');
$module->addLink('DASHBOARDWIDGET', 'Notebook', 'index.php?module=Home&view=ShowWidget&name=Notebook');

Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_module_dashboard_widgets MODIFY data TEXT',array());

$linkIdResult = $adb->pquery('SELECT linkid FROM vtiger_links WHERE vtiger_links.linklabel="Notebook"', array());
$noteBookLinkId = $adb->query_result($linkIdResult, 0, 'linkid');

$result = $adb->pquery('SELECT vtiger_homestuff.stufftitle, vtiger_homestuff.userid, vtiger_notebook_contents.contents FROM
						vtiger_homestuff INNER JOIN vtiger_notebook_contents on vtiger_notebook_contents.notebookid = vtiger_homestuff.stuffid
						WHERE vtiger_homestuff.stufftype = ?', array('Notebook'));

for($i=0; $i<$adb->num_rows($result); $i++) {
	$noteBookTitle = $adb->query_result($result, $i, 'stufftitle');
	$userId = $adb->query_result($result, $i, 'userid');
	$noteBookContent = $adb->query_result($result, $i, 'contents');
	$query = 'INSERT INTO vtiger_module_dashboard_widgets(linkid, userid, filterid, title, data) VALUES(?,?,?,?,?)';
	$params= array($noteBookLinkId,$userId,0,$noteBookTitle,$noteBookContent);
	Migration_Index_View::ExecuteQuery($query, $params);
}

$labels = array('LBL_ADD_NOTE', 'Add Note');
$sql = 'UPDATE vtiger_links SET handler = ?, handler_class = ?, handler_path = ? WHERE linklabel IN (?, ?)';
Migration_Index_View::ExecuteQuery($sql, array('isLinkPermitted', 'Documents', 'modules/Documents/Documents.php', $labels));

$sql = 'UPDATE vtiger_links SET handler = ?, handler_class = ?, handler_path = ? WHERE linklabel = ?';
Migration_Index_View::ExecuteQuery($sql, array('isLinkPermitted', 'ProjectTask', 'modules/ProjectTask/ProjectTask.php', 'Add Project Task'));

$tabIdList = array();
$tabIdList[] = getTabid('Invoice');
$tabIdList[] = getTabid('PurchaseOrder');

$query = 'SELECT fieldid FROM vtiger_field WHERE (fieldname=? or fieldname=? or fieldname=? ) AND tabid IN ('.generateQuestionMarks($tabIdList).')';
$result = $adb->pquery($query, array('received', 'paid', 'balance',$tabIdList));
$numrows = $adb->num_rows($result);

for ($i = 0; $i < $numrows; $i++) {
	$fieldid = $adb->query_result($result, $i, 'fieldid');
	$query = 'Update vtiger_profile2field set readonly = 0 where fieldid=?';
	Migration_Index_View::ExecuteQuery($query, array($fieldid));
}

//Update leads salutation value of none to empty value
Migration_Index_View::ExecuteQuery("UPDATE vtiger_leaddetails SET salutation='' WHERE salutation = ?", array('--None--'));

//Update contacts salutation value of none to empty value
Migration_Index_View::ExecuteQuery("UPDATE vtiger_contactdetails SET salutation='' WHERE salutation = ?", array('--None--'));
// END 2013-06-25

// Start 2013-09-24
Migration_Index_View::ExecuteQuery('UPDATE vtiger_eventhandlers SET handler_path = ? WHERE handler_class = ?',
				array('modules/Vtiger/handlers/RecordLabelUpdater.php', 'Vtiger_RecordLabelUpdater_Handler'));

$result = $adb->pquery('SELECT taxname FROM vtiger_shippingtaxinfo', array());
$numOfRows = $adb->num_rows($result);
$shippingTaxes = array();
$tabIds = array();
for ($i = 0; $i < $numOfRows; $i++) {
	$shippingTaxName = $adb->query_result($result, $i, 'taxname');
	array_push($shippingTaxes, $shippingTaxName);
}

$modules = array('Invoice','Quotes','PurchaseOrder','SalesOrder');
$tabIdQuery = 'SELECT tabid FROM vtiger_tab where name IN ('.generateQuestionMarks($modules).')';
$tabIdRes = $adb->pquery($tabIdQuery,$modules);
$num_rows = $adb->num_rows($tabIdRes);
for ($i = 0; $i < $num_rows; $i++) {
	$tabIds[] = $adb->query_result($tabIdRes,0,'tabid');
}

$query = 'DELETE FROM vtiger_field WHERE tabid IN (' . generateQuestionMarks($tabIds) . ') AND fieldname IN (' . generateQuestionMarks($shippingTaxes) . ')';
Migration_Index_View::ExecuteQuery($query, array_merge($tabIds, $shippingTaxes));

Migration_Index_View::ExecuteQuery('UPDATE vtiger_currencies SET currency_name = ? where currency_name = ? and currency_code = ?',
		array('Hong Kong, Dollars', 'LvHong Kong, Dollars', 'HKD'));
Migration_Index_View::ExecuteQuery('UPDATE vtiger_currency_info SET currency_name = ? where currency_name = ?',
		array('Hong Kong, Dollars', 'LvHong Kong, Dollars'));
Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_role ADD allowassignedrecordsto INT(2) NOT NULL DEFAULT 1', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE com_vtiger_workflowtask_queue ADD COLUMN task_contents text', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE com_vtiger_workflowtask_queue DROP INDEX com_vtiger_workflowtask_queue_idx',array());
$potentialModule = Vtiger_Module::getInstance('Potentials');
$block = Vtiger_Block::getInstance('LBL_OPPORTUNITY_INFORMATION', $potentialModule);

$relatedToField = Vtiger_Field::getInstance('related_to', $potentialModule);
$relatedToField->unsetRelatedModules(array('Contacts'));

$lastPotentialId = 0;
do {
	$result = $adb->pquery("SELECT potentialid ,related_to FROM vtiger_potential WHERE potentialid > ? LIMIT 500",
			array($lastPotentialId));
	if (!$adb->num_rows($result)) break;

	while ($row = $adb->fetch_array($result)) {
		$relatedTo = $row['related_to'];
		$potentialId = $row['potentialid'];

		$relatedToType = getSalesEntityType($relatedTo);
		if($relatedToType != 'Accounts') {
			Migration_Index_View::ExecuteQuery('UPDATE vtiger_potential SET contact_id = ?, related_to = null WHERE potentialid = ?',
					array($relatedTo, $potentialId));
		}
		if (intval($potentialId) > $lastPotentialId) {
			$lastPotentialId = intval($row['potentialid']);
		}
		unset($relatedTo);
	}
	unset($result);
} while(true);

$filterResult = $adb->pquery('SELECT * FROM vtiger_cvadvfilter WHERE columnname like ?',
		array('vtiger_potential:related_to:related_to:Potentials_Related_%'));
$rows = $adb->num_rows($filterResult);
for($i=0; $i<$rows; $i++) {
	$cvid = $adb->query_result($filterResult, $i, 'cvid');
	$columnIndex = $adb->query_result($filterResult, $i, 'columnindex');
	$comparator = $adb->query_result($filterResult, $i, 'comparator');
	$value = $adb->query_result($filterResult, $i, 'value');

	Migration_Index_View::ExecuteQuery('UPDATE vtiger_cvadvfilter SET groupid = 2, column_condition = ? WHERE cvid = ?', array('or', $cvid));
	Migration_Index_View::ExecuteQuery('UPDATE vtiger_cvadvfilter_grouping SET groupid = 2 WHERE cvid = ?', array($cvid));

	Migration_Index_View::ExecuteQuery('INSERT INTO vtiger_cvadvfilter(cvid,columnindex,columnname,comparator,value,groupid,column_condition)
		VALUES(?,?,?,?,?,?,?)', array($cvid, ++$columnIndex,'vtiger_potential:contact_id:contact_id:Potentials_Contact_Name:V',
			$comparator, $value, 2, ''));
}
unset($filterResult);

$filterColumnList = $adb->pquery('SELECT * FROM vtiger_cvcolumnlist WHERE columnname like ?',
		array('vtiger_potential:related_to:related_to:Potentials_Related_%'));
$filterColumnRows = $adb->num_rows($filterColumnList);
for($j=0; $j<$filterColumnRows; $j++) {
	$cvid = $adb->query_result($filterColumnList, $j, 'cvid');
	$filterResult = $adb->pquery('SELECT MAX(columnindex) AS maxcolumn FROM vtiger_cvcolumnlist WHERE cvid = ?', array($cvid));
	$maxColumnIndex = $adb->query_result($filterResult, 0, 'maxcolumn');
	Migration_Index_View::ExecuteQuery('INSERT INTO vtiger_cvcolumnlist(cvid,columnindex,columnname) VALUES (?,?,?)', array($cvid, ++$maxColumnIndex,
		'vtiger_potential:contact_id:contact_id:Potentials_Contact_Name:V'));
	unset($filterResult);
}
unset($filterColumnList);


$ticketsModule = Vtiger_Module::getInstance('HelpDesk');
$ticketsBlock = Vtiger_Block::getInstance('LBL_TICKET_INFORMATION', $ticketsModule);

$relatedToField = Vtiger_Field::getInstance('parent_id', $ticketsModule);
$relatedToField->setRelatedModules(array('Accounts'));

$lastTicketId = 0;
do {
	$ticketsResult = $adb->pquery("SELECT ticketid ,parent_id FROM vtiger_troubletickets WHERE ticketid > ?
						LIMIT 500", array($lastTicketId));
	if (!$adb->num_rows($ticketsResult)) break;

	while ($row = $adb->fetch_array($ticketsResult)) {
		$parent = $row['parent_id'];
		$ticketId = $row['ticketid'];

		$parentType = getSalesEntityType($parent);
		if($parentType != 'Accounts') {
			Migration_Index_View::ExecuteQuery('UPDATE vtiger_troubletickets SET contact_id = ?, parent_id = null WHERE ticketid = ?',
					array($parent, $ticketId));
		}
		if (intval($ticketId) > $lastTicketId) {
			$lastTicketId = intval($row['ticketid']);
		}
		unset($parent);
	}
	unset($ticketsResult);
} while(true);

$ticketFilterResult = $adb->pquery('SELECT * FROM vtiger_cvadvfilter WHERE columnname like ?',
						array('vtiger_troubletickets:parent_id:parent_id:HelpDesk_Related%'));
$rows = $adb->num_rows($ticketFilterResult);
for($i=0; $i<$rows; $i++) {
	$cvid = $adb->query_result($ticketFilterResult, $i, 'cvid');
	$columnIndex = $adb->query_result($ticketFilterResult, $i, 'columnindex');
	$comparator = $adb->query_result($ticketFilterResult, $i, 'comparator');
	$value = $adb->query_result($ticketFilterResult, $i, 'value');

	Migration_Index_View::ExecuteQuery('UPDATE vtiger_cvadvfilter SET groupid = 2, column_condition = ? WHERE cvid = ?', array('or', $cvid));
	Migration_Index_View::ExecuteQuery('UPDATE vtiger_cvadvfilter_grouping SET groupid = 2 WHERE cvid = ?', array($cvid));

	Migration_Index_View::ExecuteQuery('INSERT INTO vtiger_cvadvfilter(cvid,columnindex,columnname,comparator,value,groupid,column_condition)
		VALUES(?,?,?,?,?,?,?)', array($cvid, ++$columnIndex,'vtiger_troubletickets:contact_id:contact_id:HelpDesk_Contact_Name:V',
			$comparator, $value, 2, ''));
}
unset($ticketFilterResult);

$filterColumnList = $adb->pquery('SELECT * FROM vtiger_cvcolumnlist WHERE columnname like ?',
		array('vtiger_troubletickets:parent_id:parent_id:HelpDesk_Related_%'));
$filterColumnRows = $adb->num_rows($filterColumnList);
for($j=0; $j<$filterColumnRows; $j++) {
	$cvid = $adb->query_result($filterColumnList, $j, 'cvid');
	$filterResult = $adb->pquery('SELECT MAX(columnindex) AS maxcolumn FROM vtiger_cvcolumnlist WHERE cvid = ?', array($cvid));
	$maxColumnIndex = $adb->query_result($filterResult, 0, 'maxcolumn');
	Migration_Index_View::ExecuteQuery('INSERT INTO vtiger_cvcolumnlist(cvid,columnindex,columnname) VALUES (?,?,?)', array($cvid, ++$maxColumnIndex,
		'vtiger_troubletickets:contact_id:contact_id:HelpDesk_Contact_Name:V'));
	unset($filterResult);
}
unset($filterColumnList);

Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_shorturls ADD COLUMN onetime int(5)', array());

$checkQuery = 'SELECT 1 FROM vtiger_currencies  WHERE currency_name=?';
$checkResult = $adb->pquery($checkQuery,array('Iraqi Dinar'));
if($adb->num_rows($checkResult) <= 0) {
	Migration_Index_View::ExecuteQuery('INSERT INTO vtiger_currencies VALUES ('.$adb->getUniqueID("vtiger_currencies").',"Iraqi Dinar","IQD","ID")',array());
}

$checkQuery = 'SELECT 1 FROM vtiger_currencies  WHERE currency_name=?';
$checkResult = $adb->pquery($checkQuery,array('Maldivian Ruffiya'));
if($adb->num_rows($checkResult) <= 0) {
	Migration_Index_View::ExecuteQuery('INSERT INTO vtiger_currencies VALUES ('.$adb->getUniqueID("vtiger_currencies").',"Maldivian Ruffiya","MVR","MVR")',array());
}

//Start: Customer - Feature #10254 Configuring all Email notifications including Ticket notifications
$moduleName = 'HelpDesk';
$workflowManager = new VTWorkflowManager($adb);
$taskManager = new VTTaskManager($adb);

// Comment Added From Portal
$workflowConditions = [
    [
        'fieldname'     => '_VT_add_comment',
        'operation'     => 'is added',
        'value'         => '',
        'valuetype'     => 'rawtext',
        'joincondition' => '',
        'groupjoin'     => 'and',
        'groupid'       => '0'
    ],[
        'fieldname'     => 'from_portal',
        'operation'     => 'is',
        'value'         => '1',
        'valuetype'     => 'rawtext',
        'joincondition' => '',
        'groupjoin'     => 'and',
        'groupid'       => '0'
    ]
];

$commentsWorkflow = $workflowManager->newWorkFlow($moduleName);
$commentsWorkflow->test = Zend_Json::encode($workflowConditions);
$commentsWorkflow->description = 'Comment Added From Portal : Send Email to Record Owner';
$commentsWorkflow->executionCondition = VTWorkflowManager::$ON_FIRST_SAVE;
$commentsWorkflow->defaultworkflow = 1;
$workflowManager->save($commentsWorkflow);

include_once 'modules/com_vtiger_workflow/tasks/VTEmailTask.inc';
$emailTask = new VTEmailTask();
$emailTask->id = '';
$emailTask->executeImmediately = 0;
$emailTask->active = true;
$emailTask->workflowId = $commentsWorkflow->id;
$emailTask->summary = 'Comment Added From Portal : Send Email to Record Owner';
$emailTask->fromEmail = '$(contact_id : (Contacts) lastname) $(contact_id : (Contacts) firstname)&lt;$(contact_id : (Contacts) email)&gt;';
$emailTask->recepient = ',$(assigned_user_id : (Users) email1)';
$emailTask->subject = 'Respond to Ticket ID## $(general : (__VtigerMeta__) recordId) ## in Customer Portal - URGENT';
$emailTask->content = 'Dear $(assigned_user_id : (Users) last_name) $(assigned_user_id : (Users) first_name),<br><br>
								Customer has provided the following additional information to your reply:<br><br>
								<b>$lastComment</b><br><br>
								Kindly respond to above ticket at the earliest.<br><br>
								Regards<br>Support Administrator';
$taskManager->saveTask($emailTask);

$crmCommentConditions = [
    [
        'fieldname'     => '_VT_add_comment',
        'operation'     => 'is added',
        'value'         => '',
        'valuetype'     => 'rawtext',
        'joincondition' => '',
        'groupjoin'     => 'and',
        'groupid'       => '0'
    ],
    [
        'fieldname'     => 'from_portal',
        'operation'     => 'is',
        'value'         => '0',
        'valuetype'     => 'rawtext',
        'joincondition' => '',
        'groupjoin'     => 'and',
        'groupid'       => '0'
    ],
    [
        'fieldname'     => '(contact_id : (Contacts) emailoptout)',
        'operation'     => 'is',
        'value'         => '0',
        'valuetype'     => 'rawtext',
        'joincondition' => 'and',
        'groupjoin'     => 'and',
        'groupid'       => '0'
    ],
];

// Comment Added From CRM - not a portal user
$workflowConditions = [
    ...$crmCommentConditions,
    [
        'fieldname'     => '(contact_id : (Contacts) portal)',
        'operation'     => 'is',
        'value'         => '0',
        'valuetype'     => 'rawtext',
        'joincondition' => 'and',
        'groupjoin'     => 'and',
        'groupid'       => '0'
    ]
];

$commentsWorkflow = $workflowManager->newWorkFlow($moduleName);
$commentsWorkflow->test = Zend_Json::encode($workflowConditions);
$commentsWorkflow->description = 'Comment Added From CRM : Send Email to Contact, where Contact is not a Portal User';
$commentsWorkflow->executionCondition = VTWorkflowManager::$ON_EVERY_SAVE;
$commentsWorkflow->defaultworkflow = 1;
$workflowManager->save($commentsWorkflow);

$emailTask->id = '';
$emailTask->workflowId = $commentsWorkflow->id;
$emailTask->summary = 'Comment Added From CRM : Send Email to Contact, where Contact is not a Portal User';
$emailTask->fromEmail = '$(general : (__VtigerMeta__) supportName)&lt;$(general : (__VtigerMeta__) supportEmailId)&gt;';
$emailTask->recepient = ',$(contact_id : (Contacts) email)';
$emailTask->subject = '$ticket_no [ Ticket Id : $(general : (__VtigerMeta__) recordId) ] $ticket_title';
$emailTask->content = 'Dear $(contact_id : (Contacts) lastname) $(contact_id : (Contacts) firstname),<br><br>
							The Ticket is replied the details are :<br><br>
							Ticket No : $ticket_no<br>
							Status : $ticketstatus<br>
							Category : $ticketcategories<br>
							Severity : $ticketseverities<br>
							Priority : $ticketpriorities<br><br>
							Description : <br>$description<br><br>
							Solution : <br>$solution<br>
							The comments are : <br>
							$allComments<br><br>
							Regards<br>Support Administrator';
$taskManager->saveTask($emailTask);

// Comment Added From CRM - portal user
$workflowConditions = [
    ...$crmCommentConditions,
    [
        'fieldname' => '(contact_id : (Contacts) portal)',
        'operation' => 'is',
        'value' => '1',
        'valuetype' => 'rawtext',
        'joincondition' => 'and',
        'groupjoin' => 'and',
        'groupid' => '0'
    ]
];

$commentsWorkflow = $workflowManager->newWorkFlow($moduleName);
$commentsWorkflow->test = Zend_Json::encode($workflowConditions);
$commentsWorkflow->description = 'Comment Added From CRM : Send Email to Contact, where Contact is Portal User';
$commentsWorkflow->executionCondition = VTWorkflowManager::$ON_EVERY_SAVE;
$commentsWorkflow->defaultworkflow = 1;
$workflowManager->save($commentsWorkflow);

$emailTask->id = '';
$emailTask->workflowId = $commentsWorkflow->id;
$emailTask->summary = 'Comment Added From CRM : Send Email to Contact, where Contact is Portal User';
$emailTask->fromEmail = '$(general : (__VtigerMeta__) supportName)&lt;$(general : (__VtigerMeta__) supportEmailId)&gt;';
$emailTask->recepient = ',$(contact_id : (Contacts) email)';
$emailTask->subject = '$ticket_no [ Ticket Id : $(general : (__VtigerMeta__) recordId) ] $ticket_title';
$emailTask->content = 'Ticket No : $ticket_no<br>
										Ticket Id : $(general : (__VtigerMeta__) recordId)<br>
										Subject : $ticket_title<br><br>
										Dear $(contact_id : (Contacts) lastname) $(contact_id : (Contacts) firstname),<br><br>
										There is a reply to <b>$ticket_title</b> in the "Customer Portal" at VTiger.
										You can use the following link to view the replies made:<br>
										<a href="$(general : (__VtigerMeta__) portaldetailviewurl)">Ticket Details</a><br><br>
										Thanks<br>$(general : (__VtigerMeta__) supportName)';
$taskManager->saveTask($emailTask);

// Comment Added From CRM - Organization
$workflowConditions = [
    [
        'fieldname'     => '_VT_add_comment',
        'operation'     => 'is added',
        'value'         => '',
        'valuetype'     => 'rawtext',
        'joincondition' => '',
        'groupjoin'     => 'and',
        'groupid'       => '0'
    ],
    [
        'fieldname'     => 'from_portal',
        'operation'     => 'is',
        'value'         => '0',
        'valuetype'     => 'rawtext',
        'joincondition' => '',
        'groupjoin'     => 'and',
        'groupid'       => '0'
    ],
    [
        'fieldname' => '(parent_id : (Accounts) emailoptout)',
        'operation' => 'is',
        'value' => '0',
        'valuetype' => 'rawtext',
        'joincondition' => 'and',
        'groupjoin' => 'and',
        'groupid' => '0'
    ]
];

$commentsWorkflow = $workflowManager->newWorkFlow($moduleName);
$commentsWorkflow->test = Zend_Json::encode($workflowConditions);
$commentsWorkflow->description = 'Comment Added From CRM : Send Email to Organization';
$commentsWorkflow->executionCondition = VTWorkflowManager::$ON_EVERY_SAVE;
$commentsWorkflow->defaultworkflow = 1;
$workflowManager->save($commentsWorkflow);

$emailTask->id = '';
$emailTask->workflowId = $commentsWorkflow->id;
$emailTask->summary = 'Comment Added From CRM : Send Email to Organization';
$emailTask->fromEmail = '$(general : (__VtigerMeta__) supportName)&lt;$(general : (__VtigerMeta__) supportEmailId)&gt;';
$emailTask->recepient = ',$(parent_id : (Accounts) email1),';
$emailTask->subject = '$ticket_no [ Ticket Id : $(general : (__VtigerMeta__) recordId) ] $ticket_title';
$emailTask->content = 'Ticket ID : $(general : (__VtigerMeta__) recordId)<br>Ticket Title : $ticket_title<br><br>
								Dear $(parent_id : (Accounts) accountname),<br><br>
								The Ticket is replied the details are :<br><br>
								Ticket No : $ticket_no<br>
								Status : $ticketstatus<br>
								Category : $ticketcategories<br>
								Severity : $ticketseverities<br>
								Priority : $ticketpriorities<br><br>
								Description : <br>$description<br><br>
								Solution : <br>$solution<br>
								The comments are : <br>
								$allComments<br><br>
								Regards<br>Support Administrator';
$taskManager->saveTask($emailTask);
//End: Moved Entity methods of Comments to Workflows

//Start: Moving Entity methods of Tickets to Workflows
$result = $adb->pquery('SELECT DISTINCT workflow_id FROM com_vtiger_workflowtasks WHERE workflow_id IN
				(SELECT workflow_id FROM com_vtiger_workflows WHERE module_name IN (?) AND defaultworkflow = ?)
				AND task LIKE ?', array($moduleName, 1, '%VTEntityMethodTask%'));
$numOfRows = $adb->num_rows($result);

for ($i = 0; $i < $numOfRows; $i++) {
	$wfs = new VTWorkflowManager($adb);
	$workflowModel = $wfs->retrieve($adb->query_result($result, $i, 'workflow_id'));
	$workflowModel->filtersavedinnew = 6;

	$tm = new VTTaskManager($adb);
	$tasks = $tm->getTasksForWorkflow($workflowModel->id);
	foreach ($tasks as $task) {
		$properties = get_object_vars($task);

		$emailTask = new VTEmailTask();
		$emailTask->executeImmediately = 0;
		$emailTask->summary = $properties['summary'];
		$emailTask->active = $properties['active'];
		switch ($properties['methodName']) {
			case 'NotifyOnPortalTicketCreation' :
				$oldCondtions = Migration_Index_View::transformAdvanceFilterToWorkFlowFilter(Zend_Json::decode($workflowModel->test));
				$newConditions = array(
					array('fieldname' => 'from_portal',
						'operation' => 'is',
						'value' => '1',
						'valuetype' => 'rawtext',
						'joincondition' => '',
						'groupjoin' => 'and',
						'groupid' => '0')
				);
				$newConditions = array_merge($oldCondtions, $newConditions);

				$workflowModel->test = Zend_Json::encode($newConditions);
				$workflowModel->description = 'Ticket Creation From Portal : Send Email to Record Owner and Contact';
				$wfs->save($workflowModel);

				$emailTask->id = '';
				$emailTask->workflowId = $properties['workflowId'];
				$emailTask->summary = 'Notify Record Owner when Ticket is created from Portal';
				$emailTask->fromEmail = '$(contact_id : (Contacts) lastname) $(contact_id : (Contacts) firstname)&lt;$(general : (__VtigerMeta__) supportEmailId)&gt;';
				$emailTask->recepient = ',$(assigned_user_id : (Users) email1)';
				$emailTask->subject = '[From Portal] $ticket_no [ Ticket Id : $(general : (__VtigerMeta__) recordId) ] $ticket_title';
				$emailTask->content = 'Ticket No : $ticket_no<br>
							  Ticket ID : $(general : (__VtigerMeta__) recordId)<br>
							  Ticket Title : $ticket_title<br><br>
							  $description';
				$tm->saveTask($emailTask);

				$emailTask->id = $properties['id'];
				$emailTask->summary = 'Notify Related Contact when Ticket is created from Portal';
				$emailTask->fromEmail = '$(general : (__VtigerMeta__) supportName)&lt;$(general : (__VtigerMeta__) supportEmailId)&gt;';
				$emailTask->recepient = ',$(contact_id : (Contacts) email)';

				$tm->saveTask($emailTask);
				break;


			case 'NotifyParentOnTicketChange' :
				$newWorkflowModel = $wfs->newWorkflow($workflowModel->moduleName);
				$workflowProperties = get_object_vars($workflowModel);
				foreach ($workflowProperties as $workflowPropertyName => $workflowPropertyValue) {
					$newWorkflowModel->$workflowPropertyName = $workflowPropertyValue;
				}

				$oldCondtions = Migration_Index_View::transformAdvanceFilterToWorkFlowFilter(Zend_Json::decode($newWorkflowModel->test));
				$newConditions = array(
					array('fieldname' => 'ticketstatus',
						'operation' => 'has changed to',
						'value' => 'Closed',
						'valuetype' => 'rawtext',
						'joincondition' => 'or',
						'groupjoin' => 'and',
						'groupid' => '1'),
					array('fieldname' => 'solution',
						'operation' => 'has changed',
						'value' => '',
						'valuetype' => '',
						'joincondition' => 'or',
						'groupjoin' => 'and',
						'groupid' => '1'),
					array('fieldname' => 'description',
						'operation' => 'has changed',
						'value' => '',
						'valuetype' => '',
						'joincondition' => 'or',
						'groupjoin' => 'and',
						'groupid' => '1')
				);
				$newConditions = array_merge($oldCondtions, $newConditions);

				$newAccountCondition = array(
					array('fieldname' => '(parent_id : (Accounts) emailoptout)',
						'operation' => 'is',
						'value' => '0',
						'valuetype' => 'rawtext',
						'joincondition' => 'and',
						'groupjoin' => 'and',
						'groupid' => '0')
				);
				$newWorkflowConditions = array_merge($newAccountCondition, $newConditions);

				unset($newWorkflowModel->id);
				$newWorkflowModel->test = Zend_Json::encode($newWorkflowConditions);
				$newWorkflowModel->description = 'Send Email to Organization on Ticket Update';
				$wfs->save($newWorkflowModel);

				$emailTask->id = '';
				$emailTask->summary = 'Send Email to Organization on Ticket Update';
				$emailTask->fromEmail = '$(general : (__VtigerMeta__) supportName)&lt;$(general : (__VtigerMeta__) supportEmailId)&gt;';
				$emailTask->recepient = ',$(parent_id : (Accounts) email1)';
				$emailTask->subject = '$ticket_no [ Ticket Id : $(general : (__VtigerMeta__) recordId) ] $ticket_title';
				$emailTask->content = 'Ticket ID : $(general : (__VtigerMeta__) recordId)<br>Ticket Title : $ticket_title<br><br>
								Dear $(parent_id : (Accounts) accountname),<br><br>
								The Ticket is replied the details are :<br><br>
								Ticket No : $ticket_no<br>
								Status : $ticketstatus<br>
								Category : $ticketcategories<br>
								Severity : $ticketseverities<br>
								Priority : $ticketpriorities<br><br>
								Description : <br>$description<br><br>
								Solution : <br>$solution<br>
								The comments are : <br>
								$allComments<br><br>
								Regards<br>Support Administrator';

				$emailTask->workflowId = $newWorkflowModel->id;
				$tm->saveTask($emailTask);

				$portalCondition = array(
					array('fieldname' => 'from_portal',
						'operation' => 'is',
						'value' => '0',
						'valuetype' => 'rawtext',
						'joincondition' => '',
						'groupjoin' => 'and',
						'groupid' => '0')
				);

				unset($newWorkflowModel->id);
				$newWorkflowModel->executionCondition = 1;
				$newWorkflowModel->test = Zend_Json::encode(array_merge($newAccountCondition, $portalCondition));
				$newWorkflowModel->description = 'Ticket Creation From CRM : Send Email to Organization';
				$wfs->save($newWorkflowModel);

				$emailTask->id = '';
				$emailTask->workflowId = $newWorkflowModel->id;
				$emailTask->summary = 'Ticket Creation From CRM : Send Email to Organization';
				$tm->saveTask($emailTask);

				$newContactCondition = array(
					array('fieldname' => '(contact_id : (Contacts) emailoptout)',
						'operation' => 'is',
						'value' => '0',
						'valuetype' => 'rawtext',
						'joincondition' => 'and',
						'groupjoin' => 'and',
						'groupid' => '0')
				);
				$newConditions = array_merge($newContactCondition, $newConditions);

				$workflowModel->test = Zend_Json::encode($newConditions);
				$workflowModel->description = 'Send Email to Contact on Ticket Update';
				$wfs->save($workflowModel);

				$emailTask->id = $properties['id'];
				$emailTask->workflowId = $properties['workflowId'];
				$emailTask->summary = 'Send Email to Contact on Ticket Update';
				$emailTask->recepient = ',$(contact_id : (Contacts) email)';
				$emailTask->content = 'Ticket ID : $(general : (__VtigerMeta__) recordId)<br>Ticket Title : $ticket_title<br><br>
								Dear $(contact_id : (Contacts) lastname) $(contact_id : (Contacts) firstname),<br><br>
								The Ticket is replied the details are :<br><br>
								Ticket No : $ticket_no<br>
								Status : $ticketstatus<br>
								Category : $ticketcategories<br>
								Severity : $ticketseverities<br>
								Priority : $ticketpriorities<br><br>
								Description : <br>$description<br><br>
								Solution : <br>$solution<br>
								The comments are : <br>
								$allComments<br><br>
								Regards<br>Support Administrator';

				$tm->saveTask($emailTask);

				unset($newWorkflowModel->id);
				$newWorkflowModel->executionCondition = 1;
				$newWorkflowModel->test = Zend_Json::encode(array_merge($newContactCondition, $portalCondition));
				$newWorkflowModel->description = 'Ticket Creation From CRM : Send Email to Contact';
				$wfs->save($newWorkflowModel);

				$emailTask->id = '';
				$emailTask->workflowId = $newWorkflowModel->id;
				$emailTask->summary = 'Ticket Creation From CRM : Send Email to Contact';
				$tm->saveTask($emailTask);
				break;


			case 'NotifyOwnerOnTicketChange' :
				$tm->deleteTask($task->id);

				$newWorkflowModel = $wfs->newWorkflow($workflowModel->moduleName);
				$workflowProperties = get_object_vars($workflowModel);
				foreach ($workflowProperties as $workflowPropertyName => $workflowPropertyValue) {
					$newWorkflowModel->$workflowPropertyName = $workflowPropertyValue;
				}

				$oldCondtions = Migration_Index_View::transformAdvanceFilterToWorkFlowFilter(Zend_Json::decode($newWorkflowModel->test));
				$newConditions = array(
					array('fieldname' => 'ticketstatus',
						'operation' => 'has changed to',
						'value' => 'Closed',
						'valuetype' => 'rawtext',
						'joincondition' => 'or',
						'groupjoin' => 'and',
						'groupid' => '1'),
					array('fieldname' => 'solution',
						'operation' => 'has changed',
						'value' => '',
						'valuetype' => '',
						'joincondition' => 'or',
						'groupjoin' => 'and',
						'groupid' => '1'),
					array('fieldname' => 'assigned_user_id',
						'operation' => 'has changed',
						'value' => '',
						'valuetype' => '',
						'joincondition' => 'or',
						'groupjoin' => 'and',
						'groupid' => '1'),
					array('fieldname' => 'description',
						'operation' => 'has changed',
						'value' => '',
						'valuetype' => '',
						'joincondition' => 'or',
						'groupjoin' => 'and',
						'groupid' => '1')
				);
				$newConditions = array_merge($oldCondtions, $newConditions);

				unset($newWorkflowModel->id);
				$newWorkflowModel->test = Zend_Json::encode($newConditions);
				$newWorkflowModel->description = 'Send Email to Record Owner on Ticket Update';
				$wfs->save($newWorkflowModel);

				$emailTask->id = '';
				$emailTask->workflowId = $newWorkflowModel->id;
				$emailTask->summary = 'Send Email to Record Owner on Ticket Update';
				$emailTask->fromEmail = '$(general : (__VtigerMeta__) supportName)&lt;$(general : (__VtigerMeta__) supportEmailId)&gt;';
				$emailTask->recepient = ',$(assigned_user_id : (Users) email1)';
				$emailTask->subject = 'Ticket Number : $ticket_no $ticket_title';
				$emailTask->content = 'Ticket ID : $(general : (__VtigerMeta__) recordId)<br>Ticket Title : $ticket_title<br><br>
								Dear $(assigned_user_id : (Users) last_name) $(assigned_user_id : (Users) first_name),<br><br>
								The Ticket is replied the details are :<br><br>
								Ticket No : $ticket_no<br>
								Status : $ticketstatus<br>
								Category : $ticketcategories<br>
								Severity : $ticketseverities<br>
								Priority : $ticketpriorities<br><br>
								Description : <br>$description<br><br>
								Solution : <br>$solution
								$allComments<br><br>
								Regards<br>Support Administrator';
				$emailTask->id = '';
				$tm->saveTask($emailTask);

				$portalCondition = array(
					array('fieldname' => 'from_portal',
						'operation' => 'is',
						'value' => '0',
						'valuetype' => 'rawtext',
						'joincondition' => '',
						'groupjoin' => 'and',
						'groupid' => '0')
				);

				unset($newWorkflowModel->id);
				$newWorkflowModel->executionCondition = 1;
				$newWorkflowModel->test = Zend_Json::encode($portalCondition);
				$newWorkflowModel->description = 'Ticket Creation From CRM : Send Email to Record Owner';
				$wfs->save($newWorkflowModel);

				$emailTask->id = '';
				$emailTask->workflowId = $newWorkflowModel->id;
				$emailTask->summary = 'Ticket Creation From CRM : Send Email to Record Owner';
				$tm->saveTask($emailTask);
				break;
		}
	}
}
$em = new VTEventsManager($adb);
$em->registerHandler('vtiger.entity.aftersave', 'modules/ModComments/ModCommentsHandler.php', 'ModCommentsHandler');

$result = $adb->pquery('SELECT 1 FROM vtiger_currencies WHERE currency_name = ?', array('Ugandan Shilling'));
if(!$adb->num_rows($result)) {
	Migration_Index_View::ExecuteQuery('INSERT INTO vtiger_currencies (currencyid, currency_name, currency_code, currency_symbol) VALUES(?, ?, ?, ?)',
			array($adb->getUniqueID('vtiger_currencies'), 'Ugandan Shilling', 'UGX', 'Sh'));
}
$em = new VTEventsManager($adb);
$em->registerHandler('vtiger.picklist.afterrename', 'modules/Settings/Picklist/handlers/PickListHandler.php', 'PickListHandler');
$em->registerHandler('vtiger.picklist.afterdelete', 'modules/Settings/Picklist/handlers/PickListHandler.php', 'PickListHandler');

Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_inventoryproductrel MODIFY comment varchar(500)', array());

$module = Vtiger_Module::getInstance('Accounts');
$module->addLink('DETAILVIEWSIDEBARWIDGET', 'Google Map', 'module=Google&view=Map&mode=showMap&viewtype=detail', '', '', '');

// Changes as on 2013.11.29

Migration_Index_View::ExecuteQuery('DELETE FROM vtiger_settings_field WHERE name=?', array('LBL_BACKUP_SERVER_SETTINGS'));

// Changes ends as on 2013.11.29
Migration_Index_View::ExecuteQuery("CREATE TABLE IF NOT EXISTS vtiger_faqcf ( 
                                faqid int(19), 
                                PRIMARY KEY (faqid), 
                                CONSTRAINT fk_1_vtiger_faqcf FOREIGN KEY (faqid) REFERENCES vtiger_faq(id) ON DELETE CASCADE 
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8", array()); 
