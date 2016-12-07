<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/*
 * Settings Module Model Class
 */
class Settings_Vtiger_Module_Model extends Vtiger_Base_Model {

	var $baseTable = 'vtiger_settings_field';
	var $baseIndex = 'fieldid';
	var $listFields = array('name' => 'Name','description' => 'Description');
	var $nameFields = array('name');
	var $name = 'Vtiger';

	public function getName($includeParentIfExists = false) {
		if($includeParentIfExists) {
			return  $this->getParentName() .':'. $this->name;
		}
		return $this->name;
	}

	public function getParentName() {
		return 'Settings';
	}

	public function getBaseTable() {
		return $this->baseTable;
	}

	public function getBaseIndex() {
		return $this->baseIndex;
	}

	public function setListFields($fieldNames) {
		$this->listFields = $fieldNames;
		return $this;
	}

	public function getListFields() {
		if(!$this->listFieldModels) {
			$fields = $this->listFields;
			$fieldObjects = array();
			foreach($fields as $fieldName => $fieldLabel) {
				$fieldObjects[$fieldName] = new Vtiger_Base_Model(array('name' => $fieldName, 'label' => $fieldLabel));
			}
			$this->listFieldModels = $fieldObjects;
		}
		return $this->listFieldModels;
	}

	/**
	 * Function to get name fields of this module
	 * @return <Array> list field names
	 */
	public function getNameFields() {
		return $this->nameFields;
	}

	/**
	 * Function to get field using field name
	 * @param <String> $fieldName
	 * @return <Field_Model>
	 */
	public function getField($fieldName) {
		return new Vtiger_Base_Model(array('name' => $fieldName, 'label' => $fieldName));
	}

	public function hasCreatePermissions() {
		return true;
	}

	/**
	 * Function to get all the Settings menus
	 * @return <Array> - List of Settings_Vtiger_Menu_Model instances
	 */
	public function getMenus() {
		return Settings_Vtiger_Menu_Model::getAll();
	}

	/**
	 * Function to get all the Settings menu items for the given menu
	 * @return <Array> - List of Settings_Vtiger_MenuItem_Model instances
	 */
	public function getMenuItems($menu=false) {
		$menuModel = false;
		if($menu) {
			$menuModel = Settings_Vtiger_Menu_Model::getInstance($menu);
		}
		return Settings_Vtiger_MenuItem_Model::getAll($menuModel);
	}

	public function isPagingSupported(){
		return true;
	}

