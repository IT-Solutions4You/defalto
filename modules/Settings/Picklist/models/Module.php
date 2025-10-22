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

require_once 'modules/PickList/PickListUtils.php';

class Settings_Picklist_Module_Model extends Vtiger_Module_Model
{
    public function getPickListTableName($fieldName)
    {
        return 'vtiger_' . $fieldName;
    }

    public function getFieldsByType($type)
    {
        $presence = ['0', '2'];

        $fieldModels = parent::getFieldsByType($type);
        $fields = [];
        foreach ($fieldModels as $fieldName => $fieldModel) {
            if (($fieldModel->get('displaytype') != '1' && $fieldName != 'salutationtype') || !in_array($fieldModel->get('presence'), $presence)) {
                continue;
            }
            $fields[$fieldName] = Settings_Picklist_Field_Model::getInstanceFromFieldObject($fieldModel);
        }

        return $fields;
    }

    public function addPickListValues($fieldModel, $newValue, $rolesSelected = [], $color = '')
    {
        $db = PearDatabase::getInstance();
        $pickListFieldName = $fieldModel->getName();
        $id = $db->getUniqueID("vtiger_$pickListFieldName");
        vimport('~~/include/ComboUtil.php');
        $picklist_valueid = getUniquePicklistID();
        $tableName = 'vtiger_' . $pickListFieldName;
        $maxSeqQuery = 'SELECT max(sortorderid) as maxsequence FROM ' . $tableName;
        $result = $db->pquery($maxSeqQuery, []);
        $sequence = $db->query_result($result, 0, 'maxsequence');

        if ($fieldModel->isRoleBased()) {
            $sql = 'INSERT INTO ' . $tableName . ' VALUES (?,?,?,?,?,?)';
            $db->pquery($sql, [$id, $newValue, 1, $picklist_valueid, ++$sequence, $color]);
        } else {
            $sql = 'INSERT INTO ' . $tableName . ' VALUES (?,?,?,?,?)';
            $db->pquery($sql, [$id, $newValue, ++$sequence, 1, $color]);
        }

        if ($fieldModel->isRoleBased() && !empty($rolesSelected)) {
            $sql = "select picklistid from vtiger_picklist where name=?";
            $result = $db->pquery($sql, [$pickListFieldName]);
            $picklistid = $db->query_result($result, 0, "picklistid");
            //add the picklist values to the selected roles
            for ($j = 0; $j < php7_count($rolesSelected); $j++) {
                $roleid = $rolesSelected[$j];
                Vtiger_Cache::delete('PicklistRoleBasedValues', $pickListFieldName . $roleid);
                $sql = "SELECT max(sortid)+1 as sortid
					   FROM vtiger_role2picklist left join vtiger_$pickListFieldName
						   on vtiger_$pickListFieldName.picklist_valueid=vtiger_role2picklist.picklistvalueid
					   WHERE roleid=? and picklistid=?";
                $sortid = $db->query_result($db->pquery($sql, [$roleid, $picklistid]), 0, 'sortid');

                $sql = "insert into vtiger_role2picklist values(?,?,?,?)";
                $db->pquery($sql, [$roleid, $picklist_valueid, $picklistid, $sortid]);
            }
        }
        // we should clear cache to update with latest values
        Vtiger_Cache::flushPicklistCache($pickListFieldName);

        return ['picklistValueId' => $picklist_valueid, 'id' => $id];
    }

