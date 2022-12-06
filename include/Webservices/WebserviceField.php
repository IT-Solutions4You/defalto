<?php
/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ******************************************************************************/

require_once 'includes/runtime/Cache.php';
require_once 'vtlib/Vtiger/Runtime.php';

class WebserviceField{
	private $fieldId;
	private $uitype;
	private $blockId;
	private $blockName;
	private $nullable;
	private $default;
	private $tableName;
	private $columnName;
	private $fieldName;
	private $fieldLabel;
	private $editable;
	private $fieldType;
	private $displayType;
	private $mandatory;
	private $massEditable;
	private $tabid;
	private $presence;
	/**
	 *
	 * @var PearDatabase
	 */
	private $pearDB;
	private $typeOfData;
	private $fieldDataType;
	private $dataFromMeta;
	private static $tableMeta = array();
	private static $fieldTypeMapping = array();
	private $referenceList;
	private $defaultValuePresent;
	private $explicitDefaultValue;

	private $genericUIType = 10;

	private $readOnly = 0;
	private $isunique = 0;

	private function __construct($adb,$row){
		$this->uitype = isset($row['uitype'])? $row['uitype'] : 0;
		$this->blockId = isset($row['block'])? $row['block'] : 0;
		$this->blockName = null;
		$this->tableName = isset($row['tablename'])? $row['tablename'] : null;
		$this->columnName = isset($row['columnname'])? $row['columnname'] : null;
		$this->fieldName = isset($row['fieldname'])? $row['fieldname'] : null;
		$this->fieldLabel = isset($row['fieldlabel'])? $row['fieldlabel'] : null;
		$this->displayType = isset($row['displaytype'])? $row['displaytype'] : null;
		$this->massEditable = (isset($row['masseditable']) && $row['masseditable'] === '1')? true: false;
		$this->presence = isset($row['presence'])? $row['presence'] : null;
		$this->isunique = isset($row['isunique']) && $row['isunique'] ? true : false;
		$typeOfData = isset($row['typeofdata'])? $row['typeofdata'] : null;
		$this->typeOfData = $typeOfData;
		$typeOfData = explode("~",$typeOfData);
		$this->mandatory = (php7_count($typeOfData) > 1 && $typeOfData[1] == 'M')? true: false;
		if($this->uitype == 4){
			$this->mandatory = false;
		}
		$this->fieldType = $typeOfData[0];
		$this->tabid = isset($row['tabid'])? $row['tabid']: 0;
		$this->fieldId = isset($row['fieldid'])? $row['fieldid'] : 0;
		$this->pearDB = $adb;
		$this->fieldDataType = null;
		$this->dataFromMeta = false;
		$this->defaultValuePresent = false;
		$this->referenceList = null;
		$this->explicitDefaultValue = false;

		$this->readOnly = (isset($row['readonly']))? $row['readonly'] : 0;

		if(array_key_exists('defaultvalue', $row)) {
			$this->setDefault($row['defaultvalue']);
		}
	}

	public static function fromQueryResult($adb,$result,$rowNumber){
		 return new WebserviceField($adb,$adb->query_result_rowdata($result,$rowNumber));
	}

	public static function fromArray($adb,$row){
		return new WebserviceField($adb,$row);
	}

	public function getTableName(){
		return $this->tableName;
	}

	public function getFieldName(){
		return $this->fieldName;
	}

	public function getFieldLabelKey(){
		return $this->fieldLabel;
	}

	public function getFieldType(){
		return $this->fieldType;
	}

	public function isMandatory(){
		return $this->mandatory;
	}

	public function getTypeOfData(){
		return $this->typeOfData;
	}

	public function getDisplayType(){
		return $this->displayType;
	}

	public function isUnique(){
		return $this->isunique;
	}

	public function getMassEditable(){
		return $this->massEditable;
	}

	public function getFieldId(){
		return $this->fieldId;
	}

	public function getDefault(){
		if($this->dataFromMeta !== true && $this->explicitDefaultValue !== true){
			$this->fillColumnMeta();
		}
		return $this->default;
	}

	public function getColumnName(){
		return $this->columnName;
	}

	public function getBlockId(){
		return $this->blockId;
	}

	public function getBlockName(){
		if(empty($this->blockName)) {
			$this->blockName = getBlockName($this->blockId);
		}
		return $this->blockName;
	}

	public function getTabId(){
		return $this->tabid;
	}

	public function isNullable(){
		if($this->dataFromMeta !== true){
			$this->fillColumnMeta();
		}
		return $this->nullable;
	}

	public function hasDefault(){
		if($this->dataFromMeta !== true && $this->explicitDefaultValue !== true){
			$this->fillColumnMeta();
		}
		return $this->defaultValuePresent;
	}

	public function getUIType(){
		return $this->uitype;
	}

	public function isReadOnly() {
		if($this->readOnly == 1) return true;
		return false;
	}

	private function setNullable($nullable){
		$this->nullable = $nullable;
	}

