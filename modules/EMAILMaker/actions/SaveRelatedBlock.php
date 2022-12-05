<?php
/* * *******************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class EMAILMaker_SaveRelatedBlock_Action extends Vtiger_Action_Controller
{

    public $relblockid = "";

    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function preProcess(Vtiger_Request $request)
    {
        return true;
    }

    public function postProcess(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();
        $rel_module = $request->get('emailmodule');
        $this->relblockid = $request->get('record');
        $name = $request->get('blockname');
        $module = $request->get('primarymodule');
        $secmodule = $request->get('secondarymodule');
        $block = $request->get('relatedblock');
        $standardFilter = $request->get('standard_fiter');
        $advancedFilter = $request->get('advanced_filter');
        $advancedGroupFilterConditions = $request->get('advanced_group_condition');
        $sortFields = $request->get('selected_sort_fields');

        if (!empty($this->relblockid)) {
            $adb->pquery('UPDATE vtiger_emakertemplates_relblocks SET name=?, block=? WHERE relblockid=?', array($name, $block, $this->relblockid));
        } else {
            $this->relblockid = $adb->getUniqueID('vtiger_emakertemplates_relblocks');
            $adb->pquery('INSERT INTO vtiger_emakertemplates_relblocks (relblockid, name, module, secmodule, block) VALUES (?,?,?,?,?)', array($this->relblockid, $name, $module, $secmodule, $block));
            $selectedFields = $request->get('selected_fields');
            $this->saveSelectedFields($selectedFields);
        }
        $this->saveAdvancedFilters($advancedFilter);
        $this->saveSortFields($sortFields);
        echo "<script>window.opener.EMAILMaker_EditJs.refresh_related_blocks_array('" . $this->relblockid . "');
                      self.close();
              </script>";
    }

    public function saveSelectedFields($selectedFields)
    {
        $adb = PearDatabase::getInstance();
        for ($i = 0; $i < count($selectedFields); $i++) {
            if (!empty($selectedFields[$i])) {
                $adb->pquery("INSERT INTO vtiger_emakertemplates_relblockcol (relblockid, colid, columnname) VALUES (?,?,?)",
                    array($this->relblockid, $i, decode_html($selectedFields[$i])));
            }
        }
    }

    public function saveAdvancedFilters($advancedFilter)
    {
        $adb = PearDatabase::getInstance();
        if (!empty($advancedFilter)) {
            $adb->pquery('DELETE FROM vtiger_emakertemplates_relblockcriteria WHERE relblockid = ?', array($this->relblockid));
            $adb->pquery('DELETE FROM vtiger_emakertemplates_relblockcriteria_g WHERE relblockid = ?', array($this->relblockid));
            foreach ($advancedFilter as $groupIndex => $groupInfo) {
                if (empty($groupInfo)) {
                    continue;
                }
                $groupColumns = $groupInfo['columns'];
                $groupCondition = $groupInfo['condition'];
                foreach ($groupColumns as $columnIndex => $columnCondition) {
                    if (empty($columnCondition)) {
                        continue;
                    }
                    $advFilterColumn = $columnCondition["columnname"];
                    $advFilterComparator = $columnCondition["comparator"];
                    $advFilterValue = $columnCondition["value"];
                    $advFilterColumnCondition = $columnCondition["column_condition"];
                    $columnInfo = explode(":", $advFilterColumn);
                    $moduleFieldLabel = $columnInfo[2];
                    list($module, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
                    $fieldInfo = [
                        'fieldlabel' => vtranslate($fieldLabel, $module),
                    ];
                    $fieldType = null;

                    if (!empty($fieldInfo)) {
                        $field = WebserviceField::fromArray($adb, $fieldInfo);
                        $fieldType = $field->getFieldDataType();
                    }

                    if ($fieldType == 'currency') {
                        if ($field->getUIType() == '71') {
                            $advFilterValue = Vtiger_Currency_UIType::convertToDBFormat($advFilterValue, null, true);
                        } else {
                            $advFilterValue = Vtiger_Currency_UIType::convertToDBFormat($advFilterValue);
                        }
                    }
                    $tempVal = explode(",", $advFilterValue);
                    if (($columnInfo[4] == 'D' || ($columnInfo[4] == 'T' && $columnInfo[1] != 'time_start' && $columnInfo[1] != 'time_end') ||
                            ($columnInfo[4] == 'DT')) && ($columnInfo[4] != '' && $advFilterValue != '')) {
                        $val = array();
                        for ($i = 0; $i < count($tempVal); $i++) {
                            if (trim($tempVal[$i]) != '') {
                                $date = new DateTimeField(trim($tempVal[$i]));
                                if ($columnInfo[4] == 'D') {
                                    $val[$i] = DateTimeField::convertToDBFormat(trim($tempVal[$i]));
                                } elseif ($columnInfo[4] == 'DT') {
                                    $val[$i] = $date->getDBInsertDateTimeValue();
                                } else {
                                    $val[$i] = $date->getDBInsertTimeValue();
                                }
                            }
                        }
                        $advFilterValue = implode(",", $val);
                    }
                    $adb->pquery('INSERT INTO vtiger_emakertemplates_relblockcriteria (relblockid, colid, columnname, comparator, value,
                                       groupid, column_condition) VALUES (?,?,?,?,?,?,?)', array(
                        $this->relblockid,
                        $columnIndex,
                        $advFilterColumn,
                        $advFilterComparator,
                        $advFilterValue,
                        $groupIndex,
                        $advFilterColumnCondition
                    ));
                    // Update the condition expression for the group to which the condition column belongs
                    $groupConditionExpression = '';
                    if (!empty($advancedFilter[$groupIndex]["conditionexpression"])) {
                        $groupConditionExpression = $advancedFilter[$groupIndex]["conditionexpression"];
                    }
                    $groupConditionExpression = $groupConditionExpression . ' ' . $columnIndex . ' ' . $advFilterColumnCondition;
                    $advancedFilter[$groupIndex]["conditionexpression"] = $groupConditionExpression;
                }
                $groupConditionExpression = $advancedFilter[$groupIndex]["conditionexpression"];
                if (empty($groupConditionExpression)) {
                    continue;
                } // Case when the group doesn't have any column criteria
                $adb->pquery("INSERT INTO vtiger_emakertemplates_relblockcriteria_g (groupid, relblockid, group_condition, condition_expression) VALUES (?,?,?,?)",
                    array($groupIndex, $this->relblockid, $groupCondition, $groupConditionExpression));
            }
        }
    }

    public function saveSortFields($sortFields)
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vtiger_emakertemplates_relblocksortcol WHERE relblockid = ?', array($this->relblockid));
        $i = 0;
        foreach ($sortFields as $fieldInfo) {
            $adb->pquery('INSERT INTO vtiger_emakertemplates_relblocksortcol (sortcolid, relblockid, columnname, sortorder) VALUES (?,?,?,?)',
                array($i, $this->relblockid, $fieldInfo[0], $fieldInfo[1]));
            $i++;
        }
    }

    public function getSortCols($selectedcolumns)
    {
        $sortCols = array();
        for ($i = 0; $i < count($selectedcolumns); $i++) {
            $sortCols[$i]["order"] = "";
            $sortCols[$i]["sequence"] = "";
        }
        if (isset($_REQUEST["sortColCount"]) && $_REQUEST["sortColCount"] > 0) {
            $seqCounter = 1;
            for ($i = 1; $i <= $_REQUEST["sortColCount"]; $i++) {
                if (isset($_REQUEST["sortCol" . $i]) && isset($_REQUEST["sortDir" . $i])) {
                    $colIdx = array_search($_REQUEST["sortCol" . $i], $selectedcolumns);
                    if ($colIdx !== false) {
                        $sortCols[$colIdx]["order"] = $_REQUEST["sortDir" . $i];
                        $sortCols[$colIdx]["sequence"] = $seqCounter++;
                    }
                }
            }
        }
        return $sortCols;
    }
}