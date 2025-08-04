<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

require_once('include/events/SqlResultIterator.inc');

class VTEntityType
{
	function __construct($adb, $setype)
	{
		$this->moduleName = $setype;
		require_once("modules/" . $setype . "/" . $setype . ".php");
		$result = $adb->pquery(
			"select tabid from vtiger_tab where name=?",
			[$setype]
		);
		$tabId = $adb->query_result($result, 0, "tabid");
		$this->tabId = $tabId;
		$this->adb = $adb;
	}

	function getTabId()
	{
		return $this->tabId;
	}

	function getModuleName()
	{
		return $this->moduleName;
	}

	function getFieldNames()
	{
		$adb = $this->adb;
		$arr = [];
		$result = $adb->pquery(
			"select fieldname from vtiger_field where tabid=? and vtiger_field.presence in (0,2)",
			[$this->getTabId()]
		);
		$it = new SQLResultIterator($adb, $result);
		foreach ($it as $row) {
			$arr[] = $row->fieldname;
		}

		return $arr;
	}

	function getFieldType($fieldName)
	{
		$adb = $this->adb;
		$result = $adb->pquery(
			"select * from vtiger_field where fieldname=? and tabid=? and vtiger_field.presence in (0,2)",
			[$fieldName, $this->tabId]
		);
		$uitype = $adb->query_result($result, 0, "uitype");
		$fieldType = new VTFieldType();
		if (in_array($uitype, [50, 51, 73])) {
			$fieldType->type = "Related";
			$fieldType->relatedTo = "Accounts";
		} elseif ($uitype == 71) {
			$fieldType->type = "Number";
		} else {
			$fieldType->type = "String";
		}

		return $fieldType;
	}

	function getFieldTypes()
	{
		$adb = $this->adb;
		$fieldNames = $this->getFieldNames();
		$fieldTypes = [];
		foreach ($fieldNames as $fieldName) {
			$fieldTypes[$fieldName] = $this->getFieldType($fieldName);
		}

		return $fieldTypes;
	}

}

class VTFieldType
{
	function toArray()
	{
		$ro = new ReflectionObject($this);
		$props = $ro->getProperties();
		$arr = [];
		foreach ($props as $prop) {
			$arr[$prop->getName()] = $prop->getValue($this);
		}

		return $arr;
	}

}