	public function setDefault($value){
		$this->default = $value;
		$this->explicitDefaultValue = true;
		$this->defaultValuePresent = true;
	}

	public function setFieldDataType($dataType){
		$this->fieldDataType = $dataType;
	}

	public function setReferenceList($referenceList){
		$this->referenceList = $referenceList;
	}

	public function getTableFields(){
		$tableFields = null;
		if(isset(WebserviceField::$tableMeta[$this->getTableName()])){
			$tableFields = WebserviceField::$tableMeta[$this->getTableName()];
		}else{
			$dbMetaColumns = $this->pearDB->database->MetaColumns($this->getTableName());
			$tableFields = array();
			foreach ($dbMetaColumns as $key => $dbField) {
				$tableFields[$dbField->name] = $dbField;
			}
			WebserviceField::$tableMeta[$this->getTableName()] = $tableFields;
		}
		return $tableFields;
	}
	public function fillColumnMeta(){
		$tableFields = $this->getTableFields();
		foreach ($tableFields as $fieldName => $dbField) {
			if(strcmp($fieldName,$this->getColumnName())===0){
				$this->setNullable(!$dbField->not_null);
				if($dbField->has_default === true && !$this->explicitDefaultValue){
					$this->defaultValuePresent = $dbField->has_default;
					$this->setDefault($dbField->default_value);
				}
			}
		}
		$this->dataFromMeta = true;
	}

	public function getFieldDataType(){
		if($this->fieldDataType === null){
			$fieldDataType = $this->getFieldTypeFromUIType();
			if($fieldDataType === null){
				$fieldDataType = $this->getFieldTypeFromTypeOfData();
			}
			if($fieldDataType == 'date' || $fieldDataType == 'datetime' || $fieldDataType == 'time') {
				$tableFieldDataType = $this->getFieldTypeFromTable();
				if($tableFieldDataType == 'datetime'){
					$fieldDataType = $tableFieldDataType;
				}
			}
			$this->fieldDataType = $fieldDataType;
		}
		return $this->fieldDataType;
	}

	public function getReferenceList($hideDisabledModules = true){
		static $referenceList = array();
		if($this->referenceList === null){
			if(isset($referenceList[$this->getFieldId()])){
				$this->referenceList = $referenceList[$this->getFieldId()];
				return $referenceList[$this->getFieldId()];
			}
			if(!isset(WebserviceField::$fieldTypeMapping[$this->getUIType()])){
				$this->getFieldTypeFromUIType();
			}
			$fieldTypeData = WebserviceField::$fieldTypeMapping[$this->getUIType()];
			$referenceTypes = array();
			if($this->getUIType() != $this->genericUIType){
				$sql = "select type from vtiger_ws_referencetype where fieldtypeid=?";
				$params = array(isset($fieldTypeData['fieldtypeid'])? $fieldTypeData['fieldtypeid'] : 0);
			}else{
				$sql = 'SELECT relmodule AS type FROM vtiger_fieldmodulerel WHERE fieldid=? ORDER BY sequence ASC';
				$params = array($this->getFieldId());
			}
			$result = $this->pearDB->pquery($sql,$params);
			$numRows = $this->pearDB->num_rows($result);
			for($i=0;$i<$numRows;++$i){
				array_push($referenceTypes,$this->pearDB->query_result($result,$i,"type"));
			}
            if($hideDisabledModules) {
				global $current_user;
				$types = vtws_listtypes(null, $current_user);
				$accessibleTypes = $types['types'];
	            //If it is non admin user or the edit and view is there for profile then users module will be accessible
				if(!is_admin($current_user)&& !in_array("Users",$accessibleTypes)) {
					array_push($accessibleTypes, 'Users');
				}

				$referenceTypes = array_values(array_intersect($referenceTypes, $accessibleTypes));
            }
			$referenceList[$this->getFieldId()] = $referenceTypes;
			$this->referenceList = $referenceTypes;
			return $referenceTypes;
		}
		return $this->referenceList;
	}

	private function getFieldTypeFromTable(){
		$tableFields = $this->getTableFields();
		foreach ($tableFields as $fieldName => $dbField) {
			if(strcmp($fieldName,$this->getColumnName())===0){
				return $dbField->type;
			}
		}
		//This should not be returned if entries in DB are correct.
		return null;
	}

	private function getFieldTypeFromTypeOfData(){
		switch($this->fieldType){
			case 'T': return "time";
			case 'D':
			case 'DT': return "date";
			case 'E': return "email";
			case 'N':
			case 'NN': return "double";
			case 'P': return "password";
			case 'I': return "integer";
			case 'V':
			default: return "string";
		}
	}