    public function renamePickListValues($pickListFieldName, $oldValue, $newValue, $moduleName, $id, $rolesList = false, $color = '')
    {
        $db = PearDatabase::getInstance();

        $query = 'SELECT tablename, fieldid, columnname FROM vtiger_field WHERE fieldname=? and presence IN (0,2)';
        $result = $db->pquery($query, [$pickListFieldName]);
        $num_rows = $db->num_rows($result);

        //As older look utf8 characters are pushed as html-entities,and in new utf8 characters are pushed to database
        //so we are checking for both the values
        $primaryKey = Vtiger_Util_Helper::getPickListId($pickListFieldName);
        if (!empty($color)) {
            $query = 'UPDATE ' . $this->getPickListTableName($pickListFieldName) . ' SET ' . $pickListFieldName . '= ?, color = ? WHERE ' . $primaryKey . ' = ?';
            $db->pquery($query, [$newValue, $color, $id]);
        } else {
            $query = 'UPDATE ' . $this->getPickListTableName($pickListFieldName) . ' SET ' . $pickListFieldName . '=? WHERE ' . $primaryKey . ' = ?';
            $db->pquery($query, [$newValue, $id]);
        }

        $moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
        $fieldModel = Vtiger_Field_Model::getInstance($pickListFieldName, $moduleInstance);
        for ($i = 0; $i < $num_rows; $i++) {
            $row = $db->query_result_rowdata($result, $i);
            $tableName = $row['tablename'];
            $columnName = $row['columnname'];
            if ($fieldModel && $fieldModel->getFieldDataType() == 'multipicklist') {
                $db->pquery(
                    'UPDATE ' . $tableName . ' SET ' . $columnName . ' = TRIM(BOTH " |##| " FROM REPLACE(CONCAT(" |##| ",CONCAT(' . $columnName . ', " |##| ")) , "|##| ' . $oldValue . ' |##|", "|##| ' . $newValue . ' |##|"))',
                    []
                );
            } else {
                $query = 'UPDATE ' . $tableName . ' SET ' . $columnName . '=? WHERE ' . $columnName . '=?';
                $db->pquery($query, [$newValue, $oldValue]);
            }
        }

        $query = "UPDATE vtiger_field SET defaultvalue=? WHERE defaultvalue=? AND columnname=?";
        $db->pquery($query, [$newValue, $oldValue, $columnName]);

        vimport('~~/include/utils/CommonUtils.php');

        $query = "UPDATE vtiger_picklist_dependency SET sourcevalue=? WHERE sourcevalue=? AND sourcefield=?";
        $db->pquery($query, [$newValue, $oldValue, $pickListFieldName]);

        //To update column name for Metric Picklist and Recalculate Transition Map
        $moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
        $fieldModel = Vtiger_Field_Model::getInstance($pickListFieldName, $moduleInstance);

        Vtiger_Cache::flushPicklistCache($pickListFieldName, $rolesList);

        $em = new VTEventsManager($db);
        $data = [];
        $data['fieldId'] = $db->query_result($result, 0, 'fieldid');
        $data['fieldname'] = $pickListFieldName;
        $data['oldvalue'] = $oldValue;
        $data['newvalue'] = $newValue;
        $data['module'] = $moduleName;
        $em->triggerEvent('vtiger.picklist.afterrename', $data);

        return true;
    }

