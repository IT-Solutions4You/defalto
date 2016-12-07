<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Reports_Pivot_Model extends Reports_Record_Model {

	var $leftColumns = array();
	var $topColumns = array();
	var $resultColumns = array();

	function getReportParent() {
		return $this->reportRun;
	}

	function setReportParent($reportRun) {
		$this->reportRun = $reportRun;
	}
	/**
	 * Function returns the Reports Model instance
	 * @param <Number> $recordId
	 * @param <String> $module
	 * @return <Reports_Record_Model>
	 */
	public static function getInstanceById($recordId) {
		$db = PearDatabase::getInstance();

		$self = new self();
		$reportResult = $db->pquery('SELECT * FROM vtiger_report WHERE reportid = ? AND reporttype = ?', array($recordId, 'pivot'));
		if ($db->num_rows($reportResult)) {
			$values = $db->query_result_rowdata($reportResult, 0);
			$module = Vtiger_Module_Model::getInstance('Reports');
			$self->setReportParent(ReportRun::getInstance($recordId));
			$self->setData($values)->setId($values['reportid'])->setModuleFromInstance($module);
			$self->initialize();
		}
		return $self;
	}

	/**
	 * Function creates Reports_Record_Model
	 * @param <Number> $recordId
	 * @return <Reports_Record_Model>
	 */
	public static function getCleanInstance($recordId = null) {
		if (empty($recordId)) {
			$self = new Reports_Pivot_Model();
		} else {
			$self = self::getInstanceById($recordId);
		}
		$self->initialize();
		$module = Vtiger_Module_Model::getInstance('Reports');
		$self->setModuleFromInstance($module);
		return $self;
	}

	/**
	 * Function returns Report Type(Summary/Tabular)
	 * @return <String>
	 */
	function getReportType() {
		$reportType = $this->get('reporttype');
		if (!empty($reportType)) {
			return $reportType;
		} else {
			return $this->report->reporttype;
		}
	}

	function isRecordCount() {
		return $this->isRecordCount;
	}

	function setRecordCount() {
		$this->isRecordCount = true;
	}

	/**
	 * Function getPivotReportData
	 * @return type
	 */
	function getPivotReportData($operation = false) {
		$db = PearDatabase::getInstance();
		$reportRun = $this->getReportParent();
		$pivotSql = $this->getQuery();
		$result = $db->pquery($pivotSql, array());
		$picklistarray = $reportRun->getAccessPickListValues();
		$agregateFunctions = $this->getAggregateFunctions();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$currencyRateAndSymbol = getCurrencySymbolandCRate($currentUserModel->currency_id);

		if ($result) {
			$numOfFields = $db->num_fields($result);
			$custom_field_values = $db->fetch_array($result);
			$resultantData = array();
			do {
				$arraylists = array();
				for ($i = 0; $i < $numOfFields; $i++) {
					$field = $db->field_name($result, $i);
					//convert currency field values to user format
					if(is_array($this->dataFieldLabels) && array_key_exists($field->name, $this->dataFieldLabels)){
						list($module, $fieldLabel) = explode('_', $this->dataFieldLabels[$field->name], 2);
						$fieldInfo = getFieldByReportLabel($module, $fieldLabel);
						$value = $custom_field_values[$i];
						if(!empty($fieldInfo)) {
							$fieldModel = WebserviceField::fromArray($db, $fieldInfo);
							$fieldType = $fieldModel->getFieldDataType();
						}
						if($fieldType == 'currency' && $value != '') {
							if($fieldModel->getUIType() == '72' || $fieldModel->getUIType() == '71') {
								$fieldvalue =  CurrencyField::convertFromDollar($value, $currencyRateAndSymbol['rate']);
							} else {
								$currencyField = new CurrencyField($value);
								$fieldvalue = $currencyField->getDisplayValue();
							}
						} else {
							$fieldvalue = getReportFieldValue($reportRun, $picklistarray, $field, $custom_field_values, $i);
						}
						if($operation == 'ExcelExport') {
							$fieldvalue = array('value' => $fieldvalue, 'type' => $fieldType);
						}
					} else {
						$fieldvalue = getReportFieldValue($reportRun, $picklistarray, $field, $custom_field_values, $i);
					}

					$fieldInfo = explode('_', $field->name, 2);
					if(in_array($fieldInfo[1],$agregateFunctions) || $field->name == 'RECORD_COUNT' || $fieldInfo[1] == 'Count_SUM'){
						if($operation == 'ExcelExport' && is_array($fieldvalue) && $fieldvalue['value'] == "-") {
							$fieldvalue['value'] = 0;
						}
						if($fieldvalue == "-") $fieldvalue = 0;
					}
					$arraylists[strtolower($field->name)] = $fieldvalue;
				}
				$resultantData[] = $arraylists;
			} while ($custom_field_values = $db->fetch_array($result));
		}
		if(!empty($resultantData)) {
			$skip = false;
			foreach($this->leftColumns as $column) {
				if(strpos($column, '_Month')) {
					$resultantData = $this->sortReportByMonth($column, $resultantData);
					$skip = true;
					break;
				}
				if(strpos($column, '_Week')) {
					$resultantData = $this->sortReportByWeek($column, $resultantData);
					$skip = true;
					break;
				}
			}

			if(!$skip) {
				foreach($this->topColumns as $column) {
					if(strpos($column, '_Month')) {
						$resultantData = $this->sortReportByMonth($column, $resultantData);
						break;
					}
					if(strpos($column, '_Week')) {
						$resultantData = $this->sortReportByWeek($column, $resultantData);
						break;
					}
				}
			}
		}
		return array("data" => $resultantData, "leftColumns" => $this->leftColumns, "topColumns" => $this->topColumns, "resultColumns" => $this->resultColumns);
	}

	public function getQuery() {
		$reportRun = $this->getReportParent();
		$reportInfo = Zend_Json::decode(decode_html($this->getReportTypeInfo()));
		$selectedColumns = $this->getPivotSelectColumnList($reportInfo);
		$filtersql = $reportRun->getAdvFilterSql($this->getId());
		$groupByList = $this->getGroupByColumnList($reportInfo);
		$orderByList = $this->getOrderByColumns($reportInfo);
		$reportQuery = $reportRun->getReportsQuery($this->getPrimaryModule());
		$reportRun->queryPlanner->initializeTempTables();
		$pivotSql = 'SELECT ';
		if ($filtersql != "") {
			$wheresql .= " AND " . $filtersql;
		}
		if ($groupByList != "") {
			$wheresql .= " GROUP BY " . $groupByList;
		}

		if ($orderByList != "") {
			$wheresql .= $orderByList;
		}

		if ($this->isRecordCount()) {
			$pivotSql .= " count(*) AS RECORD_COUNT,";
		}
		$pivotSql .= $selectedColumns . ' ' . $reportQuery;
		preg_match('/&/', $pivotSql, $matches);
		if(!empty($matches)){
			$report=str_replace('&', 'and', $pivotSql);
			$pivotSql = $reportRun->replaceSpecialChar($report);
		}
		$pivotSql .= $wheresql;
		return $pivotSql;
	}

	public function getPivotSelectColumnList($reportInfo) {
		$selectedRows = $reportInfo['rows'];
		$selectedColumns = $reportInfo['columns'];
		$dataFields = $reportInfo['functions'];
		$leftColumns = $this->getQueryColumnList($selectedRows);
		$topColumns = $this->getQueryColumnList($selectedColumns);
		$dataColumns = $this->getQueryDataColumnList($dataFields);
		$columns = implode(',', array_merge(array_values($leftColumns), array_values($topColumns), array_values($dataColumns)));
		return $columns;
	}

	function getQueryColumnList($columns) {
		$reportRun = $this->getReportParent();
		if (is_array($columns)) {
			$columnList = array();
			foreach ($columns as $column) {
				$selectedField = explode(":", $column);
				$reportColumnSQL = $reportRun->getEscapedColumns($selectedField);
				if($reportColumnSQL == "")
					$reportColumnSQL = $reportRun->getColumnSQL($selectedField);
				$columnList[$column] = $reportColumnSQL;
			}
		}
		return $columnList;
	}

	function getQueryDataColumnList($columns){
		$reportRun = $this->getReportParent();
		$dataColumnsList = array();
		foreach ($columns as $column) {
			if ($column == 'record_count') {
				$this->setRecordCount();
				array_push($this->resultColumns, $column);
			} else {
				list($tablename, $colname, $module_field, $fieldname, $single, $agregatefunction) = split(":", $column);
				list($module, $field) = split("_", $module_field, 2);
				$translatedLabel = getTranslatedString($field, $module);
				if ($field == $translatedLabel) {
					$translatedLabel = getTranslatedString(str_replace('_', ' ', $field), $module);
				} else {
					$translatedLabel = str_replace('_', ' ', $translatedLabel);
				}
				$nonTranslatedModuleField = $module_field;
				$module_field = getTranslatedString($module, $module).'_'.$translatedLabel;
				$primaryModuleInstance = CRMEntity::getInstance($module);
				if ($tablename == 'vtiger_inventoryproductrel') {
					$tablename = $tablename.'tmp'.$module;
				}
				$columnLabel = $module_field.'_'.$agregatefunction;
				if($tablename == 'vtiger_notes' && $colname == 'filesize') {
					$column = $agregatefunction."(CASE ".$tablename.".".$colname." when '' then '0' else concat(".$tablename.".".$colname."/1024,' ','KB')END) AS `".$columnLabel.'`';
				} else if(($tablename == 'vtiger_invoice' || $tablename == 'vtiger_quotes' || $tablename == 'vtiger_purchaseorder' || $tablename == 'vtiger_salesorder') && ($colname == 'total'
						|| $colname == 'subtotal' || $colname == 'discount_amount' || $colname == 's_h_amount' || $colname == 'paid' || $colname == 'balance' || $colname == 'received'
						|| $colname == 'pre_tax_total' || $colname == 'adjustment')) {
					$column = $agregatefunction.'('.$tablename.'.'.$colname.'/'.$tablename.'.conversion_rate) AS `' . $columnLabel."`";
				} else if($colname == 'listprice') {
					$column = $agregatefunction.'('.$tablename.'.'.$colname.'/'.$primaryModuleInstance->table_name.'.conversion_rate) AS `' . $columnLabel."`";
				} else if($colname == 'discount_amount') {
					$column = $agregatefunction .'(CASE WHEN '.$tablename.'.discount_amount > 0 THEN ('.$tablename.'.discount_amount/'.$primaryModuleInstance->table_name.'.conversion_rate) '.
							'WHEN '.$tablename.'.discount_percent > 0 THEN (('.$tablename.'.listprice*'.$tablename.'.quantity*'.$tablename.'.discount_percent/100/'.
							$primaryModuleInstance->table_name.'.conversion_rate)) ELSE 0 END) AS `'.$columnLabel."`";
				} else {
					$column = $agregatefunction.'(' . $tablename . '.' . $colname . ') AS `' . $columnLabel."`";
				}
				$dataColumnsList[$columnLabel] = $column;
				$reportRun->queryPlanner->addTable($tablename);
				array_push($this->resultColumns, $columnLabel);
				$this->dataFieldLabels[$columnLabel] = $nonTranslatedModuleField;
			}
		}
		return $dataColumnsList;
	}

	function getGroupByColumnList($reportInfo) {
		$selectedRows = $reportInfo['rows'];
		$selectedColumns = $reportInfo['columns'];
		$reportRun = $this->getReportParent();
		$columnslist = array();
		if (!empty($selectedRows)) {
			foreach ($selectedRows as $key => $selectedRow) {
				list($tablename, $colname, $module_field, $fieldname, $single, $function) = split(":", $selectedRow);
				list($module, $field) = split("_", $module_field, 2);
				$reportRun->queryPlanner->addTable($tablename . $module);
				if($tablename == 'vtiger_activity' && $colname == 'date_start'){
					if($module == 'Emails') {
						$module_field = 'Emails_Date_Sent';
					}else{
						$module_field = 'Calendar_Start_Date_and_Time';
					}
				}
				if ($single == 'D' || $single == 'DT') {
					if ($function == 'M') {
						$module_field = $module_field . '_Month';
						array_push($columnslist, "`".$module_field."`");
					} else if ($function == 'Y') {
						$module_field = $module_field . '_Year';
						array_push($columnslist, "`".$module_field."`");
					} else if ($function == 'W') {
						$module_field = $module_field . '_Week';
						array_push($columnslist, "`".$module_field."`");
					} else {
						array_push($columnslist, "`".$module_field."`");
						$module_field = $module_field . '_DateOrder';
					}
				} else {
					array_push($columnslist, "`".$module_field."`"); // to escape special characters
				}
				array_push($this->leftColumns, $module_field);
			}
		}
		if (!empty($selectedColumns)) {
			foreach ($selectedColumns as $key => $selectedColumn) {
				list($tablename, $colname, $module_field, $fieldname, $single, $function) = split(":", $selectedColumn);
				list($module, $field) = split("_", $module_field, 2);
				$reportRun->queryPlanner->addTable($tablename . $module);
				if($tablename == 'vtiger_activity' && $colname == 'date_start'){
				   if($module == 'Emails') {
						$module_field = 'Emails_Date_Sent';
					}else{
						$module_field = 'Calendar_Start_Date_and_Time';
					}
				}
				if ($single == 'D' || $single == 'DT') {
					if ($function == 'M') {
						$module_field = $module_field . '_Month';
						array_push($columnslist, "`".$module_field."`");
					} else if ($function == 'Y') {
						$module_field = $module_field . '_Year';
						array_push($columnslist, "`".$module_field."`");
					} else if ($function == 'W') {
						$module_field = $module_field . '_Week';
						array_push($columnslist, "`".$module_field."`");
					} else {
						array_push($columnslist, "`".$module_field."`");
						$module_field = $module_field . '_DateOrder';
					}
				} else {
					array_push($columnslist, "`".$module_field."`"); // to escape special characters
				}

				array_push($this->topColumns, $module_field);
			}
		}
		$columnslist = implode(',', array_values($columnslist));
		preg_match('/&/', $columnslist, $matches);
		if(!empty($matches)){
			$columnslist = str_replace('&', 'and', $columnslist);
			$columnslist = $reportRun->replaceSpecialChar($columnslist);
		}
		return $columnslist;
	}

	function getOrderByColumns($reportInfo){
		$selectedRows = $reportInfo['rows'];
		$selectedColumns = $reportInfo['columns'];
		$skip = false;
		if (!$skip) {
			foreach ($selectedRows as $key => $selectedRow) {
				list($tablename, $colname, $module_field, $fieldname, $single, $function) = split(":", $selectedRow);
				if ($single == 'D' || $single == 'DT') {
					if ($function == '' ) {
						$skip = true;
						$query = ' ORDER BY `' .$module_field. '` ASC';
						break;
					}
				}
			}
		}
		if (!$skip) {
			foreach ($selectedColumns as $key => $selectedColumn) {
				list($tablename, $colname, $module_field, $fieldname, $single, $function) = split(":", $selectedColumn);
				if ($single == 'D' || $single == 'DT') {
					if ($function == '' ) {
						$skip = true;
						$query = ' ORDER BY `' .$module_field. '` ASC';
						break;
					}
				}
			}
		}
		$reportRun = $this->getReportParent();
		preg_match('/&/', $query, $matches);
		if(!empty($matches)){
			$query = str_replace('&', 'and', $query);
			$query = $reportRun->replaceSpecialChar($query);
		}
		return $query;
	}

	function sortReportByMonth($column, $resultantData) {
		$column = strtolower($column);
		$sortedArray = array();
		$mOrder = array("January","February","March", "April", "May", "June","July","August","September","October","November","December","");
		foreach ($resultantData as $key=>$result) {
			$months[$key] = $result[$column];
		}
		foreach($mOrder as $order) {
			foreach ($months as $key => $month) {
				if(strtolower($order) == strtolower($month)) {
					$sortedArray[] = $resultantData[$key];
				}
			}
		}
		if(count($sortedArray) != 0) {
			return $sortedArray;
		}

		return $resultantData;
	}

	function sortReportByWeek($column, $resultantData) {
		$column = strtolower($column);
		$sortedArray = array();
		for($i = 1; $i <= 53; $i++) {
			$mOrder[$i-1] = 'Week '.$i;
		}
		$mOrder[$i] = '';
		foreach ($resultantData as $key=>$result) {
			$weeks[$key] = $result[$column];
		}
		foreach($mOrder as $order) {
			foreach ($weeks as $key => $week) {
				if(strtolower($order) == strtolower($week)) {
					$sortedArray[] = $resultantData[$key];
				}
			}
		}
		if(count($sortedArray) != 0) {
			return $sortedArray;
		}

		return $resultantData;
	}

}