	private function getFieldTypeFromUIType(){

		// Cache all the information for futher re-use
		if(empty(self::$fieldTypeMapping)) {
			$result = $this->pearDB->pquery("select * from vtiger_ws_fieldtype", array());
			while($resultrow = $this->pearDB->fetch_array($result)) {
				self::$fieldTypeMapping[$resultrow['uitype']] = $resultrow;
			}
		}

		if(isset(WebserviceField::$fieldTypeMapping[$this->getUIType()])){
			if(WebserviceField::$fieldTypeMapping[$this->getUIType()] === false){
				return null;
			}
			$row = WebserviceField::$fieldTypeMapping[$this->getUIType()];
			return $row['fieldtype'];
		} else {
			WebserviceField::$fieldTypeMapping[$this->getUIType()] = false;
			return null;
		}
	}

	function getPicklistDetails(){
		$cache = Vtiger_Cache::getInstance();
        $fieldName = $this->getFieldName();
		if($cache->getPicklistDetails($this->getTabId(),$this->getFieldName())){
			return $cache->getPicklistDetails($this->getTabId(),$this->getFieldName());
		} else {
			//Inventory picklist values
			if ($this->getDisplayType() == 5 && $this->getFieldName() === 'region_id') {
				$picklistDetails = array();
				$allRegions = getAllRegions();
				foreach ($allRegions as $regionId => $regionDetails) {
					$picklistDetails[] = array('value' => $regionId, 'label' => $regionDetails['name']);
				}
			}elseif ($fieldName == 'defaultlandingpage') {
                $picklistDetails = array(); 
                $presence = array(0);
                $restrictedModules = array('Webmails', 'Emails', 'Integration', 'Dashboard','ModComments');
                $query = 'SELECT name, tablabel, tabid FROM vtiger_tab WHERE presence IN (' . generateQuestionMarks($presence) . ') AND isentitytype = ? AND name NOT IN (' . generateQuestionMarks($restrictedModules) . ')';

                $result = $this->pearDB->pquery($query, array($presence, '1', $restrictedModules));
                $numOfRows = $this->pearDB->num_rows($result);

                $picklistDetails[] = array('value' => 'Home', 'label' => vtranslate('Home', 'Home'));
                for ($i = 0; $i < $numOfRows; $i++) {
                    $moduleName = $this->pearDB->query_result($result, $i, 'name');

                    // check the module access permission, if user has permission then show it in default module list
                    if (vtlib_isModuleActive($moduleName)) {
                        $moduleLabel = $this->pearDB->query_result($result, $i, 'tablabel');
                        $picklistDetails[] = array('value' => $moduleName, 'label' => vtranslate($moduleLabel, $moduleName));
                    }
                }
            } else {
				$hardCodedPickListNames = array('hdntaxtype','email_flag');
				$hardCodedPickListValues = array('hdntaxtype'=> array(	array('label' => 'Individual',	'value' => 'individual'),
																		array('label' => 'Group',		'value' => 'group')),
												 'email_flag'=> array(	array('label' => 'SAVED',		'value' => 'SAVED'),
																		array('label' => 'SENT',		'value' => 'SENT'),
																		array('label' => 'MAILSCANNER',	'value' => 'MAILSCANNER')));

				if (in_array(strtolower($this->getFieldName()), $hardCodedPickListNames)) {
					return $hardCodedPickListValues[strtolower($this->getFieldName())];
				}
				$picklistDetails = $this->getPickListOptions($this->getFieldName());
			}
			$cache->setPicklistDetails($this->getTabId(),$this->getFieldName(),$picklistDetails);
			return $picklistDetails;
		}
	}

	function getPickListOptions(){
		$fieldName = $this->getFieldName();
		$language = Vtiger_Language_Handler::getLanguage();

		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$options = array();
		$sql = "select * from vtiger_picklist where name=?";
		$result = $this->pearDB->pquery($sql,array($fieldName));
		$numRows = $this->pearDB->num_rows($result);

		$moduleName = getTabModuleName($this->getTabId());
		if ($moduleName == 'Events') $moduleName = 'Calendar';

		if($numRows == 0){
			$sql = "SELECT * FROM vtiger_$fieldName ORDER BY sortorderid";
			$result = $this->pearDB->pquery($sql,array());
			$numRows = $this->pearDB->num_rows($result);
			for($i=0;$i<$numRows;++$i){
				$elem = array();
				$picklistValue = $this->pearDB->query_result($result,$i,$fieldName);
				$picklistValue = decode_html($picklistValue);
				$elem["label"] = getTranslatedString($picklistValue, $moduleName, $language);
				$elem["value"] = $picklistValue;
				array_push($options,$elem);
			}
		}else{
			$user = VTWS_PreserveGlobal::getGlobal('current_user');
			$details = getPickListValues($fieldName,$user->roleid);
			for($i=0;$i<sizeof($details);++$i){
				$elem = array();
				$picklistValue = decode_html($details[$i]);
				$elem["label"] = getTranslatedString($picklistValue, $moduleName, $language);
				$elem["value"] = $picklistValue;
				array_push($options,$elem);
			}
		}
		return $options;
	}

	function getPresence() {
		return $this->presence;
	}

}

?>