    public function remove($pickListFieldName, $valueToDeleteId, $replaceValueId, $moduleName)
    {
        $db = PearDatabase::getInstance();
        if (!is_array($valueToDeleteId)) {
            $valueToDeleteId = [$valueToDeleteId];
        }
        $primaryKey = Vtiger_Util_Helper::getPickListId($pickListFieldName);

        $pickListValues = [];
        $valuesOfDeleteIds = "SELECT $pickListFieldName FROM " . $this->getPickListTableName($pickListFieldName) . " WHERE $primaryKey IN (" . generateQuestionMarks(
                $valueToDeleteId
            ) . ")";
        $pickListValuesResult = $db->pquery($valuesOfDeleteIds, [$valueToDeleteId]);
        $num_rows = $db->num_rows($pickListValuesResult);
        for ($i = 0; $i < $num_rows; $i++) {
            $pickListValues[] = decode_html($db->query_result($pickListValuesResult, $i, $pickListFieldName));
        }

        $replaceValueQuery = $db->pquery(
            "SELECT $pickListFieldName FROM " . $this->getPickListTableName($pickListFieldName) . " WHERE $primaryKey IN (" . generateQuestionMarks($replaceValueId) . ")",
            [$replaceValueId]
        );
        $replaceValue = decode_html($db->query_result($replaceValueQuery, 0, $pickListFieldName));

        //As older look utf8 characters are pushed as html-entities,and in new utf8 characters are pushed to database
        //so we are checking for both the values
        $encodedValueToDelete = [];
        foreach ($pickListValues as $key => $value) {
            $encodedValueToDelete[$key] = Vtiger_Util_Helper::toSafeHTML($value);
        }
        $mergedValuesToDelete = array_merge($pickListValues, $encodedValueToDelete);

        $fieldModel = Settings_Picklist_Field_Model::getInstance($pickListFieldName, $this);
        //if role based then we need to delete all the values in role based picklist
        if ($fieldModel->isRoleBased()) {
            $picklistValueIdToDelete = [];
            $query = 'SELECT DISTINCT picklist_valueid,roleid FROM ' . $this->getPickListTableName($pickListFieldName) .
                ' AS picklisttable LEFT JOIN vtiger_role2picklist AS roletable ON roletable.picklistvalueid = picklisttable.picklist_valueid
					  WHERE ' . $primaryKey . ' IN (' . generateQuestionMarks($valueToDeleteId) . ')';
            $result = $db->pquery($query, $valueToDeleteId);
            $num_rows = $db->num_rows($result);
            for ($i = 0; $i < $num_rows; $i++) {
                $picklistValueId = $db->query_result($result, $i, 'picklist_valueid');
                $roleId = $db->query_result($result, $i, 'roleid');
                // clear cache to update with lates values
                Vtiger_Cache::delete('PicklistRoleBasedValues', $pickListFieldName . $roleId);
                $picklistValueIdToDelete[$picklistValueId] = $picklistValueId;
            }
            $query = 'DELETE FROM vtiger_role2picklist WHERE picklistvalueid IN (' . generateQuestionMarks($picklistValueIdToDelete) . ')';
            $db->pquery($query, $picklistValueIdToDelete);
        }

        // we should clear cache to update with latest values
        Vtiger_Cache::flushPicklistCache($pickListFieldName);

        $query = 'DELETE FROM ' . $this->getPickListTableName($pickListFieldName) .
            ' WHERE ' . $primaryKey . ' IN (' . generateQuestionMarks($valueToDeleteId) . ')';
        $db->pquery($query, $valueToDeleteId);

        vimport('~~/include/utils/CommonUtils.php');
        $tabId = getTabId($moduleName);
        $query = 'DELETE FROM vtiger_picklist_dependency WHERE sourcevalue IN (' . generateQuestionMarks($pickListValues) . ')' .
            ' AND sourcefield=?';
        $params = [];
        array_push($params, $pickListValues);
        array_push($params, $pickListFieldName);
        $db->pquery($query, $params);

        $query = 'SELECT tablename,columnname FROM vtiger_field WHERE fieldname=? AND presence in (0,2)';
        $result = $db->pquery($query, [$pickListFieldName]);
        $num_row = $db->num_rows($result);

        for ($i = 0; $i < $num_row; $i++) {
            $row = $db->query_result_rowdata($result, $i);
            $tableName = $row['tablename'];
            $columnName = $row['columnname'];

            if ($fieldModel && $fieldModel->getFieldDataType() == 'multipicklist') {
                foreach ($pickListValues as $key => $multipicklistValue) {
                    $db->pquery(
                        'UPDATE ' . $tableName . ' SET ' . $columnName . ' = TRIM(BOTH " |##| " FROM REPLACE(CONCAT(" |##| ",CONCAT(' . $columnName . ', " |##| ")) , " |##| ' . $multipicklistValue . ' |##| ", CASE WHEN INSTR(CONCAT(" |##| " ,CONCAT(' . $columnName . ', " |##| ")), " |##| ' . $replaceValue . ' |##| ") > 0 THEN " |##| " ELSE " |##| ' . $replaceValue . ' |##| " END))',
                        []
                    );
                }
            } else {
                $query = 'UPDATE ' . $tableName . ' SET ' . $columnName . '=? WHERE ' . $columnName . ' IN (' . generateQuestionMarks($pickListValues) . ')';
                $params = [$replaceValue];
                array_push($params, $pickListValues);
                $db->pquery($query, $params);
            }
        }

        $query = 'UPDATE vtiger_field SET defaultvalue=? WHERE defaultvalue IN (' . generateQuestionMarks($pickListValues) . ') AND columnname=?';
        $params = [$replaceValue];
        array_push($params, $pickListValues);
        array_push($params, $columnName);
        $db->pquery($query, $params);

        $em = new VTEventsManager($db);
        $data = [];
        $data['fieldId'] = $fieldModel->id;
        $data['fieldname'] = $pickListFieldName;
        $data['valuetodelete'] = $pickListValues;
        $data['replacevalue'] = $replaceValue;
        $data['module'] = $moduleName;
        $em->triggerEvent('vtiger.picklist.afterdelete', $data);

        return true;
    }

