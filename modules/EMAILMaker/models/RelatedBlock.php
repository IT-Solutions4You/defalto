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
    public function __construct()
    {
    }

    public static function getRelatedModulesList($rel_module)
    {
        $rel_module_id = getTabid($rel_module);
        $adb = PearDatabase::getInstance();
        $restricted_modules = [];
        $Related_Modules = array();

        $rsql = "SELECT vtiger_tab.name FROM vtiger_tab 
				INNER JOIN vtiger_relatedlists on vtiger_tab.tabid=vtiger_relatedlists.related_tabid 
				WHERE vtiger_tab.isentitytype=1 
				AND vtiger_tab.name NOT IN(" . generateQuestionMarks($restricted_modules) . ") 
				AND vtiger_tab.presence=0 AND vtiger_relatedlists.label!='Activity History'
                                AND vtiger_relatedlists.tabid = ? AND vtiger_tab.tabid != ?";
        $relatedmodules = $adb->pquery($rsql, array($restricted_modules, $rel_module_id, $rel_module_id));

        if ($adb->num_rows($relatedmodules)) {
            while ($resultrow = $adb->fetch_array($relatedmodules)) {
                $Related_Modules[] = $resultrow['name'];
            }
        }
        if (!in_array("ModComments", $Related_Modules) && vtlib_isModuleActive("ModComments")) {
            $sql_mc = "SELECT linkid FROM vtiger_links WHERE tabid = ? AND linktype = ? AND linklabel = ? AND linkurl  = ?";
            $result_mc = $adb->pquery($sql_mc, array($rel_module_id, "DETAILVIEWWIDGET", "DetailViewBlockCommentWidget", "block://ModComments:modules/ModComments/ModComments.php"));
            $num_rows_mc = $adb->num_rows($result_mc);
            if ($num_rows_mc > 0) {
                $Related_Modules[] = "ModComments";
            }
        }

        return $Related_Modules;
    }

    public function setId($value)
    {
        return $this->set('relblockid', $value);
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
        $params = array($tabid, $blockids);
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

    public function getColumnsListbyBlock($module, $block, $pri_module, $current_user)
    {
        $adb = PearDatabase::getInstance();

        if (is_string($block)) {
            $block = explode(",", $block);
        }

        $tabid = getTabid($module);
        $params = array($tabid, $block);
        $is_admin = false;
        $profileGlobalPermission = [];

        require('user_privileges/user_privileges_' . $current_user->id . '.php');
        //Security Check 
        if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
            $sql = "select * from vtiger_field where vtiger_field.tabid in (" . generateQuestionMarks($tabid) . ") and vtiger_field.block in (" . generateQuestionMarks($block) . ") and vtiger_field.displaytype in (1,2,3) and vtiger_field.presence in (0,2) ";

            //fix for Ticket #4016
            $sql .= " order by sequence";
        } else {

            $profileList = getCurrentUserProfileList();
            $sql = "select * from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid in (" . generateQuestionMarks($tabid) . ")  and vtiger_field.block in (" . generateQuestionMarks($block) . ") and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
            if (count($profileList) > 0) {
                $sql .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
                array_push($params, $profileList);
            }

            //fix for Ticket #4016
            $sql .= " group by vtiger_field.fieldid order by sequence";
        }

        $result = $adb->pquery($sql, $params);
        $noofrows = $adb->num_rows($result);
        for ($i = 0; $i < $noofrows; $i++) {
            $fieldtablename = $adb->query_result($result, $i, "tablename");
            $fieldcolname = $adb->query_result($result, $i, "columnname");
            $fieldname = $adb->query_result($result, $i, "fieldname");
            $fieldtype = $adb->query_result($result, $i, "typeofdata");
            $uitype = $adb->query_result($result, $i, "uitype");
            $fieldtype = explode("~", $fieldtype);
            $fieldtypeofdata = $fieldtype[0];

            //Here we Changing the displaytype of the field. So that its criteria will be displayed correctly in Reports Advance Filter.
            $fieldtypeofdata = ChangeTypeOfData_Filter($fieldtablename, $fieldcolname, $fieldtypeofdata);

            if ($uitype == 68 || $uitype == 59) {
                $fieldtypeofdata = 'V';
            }
            if ($fieldtablename == "vtiger_crmentity") {
                $fieldtablename = $fieldtablename . $module;
            }
            if ($fieldname == "assigned_user_id") {
                $fieldtablename = "vtiger_users" . $module;
                $fieldcolname = "user_name";
            }
            if ($fieldname == "account_id") {
                $fieldtablename = "vtiger_account" . $module;
                $fieldcolname = "accountname";
            }
            if ($fieldname == "contact_id") {
                $fieldtablename = "vtiger_contactdetails" . $module;
                $fieldcolname = "lastname";
            }
            if ($fieldname == "parent_id") {
                $fieldtablename = "vtiger_crmentityRel" . $module;
                $fieldcolname = "setype";
            }
            if ($fieldname == "vendor_id") {
                $fieldtablename = "vtiger_vendorRel" . $module;
                $fieldcolname = "vendorname";
            }
            if ($fieldname == "potential_id") {
                $fieldtablename = "vtiger_potentialRel" . $module;
                $fieldcolname = "potentialname";
            }
            if ($fieldname == "assigned_user_id1") {
                $fieldtablename = "vtiger_usersRel1";
                $fieldcolname = "user_name";
            }
            if ($fieldname == 'quote_id') {
                $fieldtablename = "vtiger_quotes" . $module;
                $fieldcolname = "subject";
            }

            $product_id_tables = array(
                "vtiger_troubletickets" => "vtiger_productsRel",
                "vtiger_campaign" => "vtiger_productsCampaigns",
                "vtiger_faq" => "vtiger_productsFaq",
            );
            if ($fieldname == 'product_id' && isset($product_id_tables[$fieldtablename])) {
                $fieldtablename = $product_id_tables[$fieldtablename];
                $fieldcolname = "productname";
            }
            if ($fieldname == 'campaignid' && $module == 'Potentials') {
                $fieldtablename = "vtiger_campaign" . $module;
                $fieldcolname = "campaignname";
            }
            if ($fieldname == 'currency_id' && $fieldtablename == 'vtiger_pricebook') {
                $fieldtablename = "vtiger_currency_info" . $module;
                $fieldcolname = "currency_name";
            }

            $fieldlabel = $adb->query_result($result, $i, "fieldlabel");
            $fieldlabel1 = str_replace(" ", "_", $fieldlabel);
            $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $fieldlabel1 . ":" . $fieldname . ":" . $fieldtypeofdata;
            //$this->adv_rel_fields[$fieldtypeofdata][] = '$'.$module.'#'.$fieldname.'$'."::".vtranslate($module,$module)." ".$fieldlabel;
            //added to escape attachments fields in Reports as we have multiple attachments
            if ($module != 'HelpDesk' || $fieldname != 'filename') {
                $module_columnlist[$optionvalue] = vtranslate($fieldlabel, $module);
            }
        }
        $blockname = getBlockName($block);
        if ($blockname == 'LBL_RELATED_PRODUCTS' && ($module == 'PurchaseOrder' || $module == 'SalesOrder' || $module == 'Quotes' || $module == 'Invoice')) {
            $fieldtablename = 'vtiger_inventoryproductrel';
            $fields = array(
                'productid' => vtranslate('Product Name', $module),
                'serviceid' => vtranslate('Service Name', $module),
                'listprice' => vtranslate('List Price', $module),
                'discount' => vtranslate('Discount', $module),
                'quantity' => vtranslate('Quantity', $module),
                'comment' => vtranslate('Comments', $module),
            );
            $fields_datatype = array(
                'productid' => 'V',
                'serviceid' => 'V',
                'listprice' => 'I',
                'discount' => 'I',
                'quantity' => 'I',
                'comment' => 'V',
            );
            foreach ($fields as $fieldcolname => $label) {
                $fieldtypeofdata = $fields_datatype[$fieldcolname];
                $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $label . ":" . $fieldcolname . ":" . $fieldtypeofdata;
                $module_columnlist[$optionvalue] = $label;
            }
        } elseif ($pri_module == "PriceBooks" && $blockname == "LBL_PRICING_INFORMATION" && ($module == "Products" || $module == "Services")) {
            $fieldtablename = "vtiger_pricebookproductreltmp" . $module;
            $fieldcolname = "listprice";
            $label = vtranslate("LBL_PB_LIST_PRICE", $module);
            $customTmpLabel = "LBL@~@PB@~@LIST@~@PRICE";    // "@~@" stands for "_" that needs special handling because of translation of RB header
            $fieldtypeofdata = "I";
            $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $customTmpLabel . ":" . $fieldcolname . ":" . $fieldtypeofdata;
            $module_columnlist[$optionvalue] = $label;
        }

        return $module_columnlist;
    }

    public function getModuleList($sec_module)
    {
        $adb = PearDatabase::getInstance();
        $sec_module_id = getTabid($sec_module);
        $reportblocks = $adb->pquery("SELECT blockid, blocklabel, tabid FROM vtiger_blocks WHERE tabid IN (?)", array($sec_module_id));
        $prev_block_label = '';
        if ($adb->num_rows($reportblocks)) {
            while ($resultrow = $adb->fetch_array($reportblocks)) {
                $blockid = $resultrow['blockid'];
                $blocklabel = $resultrow['blocklabel'];
                if (!empty($blocklabel)) {
                    $module_list[$blockid] = vtranslate($blocklabel, $sec_module);
                    $prev_block_label = $blocklabel;
                } else {
                    $module_list[$blockid] = vtranslate($prev_block_label, $sec_module);
                }
            }
        }
        return $module_list;
    }

    public function setPrimaryModule($module)
    {
        $this->primodule = $module;
    }

    public function setSecondaryModule($modules)
    {
        $this->secmodule = $modules;
    }

    public function getPrimaryModuleFields()
    {
        $primaryModule = $this->getPrimaryModule();
        $pri_module_columnslist = $this->getPriModuleColumnsList($primaryModule);
        //need to add this vtiger_crmentity:crmid:".$module."_ID:crmid:I
        return $pri_module_columnslist;
    }

    public function getPrimaryModule()
    {
        return $this->primodule;
    }

    public function getSecondaryModuleFields()
    {
        $secondaryModule = $this->getSecondaryModule();
        $sec_module_columnslist = $this->getSecModuleColumnsList($secondaryModule);
        return $sec_module_columnslist;
    }

    public function getSecondaryModule()
    {
        return $this->secmodule;
    }

    public function transformToNewAdvancedFilter()
    {
        $standardFilter = $this->transformStandardFilter();
        $advancedFilter = $this->getSelectedAdvancedFilter();
        $allGroupColumns = $anyGroupColumns = array();
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
        $transformedAdvancedCondition = array();
        $transformedAdvancedCondition[1] = array('columns' => $allGroupColumns, 'condition' => 'and');
        $transformedAdvancedCondition[2] = array('columns' => $anyGroupColumns, 'condition' => '');

        return $transformedAdvancedCondition;
    }

    public function transformStandardFilter()
    {
        $standardFilter = $this->getSelectedStandardFilter();
        if (!empty($standardFilter)) {
            $tranformedStandardFilter = array();
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
            return array($tranformedStandardFilter);
        } else {
            return false;
        }
    }

    public function getSelectedStandardFilter()
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT * FROM vtiger_emakertemplates_relblockdatefilter WHERE datefilterid = ?', array($this->getId()));
        $standardFieldInfo = array();
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

    public function getId()
    {
        return $this->get('relblockid');
    }

    public function getSelectedAdvancedFilter()
    {
        return $this->getAdvancedFilterList($this->getId());
    }

    public function getAdvancedFilterList($relblockid)
    {
        $adb = PearDatabase::getInstance();
        global $modules;
        $advft_criteria = array();
        $groupsresult = $adb->pquery('SELECT * FROM vtiger_emakertemplates_relblockcriteria_g WHERE relblockid = ? ORDER BY relblockid', array($relblockid));

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

            $result = $adb->pquery($ssql, array($relblockid, $groupId, $relblockid));

            $noOfColumns = $adb->num_rows($result);
            if ($noOfColumns <= 0) {
                continue;
            }

            while ($relcriteriarow = $adb->fetch_array($result)) {
                $columnIndex = $relcriteriarow["colid"];
                $criteria = array();
                $criteria['columnname'] = html_entity_decode($relcriteriarow["columnname"]);
                $criteria['comparator'] = $relcriteriarow["comparator"];
                $advfilterval = $relcriteriarow["value"];
                $col = explode(":", $relcriteriarow["columnname"]);
                $temp_val = explode(",", $relcriteriarow["value"]);
                if ($col[4] == 'D' || ($col[4] == 'T' && $col[1] != 'time_start' && $col[1] != 'time_end') || ($col[4] == 'DT')) {
                    $val = array();
                    for ($x = 0; $x < count($temp_val); $x++) {
                        list($temp_date, $temp_time) = explode(" ", $temp_val[$x]);
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
        $advancedFilterCriteria = array();
        $advancedFilterCriteriaGroup = array();
        foreach ($advancedFilter as $groupIndex => $groupInfo) {
            $groupColumns = $groupInfo['columns'];
            $groupCondition = $groupInfo['condition'];
            if (empty ($groupColumns)) {
                unset($advancedFilter[1]['condition']);
            } else {
                if (!empty($groupCondition)) {
                    $advancedFilterCriteriaGroup[$groupIndex] = array('groupcondition' => $groupCondition);
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

    public function getPrimaryModuleRecordStructure()
    {
        $primaryModule = $this->getPrimaryModule();
        $primaryModuleModel = Vtiger_Module_Model::getInstance($primaryModule);
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($primaryModuleModel);
        return $recordStructureInstance;
    }

    public function getSecondaryModuleRecordStructure()
    {
        $recordStructureInstances = array();
        $secondaryModule = $this->getSecondaryModule();
        if (!empty($secondaryModule)) {
            $moduleList = explode(':', $secondaryModule);

            foreach ($moduleList as $module) {
                if (!empty($module)) {
                    $moduleModel = Vtiger_Module_Model::getInstance($module);
                    $recordStructureInstances[$module] = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
                }
            }
        }
        return $recordStructureInstances;
    }

    public function getSelectedSortFields()
    {
        $db = PearDatabase::getInstance();

        $result = $db->pquery('SELECT * FROM vtiger_emakertemplates_relblocksortcol
                                   WHERE relblockid = ? ORDER BY sortcolid', array($this->getId()));

        $sortColumns = array();
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $column = $db->query_result($result, $i, 'columnname');
            $order = $db->query_result($result, $i, 'sortorder');
            $sortColumns[$column] = $order;
        }
        return $sortColumns;
    }

    /**
     * @param int $record
     * @return array
     * @throws Exception
     */
    public static function getBlockData($record)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM vtiger_emakertemplates_relblocks WHERE relblockid=?', array($record));

        return $adb->fetchByAssoc($result);
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
                'RELBLOCK_END'
            ],
            [
                sprintf('RELBLOCK%s_START', $record),
                sprintf('RELBLOCK%s_END', $record)
            ],
            $blockData['block']
        );

        return html_entity_decode($body);
    }

    /**
     * @throws Exception
     */
    public static function getBlockDateFilter($record)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM vtiger_emakertemplates_relblockdatefilter WHERE datefilterid=?', array($record));

        return $adb->fetchByAssoc($result);
    }

    /**
     * @throws Exception
     */
    public static function getBlockValue($record, $name)
    {
        $adb = PearDatabase::getInstance();
        $sql = sprintf('SELECT %s FROM vtiger_emakertemplates_relblocks WHERE relblockid=?', $name);
        $result = $adb->pquery($sql, array($record));

        return $adb->query_result($result, 0, $name);
    }
}
