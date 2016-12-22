<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/

if(defined('VTIGER_UPGRADE')) {
	global $adb, $current_user;
	$db = PearDatabase::getInstance();

	if (!Vtiger_Utils::CheckTable('vtiger_activity_recurring_info')) {
		$db->pquery('CREATE TABLE IF NOT EXISTS vtiger_activity_recurring_info(activityid INT(19) NOT NULL, recurrenceid INT(19) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=UTF8;', array());
	}

	$columns = $db->getColumnNames('vtiger_crmentity');
	if (!in_array('smgroupid', $columns)) {
		$db->pquery('ALTER TABLE vtiger_crmentity ADD COLUMN smgroupid INT(19)', array());
	}

	$db->pquery('UPDATE vtiger_settings_field SET name=? WHERE name=?', array('Configuration Editor', 'LBL_CONFIG_EDITOR'));
	$db->pquery('UPDATE vtiger_links SET linktype=? WHERE linklabel=?', array('DETAILVIEW', 'LBL_SHOW_ACCOUNT_HIERARCHY'));
	$db->pquery('UPDATE vtiger_field SET defaultvalue=? WHERE fieldname=?', array('1', 'discontinued'));

	$lineItemModules = array('Products' => 'vtiger_products', 'Services' => 'vtiger_service');
	foreach ($lineItemModules as $moduleName => $tableName) {
		$moduleInstance = Vtiger_Module::getInstance($moduleName);
		$blockInstance = Vtiger_Block::getInstance('LBL_PRICING_INFORMATION', $moduleInstance);
		if ($blockInstance) {
			$fieldInstance = Vtiger_Field::getInstance('purchase_cost', $moduleInstance);
			if (!$fieldInstance) {
				$fieldInstance = new Vtiger_Field();
				$fieldInstance->name		= 'purchase_cost';
				$fieldInstance->column		= 'purchase_cost';
				$fieldInstance->label		= 'Purchase Cost';
				$fieldInstance->columntype	= 'decimal(27,8)';
				$fieldInstance->table		= $tableName;
				$fieldInstance->typeofdata	= 'N~O';
				$fieldInstance->uitype		= '71';
				$fieldInstance->presence	= '0';

				$blockInstance->addField($fieldInstance);
			}
		}
	}

	$userModuleModel = Vtiger_Module_Model::getInstance('Users');
	$defaultActivityTypeFieldModel = Vtiger_Field_Model::getInstance('defaultactivitytype', $userModuleModel);
	if ($defaultActivityTypeFieldModel) {
		$defaultActivityTypeFieldModel->set('defaultvalue', 'Call');
		$defaultActivityTypeFieldModel->save();
		$db->pquery('UPDATE vtiger_users SET defaultactivitytype=? WHERE defaultactivitytype=? OR defaultactivitytype IS NULL', array('Call', ''));
	}

	$defaultEventStatusFieldModel = Vtiger_Field_Model::getInstance('defaulteventstatus', $userModuleModel);
	if ($defaultEventStatusFieldModel) {
		$defaultEventStatusFieldModel->set('defaultvalue', 'Planned');
		$defaultEventStatusFieldModel->save();
		$db->pquery('UPDATE vtiger_users SET defaultactivitytype=? WHERE defaulteventstatus=? OR defaulteventstatus IS NULL', array('Planned', ''));
	}

	$fieldNamesList = array();
	$updateQuery = 'UPDATE vtiger_field SET fieldlabel = CASE fieldname';
	$result = $db->pquery('SELECT taxname, taxlabel FROM vtiger_inventorytaxinfo', array());
	while($row = $db->fetch_array($result)) {
		$fieldName = $row['taxname'];
		$fieldLabel = $row['taxlabel'];

		$updateQuery .= " WHEN '$fieldName' THEN '$fieldLabel' ";
		$fieldNamesList[] = $fieldName;
	}
	$updateQuery .= 'END WHERE fieldname in ('. generateQuestionMarks($fieldNamesList) .')';

	$db->pquery($updateQuery, $fieldNamesList);
	$db->pquery('UPDATE vtiger_field SET fieldlabel=? WHERE displaytype=? AND fieldname=?', array('Item Discount Amount', 5, 'discount_amount'));

	$inventoryModules = getInventoryModules();
	foreach ($inventoryModules as $moduleName) {
		$tabId = getTabid($moduleName);
		$blockId = getBlockId($tabId, 'LBL_ITEM_DETAILS');
		$db->pquery('UPDATE vtiger_field SET displaytype=?, block=? WHERE tabid=? AND fieldname IN (?, ?)', array(5, $blockId, $tabId, 'hdnDiscountAmount', 'hdnDiscountPercent'));
	}

	$itemFieldsName = array('image','purchase_cost','margin');
	$itemFieldsLabel = array('Image','Purchase Cost','Margin');
	$itemFieldsTypeOfData = array('V~O','N~O','N~O');
	$itemFieldsDisplayType = array('56', '71', '71');
	$itemFieldsDataType = array('VARCHAR(2)', 'decimal(27,8)', 'decimal(27,8)');

	$fieldIdsList = array();
	foreach ($inventoryModules as $moduleName) {
		$moduleInstance = Vtiger_Module::getInstance($moduleName);
		$blockInstance = Vtiger_Block::getInstance('LBL_ITEM_DETAILS', $moduleInstance);

		for($i=0; $i<count($itemFieldsName); $i++) {
			$fieldName = $itemFieldsName[$i];

			if ($moduleName === 'PurchaseOrder' && $fieldName !== 'image') {
				continue;
			}

			$fieldInstance = Vtiger_Field::getInstance($fieldName, $moduleInstance);
			if (!$fieldInstance) {
				$fieldInstance = new Vtiger_Field();

				$fieldInstance->name		= $fieldName;
				$fieldInstance->column		= $fieldName;
				$fieldInstance->label		= $itemFieldsLabel[$i];
				$fieldInstance->columntype	= $itemFieldsDataType[$i];
				$fieldInstance->typeofdata	= $itemFieldsTypeOfData[$i];
				$fieldInstance->uitype		= $itemFieldsDisplayType[$i];
				$fieldInstance->table		= 'vtiger_inventoryproductrel';
				$fieldInstance->presence	= '1';
				$fieldInstance->readonly	= '0';
				$fieldInstance->displaytype = '5';
				$fieldInstance->masseditable = '0';

				$blockInstance->addField($fieldInstance);
				$fieldIdsList[] = $fieldInstance->id;
			}
		}
	}

	$columns = $db->getColumnNames('vtiger_products');
	if (!in_array('is_subproducts_viewable', $columns)) {
		$db->pquery('ALTER TABLE vtiger_products ADD COLUMN is_subproducts_viewable INT(1) DEFAULT 1', array());
	}
	$columns = $db->getColumnNames('vtiger_seproductsrel');
	if (!in_array('quantity', $columns)) {
		$db->pquery('ALTER TABLE vtiger_seproductsrel ADD COLUMN quantity INT(19) DEFAULT 1', array());
	}
	$columns = $db->getColumnNames('vtiger_inventorysubproductrel');
	if (!in_array('quantity', $columns)) {
		$db->pquery('ALTER TABLE vtiger_inventorysubproductrel ADD COLUMN quantity INT(19) DEFAULT 1', array());
	}

	$columns = $db->getColumnNames('vtiger_calendar_default_activitytypes');
	if (!in_array('isdefault', $columns)) {
		$db->pquery('ALTER TABLE vtiger_calendar_default_activitytypes ADD COLUMN isdefault INT(11) DEFAULT 1', array());
	}
	if (!in_array('conditions', $columns)) {
		$db->pquery('ALTER TABLE vtiger_calendar_default_activitytypes ADD COLUMN conditions VARCHAR(255) DEFAULT ""', array());
	}

	$updateList = array();
	$updateList[] = array('module' => 'Events',		'fieldname' => 'Events',			'newfieldname' => array('date_start', 'due_date'));
	$updateList[] = array('module' => 'Calendar',	'fieldname' => 'Tasks',				'newfieldname' => array('date_start', 'due_date'));
	$updateList[] = array('module' => 'Contacts',	'fieldname' => 'support_end_date',	'newfieldname' => array('support_end_date'));
	$updateList[] = array('module' => 'Contacts',	'fieldname' => 'birthday',			'newfieldname' => array('birthday'));
	$updateList[] = array('module' => 'Potentials',	'fieldname' => 'Potentials',		'newfieldname' => array('closingdate'));
	$updateList[] = array('module' => 'Invoice',	'fieldname' => 'Invoice',			'newfieldname' => array('duedate'));
	$updateList[] = array('module' => 'Project',	'fieldname' => 'Project',			'newfieldname' => array('startdate', 'targetenddate'));
	$updateList[] = array('module' => 'ProjectTask','fieldname' => 'Project Task',		'newfieldname' => array('startdate', 'enddate'));

	foreach ($updateList as $list) {
		$db->pquery('UPDATE vtiger_calendar_default_activitytypes SET fieldname=? WHERE module=? AND fieldname=? AND isdefault=?', array(Zend_Json::encode($list['newfieldname']), $list['module'], $list['fieldname'], '1'));
	}

	$model = Settings_Vtiger_TermsAndConditions_Model::getInstance('Inventory');
	$tAndC = $model->getText();
	$db->pquery('DELETE FROM vtiger_inventory_tandc', array());

	$inventoryModules = getInventoryModules();
	foreach ($inventoryModules as $moduleName) {
		$model = Settings_Vtiger_TermsAndConditions_Model::getInstance($moduleName);
		$model->setText($tAndC);
		$model->setType($moduleName);
		$model->save();
	}

	$columns = $db->getColumnNames('vtiger_import_queue');
	if (!in_array('lineitem_currency_id', $columns)) {
		$db->pquery('ALTER TABLE vtiger_import_queue ADD COLUMN lineitem_currency_id INT(5)', array());
	}
	if (!in_array('paging', $columns)) {
		$db->pquery('ALTER TABLE vtiger_import_queue ADD COLUMN paging INT(1) DEFAULT 0', array());
	}

	$documentsInstance = Vtiger_Module::getInstance('Documents');
	if ($documentsInstance) {
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('Contacts'), 'Contacts', true);
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('Accounts'), 'Accounts', true);
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('Potentials'), 'Potentials', true);
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('Leads'), 'Leads', true);
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('Products'), 'Products', true);
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('Services'), 'Services', true);
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('Project'), 'Project', true);
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('Assets'), 'Assets', true);
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('ServiceContracts'), 'ServiceContracts', true);
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('Quotes'), 'Quotes', true);
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('Invoice'), 'Invoice', true);
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('SalesOrder'), 'SalesOrder', true);
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('PurchaseOrder'), 'PurchaseOrder', true);
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('HelpDesk'), 'HelpDesk', true);
		$documentsInstance->setRelatedList(Vtiger_Module::getInstance('Faq'), 'Faq', true);
	}

	$columns = $db->getColumnNames('vtiger_relatedlists');
	if (!in_array('relationfieldid', $columns)) {
		$db->pquery('ALTER TABLE vtiger_relatedlists ADD COLUMN relationfieldid INT(18)', array());
	}
	if (!in_array('source', $columns)) {
		$db->pquery('ALTER TABLE vtiger_relatedlists ADD COLUMN source varchar(25)', array());
	}
	if (!in_array('relationtype', $columns)) {
		$db->pquery('ALTER TABLE vtiger_relatedlists ADD COLUMN relationtype varchar(10)', array());
	}

	$accountsTabId = getTabId('Accounts');
	$db->pquery('UPDATE vtiger_relatedlists SET name=? WHERE name=? and tabid=?', array('get_merged_list', 'get_dependents_list', $accountsTabId));

	//Update relation field for existing relation ships
	$ignoreRelationFieldMapping = array('Emails');
	$query = 'SELECT * FROM vtiger_relatedlists ORDER BY tabid ';
	$result = $db->pquery($query, array());
	$num_rows = $db->num_rows($result);
	$relationShipMapping = array();
	for ($i=0; $i<$num_rows; $i++) {
		$tabId = $db->query_result($result, $i, 'tabid');
		$relatedTabid = $db->query_result($result, $i, 'related_tabid');
		$relationId = $db->query_result($result, $i, 'relation_id');
		$primaryModuleInstance = Vtiger_Module_Model::getInstance($tabId);
		$relatedModuleInstance = Vtiger_Module_Model::getInstance($relatedTabid);

		if (empty($relatedModuleInstance)) {
			continue;
		}

		$primaryModuleName = $primaryModuleInstance->getName();
		$relatedModuleName = $relatedModuleInstance->getName();

		$relatedModulesIgnored = $ignoreRelationFieldMapping[$primaryModuleName];
		if (in_array($relatedModuleName, $ignoreRelationFieldMapping)) {
			continue;
		}
		$relatedModuleReferenceFields = $relatedModuleInstance->getFieldsByType('reference');
		foreach ($relatedModuleReferenceFields as $fieldModel) {
			if ($fieldModel->isCustomField()) {
				//for custom reference field we cannot do relation ships so ignoring them
				continue;
			}
			$referenceList = $fieldModel->getReferenceList(false);
			if (in_array($primaryModuleName, $referenceList)) {
				$relationShipMapping[$primaryModuleName][$relatedModuleName] = $fieldModel->getName();
				$updateQuery = 'UPDATE vtiger_relatedlists SET relationfieldid=? WHERE relation_id=?';
				$db->pquery($updateQuery, array($fieldModel->getId(), $relationId));
				break;
			}
		}
	}

	$columns = $db->getColumnNames('vtiger_links');
	if (!in_array('parent_link', $columns)) {
		$db->pquery('ALTER TABLE vtiger_links ADD COLUMN parent_link INT(19)', array());
	}

	$moduleName = 'Reports';
	$reportModel = Vtiger_Module_Model::getInstance($moduleName);
	$reportTabId = $reportModel->getId();
	Vtiger_Link::addLink($reportTabId, 'LISTVIEWBASIC', 'LBL_ADD_RECORD', '', '', '0');

	$reportAddRecordLink = $db->pquery('SELECT linkid FROM vtiger_links WHERE tabid = ? AND linklabel = ?', array($reportTabId, 'LBL_ADD_RECORD'));
	$parentLinkId = $db->query_result($reportAddRecordLink, 0, 'linkid');

	$reportModelHandler = array('path' => 'modules/Reports/models/Module.php', 'class' => 'Reports_Module_Model', 'method' => 'checkLinkAccess');
	Vtiger_Link::addLink($reportTabId, 'LISTVIEWBASIC', 'LBL_DETAIL_REPORT', 'javascript:Reports_List_Js.addReport("'.$reportModel->getCreateRecordUrl().'")', '', '0', $reportModelHandler, $parentLinkId);
	Vtiger_Link::addLink($reportTabId, 'LISTVIEWBASIC', 'LBL_CHARTS', 'javascript:Reports_List_Js.addReport("index.php?module=Reports&view=ChartEdit")', '', '0', $reportModelHandler, $parentLinkId);
	Vtiger_Link::addLink($reportTabId, 'LISTVIEWBASIC', 'LBL_ADD_FOLDER', 'javascript:Reports_List_Js.triggerAddFolder("'.$reportModel->getAddFolderUrl().'")', '', '0', $reportModelHandler);

	$allFolders = Reports_Folder_Model::getAll();
	foreach ($allFolders as $folderId => $folderModel) {
		$folderModel->set('foldername', decode_html(vtranslate($folderModel->getName(), $moduleName)));
		$folderModel->set('folderdesc', decode_html(vtranslate($folderModel->get('folderdesc'), $moduleName)));
		$folderModel->save();
	}

	$modCommentsInstance = Vtiger_Module_Model::getInstance('ModComments');
	$modCommentsTabId = $modCommentsInstance->getId();

	$modCommentFieldInstance = Vtiger_Field_Model::getInstance('related_to', $modCommentsInstance);
	$modCommentFieldInstance->setRelatedModules(getInventoryModules());

	$refModulesList = $modCommentFieldInstance->getReferenceList();
	foreach ($refModulesList as $refModuleName) {
		$refModuleModel = Vtiger_Module_Model::getInstance($refModuleName);
		$refModuleTabId = $refModuleModel->getId();
		$db->pquery('UPDATE vtiger_relatedlists SET sequence=(sequence+1) WHERE tabid=?', array($refModuleTabId));

		$query = 'SELECT 1 FROM vtiger_relatedlists WHERE tabid=? AND related_tabid =?';
		$result = $db->pquery($query, array($refModuleTabId, $modCommentsTabId));
		if (!$db->num_rows($result)) {
			$db->pquery('INSERT INTO vtiger_relatedlists VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array($db->getUniqueID('vtiger_relatedlists'), $refModuleTabId, $modCommentsTabId, 'get_comments', '1', 'ModComments', '0', '', $fieldId, 'NULL', '1:N'));
		}
	}

	$columns = $db->getColumnNames('vtiger_modcomments');
	if (in_array('parent_comments', $columns)) {
		$db->pquery('ALTER TABLE vtiger_modcomments MODIFY parent_comments INT(19)',array());
	}
	if (in_array('customer', $columns)) {
		$db->pquery('ALTER TABLE vtiger_modcomments MODIFY customer INT(19)', array());
	}
	if (in_array('userid', $columns)) {
		$db->pquery('ALTER TABLE vtiger_modcomments MODIFY userid INT(19)', array());
	}

	$moduleName = 'Calendar';
	$inviteUsersTemplate = $db->pquery('SELECT 1 FROM vtiger_emailtemplates WHERE subject=?', array('Invitation'));
	if (!$db->num_rows($inviteUsersTemplate)) {
		$body = '<p>$invitee_name$,<br/><br/>' .
				vtranslate('LBL_ACTIVITY_INVITATION', $moduleName).'<br/><br/>' .
				vtranslate('LBL_DETAILS_STRING', $moduleName).' :<br/>
						&nbsp; '.vtranslate('Subject', $moduleName).' : $events-subject$<br/>
						&nbsp; '.vtranslate('Start Date & Time', $moduleName).' : $events-date_start$<br/> 
						&nbsp; '.vtranslate('End Date & Time', $moduleName).' : $events-due_date$<br/>
						&nbsp; '.vtranslate('LBL_STATUS', $moduleName).' : $events-eventstatus$<br/>
						&nbsp; '.vtranslate('Priority', $moduleName).' : $events-priority$<br/>
						&nbsp; '.vtranslate('Related To', $moduleName).' : $events-crmid$<br/>
						&nbsp; '.vtranslate('LBL_CONTACT_LIST', $moduleName).' : $events-contactid$<br/>
						&nbsp; '.vtranslate('Location', $moduleName).' : $events-location$<br/>
						&nbsp; '.vtranslate('LBL_APP_DESCRIPTION', $moduleName).' : $events-description$<br/><br/>
						'.vtranslate('LBL_REGARDS_STRING', $moduleName).',<br/>
						$current_user_name$
						<p/>';
		$db->pquery('INSERT INTO vtiger_emailtemplates(foldername,templatename,subject,description,body,systemtemplate) values(?,?,?,?,?,?)', array('Public', 'Invite Users', 'Invitation', 'Invite Users', $body, '1'));
	}

	if (!Vtiger_Utils::CheckTable('vtiger_emailslookup')) {
		$query = 'CREATE TABLE vtiger_emailslookup(crmid int(20) DEFAULT NULL, 
						setype varchar(30) DEFAULT NULL, value varchar(100) DEFAULT NULL, 
						fieldid int(20) DEFAULT NULL, UNIQUE KEY emailslookup_crmid_setype_fieldname_uk (crmid,setype,fieldid),
						KEY emailslookup_fieldid_setype_idx (fieldid, setype), 
						CONSTRAINT emailslookup_crmid_fk FOREIGN KEY (crmid) REFERENCES vtiger_crmentity (crmid) ON DELETE CASCADE)';
		$db->pquery($query, array());
	}

	$EventManager = new VTEventsManager($db);
	$createEvent = 'vtiger.entity.aftersave';
	$handler_path = 'modules/Vtiger/handlers/EmailLookupHandler.php';
	$className = 'EmailLookupHandler';
	$EventManager->registerHandler($createEvent, $handler_path, $className, '', '["VTEntityDelta"]');

	$deleteEvent = 'vtiger.entity.afterdelete';
	$EventManager->registerHandler($deleteEvent, $handler_path, $className, '');

	$restoreEvent = 'vtiger.entity.afterrestore';
	$EventManager->registerHandler($restoreEvent, $handler_path, $className, '');

	$createBatchEvent = 'vtiger.batchevent.save';
	$EventManager->registerHandler($createBatchEvent, $handler_path, 'EmailLookupBatchHandler', '');

	$EmailsModuleModel = Vtiger_Module_Model::getInstance('Emails');
	$emailSupportedModulesList = $EmailsModuleModel->getEmailRelatedModules();

	$recordModel = new Emails_Record_Model();
	foreach ($emailSupportedModulesList as $module) {
		if ($module != 'Users') {
			$moduleInstance = CRMEntity::getInstance($module);

			$query = $moduleInstance->buildSearchQueryForFieldTypes(array('13'));
			$moduleModel = Vtiger_Module_Model::getInstance($module);
			$emailFieldModels = $moduleModel->getFieldsByType('email');
			$emailFieldNames = array_keys($emailFieldModels);
			foreach ($emailFieldModels as $fieldName => $fieldModel) {
				$emailFieldIds[$fieldModel->get('name')] = $fieldModel->get('id');
			}
			$result = $db->pquery($query, array());

			$values['setype'] = $module;
			while ($row = $db->fetchByAssoc($result)) {
				$values['crmid'] = $row['id'];
				foreach ($row as $fieldName => $value) {
					if (in_array($fieldName, $emailFieldNames) && !empty($value)) {
						$fieldId = $emailFieldIds[$fieldName];
						$values[$fieldId] = $value;
						$recordModel->recieveEmailLookup($fieldId, $values);
					}
				}
			}
		}
	}

	$massEditSql = 'UPDATE vtiger_field SET masseditable=0 WHERE fieldname IN(?,?,?,?)';
	$db->pquery($massEditSql, array('created_user_id', 'createdtime', 'modifiedtime', 'modifiedby'));

	$db->pquery('UPDATE vtiger_eventhandlers SET is_active = 1 WHERE handler_class=?', array('ModTrackerHandler'));
	Vtiger_Link_Model::deleteLink('0', 'DETAILVIEWBASIC', 'Print');

	$db->pquery('ALTER TABLE vtiger_emailtemplates MODIFY COLUMN subject VARCHAR(255)', array());
	$db->pquery('ALTER TABLE vtiger_activity MODIFY COLUMN subject VARCHAR(255)', array());

	//Start: Update Currency symbol for Egypt
	$db->pquery('UPDATE vtiger_currencies SET currency_symbol=? WHERE currency_name=?', array('E£', 'Egypt, Pounds'));
	$db->pquery('UPDATE vtiger_currency_info SET currency_symbol=? WHERE currency_name=?', array('E£', 'Egypt, Pounds'));

	//setting is_private value of comments to 0 if internal comments is not supported for that module
	$commentIds = array();
	$internalCommentModules = Vtiger_Functions::getPrivateCommentModules();
	$commentsResult = $db->pquery('SELECT vtiger_modcomments.modcommentsid FROM vtiger_modcomments 
												LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_modcomments.related_to 
												WHERE vtiger_crmentity.setype NOT IN ('.generateQuestionMarks($internalCommentModules).') 
												OR vtiger_crmentity.setype IS NULL', $internalCommentModules, array());
	$commentCount = $db->num_rows($commentsResult);
	for ($i=0; $i<$commentCount; $i++) {
		$commentIds[] = $db->query_result($commentsResult, $i, 'modcommentsid');
	}
	if (count($commentIds) > 0) {
		$db->pquery('UPDATE vtiger_modcomments SET is_private = 0 WHERE modcommentsid IN ('.generateQuestionMarks($commentIds).')', $commentIds);
	}
	//Start - Add Contact Name to Default filter of project
	$cvidQuery = $db->pquery('SELECT cvid FROM vtiger_customview where viewname=? AND entitytype=?', array('All', 'Project'));
	$row = $db->fetch_array($cvidQuery);
	if ($row['cvid']) {
		$columnNameCount = $db->pquery('SELECT 1 FROM vtiger_cvcolumnlist WHERE cvid=? and columnname=?', array($row['cvid'], 'vtiger_project:contactid:contactid:Project_Contact_Name:V'));
		if (!$db->num_rows($columnNameCount)) {
			$columnIndexQuery = $db->pquery('SELECT MAX(columnindex) AS columnindex FROM vtiger_cvcolumnlist WHERE cvid=?', array($row['cvid']));
			$colIndex = $db->fetch_array($columnIndexQuery);
			$db->pquery('INSERT INTO vtiger_cvcolumnlist(cvid,columnindex,columnname) VALUES(?,?,?)', array($row['cvid'], $colIndex['columnindex']+11, 'vtiger_project:contactid:contactid:Project_Contact_Name:V'));
		}
	}
	//End

	$moduleSpecificHeaderFields = array(
		'Accounts'			=> array('website', 'email1', 'phone'),
		'Contacts'			=> array('email', 'phone'),
		'Leads'				=> array('email', 'phone'),
		'Potentials'		=> array('related_to', 'email', 'amount', 'sales_stage'),
		'HelpDesk'			=> array('ticketpriorities'),
		'Invoice'			=> array('contact_id', 'account_id', 'assigned_user_id', 'invoicestatus'),
		'Products'			=> array('product_no', 'discontinued', 'qtyinstock', 'productcategory'),
		'Project'			=> array('linktoaccountscontacts', 'contactid'),
		'PurchaseOrder'		=> array('contact_id', 'assigned_user_id', 'postatus'),
		'Quotes'			=> array('account_id', 'contact_id', 'hdnGrandTotal', 'quotestage'),
		'SalesOrder'		=> array('contact_id', 'account_id', 'assigned_user_id', 'sostatus'),
		'Vendors'			=> array('website', 'email', 'phone')
	);
	$moduleTabIds = array();
	foreach ($moduleSpecificHeaderFields as $moduleName => $headerFields) {
		$tabid = getTabid($moduleName);
		if ($tabid) {
			$sql = 'UPDATE vtiger_field SET headerfield=?, summaryfield=? WHERE tabid=? AND fieldname IN ('.generateQuestionMarks($headerFields).')';
			$db->pquery($sql, array_merge(array(1, 0, $tabid), $headerFields));
		}
	}

	//Update Calendar time_start as mandatory.
	$updateQuery = 'UPDATE vtiger_field SET typeofdata=? WHERE fieldname=? AND tabid=?';
	$db->pquery($updateQuery, array('T~M', 'time_start', getTabid('Calendar')));

	$result = $db->pquery('SELECT name FROM vtiger_tab WHERE isentitytype=?', array(1));
	while ($row = $db->fetchByAssoc($result)) {
		$modules[] = $row['name'];
	}

	$ignoreModules = array('SMSNotifier', 'ModComments');
	foreach ($modules as $module) {
		if (in_array($module, $ignoreModules)) {
			continue;
		}
		$moduleInstance = Vtiger_Module::getInstance($module);
		if ($moduleInstance) {
			$fieldInstance = Vtiger_Field::getInstance('source', $moduleInstance);
			if ($fieldInstance) {
				continue;
			}
			$blockQuery = 'SELECT blockid FROM vtiger_blocks WHERE tabid=? ORDER BY sequence LIMIT 1';
			$result = $db->pquery($blockQuery, array($moduleInstance->id, 1));
			$block = $db->query_result($result, 0, 'blockid');
			if ($block) {
				$blockInstance = Vtiger_Block::getInstance($block, $moduleInstance);
				$field = new Vtiger_Field();
				$field->name = 'source';
				$field->label = 'Source';
				$field->table = 'vtiger_crmentity';
				$field->presence = 2;
				$field->displaytype = 2;
				$field->readonly = 1;
				$field->uitype = 1;
				$field->typeofdata = 'V~O';
				$field->quickcreate = 3;
				$field->masseditable = 0;
				$blockInstance->addField($field);
			}
		}
	}
	$projectModule = Vtiger_Module_Model::getInstance('Project');
	$emailModule = Vtiger_Module_Model::getInstance('Emails');
	$projectModule->setRelatedList($emailModule, 'Emails', 'ADD', 'get_emails');

	$projectTaskModule = Vtiger_Module_Model::getInstance('ProjectTask');
	$projectTaskModule->setRelatedList($emailModule, 'Emails', 'ADD', 'get_emails');

	$sql = "CREATE TABLE IF NOT EXISTS vtiger_emails_recipientprefs(`id` INT(11) NOT NULL AUTO_INCREMENT,`tabid` INT(11) NOT NULL,
				`prefs` VARCHAR(255) NULL DEFAULT NULL, `userid` INT(11), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8";
	$db->pquery($sql, array());

	//To change the convert lead webserice operation parameters which was wrong earliear 
	require_once 'include/Webservices/Utils.php';
	$convertLeadOperationQueryRes = $db->pquery('SELECT operationid FROM vtiger_ws_operation WHERE name=?', array('convertlead'));
	if (!$db->num_rows($convertLeadOperationQueryRes)) {
		$operationId = $db->query_result($convertLeadOperationQueryRes, '0', 'operationid');
		$deleteParameterQuery = $db->pquery('DELETE FROM vtiger_ws_operation_parameters WHERE operationid=?', array($operationId));
		vtws_addWebserviceOperationParam($operationId, 'element', 'encoded', 1);
	}

	//Start : Change fieldLabel of description field to Description - Project module.
	$fieldId = getFieldid(getTabid('Project'), 'description');
	$fieldModel = Vtiger_Field_Model::getInstance($fieldId);
	$fieldModel->set('label', 'Description');
	$fieldModel->__update();

	$db->pquery('ALTER TABLE vtiger_mail_accounts MODIFY mail_password TEXT', array());

	//making priority as mandatory field in Tickets.
	$module = 'HelpDesk';
	$fieldModel = Vtiger_Functions::getModuleFieldInfo(getTabid($module), 'ticketpriorities');
	$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($fieldModel['fieldid']);
	$fieldInstance->set('typeofdata', 'V~M');
	$fieldInstance->save();

	if (Vtiger_Utils::CheckTable('vtiger_customerportal_tabs')) {
		$db->pquery('UPDATE vtiger_customerportal_tabs SET visible=? WHERE tabid IN(?,?)', array(0, getTabid('Contacts'), getTabid('Accounts')));
		$moduleId = getTabid('ServiceContracts');
		$sequenceQuery = 'SELECT max(sequence) as sequence FROM vtiger_customerportal_tabs';
		$seqResult = $db->pquery($sequenceQuery, array());
		$sequence = $db->query_result($seqResult, 0, 'sequence');
		$db->pquery('INSERT INTO vtiger_customerportal_tabs(tabid,visible,sequence) VALUES (?,?,?)', array($moduleId, 1, $sequence+11));
	}

	if (Vtiger_Utils::CheckTable('vtiger_customerportal_fields')) {
		$columns = $db->getColumnNames('vtiger_customerportal_fields');
		if (!in_array('fieldinfo', $columns)) {
			$db->pquery('ALTER TABLE vtiger_customerportal_fields CHANGE fieldid fieldinfo TEXT', array());
		}
		if (!in_array('records_visible', $columns)) {
			$db->pquery('ALTER TABLE vtiger_customerportal_fields CHANGE visible records_visible INT(1)', array());
		}

		$moduleModel = Settings_Vtiger_Module_Model::getInstance('Settings:CustomerPortal');
		$modules = $moduleModel->getModulesList();

		foreach ($modules as $tabid => $model) {
			$moduleModel = Vtiger_Module_Model::getInstance($model->getName());
			$allFields = $moduleModel->getFields();
			$mandatoryFields = array();
			foreach ($allFields as $key => $value) {
				if ($value->isMandatory() && $value->isViewableInDetailView()) {
					$mandatoryFields[$value->name] = 1;
				}
			}
			if ($tabid == getTabid('HelpDesk')) {
				$mandatoryFields['description'] = 1;
				$mandatoryFields['product_id'] = 1;
				$mandatoryFields['ticketseverities'] = 1;
				$mandatoryFields['ticketcategories'] = 1;
			}
			if ($tabid == getTabid('Documents')) {
				$mandatoryFields['filename'] = 0;
			}
			$recordVisibilityQuery = 'SELECT prefvalue from vtiger_customerportal_prefs WHERE tabid=? AND prefkey=?';
			$recordVisibilityQueryResult = $db->pquery($recordVisibilityQuery, array($tabid, 'showrelatedinfo'));
			$visibilty = 1;
			if (!$db->num_rows($recordVisibilityQueryResult)) {
				$visibilty = $db->query_result($recordVisibilityQueryResult, 0, 'prefvalue');
			}
			$db->pquery('INSERT INTO vtiger_customerportal_fields(tabid,fieldinfo,records_visible) VALUES(?,?,?)', array($tabid, json_encode($mandatoryFields), $visibilty));
		}
	}

	if (!Vtiger_Utils::CheckTable('vtiger_customerportal_relatedmoduleinfo')) {
		$db->pquery('CREATE TABLE vtiger_customerportal_relatedmoduleinfo(module INT(11),relatedmodules TEXT) ', array());
		$moduleModel = Settings_Vtiger_Module_Model::getInstance('Settings:CustomerPortal');
		$modules = $moduleModel->getModulesList();
		$oneOperation = array('Invoice', 'Quotes', 'Products', 'Services', 'Documents', 'Assets', 'ProjectMilestone', 'ServiceContracts');
		$twoOperations = array('ProjectTask');
		$fiveOperations = array('Project');
		$threeOperations = array('HelpDesk');
		$availableTwoOperations = array(array('name' => 'History', 'value' => 1), array('name' => 'ModComments', 'value' => 1));
		$availableThreeOperations = array(array('name' => 'History', 'value' => 1), array('name' => 'ModComments', 'value' => 1), array('name' => 'Documents', 'value' => 1));
		$availableOneOperations = array(array('name' => 'History', 'value' => 1));
		$availableFourOperations = array(array('name' => 'History', 'value' => 1), array('name' => 'ModComments', 'value' => 1), array('name' => 'ProjectTask', 'value' => 1), array('name' => 'ProjectMilestone', 'value' => 1));
		$availableFiveOperations = array(array('name' => 'History', 'value' => 1), array('name' => 'ModComments', 'value' => 1), array('name' => 'ProjectTask', 'value' => 1), array('name' => 'ProjectMilestone', 'value' => 1), array('name' => 'Documents', 'value' => 1));

		foreach ($modules as $tabid => $model) {
			$moduleName = $model->getName();
			$tabid = getTabid($moduleName);
			if (in_array($moduleName, $oneOperation)) {
				$db->pquery('INSERT INTO vtiger_customerportal_relatedmoduleinfo(module,relatedmodules) VALUES(?,?)', array($tabid, json_encode($availableOneOperations)));
			} else if (in_array($moduleName, $threeOperations)) {
				$db->pquery('INSERT INTO vtiger_customerportal_relatedmoduleinfo(module,relatedmodules) VALUES(?,?)', array($tabid, json_encode($availableThreeOperations)));
			} else if (in_array($moduleName, $twoOperations)) {
				$db->pquery('INSERT INTO vtiger_customerportal_relatedmoduleinfo(module,relatedmodules) VALUES(?,?)', array($tabid, json_encode($availableTwoOperations)));
			} else if (in_array($moduleName, $fiveOperations)) {
				$db->pquery('INSERT INTO vtiger_customerportal_relatedmoduleinfo(module,relatedmodules) VALUES(?,?)', array($tabid, json_encode($availableFiveOperations)));
			}
		}
	}

	if (!Vtiger_Utils::CheckTable('vtiger_customerportal_settings')) {
		$db->pquery('CREATE TABLE vtiger_customerportal_settings(id int, url VARCHAR(250),default_assignee INT(11),
							support_notification INT(11), announcement TEXT, shortcuts TEXT,widgets TEXT,charts TEXT)', array());
		$availableModules = array('Documents' => array('LBL_ADD_DOCUMENT' => 1), 'HelpDesk' => array('LBL_CREATE_TICKET' => 1, 'LBL_OPEN_TICKETS' => 1));
		$availableWidgets = array('widgets' => array('HelpDesk' => 1, 'Documents' => 1, 'Faq' => 1));
		$availableCharts = array('charts' => array('OpenTicketsByPriority' => 1, 'TicketsClosureTimeByPriority' => 1));
		$encodedShortcuts = json_encode($availableModules);
		$encodedWidgets = json_encode($availableWidgets);
		$encodedCharts = json_encode($availableCharts);
		$db->pquery('INSERT INTO vtiger_customerportal_settings(id,default_assignee,shortcuts,widgets,charts) VALUES(?,?,?,?,?)', array(1, 1, $encodedShortcuts, $encodedWidgets, $encodedCharts));
	}

	$query = 'ALTER TABLE vtiger_portalinfo MODIFY user_password VARCHAR(255)';
	$db->pquery($query, array());

	//Enable mass edit for portal field under Contacts
	$moduleContacts = 'Contacts';
	$contactsFieldModel = Vtiger_Functions::getModuleFieldInfo(getTabid($moduleContacts), 'portal');
	$contactsFieldId = $contactsFieldModel['fieldid'];
	$contactsFieldInstance = Settings_LayoutEditor_Field_Model::getInstance($contactsFieldId);
	$contactsFieldInstance->set('masseditable', '1');
	$contactsFieldInstance->save();
	//Customer portal changes end

	 $relatedWebservicesOperations = array(
		array(
			'name' => 'relatedtypes',
			'path' => 'include/Webservices/RelatedTypes.php',
			'method' => 'vtws_relatedtypes',
			'type' => 'GET',
			'params' => array(
				array('name' => 'elementType', 'type' => 'string')
			)
		),
		array(
			'name' => 'retrieve_related',
			'path' => 'include/Webservices/RetrieveRelated.php',
			'method' => 'vtws_retrieve_related',
			'type' => 'GET',
			'params' => array(
				array('name' => 'id', 'type' => 'string'),
				array('name' => 'relatedType', 'type' => 'string'),
				array('name' => 'relatedLabel', 'type' => 'string')
			)
		),
		array(
			'name' => 'query_related',
			'path' => 'include/Webservices/QueryRelated.php',
			'method' => 'vtws_query_related',
			'type' => 'GET',
			'params' => array(
				array('name' => 'query', 'type' => 'string'),
				array('name' => 'id', 'type' => 'string'),
				array('name' => 'relatedLabel', 'type' => 'string')
			)
		)
	);
	foreach ($relatedWebservicesOperations as $operation) {
		$rs = $db->pquery('SELECT 1 FROM vtiger_ws_operation WHERE name=?', array($operation['name']));
		if (!$db->num_rows($rs)) {
			$operationId = vtws_addWebserviceOperation($operation['name'], $operation['path'], $operation['method'], $operation['type']);
			$sequence = 1;
			foreach ($operation['params'] as $param) {
				vtws_addWebserviceOperationParam($operationId, $param['name'], $param['type'], $sequence++);
			}
		}
	}
	//Change to modify shipping tax percent column type
	$db->pquery('ALTER TABLE vtiger_invoice MODIFY s_h_percent DECIMAL(25,8)', array());

	if (!Vtiger_Utils::CheckTable('vtiger_projecttask_status_color')) {
		$db->pquery('CREATE TABLE vtiger_projecttask_status_color (
									status varchar(255),
									defaultcolor varchar(50),
									color varchar(50),
									UNIQUE KEY status (status)) ENGINE=InnoDB DEFAULT CHARSET=utf8');
	}

	$statusColorMap = array(
				'Open'			=> '#0099ff',
				'In Progress'	=> '#fdff00',
				'Completed'		=> '#3BBF67',
				'Deferred'		=> '#fbb11e',
				'Canceled'		=> '#660066');

	foreach ($statusColorMap as $status => $color) {
		$db->pquery('INSERT INTO vtiger_projecttask_status_color(status,defaultcolor) VALUES(?,?) ON DUPLICATE KEY UPDATE defaultcolor=?', array($status, $color, $color));
	}

	//Increasing Lead Status column size to 200 for Leads module
	$db->pquery('ALTER TABLE vtiger_leaddetails MODIFY leadstatus VARCHAR(200)', array());

	//Start : Increase tablabel and setype size
	$db->pquery('ALTER TABLE vtiger_tab MODIFY tablabel VARCHAR(100)', array());
	$db->pquery('ALTER TABLE vtiger_crmentity MODIFY setype VARCHAR(100)', array());

	//Changing type of data for Used Units and Total Units fields of Service Contracts module to Decimal
	$fields = array('total_units', 'used_units');
	$serviceContractsModuleModel = Vtiger_Module_Model::getInstance('ServiceContracts');
	foreach ($fields as $field) {
		$fieldInstance = $serviceContractsModuleModel->getField($field);
		$typeOfData = 'NN~O';
		if ($fieldInstance->isMandatory()) {
			$typeOfData = 'NN~M';
		}
		$fieldInstance->set('typeofdata', $typeOfData);
		$fieldInstance->save();
	}

	//Creating new reminder block in calendar todo
	$moduleName = 'Calendar';
	$calendarInstance = Vtiger_Module_Model::getInstance($moduleName);
	$tabId = $calendarInstance->getId();

	//Updates sequence of blocks available in users module.
	Vtiger_Block_Model::pushDown('1', $tabId);

	if (!Vtiger_Block_Model::checkDuplicate('LBL_REMINDER_INFORMATION', $tabId)) {
		$reminderBlock = new Vtiger_Block();
		$reminderBlock->label = 'LBL_REMINDER_INFORMATION';
		$reminderBlock->sequence = 2;
		$calendarInstance->addBlock($reminderBlock);
	}

	//updating block and displaytype for send reminder field
	$reminderBlockInstance = Vtiger_Block_Model::getInstance('LBL_REMINDER_INFORMATION', $calendarInstance);
	$db->pquery('UPDATE vtiger_field SET block=?, displaytype=? WHERE tabid=? AND fieldname=?', array($reminderBlockInstance->id, '1', $tabId, 'reminder_time'));

	//adding new reminder template for todo
	$reminderTemplate = $db->pquery('SELECT 1 FROM vtiger_emailtemplates WHERE subject=? AND systemtemplate=?', array('Activity Reminder', '1'));
	if (!$db->num_rows($reminderTemplate)) {
		$body = '<p>'.vtranslate('LBL_REMINDER_NOTIFICATION', $moduleName).'<br/>' .
				vtranslate('LBL_DETAILS_STRING', $moduleName).' :<br/>
								&nbsp; '.vtranslate('Subject', $moduleName).' : $calendar-subject$<br/>
								&nbsp; '.vtranslate('Start Date & Time', $moduleName).' : $calendar-date_start$<br/>
								&nbsp; '.vtranslate('Due Date', $moduleName).' : $calendar-due_date$<br/>
								&nbsp; '.vtranslate('LBL_STATUS', $moduleName).' : $calendar-status$<br/>
								&nbsp; '.vtranslate('Location', $moduleName).' : $calendar-location$<br/>
								&nbsp; '.vtranslate('LBL_APP_DESCRIPTION', $moduleName).' : $calendar-description$<br/><br/>
								<p/>';
		$db->pquery('INSERT INTO vtiger_emailtemplates(foldername,templatename,subject,description,body,systemtemplate,templateid) values(?,?,?,?,?,?,?)', array('Public', 'ToDo Reminder', 'Activity Reminder', 'Reminder', $body, '1', $db->getUniqueID('vtiger_emailtemplates')));
	}

	$db->pquery('ALTER TABLE vtiger_webforms_field MODIFY COLUMN defaultvalue TEXT', array());

	$integrationBlock = $db->pquery('SELECT 1 FROM vtiger_settings_blocks WHERE label=?', array('LBL_OTHER_SETTINGS'));
	if (!$db->num_rows($integrationBlock)) {
		$blockid = $db->query_result($integrationBlock, 0, 'blockid');
		//To add a Field
		$fieldid = $db->getUniqueID('vtiger_settings_field');
		$db->pquery('INSERT INTO vtiger_settings_field(fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES(?,?,?,?,?,?,?,?)', array($fieldid, $blockid, 'LBL_MAILROOM', '', 'Mailroom', 'index.php?module=Mailroom&parent=Settings&view=List', 12, 0));
	}

	//Rollup Comments Settings table
	if (!Vtiger_Utils::CheckTable('vtiger_rollupcomments_settings')) {
		Vtiger_Utils::CreateTable('vtiger_rollupcomments_settings', 
				"(`rollupid` INT(19) NOT NULL AUTO_INCREMENT,
				`userid` INT(19) NOT NULL,
				`tabid` INT(19) NOT NULL,
				`rollup_status` INT(2) NOT NULL DEFAULT '0',
				PRIMARY KEY (`rollupid`))", true);
	}

	$modulesList = array('Products', 'Services');
	foreach ($modulesList as $moduleName) {
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$taxFieldModel = Vtiger_Field_Model::getInstance('taxclass', $moduleModel);
		$taxFieldModel->set('label', 'Taxes');
		$taxFieldModel->set('quickcreate', 2);
		$taxFieldModel->save();
	}

	$columns = $db->getColumnNames('com_vtiger_workflowtask_queue');
	if (!in_array('relatedinfo', $columns)) {
		$db->pquery('ALTER TABLE com_vtiger_workflowtask_queue ADD COLUMN relatedinfo VARCHAR(255)', array());
	}

	$db->pquery('ALTER TABLE vtiger_freetagged_objects MODIFY module VARCHAR(100)', array());
	$db->pquery('ALTER TABLE vtiger_emailslookup MODIFY setype VARCHAR(100)', array());
	$db->pquery('ALTER TABLE vtiger_entityname MODIFY modulename VARCHAR(100)', array());
	$db->pquery('ALTER TABLE vtiger_modentity_num MODIFY semodule VARCHAR(100)', array());
	$db->pquery('ALTER TABLE vtiger_reportmodules MODIFY primarymodule VARCHAR(100)', array());

	$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
	$ProjectModuleModel = Vtiger_Module_Model::getInstance('Project');
	$relationModel = Vtiger_Relation_Model::getInstance($ProjectModuleModel, $calendarModuleModel, 'Activities');

	if ($relationModel !== false) {
		$fieldModel = $calendarModuleModel->getField('parent_id');
		$fieldId = $fieldModel->getId();

		$projectTabId = getTabid('Project');
		$calendarTabId = getTabid('Calendar');
		$result = $db->pquery('SELECT fieldtypeid FROM vtiger_ws_fieldtype WHERE uitype=?', array($fieldModel->get('uitype')));
		$fieldType = $db->query_result($result, 0, 'fieldtypeid');

		$result = $db->pquery('SELECT 1 FROM vtiger_ws_referencetype WHERE fieldtypeid=? and type=?', array($fieldType, 'Project'));
		if (!$db->num_rows($result)) {
			$db->pquery('INSERT INTO vtiger_ws_referencetype(fieldtypeid,type) VALUES(?, ?)', array($fieldType, 'Project'));
		}

		if (!$relationModel->get('relationfieldid')) {
			$query = 'UPDATE vtiger_relatedlists SET relationfieldid=? ,name=?, relationtype=? WHERE tabid=? AND related_tabid=?';
			$db->pquery($query, array($fieldId, 'get_activities', '1:N', $projectTabId, $calendarTabId));
		}

		//Migrate data from vtiger_crmentityrel to vtiger_seactivityrel
		$query = 'SELECT 1 FROM vtiger_crmentityrel WHERE module=? AND relmodule= ?';
		$result = $db->pquery($query, array('Project', 'Calendar'));
		if ($db->num_rows($result)) {
			$insertQuery = 'INSERT INTO vtiger_seactivityrel(crmid, activityid) values(?,?)';
			while($data = $db->fetch_array($result)) {
				$db->pquery($insertQuery, array($data['crmid'], $data['relcrmid']));
			}
			$db->pquery('DELETE FROM vtiger_crmentityrel WHERE module=? AND relmodule= ?', array('Project', 'Calendar'));
		}
	}

	$db->pquery('ALTER TABLE vtiger_crmentityrel ADD INDEX crmid_idx(crmid)', array());
	$db->pquery('ALTER TABLE vtiger_crmentityrel ADD INDEX relcrmid_idx(relcrmid)', array());

	//Start : Inactivate update_log field from ticket module
	$fieldId = getFieldid(getTabid('HelpDesk'), 'update_log');
	$fieldModel = Vtiger_Field_Model::getInstance($fieldId);
	if ($fieldModel) {
		$fieldModel->set('presence', 1);
		$fieldModel->__update();
	}

	//Start : Project added as related tab for Potentials module.
	$projectModuleModel = Vtiger_Module_Model::getInstance('Project');
	$fieldModel = Vtiger_Field::getInstance('potentialid', $projectModuleModel);
	if ($fieldModel) {
		$fieldModel->setRelatedModules(array('Potentials'));
		$result = $db->pquery('SELECT 1 FROM vtiger_relatedlists where tabid=? AND relationfieldid=? AND related_tabid=?', array(getTabid('Potentials'), $fieldModel->id, getTabid('Project')));
		if (!($db->num_rows($result))) {
			$potentialModuleModel = Vtiger_Module_Model::getInstance('Potentials');
			$potentialModuleModel->setRelatedList($projectModuleModel, 'Projects', array('ADD', 'SELECT'), 'get_dependents_list', $fieldModel->id);
		}
	}
	//End

	//Start : Change fieldLabel of description field to Description - ProjectMilestone module.
	$fieldId = getFieldid(getTabid('ProjectMilestone'), 'description');
	$fieldModel = Vtiger_Field_Model::getInstance($fieldId);
	if ($fieldModel) {
		$fieldModel->set('label', 'Description');
		$fieldModel->__update();
	}
	//End

	$module = Vtiger_Module_Model::getInstance('Emails');
	$blocks = $module->getBlocks();
	$block = current($blocks);

	$field = new vtiger_field();
	$field->label = 'Click Count';
	$field->name = 'click_count';
	$field->table = 'vtiger_email_track';
	$field->column = 'click_count';
	$field->columntype = 'INT';
	$field->uitype = 25;
	$field->typeofdata = 'I~O';
	$field->displaytype = 3;
	$field->masseditable = 0;
	$field->quickcreate = 0;
	$field->defaultvalue = 0;
	$block->addfield($field);

	$criteria = ' MODIFY COLUMN click_count INT NOT NULL default 0';
	Vtiger_Utils::AlterTable('vtiger_email_track', $criteria);

	$em = new VTEventsManager($db);
	$em->registerHandler('vtiger.lead.convertlead', 'modules/Leads/handlers/LeadHandler.php', 'LeadHandler');

	Vtiger_Cache::flushModuleCache('Contacts');
	Vtiger_Cache::flushModuleCache('Leads');
	Vtiger_Cache::flushModuleCache('Emails');

	//Add create and edit to field to vtiger_customerportal_tabs to track Create and Edit permission of a module.
	$columns = $db->getColumnNames('vtiger_customerportal_tabs');
	if (!in_array('createrecord', $columns)) {
		$db->pquery('ALTER TABLE vtiger_customerportal_tabs ADD createrecord BOOLEAN NOT NULL DEFAULT FALSE', array());
	}
	if (!in_array('editrecord', $columns)) {
		$db->pquery('ALTER TABLE vtiger_customerportal_tabs ADD editrecord BOOLEAN NOT NULL DEFAULT FALSE', array());
	}

	//Update create and edit status for HelpDesk and Assets.
	$updateCreateEditStatusQuery = 'UPDATE vtiger_customerportal_tabs SET createrecord=?,editrecord=? WHERE tabid IN (?)';
	$db->pquery($updateCreateEditStatusQuery, array(1, 1, getTabid('HelpDesk')));
	$db->pquery($updateCreateEditStatusQuery, array(0, 1, getTabid('Contacts')));
	$db->pquery($updateCreateEditStatusQuery, array(0, 1, getTabid('Accounts')));
	$db->pquery($updateCreateEditStatusQuery, array(1, 0, getTabid('Documents')));
	$db->pquery($updateCreateEditStatusQuery, array(0, 1, getTabid('Assets')));

	$accessCountFieldId = getFieldid(getTabid('Emails'), 'access_count');
	$accessCountFieldModel = Vtiger_Field_Model::getInstance($accessCountFieldId);
	if ($accessCountFieldModel) {
		$accessCountFieldModel->set('typeofdata', 'I~O');
		$accessCountFieldModel->__update();
		Vtiger_Cache::flushModuleCache('Emails');
	}

	//Adding Create Event and Create Todo workflow tasks for Project module.
	$taskResult = $db->pquery('SELECT id, modules FROM com_vtiger_workflow_tasktypes WHERE tasktypename IN (?, ?)', array('VTCreateTodoTask', 'VTCreateEventTask'));
	$taskResultCount = $db->num_rows($taskResult);
	for ($i=0; $i<$taskResultCount; $i++) {
		$taskId = $db->query_result($taskResult, $i, 'id');
		$modules = Zend_Json::decode(decode_html($db->query_result($taskResult, $i, 'modules')));
		$modules['include'][] = 'Project';
		$modulesJson = Zend_Json::encode($modules);
		$db->pquery('UPDATE com_vtiger_workflow_tasktypes SET modules=? WHERE id=?', array($modulesJson, $taskId));
	}
	//End

	//Multiple attachment support for comments
	$db->pquery('ALTER TABLE vtiger_seattachmentsrel DROP PRIMARY KEY', array());
	$db->pquery('ALTER TABLE vtiger_seattachmentsrel ADD CONSTRAINT PRIMARY KEY (crmid,attachmentsid)', array());
	$db->pquery('ALTER TABLE vtiger_seattachmentsrel ADD CONSTRAINT fk_2_vtiger_seattachmentsrel FOREIGN KEY (crmid) REFERENCES vtiger_crmentity(crmid) ON DELETE CASCADE', array());
	$db->pquery('ALTER TABLE vtiger_project MODIFY COLUMN projectid INT(19) PRIMARY KEY');

	if (!Vtiger_Utils::CheckTable('vtiger_wsapp_logs_basic')) {
		Vtiger_Utils::CreateTable('vtiger_wsapp_logs_basic',
				'(`id` int(25) NOT NULL AUTO_INCREMENT,
				`extensiontabid` int(19) DEFAULT NULL,
				`module` varchar(50) NOT NULL,
				`sync_datetime` datetime NOT NULL,
				`app_create_count` int(11) DEFAULT NULL,
				`app_update_count` int(11) DEFAULT NULL,
				`app_delete_count` int(11) DEFAULT NULL,
				`app_skip_count` int(11) DEFAULT NULL,
				`vt_create_count` int(11) DEFAULT NULL,
				`vt_update_count` int(11) DEFAULT NULL,
				`vt_delete_count` int(11) DEFAULT NULL,
				`vt_skip_count` int(11) DEFAULT NULL,
				`userid` int(11) DEFAULT NULL,
				PRIMARY KEY (`id`))', true);
	}

	if (!Vtiger_Utils::CheckTable('vtiger_wsapp_logs_details')) {
		Vtiger_Utils::CreateTable('vtiger_wsapp_logs_details',
				'(`id` int(25) NOT NULL,
				`app_create_ids` mediumtext,
				`app_update_ids` mediumtext,
				`app_delete_ids` mediumtext,
				`app_skip_info` mediumtext,
				`vt_create_ids` mediumtext,
				`vt_update_ids` mediumtext,
				`vt_delete_ids` mediumtext,
				`vt_skip_info` mediumtext,
				KEY `vtiger_wsapp_logs_basic_ibfk_1` (`id`),
				CONSTRAINT `vtiger_wsapp_logs_basic_ibfk_1` FOREIGN KEY (`id`) REFERENCES `vtiger_wsapp_logs_basic` (`id`) ON DELETE CASCADE)', true);
	}

	if (!Vtiger_Utils::CheckTable('vtiger_cv2users')) {
		Vtiger_Utils::CreateTable('vtiger_cv2users', 
				'(`cvid` int(25) NOT NULL,
				`userid` int(25) NOT NULL,
				KEY `vtiger_cv2users_ibfk_1` (`cvid`),
				CONSTRAINT `vtiger_customview_ibfk_1` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE,
				CONSTRAINT `vtiger_users_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE)', true);
	}

	if (!Vtiger_Utils::CheckTable('vtiger_cv2group')) {
		Vtiger_Utils::CreateTable('vtiger_cv2group', 
				'(`cvid` int(25) NOT NULL,
				`groupid` int(25) NOT NULL,
				KEY `vtiger_cv2group_ibfk_1` (`cvid`),
				CONSTRAINT `vtiger_customview_ibfk_2` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE,
				CONSTRAINT `vtiger_groups_ibfk_1` FOREIGN KEY (`groupid`) REFERENCES `vtiger_groups` (`groupid`) ON DELETE CASCADE)', true);
	}

	if (!Vtiger_Utils::CheckTable('vtiger_cv2role')) {
		Vtiger_Utils::CreateTable('vtiger_cv2role',
				'(`cvid` int(25) NOT NULL,
				`roleid` varchar(255) NOT NULL,
				KEY `vtiger_cv2role_ibfk_1` (`cvid`),
				CONSTRAINT `vtiger_customview_ibfk_3` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE,
				CONSTRAINT `vtiger_role_ibfk_1` FOREIGN KEY (`roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE)', true);
	}

	if (!Vtiger_Utils::CheckTable('vtiger_cv2rs')) {
		Vtiger_Utils::CreateTable('vtiger_cv2rs',
				'(`cvid` int(25) NOT NULL,
				`rsid` varchar(255) NOT NULL,
				KEY `vtiger_cv2role_ibfk_1` (`cvid`),
				CONSTRAINT `vtiger_customview_ibfk_4` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE,
				CONSTRAINT `vtiger_rolesd_ibfk_1` FOREIGN KEY (`rsid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE)', true);
	}

	//Rollup Comments Settings table
	if (!Vtiger_Utils::CheckTable('vtiger_rollupcomments_settings')) {
		Vtiger_Utils::CreateTable('vtiger_rollupcomments_settings', 
				"(`rollupid` int(19) NOT NULL AUTO_INCREMENT,
				`userid` int(19) NOT NULL,
				`tabid` int(19) NOT NULL,
				`rollup_status` int(2) NOT NULL DEFAULT '0',
				PRIMARY KEY (`rollupid`))", true);
	}
	//END

	$transition_table_name = 'vtiger_picklist_transitions';
	if (!Vtiger_Utils::CheckTable($transition_table_name)) {
		Vtiger_Utils::CreateTable($transition_table_name,
				'(fieldname VARCHAR(255) NOT NULL PRIMARY KEY,
				module VARCHAR(100) NOT NULL,
				transition_data VARCHAR(1000) NOT NULL)', true);
	}

	$modCommentsInstance = Vtiger_Module::getInstance('ModComments');
	$blockInstance = Vtiger_Block::getInstance('LBL_MODCOMMENTS_INFORMATION', $modCommentsInstance);
	if ($blockInstance) {
		$fieldInstance = Vtiger_Field::getInstance('filename', $modCommentsInstance);
		if (!$fieldInstance) {
			$fieldInstance = new Vtiger_Field();
			$fieldInstance->name = 'filename';
			$fieldInstance->column = 'filename';
			$fieldInstance->label = 'Attachment';
			$fieldInstance->columntype = 'VARCHAR(255)';
			$fieldInstance->table = 'vtiger_modcomments';
			$fieldInstance->typeofdata = 'V~O';
			$fieldInstance->uitype = '61';
			$fieldInstance->presence = '0';
			$blockInstance->addField($fieldInstance);
		}
		unset($fieldInstance);

		$fieldInstance = Vtiger_Field::getInstance('related_email_id', $modCommentsInstance);
		if (!$fieldInstance) {
			$fieldInstance = new Vtiger_Field();
			$fieldInstance->name = 'related_email_id';
			$fieldInstance->label = 'Related Email Id';
			$fieldInstance->uitype = 1;
			$fieldInstance->column = $fieldInstance->name;
			$fieldInstance->columntype = 'INT(11)';
			$fieldInstance->typeofdata = 'I~O';
			$fieldInstance->defaultvalue = 0;
			$blockInstance->addField($fieldInstance);
		}
		unset($fieldInstance);
	}

	//Adding user specific field to Calendar table instead of events table
	$db->pquery('UPDATE vtiger_field SET tablename=? WHERE tablename=?', array('vtiger_calendar_user_field', 'vtiger_events_user_field'));

	//Invite users table mod to support status tracking
	$columns = $db->getColumnNames('vtiger_invitees');
	if (!in_array('status', $columns)) {
		$db->pquery('ALTER TABLE vtiger_invitees ADD COLUMN status VARCHAR(50) DEFAULT NULL', array());
	}

	$columns = $db->getColumnNames('vtiger_customerportal_relatedmoduleinfo');
	if (!in_array('module', $columns)) {
		$db->pquery('ALTER TABLE vtiger_customerportal_relatedmoduleinfo CHANGE module tabid INT', array());
		$db->pquery('ALTER TABLE vtiger_customerportal_relatedmoduleinfo ADD PRIMARY KEY(tabid)', array());
		$db->pquery('ALTER TABLE vtiger_customerportal_fields ADD PRIMARY KEY(tabid)', array());
	}

	$ignoreModules = array('SMSNotifier', 'ModComments');
	$modules = array();
	$result = $db->pquery('SELECT name FROM vtiger_tab WHERE isentitytype=?', array(1));
	while ($row = $db->fetchByAssoc($result)) {
		$modules[] = $row['name'];
	}

	foreach ($modules as $module) {
		if (in_array($module, $ignoreModules)) {
			continue;
		}
		$moduleUserSpecificTable = Vtiger_Functions::getUserSpecificTableName($module);
		if (!Vtiger_Utils::CheckTable($moduleUserSpecificTable)) {
			Vtiger_Utils::CreateTable($moduleUserSpecificTable,
					'(`recordid` INT(25) NOT NULL,
					`userid` INT(25) NOT NULL)', true);
		}
		$moduleInstance = Vtiger_Module::getInstance($module);
		if ($moduleInstance) {
			$fieldInstance = Vtiger_Field::getInstance('starred', $moduleInstance);
			if ($fieldInstance) {
				continue;
			}
			$blockQuery = 'SELECT blocklabel FROM vtiger_blocks WHERE tabid=? ORDER BY sequence LIMIT 1';
			$result = $db->pquery($blockQuery, array($moduleInstance->id, 1));
			$block = $db->query_result($result, 0, 'blocklabel');
			if ($block) {
				$blockInstance = Vtiger_Block::getInstance($block, $moduleInstance);
				if ($blockInstance) {
					$field = new Vtiger_Field();
					$field->name = 'starred';
					$field->label = 'starred';
					$field->table = $moduleUserSpecificTable;
					$field->presence = 2;
					$field->displaytype = 6;
					$field->readonly = 1;
					$field->uitype = 56;
					$field->typeofdata = 'C~O';
					$field->quickcreate = 3;
					$field->masseditable = 0;
					$blockInstance->addField($field);
				}
			}
		}
	}
	//User specific field - star feature 

	$ignoreModules[] = 'Webmails';
	foreach ($modules as $module) {
		if (in_array($module, $ignoreModules)) {
			continue;
		}
		$moduleInstance = Vtiger_Module::getInstance($module);
		if ($moduleInstance) {
			$fieldInstance = Vtiger_Field::getInstance('tags', $moduleInstance);
			if ($fieldInstance) {
				continue;
			}
			$focus = CRMEntity::getInstance($module);
			$tableName = $focus->table_name;

			$blockQuery = 'SELECT blocklabel FROM vtiger_blocks WHERE tabid=? ORDER BY sequence LIMIT 1';
			$result = $db->pquery($blockQuery, array($moduleInstance->id, 1));
			$block = $db->query_result($result, 0, 'blocklabel');
			if ($block) {
				$blockInstance = Vtiger_Block::getInstance($block, $moduleInstance);
				if ($blockInstance) {
					$field = new Vtiger_Field();
					$field->name = 'tags';
					$field->label = 'tags';
					$field->table = $tableName;
					$field->presence = 2;
					$field->displaytype = 6;
					$field->readonly = 1;
					$field->uitype = 1;
					$field->typeofdata = 'V~O';
					$field->columntype = 'varchar(1)';
					$field->quickcreate = 3;
					$field->masseditable = 0;
					$blockInstance->addField($field);
				}
			}
		}
	}

	//Add column to track public and private for tags
	$columns = $db->getColumnNames('vtiger_freetags');
	if (!in_array('visibility', $columns)) {
		$db->pquery("ALTER TABLE vtiger_freetags ADD column visibility VARCHAR(100) NOT NULL DEFAULT 'PRIVATE'", array());
	}
	if (!in_array('owner', $columns)) {
		$db->pquery('ALTER TABLE vtiger_freetags ADD column owner INT(19) NOT NULL', array());
	}

	//remove ON update field property for tagged_on since below script will update details but we dont want to change time stamp 
	//and we did not find any test case where we will update tagged object
	$db->pquery('ALTER TABLE vtiger_freetagged_objects MODIFY tagged_on timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP', array());

	$query = 'SELECT DISTINCT tagger_id,tag_id,tag FROM vtiger_freetagged_objects INNER JOIN vtiger_freetags ON vtiger_freetagged_objects.tag_id = vtiger_freetags.id';
	$result = $db->pquery($query, array());
	$num_rows = $db->num_rows($result);

	if ($num_rows > 0) {
		$tagOwners = array();
		$tagNamesList = array();
		$visibility = Vtiger_Tag_Model::PRIVATE_TYPE;
		for ($i=0; $i<$num_rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$tagId = $row['tag_id'];
			$tagOwners[$tagId][] = $row['tagger_id'];
			$tagNamesList[$tagId] = $row['tag'];
		}
		foreach ($tagOwners as $tagId => $ownerList) {
			$tagName = $tagNamesList[$tagId];
			foreach ($ownerList as $index => $ownerId) {
				//for frist user dont have create seperate tag.for rest of the users we need to create
				if ($index != 0) {
					//creating new Tag
					$newTagId = $db->getUniqueId('vtiger_freetags');
					$db->pquery('INSERT INTO vtiger_freetags values(?,?,?,?,?)', array($newTagId, $tagName, $tagName, $visibility, $ownerId));

					//update all existing record tags to new tags 
					$db->pquery('UPDATE vtiger_freetagged_objects SET tag_id=? WHERE tag_id=? and tagger_id=?', array($newTagId, $tagId, $ownerId));
				} else {
					//update owner column for tag 
					$db->pquery('UPDATE vtiger_freetags SET owner=? WHERE id=?', array($ownerId, $tagId));
				}
			}
		}
	}

	//Adding color column for picklists
	$fieldResult = $db->pquery('SELECT fieldname FROM vtiger_field WHERE uitype IN (?,?,?,?) AND tabid NOT IN (?)', array('15', '16', '33', '114', getTabid('Users')));
	$fieldRows = $db->num_rows($fieldResult);
	$ignorePickListFields = array('hdnTaxType', 'email_flag');

	for ($i=0; $i<$fieldRows; $i++) {
		$fieldName = $db->query_result($fieldResult, $i, 'fieldname');
		if (in_array($fieldName, $ignorePickListFields) || !Vtiger_Utils::CheckTable("vtiger_$fieldName"))
			continue;

		//Add column in vtiger_tab which will hold source 
		$columns = $db->getColumnNames("vtiger_$fieldName");
		if (!in_array('color', $columns)) {
			$db->pquery("ALTER TABLE vtiger_$fieldName ADD COLUMN color VARCHAR(10)", array());
		}
	}

	//Removing color for users module
	$fieldResult = $db->pquery('SELECT fieldname FROM vtiger_field WHERE uitype IN (?,?,?,?) AND tabid IN (?)', array('15', '16', '33', '114', getTabid('Users')));
	$fieldRows = $db->num_rows($fieldResult);

	for ($i=0; $i<$fieldRows; $i++) {
		$fieldName = $db->query_result($fieldResult, $i, 'fieldname');
		if (!Vtiger_Utils::CheckTable("vtiger_$fieldName"))
			continue;

		//Drop color column
		$columns = $db->getColumnNames("vtiger_$fieldName");
		if (in_array('color', $columns)) {
			$db->pquery("ALTER TABLE vtiger_$fieldName DROP COLUMN color", array());
		}
	}

	//Dashboard Widgets
	if (!Vtiger_Utils::CheckTable('vtiger_dashboard_tabs')) {
		Vtiger_Utils::CreateTable('vtiger_dashboard_tabs', 
				'(id int(19) primary key auto_increment,
				tabname VARCHAR(50),
				isdefault INT(1) DEFAULT 0,
				sequence INT(5) DEFAULT 2,
				appname VARCHAR(20),
				modulename VARCHAR(50),
				userid int(11),
				UNIQUE KEY(tabname,userid),
				FOREIGN KEY (userid) REFERENCES vtiger_users(id) ON DELETE CASCADE)', true);
	}

	$users = Users_Record_Model::getAll();
	$userIds = array_keys($users);
	$defaultTabQuery = 'INSERT INTO vtiger_dashboard_tabs(tabname,userid) VALUES(?,?) ON DUPLICATE KEY UPDATE tabname=?, userid=?';
	foreach ($userIds as $userId) {
		$db->pquery($defaultTabQuery, array('Default', $userId, 'Default', $userId));
	}

	$columns = $db->getColumnNames('vtiger_module_dashboard_widgets');
	if (!in_array('reportid', $columns)) {
		$db->pquery('ALTER TABLE vtiger_module_dashboard_widgets ADD COLUMN reportid INT(19) DEFAULT NULL', array());
	}
	if (!in_array('dashboardtabid', $columns)) {
		$result = $db->pquery('SELECT id FROM vtiger_dashboard_tabs WHERE userid=? AND tabname=?', array(1, 'Default'));
		$defaultTabid = $db->query_result($result, 0, 'id');
		//Setting admin user default tabid to DEFAULT
		$db->pquery("ALTER TABLE vtiger_module_dashboard_widgets ADD COLUMN dashboardtabid INT(11) DEFAULT $defaultTabid", array());

		//TODO : this will fail if there are any entries to vtiger_module_dashboard_widgets
		$db->pquery('ALTER TABLE vtiger_module_dashboard_widgets ADD CONSTRAINT FOREIGN KEY (dashboardtabid) REFERENCES vtiger_dashboard_tabs(id) ON DELETE CASCADE', array());
	}
	//End

	$result = $db->pquery('SELECT * FROM vtiger_module_dashboard_widgets', array());
	$num_rows = $db->num_rows($result);
	for ($i=0; $i<$num_rows; $i++) {
		$rowdata = $db->query_result_rowdata($result, $i);
		if ($rowdata['dashboardtabid'] == null) {
			$result1 = $db->pquery('SELECT id FROM vtiger_dashboard_tabs WHERE userid=? AND tabname=?', array($rowdata['userid'], 'My Dashboard'));
			if ($db->num_rows($result1) > 0) {
				$tabid = $db->query_result($result1, 0, 'id');
				$db->pquery('UPDATE vtiger_module_dashboard_widgets SET dashboardtabid=? WHERE id=? AND userid=?', array($tabid, $rowdata['id'], $rowdata['userid']));
			}
		}
	}

	//Workflows
	$columns = $db->getColumnNames('com_vtiger_workflows');
	if (in_array('status', $columns)) {
		$db->pquery('ALTER TABLE com_vtiger_workflows ADD COLUMN status SET DEFAULT 1', array());
	}
	$db->pquery('UPDATE com_vtiger_workflows SET status=? WHERE status IS NULL', array(1));

	if (!in_array('workflowname', $columns)) {
		$db->pquery('ALTER TABLE com_vtiger_workflows ADD COLUMN workflowname VARCHAR(100)', array());
	}
	$db->pquery('UPDATE com_vtiger_workflows SET workflowname = summary', array());
	//End

	//Adding color column for vtiger_salutationtype.
	$fieldResult = $db->pquery('SELECT fieldname FROM vtiger_field WHERE fieldname=? AND tabid NOT IN (?)', array('salutationtype', getTabid('Users')));
	$fieldRows = $db->num_rows($fieldResult);

	for ($i=0; $i<$fieldRows; $i++) {
		$fieldName = $db->query_result($fieldResult, $i, 'fieldname');
		if (!Vtiger_Utils::CheckTable("vtiger_$fieldName")) {
			continue;
		}

		//Add column in vtiger_tab which will hold source 
		$columns = $db->getColumnNames("vtiger_$fieldName");
		if (!in_array('color', $columns)) {
			$db->pquery("ALTER TABLE vtiger_$fieldName ADD COLUMN color VARCHAR(10)", array());
		}
	}

	//Adding Agenda view in default my calendar view settings
	$usersModuleModel = Vtiger_Module_Model::getInstance('Users');
	$activityViewFieldModel = Vtiger_Field_Model::getInstance('activity_view', $usersModuleModel);

	$existingActivityViewTypes = $activityViewFieldModel->getPicklistValues();
	$newActivityView = 'Agenda';
	if (!in_array($newActivityView, $existingActivityViewTypes)) {
		$activityViewFieldModel->setPicklistValues(array($newActivityView));
	}

	//deleting orphan picklist fields that were delete from vtiger_field table but not from vtiger_role2picklist table
	$deletedPicklistResult = $db->pquery('SELECT DISTINCT(picklistid) AS picklistid FROM vtiger_role2picklist 
								WHERE picklistid NOT IN (SELECT vtiger_picklist.picklistid FROM vtiger_picklist
										INNER JOIN vtiger_role2picklist ON vtiger_role2picklist.picklistid = vtiger_picklist.picklistid)', array());
	$rows = $db->num_rows($deletedPicklistResult);
	$deletablePicklists = array();
	for ($i=0; $i<$rows; $i++) {
		$deletablePicklists[] = $db->query_result($deletedPicklistResult, $i, 'picklistid');
	}
	if (count($deletablePicklists)) {
		$db->pquery('DELETE FROM vtiger_role2picklist WHERE picklistid IN ('.generateQuestionMarks($deletablePicklists).')', array($deletablePicklists));
	}

	//table name exceeds more than 50 characters.
	$db->pquery('ALTER TABLE vtiger_field MODIFY COLUMN tablename VARCHAR(100)', array());

	if (!Vtiger_Utils::CheckTable('vtiger_report_shareusers')) {
		Vtiger_Utils::CreateTable('vtiger_report_shareusers',
				'(`reportid` int(25) NOT NULL,
				`userid` int(25) NOT NULL,
				KEY `vtiger_report_shareusers_ibfk_1` (`reportid`),
				CONSTRAINT `vtiger_reports_reportid_ibfk_1` FOREIGN KEY (`reportid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE,
				CONSTRAINT `vtiger_users_userid_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE)', true);
	}

	if (!Vtiger_Utils::CheckTable('vtiger_report_sharegroups')) {
		Vtiger_Utils::CreateTable('vtiger_report_sharegroups', 
				'(`reportid` int(25) NOT NULL,
				`groupid` int(25) NOT NULL,
				KEY `vtiger_report_sharegroups_ibfk_1` (`reportid`),
				CONSTRAINT `vtiger_report_reportid_ibfk_2` FOREIGN KEY (`reportid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE,
				CONSTRAINT `vtiger_groups_groupid_ibfk_1` FOREIGN KEY (`groupid`) REFERENCES `vtiger_groups` (`groupid`) ON DELETE CASCADE)', true);
	}

	if (!Vtiger_Utils::CheckTable('vtiger_report_sharerole')) {
		Vtiger_Utils::CreateTable('vtiger_report_sharerole',
				'(`reportid` int(25) NOT NULL,
				`roleid` varchar(255) NOT NULL,
				KEY `vtiger_report_sharerole_ibfk_1` (`reportid`),
				CONSTRAINT `vtiger_report_reportid_ibfk_3` FOREIGN KEY (`reportid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE,
				CONSTRAINT `vtiger_role_roleid_ibfk_1` FOREIGN KEY (`roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE)', true);
	}

	if (!Vtiger_Utils::CheckTable('vtiger_report_sharers')) {
		Vtiger_Utils::CreateTable('vtiger_report_sharers',
				'(`reportid` int(25) NOT NULL,
				`rsid` varchar(255) NOT NULL,
				KEY `vtiger_report_sharers_ibfk_1` (`reportid`),
				CONSTRAINT `vtiger_report_reportid_ibfk_4` FOREIGN KEY (`reportid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE,
				CONSTRAINT `vtiger_rolesd_rsid_ibfk_1` FOREIGN KEY (`rsid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE)', true);
	}

	//Migrating existing relations to N:N or 1:N based on relation fieldid
	$query = "UPDATE vtiger_relatedlists SET relationtype='N:N' WHERE relationfieldid IS NULL";
	$result = $db->pquery($query, array());

	$query = "UPDATE vtiger_relatedlists SET relationtype='1:N' WHERE relationfieldid IS NOT NULL";
	$result = $db->pquery($query, array());

	// For Google Synchronization
	Vtiger_Link::addLink(getTabid('Contacts'), 'EXTENSIONLINK', 'Google', 'index.php?module=Contacts&view=Extension&extensionModule=Google&extensionView=Index');
	Vtiger_Link::addLink(getTabid('Calendar'), 'EXTENSIONLINK', 'Google', 'index.php?module=Calendar&view=Extension&extensionModule=Google&extensionView=Index');
	
	//Add enabled column in vtiger_google_sync_settings
	$colums = $db->getColumnNames('vtiger_google_sync_settings');
	if (!in_array('enabled', $colums)) {
		$query = 'ALTER TABLE vtiger_google_sync_settings ADD COLUMN enabled TINYINT(3) DEFAULT 1';
		$db->pquery($query, array());
	}

	$result = $db->pquery('UPDATE vtiger_tab SET parent=NULL WHERE name=?', array('ExtensionStore'));

	//Start: Tax Enhancements - Compound Taxes, Regional Taxes, Deducted Taxes, Other Charges
	//Creating regions table
	if (!Vtiger_Utils::checkTable('vtiger_taxregions')) {
		$db->pquery('CREATE TABLE vtiger_taxregions(regionid INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL)', array());
	}

	if (!Vtiger_Utils::checkTable('vtiger_inventorycharges')) {
		//Creating inventory charges table
		$sql = 'CREATE TABLE vtiger_inventorycharges(
					chargeid INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(100) NOT NULL,
					format VARCHAR(10),
					type VARCHAR(10),
					value DECIMAL(12,5),
					regions TEXT,
					istaxable INT(1) NOT NULL DEFAULT 1,
					taxes VARCHAR(1024),
					deleted INT(1) NOT NULL DEFAULT 0
				)';
		$db->pquery($sql, array());

		$taxIdsList = array();
		$result = $db->pquery('SELECT taxid FROM vtiger_shippingtaxinfo', array());
		while ($rowData = $db->fetch_array($result)) {
			$taxIdsList[] = $rowData['taxid'];
		}

		$db->pquery('INSERT INTO vtiger_inventorycharges VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)', array(1, 'Shipping & Handling', 'Flat', 'Fixed', '', '[]', 1, ZEND_JSON::encode($taxIdsList), 0));
	}

	if (!Vtiger_Utils::checkTable('vtiger_inventorychargesrel')) {
		//Creating inventory charges relation table
		$db->pquery('CREATE TABLE vtiger_inventorychargesrel(recordid INT(19) NOT NULL, charges TEXT)', array());

		$shippingTaxNamesList = array();
		$result = $db->pquery('SELECT taxid, taxname FROM vtiger_shippingtaxinfo', array());
		while ($rowData = $db->fetch_array($result)) {
			$shippingTaxNamesList[$rowData['taxid']] = $rowData['taxname'];
		}

		$tablesList = array('quoteid' => 'vtiger_quotes', 'purchaseorderid' => 'vtiger_purchaseorder', 'salesorderid' => 'vtiger_salesorder', 'invoiceid' => 'vtiger_invoice');

		$query = 'INSERT INTO vtiger_inventorychargesrel VALUES';
		foreach ($tablesList as $index => $tableName) {
			$sql = "SELECT vtiger_inventoryshippingrel.*, s_h_amount FROM vtiger_inventoryshippingrel
			INNER JOIN $tableName ON $tableName.$index = vtiger_inventoryshippingrel.id";

			$result = $db->pquery($sql, array());
			while ($rowData = $db->fetch_array($result)) {
				$recordId = $rowData['id'];

				$taxesList = array();
				foreach ($shippingTaxNamesList as $taxId => $taxName) {
					$taxesList[$taxId] = $rowData[$taxName];
				}

				$query .= "($recordId, '".Zend_Json::encode(array(1 => array('value' => $rowData['s_h_amount'], 'taxes' => $taxesList)))."'), ";
			}
		}
		$db->pquery(rtrim($query, ', '), array());
	}

	//Updating existing tax tables
	$taxTablesList = array('vtiger_inventorytaxinfo', 'vtiger_shippingtaxinfo');
	foreach ($taxTablesList as $taxTable) {
		$sql = "ALTER TABLE $taxTable ADD (
					method VARCHAR(10),
					type VARCHAR(10),
					compoundon VARCHAR(400),
					regions TEXT
				)";
		$db->pquery($sql, array());

		$db->pquery("UPDATE $taxTable SET method =?, type=?, compoundon=?, regions=?", array('Simple', 'Fixed', '[]', '[]'));
	}

	//Updating existing tax tables
	$db->pquery('ALTER TABLE vtiger_producttaxrel ADD regions TEXT', array());
	$db->pquery('UPDATE vtiger_producttaxrel SET regions=?', array('[]'));

	$modulesList = array('Quotes' => 'vtiger_quotes', 'PurchaseOrder' => 'vtiger_purchaseorder', 'SalesOrder' => 'vtiger_salesorder', 'Invoice' => 'vtiger_invoice');
	$fieldName = 'region_id';

	foreach ($modulesList as $moduleName => $tableName) {
		//Updating existing inventory tax tables
		$db->pquery('ALTER TABLE '.$tableName.' ADD compound_taxes_info TEXT', array());
		$db->pquery('UPDATE '.$tableName.' SET compound_taxes_info=?', array('[]'));

		//creating new field in entity tables
		$moduleInstance = Vtiger_Module::getInstance($moduleName);
		$blockInstance = Vtiger_Block::getInstance('LBL_ITEM_DETAILS', $moduleInstance);

		$fieldInstance = Vtiger_Field::getInstance($fieldName, $moduleInstance);
		if (!$fieldInstance) {
			$fieldInstance = new Vtiger_Field();

			$fieldInstance->name = $fieldName;
			$fieldInstance->column = $fieldName;
			$fieldInstance->table = $tableName;
			$fieldInstance->label = 'Tax Region';
			$fieldInstance->columntype = 'int(19)';
			$fieldInstance->typeofdata = 'N~O';
			$fieldInstance->uitype = '16';
			$fieldInstance->readonly = '0';
			$fieldInstance->displaytype = '5';
			$fieldInstance->masseditable = '0';

			$blockInstance->addField($fieldInstance);
		}
	}
	//End: Tax Enhancements - Compound Taxes, Regional Taxes, Deducted Taxes, Other Charges

	if (!Vtiger_Utils::CheckTable('vtiger_app2tab')) {
		Vtiger_Utils::CreateTable('vtiger_app2tab', "(
			`tabid` INT(11) DEFAULT NULL,
			`appname` VARCHAR(20) DEFAULT NULL,
			`sequence` INT(11) DEFAULT NULL,
			`visible` TINYINT(3) DEFAULT '1',
			CONSTRAINT `vtiger_app2tab_fk_tab` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
			)", true);
	}

	$restrictedModules = array('ModComments');
	$appsList = array(	'SALES'		=> array('Potentials', 'Quotes', 'Contacts', 'Accounts'),
						'PROJECT'	=> array('Project', 'ProjectTask', 'ProjectMilestone', 'Contacts', 'Accounts'));

	$menuModelsList = Vtiger_Module_Model::getEntityModules();
	$menuStructure = Vtiger_MenuStructure_Model::getInstanceFromMenuList($menuModelsList);
	$menuGroupedByParent = $menuStructure->getMenuGroupedByParent();
	$menuGroupedByParent = $menuStructure->regroupMenuByParent($menuGroupedByParent);
	foreach ($menuGroupedByParent as $app => $appModules) {
		$modules = array();
		if ($appsList[$app]) {
			$modules = $appsList[$app];
		}
		foreach ($appModules as $moduleName => $moduleModel) {
			if (!in_array($moduleName, $modules)) {
				$modules[] = $moduleName;
			}
		}
		foreach ($modules as $moduleName) {
			if (!in_array($moduleName, $restrictedModules)) {
				Settings_MenuEditor_Module_Model::addModuleToApp($moduleName, $app);
			}
		}
	}

	$tabIdResult = $db->pquery('SELECT tabid FROM vtiger_app2tab WHERE appname=? AND tabid=?', array('SALES', getTabid('SMSNotifier')));
	$existingTabId = $db->query_result($tabIdResult, 0, 'tabid');
	if (!$existingTabId) {
		$seqResult = $db->pquery('SELECT max(sequence) as sequence FROM vtiger_app2tab WHERE appname=?', array('SALES'));
		$sequence = $db->query_result($seqResult, 0, 'sequence');
		$db->pquery('INSERT INTO vtiger_app2tab(tabid,appname,sequence,visible) values(?,?,?,?)', array(getTabid('SMSNotifier'), 'SALES', $sequence+11, 1));
	}

	$tabIdResult = $db->pquery('SELECT tabid FROM vtiger_app2tab WHERE appname=? AND tabid=?', array('SUPPORT', getTabid('SMSNotifier')));
	$existingTabId = $db->query_result($tabIdResult, 0, 'tabid');
	if (!$existingTabId) {
		$seqResult = $db->pquery('SELECT max(sequence) as sequence FROM vtiger_app2tab WHERE appname=?', array('SUPPORT'));
		$sequence = $db->query_result($seqResult, 0, 'sequence');
		$db->pquery('INSERT INTO vtiger_app2tab(tabid,appname,sequence,visible) values(?,?,?,?)', array(getTabid('SMSNotifier'), 'SUPPORT', $sequence+11, 1));
	}

	$result = $db->pquery('SELECT tabid,name FROM vtiger_tab', array());
	$moduleTabIds = array();
	while ($row = $db->fetchByAssoc($result)) {
		$moduleName = $row['name'];
		$moduleTabIds[$moduleName] = $row['tabid'];
	}

	$defSequenceList = array(
			'MARKETING'	=> array(	$moduleTabIds['Campaigns'],
									$moduleTabIds['Leads'],
									$moduleTabIds['Contacts'],
									$moduleTabIds['Accounts'],
			),
			'SALES'		=> array(	$moduleTabIds['Potentials'],
									$moduleTabIds['Quotes'],
									$moduleTabIds['Invoice'],
									$moduleTabIds['Products'],
									$moduleTabIds['Services'],
									$moduleTabIds['SMSNotifier'],
									$moduleTabIds['Contacts'],
									$moduleTabIds['Accounts']
			),
			'SUPPORT'	=> array(	$moduleTabIds['Faq'],
									$moduleTabIds['ServiceContracts'],
									$moduleTabIds['Assets'],
									$moduleTabIds['SMSNotifier'],
									$moduleTabIds['Contacts'],
									$moduleTabIds['Accounts']
			),
			'INVENTORY'	=> array(	$moduleTabIds['Products'],
									$moduleTabIds['Services'],
									$moduleTabIds['PriceBooks'],
									$moduleTabIds['Invoice'],
									$moduleTabIds['SalesOrder'],
									$moduleTabIds['PurchaseOrder'],
									$moduleTabIds['Vendors'],
									$moduleTabIds['Contacts'],
									$moduleTabIds['Accounts']
			),
			'PROJECT'	=> array(	$moduleTabIds['Project'],
									$moduleTabIds['ProjectTask'],
									$moduleTabIds['ProjectMilestone'],
									$moduleTabIds['Contacts'],
									$moduleTabIds['Accounts']
			)
	);

	foreach ($defSequenceList as $app => $sequence) {
		foreach ($sequence as $seq => $moduleTabId) {
			$params = array($moduleTabId, $app, $seq+1);
			$db->pquery('UPDATE vtiger_app2tab SET sequence=? WHERE appname =? AND tabid=?', $params);
		}
	}

	$leadsModuleInstance = Vtiger_Module::getInstance('Leads');
	$quotesModuleInstance = Vtiger_Module::getInstance('Quotes');
	$leadsModuleInstance->unsetRelatedList($quotesModuleInstance, 'Quotes', 'get_quotes');

	$leadsTabId = getTabid('Leads');
	$quotesTabId = getTabid('Quotes');
	$query = 'SELECT 1 FROM vtiger_relatedlists WHERE tabid=? AND related_tabid =? AND name=? AND label=?';
	$params = array($leadsTabId, $quotesTabId, 'get_quotes', 'Quotes');
	$result = $db->pquery($query, $params);
	if ($db->num_rows($result)) {
		$menuEditorModuleModel = new Settings_MenuEditor_Module_Model();
		$menuEditorModuleModel->addModuleToApp('Quotes', 'MARKETING');
	}

	$db->pquery('ALTER TABLE vtiger_cvstdfilter DROP FOREIGN KEY fk_1_vtiger_cvstdfilter', array());
	$db->pquery('ALTER TABLE vtiger_cvstdfilter DROP PRIMARY KEY', array());
	$db->pquery('ALTER TABLE vtiger_cvstdfilter DROP KEY cvstdfilter_cvid_idx', array());
	$keyResult = $db->pquery("show index from vtiger_cvstdfilter where key_name ='fk_1_vtiger_cvstdfilter'", array());
	if ($db->num_rows($keyResult) <= 0) {
		$db->pquery('ALTER TABLE vtiger_cvstdfilter ADD CONSTRAINT fk_1_vtiger_cvstdfilter FOREIGN KEY (cvid) REFERENCES vtiger_customview(cvid) ON DELETE CASCADE', array());
	}

	$keyResult = $db->pquery("show index from vtiger_app2tab where key_name ='vtiger_app2tab_fk_tab'", array());
	if ($db->num_rows($keyResult) <= 0) {
		$db->pquery('ALTER TABLE vtiger_app2tab ADD CONSTRAINT vtiger_app2tab_fk_tab FOREIGN KEY(tabid) REFERENCES vtiger_tab(tabid) ON DELETE CASCADE', array());
	}

	if (!Vtiger_Utils::CheckTable('vtiger_convertpotentialmapping')) {
		Vtiger_Utils::CreateTable('vtiger_convertpotentialmapping',
				"(`cfmid` int(19) NOT NULL AUTO_INCREMENT,
				`potentialfid` int(19) NOT NULL,
				`projectfid` int(19) DEFAULT NULL,
				`editable` int(11) DEFAULT '1',
				PRIMARY KEY (`cfmid`)
				)", true);
		$fieldMap = array(
			array('potentialname', 'projectname', 0),
			array('description', 'description', 1),
		);

		$potentialTab = getTabid('Potentials');
		$projectTab = getTabid('Project');
		$mapSql = 'INSERT INTO vtiger_convertpotentialmapping(potentialfid, projectfid, editable) values(?,?,?)';

		foreach ($fieldMap as $values) {
			$potentialfid = getFieldid($potentialTab, $values[0]);
			$projectfid = getFieldid($projectTab, $values[1]);
			$editable = $values[4];
			$db->pquery($mapSql, array($potentialfid, $projectfid, $editable));
		}
	}

	$db->pquery('ALTER TABLE vtiger_potential ADD converted INT(1) NOT NULL DEFAULT 0', array());

	$Vtiger_Utils_Log = true;
	$moduleArray = array('Project' => 'LBL_PROJECT_INFORMATION');
	foreach ($moduleArray as $module => $block) {
		$moduleInstance = Vtiger_Module::getInstance($module);
		$blockInstance = Vtiger_Block::getInstance($block, $moduleInstance);

		$field = Vtiger_Field::getInstance('isconvertedfrompotential', $moduleInstance);
		if (!$field) {
			$field = new Vtiger_Field();
			$field->name = 'isconvertedfrompotential';
			$field->label = 'Is Converted From Opportunity';
			$field->uitype = 56;
			$field->column = 'isconvertedfrompotential';
			$field->displaytype = 2;
			$field->columntype = 'int(1) NOT NULL DEFAULT 0';
			$field->typeofdata = 'C~O';
			$blockInstance->addField($field);
		}
	}

	$projectInstance = Vtiger_Module::getInstance('Project');
	$calendarModule = Vtiger_Module::getInstance('Calendar');
	$projectInstance->setRelatedList($calendarModule, 'Activities', Array('ADD'));

	$quotesModule = Vtiger_Module::getInstance('Quotes');
	$projectInstance->setRelatedList($quotesModule, 'Quotes', Array('SELECT'));

	if (!Vtiger_Field::getInstance('potentialid', $projectInstance)) {
		$blockInstance = Vtiger_Block_Model::getInstance('LBL_PROJECT_INFORMATION', $projectInstance);
		$potentialField = new Vtiger_Field();
		$potentialField->name = 'potentialid';
		$potentialField->label = 'Potential Name';
		$potentialField->uitype = 10;
		$potentialField->typeofdata = 'I~O';
		$blockInstance->addField($potentialField);
		$potentialField->setRelatedModules(Array('Potentials'));
	}

	$productsInstance = Vtiger_Module_Model::getInstance('Products');
	$poInstance = Vtiger_Module_Model::getInstance('PurchaseOrder');
	$productsInstance->setRelatedList($poInstance, 'PurchaseOrder', false, 'get_purchaseorder');

	$modules = array('Potentials', 'Contacts', 'Accounts', 'Project');
	foreach ($modules as $moduleName) {
		$tabId = getTabid($moduleName);
		if ($moduleName == 'Project') {
			$db->pquery('UPDATE vtiger_field SET displaytype=? WHERE fieldname=? AND tabid=?', array(1, 'isconvertedfrompotential', $tabId));
		} else {
			$db->pquery('UPDATE vtiger_field SET displaytype=? WHERE fieldname=? AND tabid=?', array(1, 'isconvertedfromlead', $tabId));
		}
		Vtiger_Cache::flushModuleCache($moduleName);
	}

	$db->pquery('DELETE FROM vtiger_links WHERE linktype=? AND handler_class=?', array('DETAILVIEWBASIC', 'Documents'));

	$columns = $db->getColumnNames('vtiger_emailtemplates');
	if (!in_array('systemtemplate', $columns)) {
		$db->pquery('ALTER TABLE vtiger_emailtemplates ADD COLUMN systemtemplate INT(1) NOT NULL DEFAULT 0', array());
	}
	if (!in_array('templatepath', $columns)) {
		$db->pquery('ALTER TABLE vtiger_emailtemplates ADD COLUMN templatepath VARCHAR(100) AFTER templatename', array());
	}
	if (!in_array('module', $columns)) {
		$db->pquery('ALTER TABLE vtiger_emailtemplates ADD COLUMN module VARCHAR(100)', array());
	}
	$db->pquery('UPDATE vtiger_emailtemplates SET module=? WHERE templatename IN (?,?,?) AND module IS NULL', array('Events', 'ToDo Reminder', 'Activity Reminder', 'Invite Users'));
	$db->pquery('UPDATE vtiger_emailtemplates SET module=? WHERE module IS NULL', array('Contacts'));

	$columns = $db->getColumnNames('vtiger_mailmanager_mailrecord');
	if (!in_array('mfolder', $columns)) {
		$db->pquery('ALTER TABLE vtiger_mailmanager_mailrecord ADD COLUMN mfolder VARCHAR(250)', array());
		$duplicateResult = $db->pquery('SELECT muid FROM vtiger_mailmanager_mailrecord GROUP BY muid HAVING COUNT(muid) > ?', array('1'));
		$noOfDuplicate = $db->num_rows($duplicateResult);
		if ($noOfDuplicate) {
			$duplicateMuid = array();
			for ($i=0; $i<$noOfDuplicate; $i++) {
				$duplicateMuid[] = $db->query_result($duplicateResult, $i, 'muid');
			}
			$db->pquery('DELETE FROM vtiger_mailmanager_mailrecord WHERE muid IN ('.generateQuestionMarks($duplicateMuid).')', $duplicateMuid);
			$db->pquery('DELETE FROM vtiger_mailmanager_mailattachments WHERE muid IN ('.generateQuestionMarks($duplicateMuid).')', $duplicateMuid);
		}
	}

	if (Vtiger_Utils::CheckTable('vtiger_mailscanner_ids')) {
		$db->pquery('RENAME TABLE vtiger_mailscanner_ids TO vtiger_message_ids', array());
		$db->pquery('ALTER TABLE vtiger_message_ids ADD COLUMN refids MEDIUMTEXT', array());
		$db->pquery('ALTER TABLE vtiger_message_ids ADD INDEX messageids_crmid_idx(crmid)',array());
	}

	//Update existing package modules
	Install_Utils_Model::installModules();

	//recalculate user files to finish
	RecalculateSharingRules();

	echo '<br>Successfully updated : <b>Vtiger7</b><br>';

}