    public function enableOrDisableValuesForRole($picklistFieldName, $valuesToEnables, $valuesToDisable, $roleIdList)
    {
        $db = PearDatabase::getInstance();
        //To disable die On error since we will be doing insert without chekcing
        $dieOnErrorOldValue = $db->dieOnError;
        $db->dieOnError = false;

        $sql = "select picklistid from vtiger_picklist where name=?";
        $result = $db->pquery($sql, [$picklistFieldName]);
        $picklistid = $db->query_result($result, 0, "picklistid");

        $primaryKey = Vtiger_Util_Helper::getPickListId($picklistFieldName);

        $pickListValueList = array_merge($valuesToEnables, $valuesToDisable);
        $pickListValueDetails = [];
        $query = 'SELECT picklist_valueid,' . $picklistFieldName . ', ' . $primaryKey .
            ' FROM ' . $this->getPickListTableName($picklistFieldName) .
            ' WHERE ' . $primaryKey . ' IN (' . generateQuestionMarks($pickListValueList) . ')';
        $params = [];
        array_push($params, $pickListValueList);

        $result = $db->pquery($query, $params);
        $num_rows = $db->num_rows($result);

        for ($i = 0; $i < $num_rows; $i++) {
            $row = $db->query_result_rowdata($result, $i);

            $pickListValueDetails[decode_html($row[$primaryKey])] = [
                'picklistvalueid' => $row['picklist_valueid'],
                'picklistid'      => $picklistid
            ];
        }
        $insertValueList = [];
        $deleteValueList = [];
        foreach ($roleIdList as $roleId) {
            // clearing cache to update with latest values
            Vtiger_Cache::delete('PicklistRoleBasedValues', $picklistFieldName . $roleId);
            foreach ($valuesToEnables as $picklistValue) {
                $valueDetail = $pickListValueDetails[$picklistValue];
                if (empty($valueDetail)) {
                    $valueDetail = $pickListValueDetails[Vtiger_Util_Helper::toSafeHTML($picklistValue)];
                }
                $pickListValueId = $valueDetail['picklistvalueid'];
                $picklistId = $valueDetail['picklistid'];
                $insertValueList[] = '("' . $roleId . '","' . $pickListValueId . '","' . $picklistId . '")';
            }

            foreach ($valuesToDisable as $picklistValue) {
                $valueDetail = $pickListValueDetails[$picklistValue];
                if (empty($valueDetail)) {
                    $valueDetail = $pickListValueDetails[Vtiger_Util_Helper::toSafeHTML($picklistValue)];
                }
                $pickListValueId = $valueDetail['picklistvalueid'];
                $picklistId = $valueDetail['picklistid'];
                $deleteValueList[] = ' ( roleid = "' . $roleId . '" AND ' . 'picklistvalueid = "' . $pickListValueId . '") ';
            }
        }
        if (!empty($insertValueList)) {
            $insertQuery = 'INSERT IGNORE INTO vtiger_role2picklist (roleid,picklistvalueid,picklistid) VALUES ' . implode(",", $insertValueList);
            $result = $db->pquery($insertQuery, []);
        }

        if (!empty($deleteValueList)) {
            $deleteQuery = 'DELETE FROM vtiger_role2picklist WHERE ' . implode(' OR ', $deleteValueList);
            $result = $db->pquery($deleteQuery, []);
        }
        //retaining to older value
        $db->dieOnError = $dieOnErrorOldValue;
    }