	/**
	 * Function to get the instance of Settings module model
	 * @return Settings_Vtiger_Module_Model instance
	 */
	public static function getInstance($name='Settings:Vtiger') {
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $name);
		return new $modelClassName();
	}

	/**
	 * Function to get Index view Url
	 * @return <String> URL
	 */
	public function getIndexViewUrl() {
		return 'index.php?module='.$this->getName().'&parent='.$this->getParentName().'&view=Index';
	}

	/*
	 * Function to get supported utility actions for a module
	 */
	function getUtilityActionsNames() {
		return array();
	}

	/** 
	 * Fucntion to get the settings menu item for vtiger7
	 * @return <array> $settingsMenItems
	 */
	static function getSettingsMenuItem() {
		$settingsModel = Settings_Vtiger_Module_Model::getInstance();
		$menuModels = $settingsModel->getMenus();

		//Specific change for Vtiger7
		$settingsMenItems = array();
		foreach($menuModels as $menuModel) {
			$menuItems = $menuModel->getMenuItems();
			foreach($menuItems as $menuItem) {
				$settingsMenItems[$menuItem->get('name')] = $menuItem;
			}
		}

		return $settingsMenItems;
	}

	static function getExtensionList($settingsMenuList) {
		$exchangeConnectorInstance = Vtiger_Module_Model::getInstance('ExchangeConnector');
		if ($exchangeConnectorInstance && $exchangeConnectorInstance->isActive()) {
			$settingsMenuList['LBL_EXTENSIONS']['LBL_EXCHANGE_CONNECTOR'] = 'LBL_EXCHANGE_CONNECTOR';
		}

		return $settingsMenuList;
	}

	/**
	 * Function to get Vtiger Menu List
	 * @return string
	 */
	static function getSettingsMenuList() {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$myTagSettingsUrl = $currentUser->getMyTagSettingsListUrl();
		$extensionStoreInstance = Settings_ExtensionStore_Module_Model::getInstance();


		$settingsMenuList = array(	'LBL_USER_MANAGEMENT'	=> array('LBL_USERS'				=> 'LBL_USERS',
																	'LBL_ROLES'					=> 'LBL_ROLES',
																	'LBL_PROFILES'				=> 'LBL_PROFILES',
																	'LBL_SHARING_ACCESS'		=> 'LBL_SHARING_ACCESS',
																	'USERGROUPLIST'				=> 'USERGROUPLIST',
																	'LBL_LOGIN_HISTORY_DETAILS' => 'LBL_LOGIN_HISTORY_DETAILS'),

									'LBL_MODULE_MANAGEMENT' => array('VTLIB_LBL_MODULE_MANAGER' => 'VTLIB_LBL_MODULE_MANAGER',
																	'LBL_EDIT_FIELDS'			=> 'LBL_EDIT_FIELDS',
																	'LBL_CUSTOMIZE_MODENT_NUMBER'=> 'LBL_CUSTOMIZE_MODENT_NUMBER'),

									'LBL_AUTOMATION'		=> array('Webforms'					=> 'Webforms',
																	'Scheduler'					=> 'Scheduler',
																	'LBL_LIST_WORKFLOWS'		=> 'LBL_LIST_WORKFLOWS'),

									'LBL_CONFIGURATION'		=> array('LBL_COMPANY_DETAILS'		=> 'LBL_COMPANY_DETAILS',
																	'LBL_CUSTOMER_PORTAL'		=> 'LBL_CUSTOMER_PORTAL',
																	'LBL_CURRENCY_SETTINGS'		=> 'LBL_CURRENCY_SETTINGS',
																	'LBL_MAIL_SERVER_SETTINGS'	=> 'LBL_MAIL_SERVER_SETTINGS',
																	'Configuration Editor'		=> 'Configuration Editor',
																	'LBL_PICKLIST_EDITOR'		=> 'index.php?parent=Settings&module=Picklist&view=Index',
																	'LBL_PICKLIST_DEPENDENCY'	=> 'index.php?parent=Settings&module=PickListDependency&view=List',
																	'LBL_MENU_EDITOR'			=> 'LBL_MENU_EDITOR'),

									'LBL_MARKETING_SALES'	=> array('LBL_LEAD_MAPPING'			=> 'index.php?parent=Settings&module=Leads&view=MappingDetail',
																	'LBL_OPPORTUNITY_MAPPING'	=> 'index.php?parent=Settings&module=Potentials&view=MappingDetail'),

									'LBL_INVENTORY'			=> array('LBL_TAX_SETTINGS'			=> 'LBL_TAX_SETTINGS',
																	'INVENTORYTERMSANDCONDITIONS'=> 'INVENTORYTERMSANDCONDITIONS'),
									'LBL_MY_PREFERENCES'	=> array('My Preferences'			=> '',
																	'Calendar Settings'			=> '',
																	'LBL_MY_TAGS'				=> "$myTagSettingsUrl"),

									'LBL_TEMPLATES'			=> array('Email Templates'			=> 'index.php?module=EmailTemplates&view=List'),

									'LBL_EXTENSIONS'		=> array('LBL_EXTENSION_STORE'		=> $extensionStoreInstance->getDefaultUrl())
								);

		$settingsMenuList = self::getExtensionList($settingsMenuList);
		$webformsInstance = Vtiger_Module_Model::getInstance('Webforms');
		if($webformsInstance && !$webformsInstance->isActive()) {
			unset($settingsMenuList['LBL_AUTOMATION']['Webforms']);
		}

		return $settingsMenuList;
	}

	static function getActiveBlockName($menu, $request) {
		$settingsMenuList = array('LBL_USER_MANAGEMENT'		=> array('LBL_USERS'				=> 'Users',
																	 'LBL_ROLES'				=> 'Roles',
																	 'LBL_PROFILES'				=> 'Profiles',
																	 'LBL_SHARING_ACCESS'		=> 'SharingAccess',
																	 'USERGROUPLIST'			=> 'Groups',
																	 'LBL_LOGIN_HISTORY_DETAILS' => 'LoginHistory'),

								  'LBL_MODULE_MANAGEMENT'	=> array('VTLIB_LBL_MODULE_MANAGER' => 'ModuleManager',
																	 'LBL_EDIT_FIELDS'			=> 'LayoutEditor',
																	 'Labels Editor'			=> 'Labels Editor',
																	 'LBL_CUSTOMIZE_MODENT_NUMBER' => 'CustomRecordNumbering'),
			
								  'LBL_AUTOMATION'			=> array('Webforms'					=> 'Webforms',
																	 'Scheduler'				=> 'CronTasks',
																	 'LBL_LIST_WORKFLOWS'		=> 'Workflows'),

								  'LBL_CONFIGURATION'		=> array('LBL_COMPANY_DETAILS'		=> 'CompanyDetails',
																	 'LBL_CUSTOMER_PORTAL'		=> 'CustomerPortal',
																	 'LBL_CURRENCY_SETTINGS'	=> 'Currency',
																	 'LBL_MAIL_SERVER_SETTINGS'	=> 'OutgoingServer',
																	 'Configuration Editor'		=> 'ConfigurationEditor',
																	 'LBL_PICKLIST_EDITOR'		=> 'Picklist',
																	 'LBL_PICKLIST_DEPENDENCY'	=> 'PickListDependency',
																	 'LBL_MENU_EDITOR'			=> 'LBL_MENU_EDITOR'),

								  'LBL_MARKETING_SALES'		=> array('LBL_LEAD_MAPPING'			=> 'LeadsMappingDetail',
																	 'LBL_OPPORTUNITY_MAPPING'	=> 'PotentialsMappingDetail'),

								  'LBL_INVENTORY'			=> array('LBL_TAX_SETTINGS'			=> 'TaxIndex',
																	 'INVENTORYTERMSANDCONDITIONS'=> 'TermsAndConditionsEdit'),

								  'LBL_MY_PREFERENCES'		=> array('1'						=> 'My Preferences',
																	 '2'						=> 'Calendar Settings',
																	 'LBL_MY_TAGS'				=> 'Tags'),
								  'LBL_TEMPLATES'			=> array('Email Templates'			=> 'EmailTemplates'),
								  'LBL_EXTENSIONS'			=> array('LBL_EXTENSIONS'			=> 'Extension')
			);
		foreach ($settingsMenuList as $blockname => $menulist) {
			if($key = array_search($menu, $menulist)) {
				if($menu == 'Extension') {
					$extMenu = 'LBL_'.strtoupper($request->get('extensionModule'));
					return array('block' => $blockname, 'menu' => $extMenu);
				}
				return array('block' => $blockname, 'menu' => $key);
			}
		}
		return array();
	}

	static function getSettingsMenuListForNonAdmin() {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$myTagSettingsUrl = $currentUser->getMyTagSettingsListUrl();

		$settingsMenuList = array(	'LBL_MY_PREFERENCES'	=> array('My Preferences'			=> '',
																	'Calendar Settings'			=> '',
																	'LBL_MY_TAGS'				=> "$myTagSettingsUrl"),
									'LBL_TEMPLATES'			=> array('Email Templates'			=> 'index.php?module=EmailTemplates&view=List'),
								  );
		return $settingsMenuList;
	}

}
