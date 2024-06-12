<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_RelatedBlock_Model extends Vtiger_Module_Model
{

    protected $relblockid;

    public function getAdvancedFilterList($relblockid)
    {
        $adb = PearDatabase::getInstance();
        global $modules;
        $advft_criteria = [];
        $groupsresult = $adb->pquery('SELECT * FROM vtiger_emakertemplates_relblockcriteria_g WHERE relblockid = ? ORDER BY relblockid', [$relblockid]);

        $i = 1;
        $j = 0;
        while ($relcriteriagroup = $adb->fetch_array($groupsresult)) {
            $groupId = $relcriteriagroup["groupid"];
            $groupCondition = $relcriteriagroup["group_condition"];

            $ssql = "SELECT rbc.* FROM vtiger_emakertemplates_relblockcriteria AS rbc
                 LEFT JOIN vtiger_emakertemplates_relblockcriteria_g AS rbcg
                    USING(groupid)
                 WHERE rbc.relblockid = ?
                    AND rbc.groupid = ?
                    AND rbcg.relblockid = ?
                 ORDER BY rbc.colid";

            $result = $adb->pquery($ssql, [$relblockid, $groupId, $relblockid]);

            $noOfColumns = $adb->num_rows($result);
            if ($noOfColumns <= 0) {
                continue;
            }

            while ($relcriteriarow = $adb->fetch_array($result)) {
                $columnIndex = $relcriteriarow["colid"];
                $criteria = [];
                $criteria['columnname'] = html_entity_decode($relcriteriarow["columnname"]);
                $criteria['comparator'] = $relcriteriarow["comparator"];
                $advfilterval = $relcriteriarow["value"];
                $col = explode(":", $relcriteriarow["columnname"]);
                $temp_val = explode(",", $relcriteriarow["value"]);
                if ($col[4] == 'D' || ($col[4] == 'T' && $col[1] != 'time_start' && $col[1] != 'time_end') || ($col[4] == 'DT')) {
                    $val = [];
                    for ($x = 0; $x < count($temp_val); $x++) {
                        [$temp_date, $temp_time] = explode(" ", $temp_val[$x]);
                        $temp_date = getValidDisplayDate(trim($temp_date));
                        if (trim($temp_time) != '') {
                            $temp_date .= ' ' . $temp_time;
                        }
                        $val[$x] = $temp_date;
                    }
                    $advfilterval = implode(",", $val);
                }
                $criteria['value'] = decode_html($advfilterval);
                $criteria['column_condition'] = $relcriteriarow["column_condition"];

                $advft_criteria[$i]['columns'][$j] = $criteria;
                $advft_criteria[$i]['condition'] = $groupCondition;
                $j++;
            }
            $i++;
        }
        return $advft_criteria;
    }

    public function getAdvancedFilterSQL()
    {
        $advancedFilter = $this->get('advancedFilter');
        $advancedFilterCriteria = [];
        $advancedFilterCriteriaGroup = [];
        foreach ($advancedFilter as $groupIndex => $groupInfo) {
            $groupColumns = $groupInfo['columns'];
            $groupCondition = $groupInfo['condition'];
            if (empty ($groupColumns)) {
                unset($advancedFilter[1]['condition']);
            } else {
                if (!empty($groupCondition)) {
                    $advancedFilterCriteriaGroup[$groupIndex] = ['groupcondition' => $groupCondition];
                }
            }
            foreach ($groupColumns as $groupColumn) {
                $groupColumn['groupid'] = $groupIndex;
                $groupColumn['columncondition'] = $groupColumn['column_condition'];
                unset($groupColumn['column_condition']);
                $advancedFilterCriteria[] = $groupColumn;
            }
        }

        $this->reportRun = ReportRun::getInstance($this->getId());
        $filterQuery = $this->reportRun->RunTimeAdvFilter($advancedFilterCriteria, $advancedFilterCriteriaGroup);
        return $filterQuery;
    }

    /**
     * @param int $record
     * @return string
     * @throws Exception
     */
    public static function getBlockBody($record)
    {
        $blockData = self::getBlockData($record);
        $body = str_replace(
            [
                'RELBLOCK_START',
                'RELBLOCK_END',
            ],
            [
                sprintf('RELBLOCK%s_START', $record),
                sprintf('RELBLOCK%s_END', $record),
            ],
            $blockData['block']
        );

        return html_entity_decode($body);
    }

    /**
     * @param int $record
     * @return array
     * @throws Exception
     */
    public static function getBlockData($record)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM vtiger_emakertemplates_relblocks WHERE relblockid=?', [$record]);

        return $adb->fetchByAssoc($result);
    }

    /**
     * @throws Exception
     */
    public static function getBlockDateFilter($record)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM vtiger_emakertemplates_relblockdatefilter WHERE datefilterid=?', [$record]);

        return $adb->fetchByAssoc($result);
    }

    /**
     * @throws Exception
     */
    public static function getBlockValue($record, $name)
    {
        $adb = PearDatabase::getInstance();
        $sql = sprintf('SELECT %s FROM vtiger_emakertemplates_relblocks WHERE relblockid=?', $name);
        $result = $adb->pquery($sql, [$record]);

        return $adb->query_result($result, 0, $name);
    }

    public function isAdminQuery($user)
    {
        $is_admin = false;
        $profileGlobalPermission = [];

        require 'user_privileges/user_privileges_' . $user->id . '.php';

        return $is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0;
    }

    public function getColumnsListbyBlock($module, $block, $pri_module, $current_user)
    {
        if (is_string($block)) {
            $block = explode(',', $block);
        }

        $adb = PearDatabase::getInstance();
        $tabId = getTabid($module);
        $params = [$tabId, $block];

        if ($this->isAdminQuery($current_user)) {
            $sql = 'SELECT * FROM vtiger_field 
                WHERE vtiger_field.tabid IN (' . generateQuestionMarks($tabId) . ') AND vtiger_field.block IN (' . generateQuestionMarks($block) . ') AND vtiger_field.displaytype IN (1,2,3) AND vtiger_field.presence IN (0,2) 
                ORDER BY sequence';
        } else {
            $profileList = getCurrentUserProfileList();
            $sql = 'SELECT * FROM vtiger_field 
                INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid=vtiger_field.fieldid 
                INNER JOIN vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid 
                WHERE vtiger_field.tabid IN (' . generateQuestionMarks($tabId) . ') AND vtiger_field.block IN (' . generateQuestionMarks($block) . ') AND vtiger_field.displaytype IN (1,2,3) AND vtiger_profile2field.visible=0 AND vtiger_def_org_field.visible=0 AND vtiger_field.presence IN (0,2)';

            if (count($profileList) > 0) {
                $sql .= ' AND vtiger_profile2field.profileid IN (' . generateQuestionMarks($profileList) . ')';
                $params[] = $profileList;
            }

            $sql .= ' GROUP BY vtiger_field.fieldid ORDER BY sequence';
        }

        $moduleColumnList = [];
        $result = $adb->pquery($sql, $params);

        while ($row = $adb->fetchByAssoc($result)) {
            $fieldTableName = $row['tablename'];
            $fieldColumnName = $row['columnname'];
            $fieldName = $row['fieldname'];
            $uiType = $row['uitype'];
            $fieldLabel = $row['fieldlabel'];
            $fieldType = explode('~', $row['typeofdata']);
            $fieldTypeOfData = ChangeTypeOfData_Filter($fieldTableName, $fieldColumnName, $fieldType[0]);
            $blockId = $row['block'];

            if ($uiType == 68 || $uiType == 59) {
                $fieldTypeOfData = 'V';
            }

            if ($fieldTableName == 'vtiger_crmentity') {
                $fieldTableName = $fieldTableName . $module;
            }

            $fields = [
                'assigned_user_id' => ['vtiger_users' . $module, 'user_name'],
                'account_id' => ['vtiger_account' . $module, 'accountname'],
                'contact_id' => ['vtiger_contactdetails' . $module, 'lastname'],
                'parent_id' => ['vtiger_crmentityRel' . $module, 'setype'],
                'vendor_id' => ['vtiger_vendorRel' . $module, 'vendorname'],
                'potential_id' => ['vtiger_potentialRel' . $module, 'potentialname'],
                'assigned_user_id1' => ['vtiger_usersRel1' . $module, 'user_name'],
                'quote_id' => ['vtiger_quotes' . $module, 'subject'],
            ];

            if (isset($fields[$fieldName])) {
                [$fieldTableName, $fieldColumnName] = $fields[$fieldName];
            }

            $product_id_tables = [
                'vtiger_troubletickets' => 'vtiger_productsRel',
                'vtiger_campaign' => 'vtiger_productsCampaigns',
                'vtiger_faq' => 'vtiger_productsFaq',
            ];

            if ($fieldName == 'product_id' && isset($product_id_tables[$fieldTableName])) {
                $fieldTableName = $product_id_tables[$fieldTableName];
                $fieldColumnName = 'productname';
            }
            if ($fieldName == 'campaignid' && $module == 'Potentials') {
                $fieldTableName = 'vtiger_campaign' . $module;
                $fieldColumnName = 'campaignname';
            }
            if ($fieldName == 'currency_id' && $fieldTableName == 'vtiger_pricebook') {
                $fieldTableName = 'vtiger_currency_info' . $module;
                $fieldColumnName = 'currency_name';
            }

            $optionValue = implode(':', [$fieldTableName, $fieldColumnName, $module . '_' . str_replace(' ', '_', $fieldLabel), $fieldName, $fieldTypeOfData]);

            if ($module != 'HelpDesk' || $fieldName != 'filename') {
                $moduleColumnList[$optionValue] = vtranslate($fieldLabel, $module);
            }

            $blockName = getBlockName($blockId);

            if ($blockName == 'LBL_RELATED_PRODUCTS' && ($module == 'PurchaseOrder' || $module == 'SalesOrder' || $module == 'Quotes' || $module == 'Invoice')) {
                $fieldTableName = 'vtiger_inventoryproductrel';
                $fields = [
                    'productid' => vtranslate('Product Name', $module),
                    'serviceid' => vtranslate('Service Name', $module),
                    'listprice' => vtranslate('List Price', $module),
                    'discount' => vtranslate('Discount', $module),
                    'quantity' => vtranslate('Quantity', $module),
                    'comment' => vtranslate('Comments', $module),
                ];
                $fields_datatype = [
                    'productid' => 'V',
                    'serviceid' => 'V',
                    'listprice' => 'I',
                    'discount' => 'I',
                    'quantity' => 'I',
                    'comment' => 'V',
                ];

                foreach ($fields as $fieldColumnName => $label) {
                    $fieldTypeOfData = $fields_datatype[$fieldColumnName];
                    $optionValue = implode(':', [$fieldTableName, $fieldColumnName, $module . '_' . $label, $fieldColumnName, $fieldTypeOfData]);
                    $moduleColumnList[$optionValue] = $label;
                }
            } elseif ($pri_module == 'PriceBooks' && $blockName == 'LBL_PRICING_INFORMATION' && ($module == 'Products' || $module == 'Services')) {
                $fieldTableName = 'vtiger_pricebookproductreltmp' . $module;
                $fieldColumnName = 'listprice';
                $label = vtranslate('LBL_PB_LIST_PRICE', $module);
                $customTmpLabel = 'LBL@~@PB@~@LIST@~@PRICE';    // "@~@" stands for "_" that needs special handling because of translation of RB header
                $fieldTypeOfData = 'I';
                $optionValue = implode(':', [$fieldTableName, $fieldColumnName, $module . '_' . $customTmpLabel, $fieldColumnName, $fieldTypeOfData]);
                $moduleColumnList[$optionValue] = $label;
            }
        }

        return $moduleColumnList;
    }

    public function getId()
    {
        return $this->get('relblockid');
    }

    /**
     * Function to get the vtiger_fields for the given module
     *
     * @param string $module
     * @return array
     */
    public function getModuleColumnsList($module): array
    {
        global $current_user;

        $moduleFields = [];
        $blocks = $this->getModuleList($module);

        foreach ($blocks as $blockId => $blockLabel) {
            $blockColumns = $this->getColumnsListbyBlock($module, [$blockId], true, $current_user);
            $moduleFields[$blockLabel] = array_merge((array)$moduleFields[$module][$blockLabel], $blockColumns);
        }

        return $moduleFields;
    }

    public function getModuleList($moduleName)
    {
        $adb = PearDatabase::getInstance();
        $moduleId = getTabid($moduleName);
        $result = $adb->pquery('SELECT blockid, blocklabel, tabid FROM vtiger_blocks WHERE tabid IN (?)', [$moduleId]);
        $prevBlockLabel = '';
        $moduleList = [];

        while ($row = $adb->fetch_array($result)) {
            $blockId = $row['blockid'];
            $blockLabel = $row['blocklabel'];

            if (!empty($blockLabel)) {
                $moduleList[$blockId] = vtranslate($blockLabel, $moduleName);
                $prevBlockLabel = $blockLabel;
            } else {
                $moduleList[$blockId] = vtranslate($prevBlockLabel, $moduleName);
            }
        }

        return $moduleList;
    }

    public function getPrimaryModule()
    {
        return $this->primodule;
    }

    public function getPrimaryModuleFields(): array
    {
        $primaryModule = $this->getPrimaryModule();

        return $this->getModuleColumnsList($primaryModule);
    }

    public function getPrimaryModuleRecordStructure()
    {
        $primaryModule = $this->getPrimaryModule();
        $primaryModuleModel = Vtiger_Module_Model::getInstance($primaryModule);
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($primaryModuleModel);
        return $recordStructureInstance;
    }

    public static function getRelatedModulesList($relatedModule)
    {
        $relatedModuleId = Vtiger_Functions::getModuleId($relatedModule);
        $adb = PearDatabase::getInstance();
        $relatedModules = [];
        $result = $adb->pquery(
            'SELECT vtiger_tab.name FROM vtiger_tab 
            INNER JOIN vtiger_relatedlists on vtiger_tab.tabid=vtiger_relatedlists.related_tabid 
            WHERE vtiger_tab.isentitytype = 1 AND vtiger_tab.presence = 0 AND vtiger_relatedlists.tabid = ? AND vtiger_relatedlists.related_tabid != ?', [$relatedModuleId, $relatedModuleId]
        );

        while ($row = $adb->fetchByAssoc($result)) {
            $relatedModules[] = $row['name'];
        }

        if (!in_array('ModComments', $relatedModules) && vtlib_isModuleActive('ModComments')) {
            $result = $adb->pquery(
                'SELECT linkid FROM vtiger_links WHERE tabid = ? AND linktype = ? AND linklabel = ? AND linkurl  = ?', [$relatedModuleId, 'DETAILVIEWWIDGET', 'DetailViewBlockCommentWidget', 'block://ModComments:modules/ModComments/ModComments.php']
            );

            if ($adb->num_rows($result)) {
                $relatedModules[] = 'ModComments';
            }
        }

        return $relatedModules;
    }

    /**
     * Function to set the Secondary module fields for the given module.
     *
     * @param string $module
     * @return array
     */
    public function getSecModuleColumnsList($module): array
    {
        if (empty($module)) {
            return [];
        }

        $columnsList = [];
        $secondaryModules = explode(':', $module);

        foreach ($secondaryModules as $secondaryModule) {
            $columnsList[$secondaryModule] = $this->getModuleColumnsList($secondaryModule);
        }

        return $columnsList;
    }

    public function getSecondaryModule()
    {
        return $this->secmodule;
    }

    public function getSecondaryModuleFields(): array
    {
        $secondaryModule = $this->getSecondaryModule();

        return $this->getSecModuleColumnsList($secondaryModule);
    }

    public function getSecondaryModuleRecordStructure(): array
    {
        $recordStructureInstances = [];
        $secondaryModule = $this->getSecondaryModule();
        
        if (!empty($secondaryModule)) {
            $moduleList = explode(':', $secondaryModule);

            foreach ($moduleList as $module) {
                if (!empty($module)) {
                    $moduleModel = Vtiger_Module_Model::getInstance($module);
                    $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
                    $moduleRecordStructure = $this->filterSystemFields($recordStructureInstance->getStructure());

                    $recordStructureInstances[$module] = $moduleRecordStructure;
                }
            }
        }

        return $recordStructureInstances;
    }

    public function filterSystemFields($moduleRecordStructure)
    {
        foreach ($moduleRecordStructure as $blockLabel => $blockFields) {
            foreach ($blockFields as $fieldName => $fieldModel) {
                if ((int)$fieldModel->getDisplayType() === 6) {
                    unset($moduleRecordStructure[$blockLabel][$fieldName]);
                }
            }
        }

        return $moduleRecordStructure;
    }

    public function getSelectedAdvancedFilter()
    {
        return $this->getAdvancedFilterList($this->getId());
    }

    public function getSelectedSortFields()
    {
        $db = PearDatabase::getInstance();

        $result = $db->pquery(
            'SELECT * FROM vtiger_emakertemplates_relblocksortcol
                                   WHERE relblockid = ? ORDER BY sortcolid',
            [$this->getId()]
        );

        $sortColumns = [];
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $column = $db->query_result($result, $i, 'columnname');
            $order = $db->query_result($result, $i, 'sortorder');
            $sortColumns[$column] = $order;
        }
        return $sortColumns;
    }

    public function getSelectedStandardFilter()
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT * FROM vtiger_emakertemplates_relblockdatefilter WHERE datefilterid = ?', [$this->getId()]);
        $standardFieldInfo = [];
        if ($db->num_rows($result)) {
            $standardFieldInfo['columnname'] = $db->query_result($result, 0, 'datecolumnname');
            $standardFieldInfo['type'] = $db->query_result($result, 0, 'datefilter');
            $standardFieldInfo['startdate'] = $db->query_result($result, 0, 'startdate');
            $standardFieldInfo['enddate'] = $db->query_result($result, 0, 'enddate');

            if ($standardFieldInfo['type'] == "custom" || $standardFieldInfo['type'] == "") {
                if ($standardFieldInfo["startdate"] != "0000-00-00" && $standardFieldInfo["startdate"] != "") {
                    $startDateTime = new DateTimeField($standardFieldInfo["startdate"] . ' ' . date('H:i:s'));
                    $standardFieldInfo["startdate"] = $startDateTime->getDisplayDate();
                }
                if ($standardFieldInfo["enddate"] != "0000-00-00" && $standardFieldInfo["enddate"] != "") {
                    $endDateTime = new DateTimeField($standardFieldInfo["enddate"] . ' ' . date('H:i:s'));
                    $standardFieldInfo["enddate"] = $endDateTime->getDisplayDate();
                }
            } else {
                $startDateTime = new DateTimeField($standardFieldInfo["startdate"] . ' ' . date('H:i:s'));
                $standardFieldInfo["startdate"] = $startDateTime->getDisplayDate();
                $endDateTime = new DateTimeField($standardFieldInfo["enddate"] . ' ' . date('H:i:s'));
                $standardFieldInfo["enddate"] = $endDateTime->getDisplayDate();
            }
        }
        return $standardFieldInfo;
    }

    public function getStdCriteriaByModule($sec_module, $module_list, $current_user)
    {
        $module = '';
        $adb = PearDatabase::getInstance();
        $is_admin = false;
        $profileGlobalPermission = [];
        require('user_privileges/user_privileges_' . $current_user->id . '.php');

        $tabid = getTabid($sec_module);
        foreach ($module_list as $blockid => $key) {
            $blockids[] = $blockid;
        }
        $params = [$tabid, $blockids];
        if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
            //uitype 6 and 23 added for start_date,EndDate,Expected Close Date
            $sql = "select * from vtiger_field where vtiger_field.tabid=? and (vtiger_field.uitype =5 or vtiger_field.uitype = 6 or vtiger_field.uitype = 23 or vtiger_field.displaytype=2) and vtiger_field.block in (" . generateQuestionMarks($blockids) . ") and vtiger_field.presence in (0,2) order by vtiger_field.sequence";
        } else {
            $profileList = getCurrentUserProfileList();
            $sql = "select * from vtiger_field inner join vtiger_tab on vtiger_tab.tabid = vtiger_field.tabid inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid  where vtiger_field.tabid=? and (vtiger_field.uitype =5 or vtiger_field.displaytype=2) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.block in (" . generateQuestionMarks($blockids) . ") and vtiger_field.presence in (0,2)";
            if (count($profileList) > 0) {
                $sql .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
                array_push($params, $profileList);
            }
            $sql .= " order by vtiger_field.sequence";
        }

        $result = $adb->pquery($sql, $params);

        while ($criteriatyperow = $adb->fetch_array($result)) {
            $fieldtablename = $criteriatyperow["tablename"];
            $fieldcolname = $criteriatyperow["columnname"];
            $fieldlabel = $criteriatyperow["fieldlabel"];

            if ($fieldtablename == "vtiger_crmentity") {
                $fieldtablename = $fieldtablename . $module;
            }
            $fieldlabel1 = str_replace(" ", "_", $fieldlabel);
            $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $fieldlabel1;
            $stdcriteria_list[$optionvalue] = vtranslate($fieldlabel, $module);
        }

        return $stdcriteria_list;
    }

    public function setId($value)
    {
        return $this->set('relblockid', $value);
    }

    public function setPrimaryModule($module)
    {
        $this->primodule = $module;
    }

    public function setSecondaryModule($modules)
    {
        $this->secmodule = $modules;
    }

    public function transformStandardFilter()
    {
        $standardFilter = $this->getSelectedStandardFilter();
        if (!empty($standardFilter)) {
            $tranformedStandardFilter = [];
            $tranformedStandardFilter['comparator'] = 'bw';

            $fields = explode(':', $standardFilter['columnname']);

            if ($fields[1] === 'createdtime' || $fields[1] === 'modifiedtime') {
                $tranformedStandardFilter['columnname'] = "$fields[0]:$fields[1]:$fields[3]:$fields[2]:DT";
                $date[] = $standardFilter['startdate'] . ' 00:00:00';
                $date[] = $standardFilter['enddate'] . ' 00:00:00';
                $tranformedStandardFilter['value'] = implode(',', $date);
            } else {
                $tranformedStandardFilter['columnname'] = "$fields[0]:$fields[1]:$fields[3]:$fields[2]:D";
                $tranformedStandardFilter['value'] = $standardFilter['startdate'] . ',' . $standardFilter['enddate'];
            }
            return [$tranformedStandardFilter];
        } else {
            return false;
        }
    }

    public function transformToNewAdvancedFilter()
    {
        $standardFilter = $this->transformStandardFilter();
        $advancedFilter = $this->getSelectedAdvancedFilter();
        $allGroupColumns = $anyGroupColumns = [];
        foreach ($advancedFilter as $index => $group) {
            $columns = $group['columns'];
            $and = $or = 0;
            if (!empty($group['condition'])) {
                $block = $group['condition'];
            }
            if (count($columns) != 1) {
                foreach ($columns as $column) {
                    if ($column['column_condition'] == 'and') {
                        ++$and;
                    } else {
                        ++$or;
                    }
                }
                if ($and == count($columns) - 1 && count($columns) != 1) {
                    $allGroupColumns = array_merge($allGroupColumns, $group['columns']);
                } else {
                    $anyGroupColumns = array_merge($anyGroupColumns, $group['columns']);
                }
            } else {
                if ($block == 'and') {
                    $allGroupColumns = array_merge($allGroupColumns, $group['columns']);
                } else {
                    $anyGroupColumns = array_merge($anyGroupColumns, $group['columns']);
                }
            }
        }
        if ($standardFilter && $standardFilter[0]['value'] != '0000-00-00,0000-00-00') {
            $allGroupColumns = array_merge($allGroupColumns, $standardFilter);
        }
        $transformedAdvancedCondition = [];
        $transformedAdvancedCondition[1] = ['columns' => $allGroupColumns, 'condition' => 'and'];
        $transformedAdvancedCondition[2] = ['columns' => $anyGroupColumns, 'condition' => ''];

        return $transformedAdvancedCondition;
    }

    public function __construct()
    {
    }
}