    public function updateSequence($pickListFieldName, $picklistValues, $rolesList = false)
    {
        $db = PearDatabase::getInstance();

        $primaryKey = Vtiger_Util_Helper::getPickListId($pickListFieldName);
        $paramArray = [];
        $query = 'UPDATE ' . $this->getPickListTableName($pickListFieldName) . ' SET sortorderid = CASE ';
        foreach ($picklistValues as $values => $sequence) {
            $query .= ' WHEN ' . $primaryKey . '=? THEN ?';
            array_push($paramArray, $values);
            array_push($paramArray, $sequence);
        }
        $query .= ' END';
        $db->pquery($query, $paramArray);
        Vtiger_Cache::flushPicklistCache($pickListFieldName, $rolesList);
    }

    public static function getPicklistSupportedModules()
    {
        $db = PearDatabase::getInstance();
        $unsupportedModuleIds = [getTabId('Users')];
        $query = "SELECT distinct vtiger_tab.tablabel, vtiger_tab.name as tabname
				  FROM vtiger_tab
						inner join vtiger_field on vtiger_tab.tabid=vtiger_field.tabid
				  WHERE uitype IN (15,33,16,114) and vtiger_field.tabid NOT IN (" . generateQuestionMarks($unsupportedModuleIds) . ")  and vtiger_tab.presence != 1 and vtiger_field.presence in (0,2)
				  ORDER BY vtiger_tab.tabid ASC";
        $result = $db->pquery($query, $unsupportedModuleIds);

        $modulesModelsList = [];
        while ($row = $db->fetch_array($result)) {
            $moduleLabel = $row['tablabel'];
            $moduleName = $row['tabname'];
            $instance = new self();
            $instance->name = $moduleName;
            $instance->label = $moduleLabel;
            $modulesModelsList[] = $instance;
        }

        return $modulesModelsList;
    }

    /**
     * Static Function to get the instance of Vtiger Module Model for the given id or name
     *
     * @param mixed id or name of the module
     */
    public static function getInstance($value)
    {
        //TODO : add caching
        $instance = false;
        $moduleObject = parent::getInstance($value);
        if ($moduleObject) {
            $instance = self::getInstanceFromModuleObject($moduleObject);
        }

        return $instance;
    }

    /**
     * Function to get the instance of Vtiger Module Model from a given Vtiger_Module object
     *
     * @param Vtiger_Module $moduleObj
     *
     * @return Vtiger_Module_Model instance
     */
    public static function getInstanceFromModuleObject(Vtiger_Module $moduleObj)
    {
        $objectProperties = get_object_vars($moduleObj);
        $moduleModel = new self();
        foreach ($objectProperties as $properName => $propertyValue) {
            $moduleModel->$properName = $propertyValue;
        }

        return $moduleModel;
    }

    public function handleLabels($moduleName, $newValues, $oldValues, $mode)
    {
        if (!is_array($newValues)) {
            $newValues = [$newValues];
        }
        if (!is_array($oldValues)) {
            $oldValues = [$oldValues];
        }

        $allLang = Vtiger_Language_Handler::getAllLanguages();
        foreach ($allLang as $langKey => $langName) {
            $langDir = 'languages/' . $langKey . '/custom/';
            if (!file_exists($langDir)) {
                mkdir($langDir);
                mkdir($langDir . '/Settings');
            }
            $fileName = $langDir . $moduleName . '.php';
            if (file_exists($fileName)) {
                require $fileName;
            }
            if (!isset($languageStrings)) {
                $languageStrings = [];
            }
            //If mode is delete and $languageStrings is empty then no need of creating file.
            if ($mode == 'delete' && empty($languageStrings)) {
                continue;
            }
            if (!empty($newValues)) {
                foreach ($newValues as $newValue) {
                    $newValue = decode_html($newValue);
                    if (!isset($languageStrings[$newValue])) {
                        $languageStrings[$newValue] = $newValue;
                    }
                }
            }

            if ($mode == 'rename' || $mode == 'delete' && !empty($oldValues)) {
                foreach ($oldValues as $oldValue) {
                    $oldValue = decode_html($oldValue);
                    unset($languageStrings[$oldValue]);
                }
            }

            //Write file
            $fp = fopen($fileName, "w");
            if ($languageStrings) {
                fwrite($fp, "<?php\n\$languageStrings = array(\n");
                foreach ($languageStrings as $key => $value) {
                    $key = addslashes($key);
                    $value = addslashes($value);
                    fwrite($fp, "'$key'\t=>\t'$value',\n");
                }
                fwrite($fp, ");");
            }
            if ($jsLanguageStrings) {
                fwrite($fp, "\n\$jsLanguageStrings = array(\n");
                foreach ($jsLanguageStrings as $key => $value) {
                    $key = addslashes($key);
                    $value = addslashes($value);
                    fwrite($fp, "'$key'\t=>\t'$value',\n");
                }
                fwrite($fp, ");");
            }
            fclose($fp);
            unset($languageStrings);
            unset($jsLanguageStrings);
        }
    }

    function getActualPicklistValues($valueToDelete, $pickListFieldName)
    {
        $db = PearDatabase::getInstance();
        if (!is_array($valueToDelete)) {
            $valueToDeleteID = [$valueToDelete];
        } else {
            $valueToDeleteID = $valueToDelete;
        }
        $primaryKey = Vtiger_Util_Helper::getPickListId($pickListFieldName);
        $pickListDeleteValue = [];
        $getPickListValueQuery = "SELECT $pickListFieldName FROM " . $this->getPickListTableName($pickListFieldName) . " WHERE $primaryKey IN (" . generateQuestionMarks(
                $valueToDeleteID
            ) . ")";
        $result = $db->pquery($getPickListValueQuery, [$valueToDeleteID]);
        $num_rows = $db->num_rows($result);
        for ($i = 0; $i < $num_rows; $i++) {
            $pickListDeleteValue[] = decode_html($db->query_result($result, $i, $pickListFieldName));
        }

        return $pickListDeleteValue;
    }

    /**
     * Function to get the picklist value color
     *
     * @param <string>  $pickListFieldName
     * @param <integer> $pickListId
     *
     * @return <string> $color
     */
    public static function getPicklistColor($pickListFieldName, $pickListId)
    {
        $db = PearDatabase::getInstance();
        $primaryKey = Vtiger_Util_Helper::getPickListId($pickListFieldName);
        $colums = $db->getColumnNames("vtiger_$pickListFieldName");
        if (in_array('color', $colums)) {
            $query = 'SELECT color FROM vtiger_' . $pickListFieldName . ' WHERE ' . $primaryKey . ' = ?';
            $result = $db->pquery($query, [$pickListId]);
            if ($db->num_rows($result) > 0) {
                $color = $db->query_result($result, 0, 'color');
            }
        }

        return $color;
    }

    /**
     * Function to get the accesable picklist values for current user
     *
     * @param <string> $name - picklist field name
     *
     * @return <array> $picklistValues
     */
    public static function getAccessiblePicklistValues($name)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $db = PearDatabase::getInstance();
        $picklistValues = [];
        if (vtws_isRoleBasedPicklist($name)) {
            $picklistValues = getAllPickListValues($name, $currentUser->roleid);
        }

        return $picklistValues;
    }

    /**
     * Function to get the picklist color map of all the picklist values for a field
     *
     * @param <string>  $fieldName - Picklist field name
     * @param <boolean> $key       - to change key of the array
     *
     * @return <array> $pickListColorMap
     */
    public static function getPicklistColorMap($fieldName, $key = false)
    {
        $db = PearDatabase::getInstance();
        $primaryKey = Vtiger_Util_Helper::getPickListId($fieldName);
        $columns = $db->getColumnNames("vtiger_$fieldName");
        if (in_array('color', $columns)) {
            $query = 'SELECT ' . $primaryKey . ',color,' . $fieldName . ' FROM vtiger_' . $fieldName;
            $result = $db->pquery($query, []);
            $pickListColorMap = [];
            $isRoleBasedPicklist = vtws_isRoleBasedPicklist($fieldName);
            $accessablePicklistValues = self::getAccessiblePicklistValues($fieldName);
            if ($db->num_rows($result) > 0) {
                for ($i = 0; $i < $db->num_rows($result); $i++) {
                    $pickListId = $db->query_result($result, $i, $primaryKey);
                    $color = $db->query_result($result, $i, 'color');
                    $picklistNameRaw = $db->query_result($result, $i, $fieldName);
                    $picklistName = decode_html($picklistNameRaw);
                    // show color only for accesable picklist values
                    if ($isRoleBasedPicklist && !isset($accessablePicklistValues[$picklistName])) {
                        $color = '';
                    }
                    if (!empty($color)) {
                        if ($key) {
                            $pickListColorMap[$picklistName] = $color;
                        } else {
                            $pickListColorMap[$pickListId] = $color;
                        }
                    }
                }
            }
        }

        return $pickListColorMap;
    }

    /**
     * Function to get the picklist color for a picklist value
     *
     * @param <string> $fieldName  - picklist field name
     * @param <string> $fieldValue - picklist value
     *
     * @return <string> $color
     */
    public static function getPicklistColorByValue($fieldName, $fieldValue)
    {
        $db = PearDatabase::getInstance();
        $tableName = "vtiger_$fieldName";
        $color = '';
        if (Vtiger_Utils::CheckTable($tableName)) {
            $colums = $db->getColumnNames($tableName);
            $fieldValue = decode_html($fieldValue);
            if (in_array('color', $colums)) {
                $query = 'SELECT color FROM ' . $tableName . ' WHERE ' . $fieldName . ' = ?';
                $result = $db->pquery($query, [$fieldValue]);
                if ($db->num_rows($result) > 0) {
                    $color = $db->query_result($result, 0, 'color');
                }
            }
        }

        return $color;
    }

    public static function getTextColor($hexcolor)
    {
        $hexcolor = str_replace('#', '', $hexcolor);
        $r = intval(substr($hexcolor, 0, 2), 16);
        $g = intval(substr($hexcolor, 2, 2), 16);
        $b = intval(substr($hexcolor, 4, 2), 16);
        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return ($yiq >= 128) ? 'black' : 'white';
    }

    public function updatePicklistColor($pickListFieldName, $id, $color = '')
    {
        $db = PearDatabase::getInstance();

        //As older look utf8 characters are pushed as html-entities,and in new utf8 characters are pushed to database
        //so we are checking for both the values
        $primaryKey = Vtiger_Util_Helper::getPickListId($pickListFieldName);
        if (!empty($color)) {
            $query = 'UPDATE ' . $this->getPickListTableName($pickListFieldName) . ' SET color = ? WHERE ' . $primaryKey . ' = ?';
            $db->pquery($query, array($color, $id));
        }

        return true;
    }
}