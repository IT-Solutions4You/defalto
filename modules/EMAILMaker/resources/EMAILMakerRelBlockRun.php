<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once('include/database/PearDatabase.php');
require_once('data/CRMEntity.php');
require_once("modules/Reports/Reports.php");
require_once 'modules/Reports/ReportUtils.php';
require_once("vtlib/Vtiger/Module.php");
require_once('modules/Vtiger/helpers/Util.php');

class EMAILMakerRelBlockRun extends CRMEntity
{
    public $primarymodule;
    public $secondarymodule;
    public $orderbylistsql;
    public $orderbylistcolumns;
    public $selectcolumns;
    public $groupbylist;
    public $reportname;
    public $totallist;
    public $_groupinglist = false;
    public $_columnslist = false;
    public $_stdfilterlist = false;
    public $_columnstotallist = false;
    public $_advfiltersql = false;
    public $convert_currency = array(
        'Potentials_Amount',
        'Accounts_Annual_Revenue',
        'Leads_Annual_Revenue',
        'Campaigns_Budget_Cost',
        'Campaigns_Actual_Cost',
        'Campaigns_Expected_Revenue',
        'Campaigns_Actual_ROI',
        'Campaigns_Expected_ROI'
    );
    public $append_currency_symbol_to_value = array(
        'hdnDiscountAmount',
        'txtAdjustment',
        'hdnSubTotal',
        'hdnGrandTotal',
        'hdnTaxType',
        'Products_Unit_Price',
        'Services_Price',
        'Invoice_Total',
        'Invoice_Sub_Total',
        'Invoice_S&H_Amount',
        'Invoice_Discount_Amount',
        'Invoice_Adjustment',
        'Quotes_Total',
        'Quotes_Sub_Total',
        'Quotes_S&H_Amount',
        'Quotes_Discount_Amount',
        'Quotes_Adjustment',
        'SalesOrder_Total',
        'SalesOrder_Sub_Total',
        'SalesOrder_S&H_Amount',
        'SalesOrder_Discount_Amount',
        'SalesOrder_Adjustment',
        'PurchaseOrder_Total',
        'PurchaseOrder_Sub_Total',
        'PurchaseOrder_S&H_Amount',
        'PurchaseOrder_Discount_Amount',
        'PurchaseOrder_Adjustment',
        'Invoice_Paid_Amount',
        'Invoice_Remaining_Amount',
        'SalesOrder_Paid_Amount',
        'SalesOrder_Remaining_Amount',
        'PurchaseOrder_Paid_Amount',
        'PurchaseOrder_Remaining_Amount'
    );
    public $ui10_fields = array();
    public $ui101_fields = array();
    public $EMAILLanguage;
    protected $queryPlanner = null;
    public $relblockid;
    public $labelMapping;

    public function __construct($crmid, $relblockid, $sorcemodule, $relatedmodule)
    {
        $this->crmid = $crmid;
        $this->relblockid = $relblockid;
        $this->primarymodule = $sorcemodule;
        $this->secondarymodule = $relatedmodule;
        $this->queryPlanner = new EMAILMaker_ReportRunQueryPlanner();
    }

    public function getAdvFilterSqlOLD2($relblockid)
    {
        global $current_user;
        $advfilter = $this->getAdvFilterByRBid($relblockid);
        $advcvsql = "";

        foreach ($advfilter as $groupid => $groupinfo) {

            $groupcolumns = $groupinfo["columns"];
            $groupcondition = $groupinfo["condition"];
            $advfiltergroupsql = "";

            foreach ($groupcolumns as $columnindex => $columninfo) {
                $columnname = $columninfo['columnname'];
                $comparator = $columninfo['comparator'];
                $value = $columninfo['value'];
                $columncondition = $columninfo['column_condition'];

                $columns = explode(":", $columnname);
                $datatype = (isset($columns[4])) ? $columns[4] : "";

                if ($columnname != "" && $comparator != "") {
                    $valuearray = explode(",", trim($value));

                    if (isset($valuearray) && count($valuearray) > 1 && $comparator != 'bw') {
                        $advorsql = [];
                        for ($n = 0; $n < count($valuearray); $n++) {
                            $advorsql[] = $this->getRealValues($columns[0], $columns[1], $comparator, trim($valuearray[$n]), $datatype);
                        }
                        if ($comparator == 'n' || $comparator == 'k') {
                            $advorsqls = implode(" and ", $advorsql);
                        } else {
                            $advorsqls = implode(" or ", $advorsql);
                        }
                        $advfiltersql = " (" . $advorsqls . ") ";
                    } elseif ($comparator == 'bw' && count($valuearray) == 2) {
                        $advfiltersql = "(" . $columns[0] . "." . $columns[1] . " between '" . getValidDBInsertDateTimeValue(trim($valuearray[0]), $datatype) . "' and '" . getValidDBInsertDateTimeValue(trim($valuearray[1]), $datatype) . "')";
                    } elseif ($comparator == 'y') {
                        $advfiltersql = sprintf("(%s.%s IS NULL OR %s.%s = '')", $columns[0], $columns[1], $columns[0], $columns[1]);
                    } elseif ('ny' === $comparator) {
                        $advfiltersql = sprintf("(%s.%s IS NOT NULL OR %s.%s != '')", $columns[0], $columns[1], $columns[0], $columns[1]);
                    } else {
                        if ($this->customviewmodule == "Documents" && $columns[1] == 'folderid') {
                            $advfiltersql = "vtiger_attachmentsfolder.foldername" . $this->getAdvComparator($comparator, trim($value), $datatype);
                        } elseif ($this->customviewmodule == "Assets") {
                            if ($columns[1] == 'account') {
                                $advfiltersql = "vtiger_account.accountname" . $this->getAdvComparator($comparator, trim($value), $datatype);
                            }
                            if ($columns[1] == 'product') {
                                $advfiltersql = "vtiger_products.productname" . $this->getAdvComparator($comparator, trim($value), $datatype);
                            }
                            if ($columns[1] == 'invoiceid') {
                                $advfiltersql = "vtiger_invoice.subject" . $this->getAdvComparator($comparator, trim($value), $datatype);
                            }
                        } else {
                            $advfiltersql = $this->getRealValues($columns[0], $columns[1], $comparator, trim($value), $datatype);
                        }
                    }

                    $advfiltergroupsql .= $advfiltersql;
                    if ($columncondition != null && $columncondition != '' && count($groupcolumns) > $columnindex) {
                        $advfiltergroupsql .= ' ' . $columncondition . ' ';
                    }
                }
            }

            if (trim($advfiltergroupsql) != "") {
                $advfiltergroupsql = "( $advfiltergroupsql ) ";
                if ($groupcondition != null && $groupcondition != '' && $advfilter > $groupid) {
                    $advfiltergroupsql .= ' ' . $groupcondition . ' ';
                }

                $advcvsql .= $advfiltergroupsql;
            }
        }
        if (trim($advcvsql) != "") {
            $advcvsql = '(' . $advcvsql . ')';
        }
        return $advcvsql;
    }

    public function getAdvFilterByRBid($relblockid)
    {
        global $adb, $log, $default_charset;
        $advft_criteria = array();
        $sql = 'SELECT * FROM vtiger_emakertemplates_relblockcriteria_g WHERE relblockid = ? ORDER BY groupid';
        $groupsresult = $adb->pquery($sql, array($relblockid));
        $i = 1;
        $j = 0;
        while ($relcriteriagroup = $adb->fetch_array($groupsresult)) {
            $groupId = $relcriteriagroup["groupid"];
            $groupCondition = $relcriteriagroup["group_condition"];

            $ssql = 'select vtiger_emakertemplates_relblockcriteria.* from vtiger_emakertemplates_relblocks
						inner join vtiger_emakertemplates_relblockcriteria on vtiger_emakertemplates_relblockcriteria.relblockid = vtiger_emakertemplates_relblocks.relblockid
						left join vtiger_emakertemplates_relblockcriteria_g on vtiger_emakertemplates_relblockcriteria.relblockid = vtiger_emakertemplates_relblockcriteria_g.relblockid
								and vtiger_emakertemplates_relblockcriteria.groupid = vtiger_emakertemplates_relblockcriteria_g.groupid';
            $ssql .= " where vtiger_emakertemplates_relblocks.relblockid = ? AND vtiger_emakertemplates_relblockcriteria.groupid = ? order by vtiger_emakertemplates_relblockcriteria.colid";

            $result = $adb->pquery($ssql, array($relblockid, $groupId));
            $noOfColumns = $adb->num_rows($result);
            if ($noOfColumns <= 0) {
                continue;
            }

            while ($relcriteriarow = $adb->fetch_array($result)) {
                $columnIndex = $relcriteriarow["columnindex"];
                $criteria = array();
                $criteria['columnname'] = html_entity_decode($relcriteriarow["columnname"], ENT_QUOTES, $default_charset);
                $criteria['comparator'] = $relcriteriarow["comparator"];
                $advfilterval = html_entity_decode($relcriteriarow["value"], ENT_QUOTES, $default_charset);
                $col = explode(":", $relcriteriarow["columnname"]);
                $temp_val = explode(",", $relcriteriarow["value"]);
                if ($col[4] == 'D' || ($col[4] == 'T' && $col[1] != 'time_start' && $col[1] != 'time_end') || ($col[4] == 'DT')) {
                    $val = array();
                    for ($x = 0; $x < count($temp_val); $x++) {
                        if ($col[4] == 'D') {
                            $date = new DateTimeField(trim($temp_val[$x]));
                            $val[$x] = $date->getDisplayDate();
                        } elseif ($col[4] == 'DT') {
                            $comparator = array('e', 'n', 'b', 'a');
                            if (in_array($criteria['comparator'], $comparator)) {
                                $originalValue = $temp_val[$x];
                                $dateTime = explode(' ', $originalValue);
                                $temp_val[$x] = $dateTime[0];
                            }
                            $date = new DateTimeField(trim($temp_val[$x]));
                            $val[$x] = $date->getDisplayDateTimeValue();
                        } else {
                            $date = new DateTimeField(trim($temp_val[$x]));
                            $val[$x] = $date->getDisplayTime();
                        }
                    }
                    $advfilterval = implode(",", $val);
                }
                $criteria['value'] = $advfilterval;
                $criteria['column_condition'] = $relcriteriarow["column_condition"];

                $advft_criteria[$i]['columns'][$j] = $criteria;
                $advft_criteria[$i]['condition'] = $groupCondition;
                $j++;
            }
            if (!empty($advft_criteria[$i]['columns'][$j - 1]['column_condition'])) {
                $advft_criteria[$i]['columns'][$j - 1]['column_condition'] = '';
            }
            $i++;
        }
        if (!empty($advft_criteria[$i - 1]['condition'])) {
            $advft_criteria[$i - 1]['condition'] = '';
        }
        return $advft_criteria;
    }

    public function getAdvComparator($comparator, $value, $datatype = "")
    {

        global $log, $adb, $default_charset;
        $value = html_entity_decode(trim($value), ENT_QUOTES, $default_charset);
        $value_len = strlen($value);
        $is_field = false;
        if ($value[0] == '$' && $value[$value_len - 1] == '$') {
            $temp = str_replace('$', '', $value);
            $is_field = true;
        }
        if ($datatype == 'C') {
            $value = str_replace("yes", "1", str_replace("no", "0", $value));
        }
        if ($is_field == true) {
            $value = $this->getFilterComparedField($temp);
        }
        if ($comparator == "e") {
            if (trim($value) == "NULL") {
                $rtvalue = " is NULL";
            } elseif (trim($value) != "") {
                $rtvalue = " = " . $adb->quote($value);
            } elseif (trim($value) == "" && $datatype == "V") {
                $rtvalue = " = " . $adb->quote($value);
            } else {
                $rtvalue = " is NULL";
            }
        }
        if ($comparator == "n") {
            if (trim($value) == "NULL") {
                $rtvalue = " is NOT NULL";
            } elseif (trim($value) != "") {
                $rtvalue = " <> " . $adb->quote($value);
            } elseif (trim($value) == "" && $datatype == "V") {
                $rtvalue = " <> " . $adb->quote($value);
            } else {
                $rtvalue = " is NOT NULL";
            }
        }
        if ($comparator == "s") {
            $rtvalue = " like '" . formatForSqlLike($value, 2, $is_field) . "'";
        }
        if ($comparator == "ew") {
            $rtvalue = " like '" . formatForSqlLike($value, 1, $is_field) . "'";
        }
        if ($comparator == "c") {
            $rtvalue = " like '" . formatForSqlLike($value, 0, $is_field) . "'";
        }
        if ($comparator == "k") {
            $rtvalue = " not like '" . formatForSqlLike($value, 0, $is_field) . "'";
        }
        if ($comparator == "l") {
            $rtvalue = " < " . $adb->quote($value);
        }
        if ($comparator == "g") {
            $rtvalue = " > " . $adb->quote($value);
        }
        if ($comparator == "m") {
            $rtvalue = " <= " . $adb->quote($value);
        }
        if ($comparator == "h") {
            $rtvalue = " >= " . $adb->quote($value);
        }
        if ($comparator == "b") {
            $rtvalue = " < " . $adb->quote($value);
        }
        if ($comparator == "a") {
            $rtvalue = " > " . $adb->quote($value);
        }
        if ($is_field == true) {
            $rtvalue = str_replace("'", "", $rtvalue);
            $rtvalue = str_replace("\\", "", $rtvalue);
        }
        $log->info("ReportRun :: Successfully returned getAdvComparator");
        return $rtvalue;
    }

    public function getFilterComparedField($field)
    {
        global $adb, $ogReport;
        if (!empty($this->secondarymodule)) {
            $secModules = explode(':', $this->secondarymodule);
            foreach ($secModules as $secModule) {
                $secondary = CRMEntity::getInstance($secModule);
                $this->queryPlanner->addTable($secondary->table_name);
            }
        }
        $field = explode('#', $field);
        $module = $field[0];
        $fieldname = trim($field[1]);
        $tabid = getTabId($module);
        $field_query = $adb->pquery("SELECT tablename,columnname,typeofdata,fieldname,uitype FROM vtiger_field WHERE tabid = ? AND fieldname= ?", array($tabid, $fieldname));
        $fieldtablename = $adb->query_result($field_query, 0, 'tablename');
        $fieldcolname = $adb->query_result($field_query, 0, 'columnname');
        $typeofdata = $adb->query_result($field_query, 0, 'typeofdata');
        $fieldtypeofdata = ChangeTypeOfData_Filter($fieldtablename, $fieldcolname, $typeofdata[0]);
        $uitype = $adb->query_result($field_query, 0, 'uitype');

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
        if ($fieldname == 'product_id' && $fieldtablename == 'vtiger_troubletickets') {
            $fieldtablename = "vtiger_productsRel";
            $fieldcolname = "productname";
        }
        if ($fieldname == 'product_id' && $fieldtablename == 'vtiger_campaign') {
            $fieldtablename = "vtiger_productsCampaigns";
            $fieldcolname = "productname";
        }
        if ($fieldname == 'product_id' && $fieldtablename == 'vtiger_products') {
            $fieldtablename = "vtiger_productsProducts";
            $fieldcolname = "productname";
        }
        if ($fieldname == 'campaignid' && $module == 'Potentials') {
            $fieldtablename = "vtiger_campaign" . $module;
            $fieldcolname = "campaignname";
        }
        $value = $fieldtablename . "." . $fieldcolname;
        $this->queryPlanner->addTable($fieldtablename);
        return $value;
    }

    public function getStandardCriterialSql($relblockid)
    {
        $adb = PearDatabase::getInstance();
        global $modules;
        global $log;

        $sreportstdfiltersql = "select vtiger_emakertemplates_relblockdatefilter.* from vtiger_emakertemplates_relblocks";
        $sreportstdfiltersql .= " inner join vtiger_emakertemplates_relblockdatefilter on vtiger_emakertemplates_relblocks.relblockid = vtiger_emakertemplates_relblockdatefilter.datefilterid";
        $sreportstdfiltersql .= " where vtiger_emakertemplates_relblocks.relblockid = ?";

        $result = $adb->pquery($sreportstdfiltersql, array($relblockid));
        $noofrows = $adb->num_rows($result);

        for ($i = 0; $i < $noofrows; $i++) {
            $fieldcolname = $adb->query_result($result, $i, "datecolumnname");
            $datefilter = $adb->query_result($result, $i, "datefilter");
            $startdate = $adb->query_result($result, $i, "startdate");
            $enddate = $adb->query_result($result, $i, "enddate");

            if ($fieldcolname != "none") {
                $selectedfields = explode(":", $fieldcolname);
                if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
                    $selectedfields[0] = "vtiger_crmentity";
                }
                if ($datefilter == "custom") {
                    if ($startdate != "0000-00-00" && $enddate != "0000-00-00" && $selectedfields[0] != "" && $selectedfields[1] != "") {
                        $sSQL .= $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startdate . "' and '" . $enddate . "'";
                    }
                } else {
                    $startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);
                    if ($startenddate[0] != "" && $startenddate[1] != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {
                        $sSQL .= $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startenddate[0] . "' and '" . $startenddate[1] . "'";
                    }
                }
            }
        }
        $log->info("ReportRun :: Successfully returned getStandardCriterialSql" . $relblockid);
        return $sSQL;
    }

    public function getStandarFiltersStartAndEndDate($type)
    {
        $today = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
        $tomorrow = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
        $yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
        $currentmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
        $currentmonth1 = date("Y-m-t");
        $lastmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, "01", date("Y")));
        $lastmonth1 = date("Y-m-t", strtotime("-1 Month"));
        $nextmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, "01", date("Y")));
        $nextmonth1 = date("Y-m-t", strtotime("+1 Month"));
        $lastweek0 = date("Y-m-d", strtotime("-2 week Sunday"));
        $lastweek1 = date("Y-m-d", strtotime("-1 week Saturday"));
        $thisweek0 = date("Y-m-d", strtotime("-1 week Sunday"));
        $thisweek1 = date("Y-m-d", strtotime("this Saturday"));
        $nextweek0 = date("Y-m-d", strtotime("this Sunday"));
        $nextweek1 = date("Y-m-d", strtotime("+1 week Saturday"));
        $next7days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 6, date("Y")));
        $next30days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 29, date("Y")));
        $next60days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 59, date("Y")));
        $next90days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 89, date("Y")));
        $next120days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 119, date("Y")));
        $last7days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 6, date("Y")));
        $last30days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 29, date("Y")));
        $last60days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 59, date("Y")));
        $last90days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 89, date("Y")));
        $last120days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 119, date("Y")));
        $currentFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
        $currentFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y")));
        $lastFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") - 1));
        $lastFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y") - 1));
        $nextFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") + 1));
        $nextFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y") + 1));

        if (date("m") <= 3) {
            $cFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
            $cFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y")));
            $nFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
            $nFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
            $pFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y") - 1));
            $pFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));
        } else {
            if (date("m") > 3 and date("m") <= 6) {
                $pFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
                $pFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y")));
                $cFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
                $cFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
                $nFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
                $nFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
            } else {
                if (date("m") > 6 and date("m") <= 9) {
                    $nFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y")));
                    $nFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
                    $pFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
                    $pFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
                    $cFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
                    $cFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
                } else {
                    if (date("m") > 9 and date("m") <= 12) {
                        $nFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") + 1));
                        $nFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y") + 1));
                        $pFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
                        $pFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
                        $cFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y")));
                        $cFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
                    }
                }
            }
        }

        if ($type == "today") {
            $datevalue[0] = $today;
            $datevalue[1] = $today;
        } elseif ($type == "yesterday") {
            $datevalue[0] = $yesterday;
            $datevalue[1] = $yesterday;
        } elseif ($type == "tomorrow") {
            $datevalue[0] = $tomorrow;
            $datevalue[1] = $tomorrow;
        } elseif ($type == "thisweek") {
            $datevalue[0] = $thisweek0;
            $datevalue[1] = $thisweek1;
        } elseif ($type == "lastweek") {
            $datevalue[0] = $lastweek0;
            $datevalue[1] = $lastweek1;
        } elseif ($type == "nextweek") {
            $datevalue[0] = $nextweek0;
            $datevalue[1] = $nextweek1;
        } elseif ($type == "thismonth") {
            $datevalue[0] = $currentmonth0;
            $datevalue[1] = $currentmonth1;
        } elseif ($type == "lastmonth") {
            $datevalue[0] = $lastmonth0;
            $datevalue[1] = $lastmonth1;
        } elseif ($type == "nextmonth") {
            $datevalue[0] = $nextmonth0;
            $datevalue[1] = $nextmonth1;
        } elseif ($type == "next7days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next7days;
        } elseif ($type == "next30days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next30days;
        } elseif ($type == "next60days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next60days;
        } elseif ($type == "next90days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next90days;
        } elseif ($type == "next120days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next120days;
        } elseif ($type == "last7days") {
            $datevalue[0] = $last7days;
            $datevalue[1] = $today;
        } elseif ($type == "last30days") {
            $datevalue[0] = $last30days;
            $datevalue[1] = $today;
        } elseif ($type == "last60days") {
            $datevalue[0] = $last60days;
            $datevalue[1] = $today;
        } else {
            if ($type == "last90days") {
                $datevalue[0] = $last90days;
                $datevalue[1] = $today;
            } elseif ($type == "last120days") {
                $datevalue[0] = $last120days;
                $datevalue[1] = $today;
            } elseif ($type == "thisfy") {
                $datevalue[0] = $currentFY0;
                $datevalue[1] = $currentFY1;
            } elseif ($type == "prevfy") {
                $datevalue[0] = $lastFY0;
                $datevalue[1] = $lastFY1;
            } elseif ($type == "nextfy") {
                $datevalue[0] = $nextFY0;
                $datevalue[1] = $nextFY1;
            } elseif ($type == "nextfq") {
                $datevalue[0] = $nFq;
                $datevalue[1] = $nFq1;
            } elseif ($type == "prevfq") {
                $datevalue[0] = $pFq;
                $datevalue[1] = $pFq1;
            } elseif ($type == "thisfq") {
                $datevalue[0] = $cFq;
                $datevalue[1] = $cFq1;
            } else {
                $datevalue[0] = "";
                $datevalue[1] = "";
            }
        }
        return $datevalue;
    }

    public function replaceSpecialChar($selectedfield)
    {
        $selectedfield = decode_html(decode_html($selectedfield));
        preg_match('/&/', $selectedfield, $matches);
        if (!empty($matches)) {
            $selectedfield = str_replace('&', 'and', ($selectedfield));
        }
        return $selectedfield;
    }

    public function getModulesSelected()
    {
        $modules_selected = array(
            $this->primarymodule
        );

        if (!empty($this->secondarymodule)) {
            $sec_modules = explode(':', $this->secondarymodule);
            for ($i = 0; $i < count($sec_modules); $i++) {
                $modules_selected[] = $sec_modules[$i];
            }
        }

        return $modules_selected;
    }

    public function retrieveCurrencyFields()
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT tabid, fieldlabel, fieldname, uitype from vtiger_field WHERE uitype in (71,72,10)', array());

        if ($result) {
            foreach ($result as $currencyFieldRow) {
                $label = getTabModuleName($currencyFieldRow['tabid']) . ' ' . $currencyFieldRow['fieldlabel'];
                $label = str_replace(' ', '_', $label);
                $name = $currencyFieldRow['fieldname'];  // ITS4YOU VlZa
                $uiType = intval($currencyFieldRow['uitype']);

                if (10 !== $uiType && 101 !== $uiType) {
                    if (!in_array($label, $this->convert_currency) && !in_array($label, $this->append_currency_symbol_to_value)) {
                        $this->convert_currency[] = $label;
                    }
                } else {
                    if (10 === $uiType && !in_array($name, $this->ui10_fields)) {
                        $this->ui10_fields[] = $label;
                    } elseif (101 === $uiType && !in_array($label, $this->ui101_fields)) {
                        $this->ui101_fields[] = $label;
                    }
                }
            }
        }
    }

    /**
     * @return array|false
     */
    public function getPicklistArray()
    {
        global $current_user, $is_admin, $profileGlobalPermission;
        require('user_privileges/user_privileges_' . $current_user->id . '.php');

        if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
            return $this->getAccessPickListValues();
        }

        return false;
    }

    /**
     * @return array|false
     */
    public function GenerateReport()
    {
        global $adb, $php_max_execution_time;

        $this->retrieveCurrencyFields();

        $sql = $this->sGetSQLforReport($this->relblockid);
        $result = $adb->pquery($sql, array());

        if ($result) {
            $returnData = array();
            $y = $adb->num_fields($result);
            $custom_field_values = $adb->fetch_array($result);
            $rowIndex = 1;
            $toShow = false;
            $picklistArray = $this->getPicklistArray();

            do {
                $rowData = array();

                for ($i = 0; $i < $y; $i++) {
                    $field = $adb->field_name($result, $i);
                    $fieldValue = $this->getEMAILMakerFieldValue($this, $picklistArray, $field, $custom_field_values, $i);

                    list($module, $fieldLabel) = explode('_', $field->name, 2);

                    // listprice is special field for PriceBook
                    if ('-' !== $fieldValue && 'listprice' !== $fieldLabel) {
                        $toShow = true;
                    }

                    $rowData[$fieldLabel] = $fieldValue;

                    if ('Assigned_To' === $fieldLabel) {
                        $rowData['assigned_user_id'] = $fieldValue;
                    }
                }

                if ($toShow) {
                    $rowData['cridx'] = $rowIndex++;
                }

                set_time_limit($php_max_execution_time);

                $returnData[] = $rowData;
            } while ($custom_field_values = $adb->fetch_array($result));

            return $returnData;
        }

        return false;
    }

    public function sGetSQLforReport($relBlockId)
    {
        global $log;

        $columnList = $this->getQueryColumnsList($relBlockId);
        $sortColSql = $this->getSortColSql($columnList, $relBlockId);
        $stdFilterList = $this->getStdFilterList($relBlockId);
        $advFilterSql = $this->getAdvFilterSql($relBlockId);
        $selectList = $columnList;
        $whereSql = '';

        if (!empty($selectList)) {
            $selectedColumns = implode(', ', $selectList);
        }
        if (!empty($stdFilterList)) {
            $stdFilterSql = implode(", ", $stdFilterList);
        }
        if (!empty($stdFilterSql)) {
            $whereSql .= ' and ' . $stdFilterSql;
        }
        if (!empty($advFilterSql)) {
            $whereSql .= ' and ' . $advFilterSql;
        }

        $reportQuery = $this->getReportsQuery($this->primarymodule);

        if (!empty($this->secondarymodule) && false !== strpos($reportQuery, 'left join vtiger_crmentityrel as ')) {
            $Exploded1 = explode('left join vtiger_crmentityrel as ', $reportQuery);
            $Exploded2 = explode(' ON ', $Exploded1[1]);
            $relAlias = $Exploded2[0];
            $whereSql .= sprintf(' AND (%s.module="%s" OR %s.relmodule="%s") ', $relAlias, $this->secondarymodule, $relAlias, $this->secondarymodule);
        }

        $allColumnsRestricted = false;

        if (empty($selectedColumns)) {
            $selectedColumns = "''"; // "''" to get blank column name
            $allColumnsRestricted = true;
        }

        $distinctModules = array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder');

        if (in_array($this->primarymodule, $distinctModules) || in_array($this->secondarymodule, $distinctModules)) {
            $selectedColumns = ' DISTINCT ' . $selectedColumns;
        }

        $reportQuery = sprintf('SELECT %s %s %s', $selectedColumns, $reportQuery, $whereSql);
        $reportQuery = listQueryNonAdminChange($reportQuery, $this->primarymodule);
        $reportQuery .= ' ' . $sortColSql;

        if ($allColumnsRestricted) {
            $reportQuery .= ' LIMIT 0';
        }

        $log->info('ReportRun :: Successfully returned sGetSQLforReport' . $relBlockId);

        return $reportQuery;
    }

    public function getQueryColumnsList($reportId, $outputformat = '')
    {
        global $log, $current_user;

        $adb = PearDatabase::getInstance();

        if ($this->_columnslist !== false) {
            return $this->_columnslist;
        }

        $sql = 'SELECT vtiger_emakertemplates_relblockcol.* FROM vtiger_emakertemplates_relblocks 
            LEFT join vtiger_emakertemplates_relblockcol ON vtiger_emakertemplates_relblockcol.relblockid = vtiger_emakertemplates_relblocks.relblockid 
            WHERE vtiger_emakertemplates_relblocks.relblockid = ? 
            ORDER BY vtiger_emakertemplates_relblockcol.colid';
        $result = $adb->pquery($sql, array($reportId));
        $permitted_fields = array();

        while ($columnsListRow = $adb->fetch_array($result)) {
            $fieldColumn = $columnsListRow['columnname'];

            [$tableName, $columnName, $moduleFieldLabel, $fieldName, $single] = explode(':', $fieldColumn);
            [$module, $field] = explode('_', $moduleFieldLabel, 2);

            $targetTableName = $tableName;
            $inventory_fields = array('serviceid');
            $inventory_modules = getInventoryModules();
            $is_admin = false;
            $profileGlobalPermission = [];

            require('user_privileges/user_privileges_' . $current_user->id . '.php');

            if (empty($permitted_fields[$module]) && false == $is_admin && 1 == $profileGlobalPermission[1] && 1 == $profileGlobalPermission[2]) {
                $permitted_fields[$module] = $this->getaccesfield($module);
            }

            if (in_array($module, $inventory_modules)) {
                if (!empty ($permitted_fields)) {
                    foreach ($inventory_fields as $value) {
                        array_push($permitted_fields[$module], $value);
                    }
                }
            }

            if (false == $is_admin && 1 == $profileGlobalPermission[1] && 1 == $profileGlobalPermission[2] && !in_array($fieldName, $permitted_fields[$module])) {
                continue;
            }

            if ('vtiger_contactdetailsHelpDesk' === $tableName) {
                $tableName = 'vtiger_contactdetailsRelHelpDesk';
            }

            $concatSql = getSqlForNameInDisplayFormat(array('first_name' => $tableName . ".first_name", 'last_name' => $tableName . ".last_name"), 'Users');
            $queryColumns = $this->getEscapedColumns(explode(':', $fieldColumn));
            $fieldLabel = trim(preg_replace("/$module/", ' ', $moduleFieldLabel, 1));
            $mod_arr = explode('_', $fieldLabel);
            $fieldLabel = trim(str_replace('_', ' ', $fieldLabel));
            $fld_arr = explode(' ', $fieldLabel);

            if (empty($mod_arr[0])) {
                $mod = $module;
            } else {
                $mod = $mod_arr[0];
                array_shift($fld_arr);
            }

            $fieldLabel = $mod . '_' . $fieldName;

            if ('vtiger_usersRel1' === $tableName && 'user_name' === $columnName && 'Quotes_Inventory_Manager' === $fieldName) {
                $columnsList[$fieldColumn] = "trim( $concatSql ) as " . $module . "_Inventory_Manager";
                $this->queryPlanner->addTable($tableName);
                continue;
            }

            if (('true' != CheckFieldPermission($fieldName, $mod) && 'crmid' != $columnName && (!in_array($fieldName, $inventory_fields) && in_array($module, $inventory_modules))) || empty($fieldName)) {
                continue;
            } else {
                $this->labelMapping[$fieldName] = str_replace(" ", "_", $fieldLabel);
                $header_label = $fieldLabel;
                $fieldInfo = getFieldByReportLabel($module, $field);

                if (empty($queryColumns)) {
                    if ('C' === $single) {
                        $field_label_data = explode('_', $moduleFieldLabel);
                        $module = $field_label_data[0];

                        if ($module != $this->primarymodule) {
                            $columnsList[$fieldColumn] = "case when (" . $tableName . "." . $columnName . "='1')then 'yes' else case when (vtiger_crmentity$module.crmid !='') then 'no' else '-' end end as '$fieldLabel'";
                        } else {
                            $columnsList[$fieldColumn] = "case when (" . $tableName . "." . $columnName . "='1')then 'yes' else case when (vtiger_crmentity.crmid !='') then 'no' else '-' end end as '$fieldLabel'";
                        }

                        $this->queryPlanner->addTable("vtiger_crmentity$module");
                    } elseif (stristr($tableName, 'vtiger_users') && 'user_name' === $columnName) {
                        $temp_module_from_tablename = str_replace('vtiger_users', '', $tableName);

                        if ($module != $this->primarymodule) {
                            $condition = "and vtiger_crmentity" . $module . ".crmid!=''";
                            $this->queryPlanner->addTable("vtiger_crmentity$module");
                        } else {
                            $condition = "and vtiger_crmentity.crmid!=''";
                        }

                        if ($temp_module_from_tablename == $module) {
                            $columnsList[$fieldColumn] = " case when(" . $tableName . ".last_name NOT LIKE '' $condition ) THEN " . $concatSql . " else vtiger_groups" . $module . ".groupname end as '" . $module . "_$field'";
                            $this->queryPlanner->addTable('vtiger_groups' . $module);
                        } else {
                            $columnsList[$fieldColumn] = $tableName . ".user_name as '" . $header_label . "'";
                        }
                    } elseif (stristr($tableName, 'vtiger_crmentity') && 'modifiedby' === $columnName) {
                        $targetTableName = 'vtiger_lastModifiedBy' . $module;
                        $concatSql = getSqlForNameInDisplayFormat(array('last_name' => $targetTableName . '.last_name', 'first_name' => $targetTableName . '.first_name'), 'Users');
                        $columnsList[$fieldColumn] = "trim($concatSql) as $header_label";
                        $this->queryPlanner->addTable("vtiger_crmentity$module");
                        $this->queryPlanner->addTable($targetTableName);
                    } elseif ('vtiger_crmentity' . $this->primarymodule === $tableName) {
                        $columnsList[$fieldColumn] = "vtiger_crmentity." . $columnName . " AS '" . $header_label . "'";
                    } elseif ('vtiger_products' === $tableName && 'unit_price' === $columnName) {
                        $columnsList[$fieldColumn] = "concat(" . $tableName . ".currency_id,'::',innerProduct.actual_unit_price) as '" . $header_label . "'";
                        $this->queryPlanner->addTable("innerProduct");
                    } elseif (in_array($fieldName, $this->append_currency_symbol_to_value)) {
                        if ('discount_amount' === $columnName) {
                            $columnsList[$fieldColumn] = "CONCAT(" . $tableName . ".currency_id,'::', IF(" . $tableName . ".discount_amount != ''," . $tableName . ".discount_amount, (" . $tableName . ".discount_percent/100) * " . $tableName . ".subtotal)) AS " . $header_label;
                        } else {
                            $columnsList[$fieldColumn] = "concat(" . $tableName . ".currency_id,'::'," . $tableName . "." . $columnName . ") as '" . $header_label . "'";
                        }
                    } elseif ('vtiger_notes' === $tableName && in_array($columnName,  ['filelocationtype', 'filesize', 'folderid', 'filestatus'])) {
                        if ('filelocationtype' === $columnName) {
                            $columnsList[$fieldColumn] = "case " . $tableName . "." . $columnName . " when 'I' then 'Internal' when 'E' then 'External' else '-' end as '$fieldName'";
                        } elseif ('folderid' === $columnName) {
                            $columnsList[$fieldColumn] = "vtiger_attachmentsfolder.foldername as '$fieldName'";
                        } elseif ('filestatus' === $columnName) {
                            $columnsList[$fieldColumn] = "case " . $tableName . "." . $columnName . " when '1' then 'yes' when '0' then 'no' else '-' end as '$fieldName'";
                        } elseif ('filesize' === $columnName) {
                            $columnsList[$fieldColumn] = "case " . $tableName . "." . $columnName . " when '' then '-' else concat(" . $tableName . "." . $columnName . "/1024,'  ','KB') end as '$fieldName'";
                        }
                    } elseif ('vtiger_inventoryproductrel' === $tableName) {
                        if ('discount_amount' === $columnName) {
                            $columnsList[$fieldColumn] = " case when (vtiger_inventoryproductrel{$module}.discount_amount != '') then vtiger_inventoryproductrel{$module}.discount_amount else ROUND((vtiger_inventoryproductrel{$module}.listprice * vtiger_inventoryproductrel{$module}.quantity * (vtiger_inventoryproductrel{$module}.discount_percent/100)),3) end as '" . $header_label . "'";
                            $this->queryPlanner->addTable($tableName . $module);
                        } elseif ('productid' === $columnName) {
                            $columnsList[$fieldColumn] = "vtiger_products{$module}.productname as '" . $header_label . "'";
                            $this->queryPlanner->addTable("vtiger_products{$module}");
                        } elseif ('serviceid' === $columnName) {
                            $columnsList[$fieldColumn] = "vtiger_service{$module}.servicename as '" . $header_label . "'";
                            $this->queryPlanner->addTable("vtiger_service{$module}");
                        } elseif ('listprice' === $columnName) {
                            $moduleInstance = CRMEntity::getInstance($module);
                            $columnsList[$fieldColumn] = $tableName . $module . "." . $columnName . "/" . $moduleInstance->table_name . ".conversion_rate as '" . $header_label . "'";
                            $this->queryPlanner->addTable($tableName . $module);
                        } else {
                            $columnsList[$fieldColumn] = $tableName . $module . "." . $columnName . " as '" . $header_label . "'";
                            $this->queryPlanner->addTable($tableName . $module);
                        }
                    } elseif (true == stristr($columnName, 'cf_') && 0 == stripos($columnName, 'cf_')) {
                        $columnsList[$fieldColumn] = $tableName . "." . $columnName . " AS '" . $adb->sql_escape_string(decode_html($header_label)) . "'";
                    } elseif(69 === intval($fieldInfo['uitype'])) {
                        $moduleInstance = CRMEntity::getInstance($module);

                        $columnsList[$fieldColumn] = $tableName . "." . $moduleInstance->tab_name_index[$tableName] . " AS '" . $header_label . "'";
                    } else {
                        $columnsList[$fieldColumn] = $tableName . "." . $columnName . " AS '" . $header_label . "'";
                    }
                } else {
                    $columnsList[$fieldColumn] = $queryColumns;
                }

                $this->queryPlanner->addTable($targetTableName);
            }
        }

        $this->_columnslist = $columnsList;

        $log->info("ReportRun :: Successfully returned getQueryColumnsList" . $reportId);

        return $columnsList;
    }

    public function getaccesfield($module)
    {
        $adb = PearDatabase::getInstance();
        $access_fields = array();
        $profileList = getCurrentUserProfileList();
        $params = array();
        $where = '';

        array_push($params, $this->primarymodule, $this->secondarymodule);
        if (count($profileList) > 0) {
            $where .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?,?)) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ") group by vtiger_field.fieldid order by block,sequence";
            array_push($params, $profileList);
        } else {
            $where .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?,?)) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 group by vtiger_field.fieldid order by block,sequence";
        }

        $query = 'select vtiger_field.fieldname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where' . $where;
        $result = $adb->pquery($query, $params);

        while ($collistrow = $adb->fetch_array($result)) {
            $access_fields[] = $collistrow["fieldname"];
        }
        if ($module == "HelpDesk") {
            $access_fields[] = "ticketid";
        }
        return $access_fields;
    }

    public function getEscapedColumns($selectedFields)
    {
        list($tableName, $columnName, $moduleFieldLabel, $fieldName) = $selectedFields;
        list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);

        $fieldInfo = getFieldByReportLabel($moduleName, $fieldLabel);
        $uiType = intval($fieldInfo['uitype']);
        $moduleFieldName = $moduleName . '_' . $fieldName;

        if ('ModComments' === $moduleName && 'creator' === $fieldName) {
            $concatSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_usersModComments.first_name', 'last_name' => 'vtiger_usersModComments.last_name'), 'Users');
            $queryColumn = "trim(case when (vtiger_usersModComments.user_name not like '' and vtiger_crmentity.crmid!='') then $concatSql end) as 'ModComments_Creator'";

        } elseif ((10 === $uiType || isReferenceUIType($uiType)) && 52 !== $uiType && 53 != $uiType) {
            $fieldSqlColumns = $this->getReferenceFieldColumnList($moduleName, $fieldInfo);

            if (count($fieldSqlColumns) > 0) {
                $queryColumn = "(CASE WHEN $tableName.$columnName NOT LIKE '' THEN (CASE";

                foreach ($fieldSqlColumns as $columnSql) {
                    $queryColumn .= " WHEN $columnSql NOT LIKE '' THEN $columnSql";
                }

                $queryColumn .= " ELSE '' END) ELSE '' END) AS $moduleFieldName";
                $this->queryPlanner->addTable($tableName);
            }
        }

        return $queryColumn;
    }

    public function getReferenceFieldColumnList($moduleName, $fieldInfo)
    {
        $adb = PearDatabase::getInstance();
        $columnsSqlList = array();
        $fieldInstance = WebserviceField::fromArray($adb, $fieldInfo);
        $referenceModuleList = $fieldInstance->getReferenceList();
        $reportSecondaryModules = explode(':', $this->secondarymodule);

        if ($moduleName != $this->primarymodule && in_array($this->primarymodule, $referenceModuleList)) {
            $entityTableFieldNames = getEntityFieldNames($this->primarymodule);
            $entityTableName = $entityTableFieldNames['tablename'];
            $entityFieldNames = $entityTableFieldNames['fieldname'];

            $columnList = array();
            if (is_array($entityFieldNames)) {
                foreach ($entityFieldNames as $entityColumnName) {
                    $columnList["$entityColumnName"] = "$entityTableName.$entityColumnName";
                }
            } else {
                $columnList[] = "$entityTableName.$entityFieldNames";
            }
            if (count($columnList) > 1) {
                $columnSql = getSqlForNameInDisplayFormat($columnList, $this->primarymodule);
            } else {
                $columnSql = implode('', $columnList);
            }
            $columnsSqlList[] = $columnSql;
        } else {
            foreach ($referenceModuleList as $referenceModule) {
                $entityTableFieldNames = getEntityFieldNames($referenceModule);
                $entityTableName = $entityTableFieldNames['tablename'];
                $entityFieldNames = $entityTableFieldNames['fieldname'];
                $referenceTableName = $dependentTableName = '';
                if ($moduleName == 'HelpDesk' && $referenceModule == 'Accounts') {
                    $referenceTableName = 'vtiger_accountRelHelpDesk';
                } elseif ($moduleName == 'HelpDesk' && $referenceModule == 'Contacts') {
                    $referenceTableName = 'vtiger_contactdetailsRelHelpDesk';
                } elseif ($moduleName == 'HelpDesk' && $referenceModule == 'Products') {
                    $referenceTableName = 'vtiger_productsRel';
                } elseif ($moduleName == 'Contacts' && $referenceModule == 'Accounts') {
                    $referenceTableName = 'vtiger_accountContacts';
                } elseif ($moduleName == 'Contacts' && $referenceModule == 'Contacts') {
                    $referenceTableName = 'vtiger_contactdetailsContacts';
                } elseif ($moduleName == 'Accounts' && $referenceModule == 'Accounts') {
                    $referenceTableName = 'vtiger_accountAccounts';
                } elseif ($moduleName == 'Campaigns' && $referenceModule == 'Products') {
                    $referenceTableName = 'vtiger_productsCampaigns';
                } elseif ($moduleName == 'Faq' && $referenceModule == 'Products') {
                    $referenceTableName = 'vtiger_productsFaq';
                } elseif ($moduleName == 'Invoice' && $referenceModule == 'SalesOrder') {
                    $referenceTableName = 'vtiger_salesorderInvoice';
                } elseif ($moduleName == 'Invoice' && $referenceModule == 'Contacts') {
                    $referenceTableName = 'vtiger_contactdetailsInvoice';
                } elseif ($moduleName == 'Invoice' && $referenceModule == 'Accounts') {
                    $referenceTableName = 'vtiger_accountInvoice';
                } elseif ($moduleName == 'Potentials' && $referenceModule == 'Campaigns') {
                    $referenceTableName = 'vtiger_campaignPotentials';
                } elseif ($moduleName == 'Products' && $referenceModule == 'Vendors') {
                    $referenceTableName = 'vtiger_vendorRelProducts';
                } elseif ($moduleName == 'PurchaseOrder' && $referenceModule == 'Contacts') {
                    $referenceTableName = 'vtiger_contactdetailsPurchaseOrder';
                } elseif ($moduleName == 'PurchaseOrder' && $referenceModule == 'Vendors') {
                    $referenceTableName = 'vtiger_vendorRelPurchaseOrder';
                } elseif ($moduleName == 'Quotes' && $referenceModule == 'Potentials') {
                    $referenceTableName = 'vtiger_potentialRelQuotes';
                } elseif ($moduleName == 'Quotes' && $referenceModule == 'Accounts') {
                    $referenceTableName = 'vtiger_accountQuotes';
                } elseif ($moduleName == 'Quotes' && $referenceModule == 'Contacts') {
                    $referenceTableName = 'vtiger_contactdetailsQuotes';
                } elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Potentials') {
                    $referenceTableName = 'vtiger_potentialRelSalesOrder';
                } elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Accounts') {
                    $referenceTableName = 'vtiger_accountSalesOrder';
                } elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Contacts') {
                    $referenceTableName = 'vtiger_contactdetailsSalesOrder';
                } elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Quotes') {
                    $referenceTableName = 'vtiger_quotesSalesOrder';
                } elseif ($moduleName == 'Potentials' && $referenceModule == 'Contacts') {
                    $referenceTableName = 'vtiger_contactdetailsPotentials';
                } elseif ($moduleName == 'Potentials' && $referenceModule == 'Accounts') {
                    $referenceTableName = 'vtiger_accountPotentials';
                } elseif (in_array($referenceModule, $reportSecondaryModules) && $fieldInstance->getUIType() != 10) {
                    $referenceTableName = "{$entityTableName}Rel$referenceModule";
                    $dependentTableName = "vtiger_crmentityRel{$referenceModule}{$fieldInstance->getFieldId()}";
                } elseif (in_array($moduleName, $reportSecondaryModules) && $fieldInstance->getUIType() != 10) {
                    $referenceTableName = "{$entityTableName}Rel$moduleName";
                    $dependentTableName = "vtiger_crmentityRel{$moduleName}{$fieldInstance->getFieldId()}";
                } else {
                    $referenceTableName = "{$entityTableName}Rel{$moduleName}{$fieldInstance->getFieldId()}";
                    $dependentTableName = "vtiger_crmentityRel{$moduleName}{$fieldInstance->getFieldId()}";
                }
                $this->queryPlanner->addTable($referenceTableName);
                if (isset($dependentTableName)) {
                    $this->queryPlanner->addTable($dependentTableName);
                }
                $columnList = array();
                if (is_array($entityFieldNames)) {
                    foreach ($entityFieldNames as $entityColumnName) {
                        $columnList["$entityColumnName"] = "$referenceTableName.$entityColumnName";
                    }
                } else {
                    $columnList[] = "$referenceTableName.$entityFieldNames";
                }
                if (count($columnList) > 1) {
                    $columnSql = getSqlForNameInDisplayFormat($columnList, $referenceModule);
                } else {
                    $columnSql = implode('', $columnList);
                }
                if ($referenceModule == 'DocumentFolders' && $fieldInstance->getFieldName() == 'folderid') {
                    $columnSql = 'vtiger_attachmentsfolder.foldername';
                    $this->queryPlanner->addTable("vtiger_attachmentsfolder");
                }
                if ($referenceModule == 'Currency' && $fieldInstance->getFieldName() == 'currency_id') {
                    $columnSql = "vtiger_currency_info$moduleName.currency_name";
                    $this->queryPlanner->addTable("vtiger_currency_info$moduleName");
                }
                $columnsSqlList[] = $columnSql;
            }
        }
        return $columnsSqlList;
    }

    public function getTranslatedString($str, $module = '')
    {
        return Vtiger_Language_Handler::getTranslatedString($str, $module, $this->EMAILLanguage);
    }

    public function getSortColSql($columnlist, $relblockid)
    {
        $adb = PearDatabase::getInstance();
        $sql = "SELECT columnname, sortorder
                FROM vtiger_emakertemplates_relblocksortcol
                WHERE relblockid=?
                ORDER BY sortcolid ASC";
        $result = $adb->pquery($sql, array($relblockid));
        $sortColList = array();
        while ($row = $adb->fetchByAssoc($result)) {
            if (isset($columnlist[$row["columnname"]])) {
                $sortDir = ($row["sortorder"] == "Descending" ? "DESC" : "ASC");
                $columnName = $columnlist[$row["columnname"]];
                $columnName = str_replace(" as ", " AS ", $columnName);
                $tmpArr = explode(" AS ", $columnName);
                $columnAlias = $tmpArr[count($tmpArr) - 1];
                if (isset($columnAlias)) {
                    $columnName = trim($columnAlias, " '");
                }
                $sortColList[$row["columnname"]] = $columnName . " " . $sortDir;
            }
        }

        $sortColSql = "";
        if (count($sortColList) > 0) {
            $sortColSql = "ORDER BY ";
            $sortColSql .= implode(", ", $sortColList);
        }

        return $sortColSql;
    }

    public function getStdFilterList($relblockid)
    {
        if ($this->_stdfilterlist !== false) {
            return $this->_stdfilterlist;
        }
        $adb = PearDatabase::getInstance();
        global $modules;
        global $log;

        $stdfiltersql = "select vtiger_emakertemplates_relblockdatefilter.* from vtiger_emakertemplates_relblocks";
        $stdfiltersql .= " inner join vtiger_emakertemplates_relblockdatefilter on vtiger_emakertemplates_relblocks.relblockid = vtiger_emakertemplates_relblockdatefilter.datefilterid";
        $stdfiltersql .= " where vtiger_emakertemplates_relblocks.relblockid = ?";

        $result = $adb->pquery($stdfiltersql, array($relblockid));
        $stdfilterrow = $adb->fetch_array($result);
        if (isset($stdfilterrow)) {
            $fieldcolname = $stdfilterrow["datecolumnname"];
            $datefilter = $stdfilterrow["datefilter"];
            $startdate = $stdfilterrow["startdate"];
            $enddate = $stdfilterrow["enddate"];

            if ($fieldcolname != "none") {
                $selectedfields = explode(":", $fieldcolname);
                if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
                    $selectedfields[0] = "vtiger_crmentity";
                }
                $this->queryPlanner->addTable($selectedfields[0]);
                if ($datefilter == "custom") {
                    if ($startdate != "0000-00-00" && $enddate != "0000-00-00" && $selectedfields[0] != "" && $selectedfields[1] != "") {
                        $stdfilterlist[$fieldcolname] = $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startdate . " 00:00:00' and '" . $enddate . " 23:59:59'";
                    }
                } else {
                    $startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);
                    if ($startenddate[0] != "" && $startenddate[1] != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {
                        $stdfilterlist[$fieldcolname] = $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startenddate[0] . " 00:00:00' and '" . $startenddate[1] . " 23:59:59'";
                    }
                }
            }
        }
        $this->_stdfilterlist = $stdfilterlist;

        $log->info("ReportRun :: Successfully returned getStdFilterList" . $relblockid);
        return $stdfilterlist;
    }

    public function getAdvFilterSql($relblockid)
    {
        global $adb;
        global $current_user;

        $advfilterlist = $this->getAdvFilterByRBid($relblockid);
        $advfiltersql = "";
        $customView = new CustomView();
        $dateSpecificConditions = $customView->getStdFilterConditions();

        foreach ($advfilterlist as $groupindex => $groupinfo) {
            $groupcondition = $groupinfo['condition'];
            $groupcolumns = $groupinfo['columns'];

            if (count($groupcolumns) > 0) {

                $advfiltergroupsql = "";
                foreach ($groupcolumns as $columnindex => $columninfo) {
                    $fieldcolname = $columninfo["columnname"];
                    $comparator = $columninfo["comparator"];
                    $value = $columninfo["value"];
                    $columncondition = $columninfo["column_condition"];

                    if ($fieldcolname != "" && $comparator != "") {
                        if (in_array($comparator, $dateSpecificConditions)) {
                            if ($fieldcolname != 'none') {
                                $selectedFields = explode(':', $fieldcolname);
                                if ($selectedFields[0] == 'vtiger_crmentity' . $this->primarymodule) {
                                    $selectedFields[0] = 'vtiger_crmentity';
                                }

                                if ($comparator != 'custom') {
                                    list($startDate, $endDate) = $this->getStandarFiltersStartAndEndDate($comparator);
                                } else {
                                    list($startDateTime, $endDateTime) = explode(',', $value);
                                    list($startDate, $startTime) = explode(' ', $startDateTime);
                                    list($endDate, $endTime) = explode(' ', $endDateTime);
                                }

                                $type = $selectedFields[4];
                                if ($startDate != '0000-00-00' && $endDate != '0000-00-00' && $startDate != '' && $endDate != '') {
                                    $startDateTime = new DateTimeField($startDate . ' ' . date('H:i:s'));
                                    $userStartDate = $startDateTime->getDisplayDate();
                                    if ($type == 'DT') {
                                        $userStartDate = $userStartDate . ' 00:00:00';
                                    }
                                    $startDateTime = getValidDBInsertDateTimeValue($userStartDate);

                                    $endDateTime = new DateTimeField($endDate . ' ' . date('H:i:s'));
                                    $userEndDate = $endDateTime->getDisplayDate();
                                    if ($type == 'DT') {
                                        $userEndDate = $userEndDate . ' 23:59:59';
                                    }
                                    $endDateTime = getValidDBInsertDateTimeValue($userEndDate);

                                    if ($selectedFields[1] == 'birthday') {
                                        $tableColumnSql = 'DATE_FORMAT(' . $selectedFields[0] . '.' . $selectedFields[1] . ', "%m%d")';
                                        $startDateTime = "DATE_FORMAT('$startDateTime', '%m%d')";
                                        $endDateTime = "DATE_FORMAT('$endDateTime', '%m%d')";
                                    } else {
                                        if ($selectedFields[0] == 'vtiger_activity' && ($selectedFields[1] == 'date_start')) {
                                            $tableColumnSql = 'CAST((CONCAT(date_start, " ", time_start)) AS DATETIME)';
                                        } else {
                                            $tableColumnSql = $selectedFields[0] . '.' . $selectedFields[1];
                                        }
                                        $startDateTime = "'$startDateTime'";
                                        $endDateTime = "'$endDateTime'";
                                    }

                                    $advfiltergroupsql .= "$tableColumnSql BETWEEN $startDateTime AND $endDateTime";
                                    if (!empty($columncondition)) {
                                        $advfiltergroupsql .= ' ' . $columncondition . ' ';
                                    }

                                    $this->queryPlanner->addTable($selectedFields[0]);
                                }
                            }
                            continue;
                        }

                        $selectedfields = explode(":", $fieldcolname);
                        $moduleFieldLabel = $selectedfields[2];
                        list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
                        $fieldInfo = $this->getFieldByEMAILMakerLabel($moduleName, $fieldLabel);
                        $concatSql = getSqlForNameInDisplayFormat(array('first_name' => $selectedfields[0] . ".first_name", 'last_name' => $selectedfields[0] . ".last_name"), 'Users');
                        if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
                            $selectedfields[0] = "vtiger_crmentity";
                        }
                        if ($selectedfields[4] == 'C') {
                            if (strcasecmp(trim($value), "yes") == 0) {
                                $value = "1";
                            }
                            if (strcasecmp(trim($value), "no") == 0) {
                                $value = "0";
                            }
                        }
                        if (in_array($comparator, $dateSpecificConditions)) {
                            $customView = new CustomView($moduleName);
                            $columninfo['stdfilter'] = $columninfo['comparator'];
                            $valueComponents = explode(',', $columninfo['value']);
                            if ($comparator == 'custom') {
                                if ($selectedfields[4] == 'DT') {
                                    $startDateTimeComponents = explode(' ', $valueComponents[0]);
                                    $endDateTimeComponents = explode(' ', $valueComponents[1]);
                                    $columninfo['startdate'] = DateTimeField::convertToDBFormat($startDateTimeComponents[0]);
                                    $columninfo['enddate'] = DateTimeField::convertToDBFormat($endDateTimeComponents[0]);
                                } else {
                                    $columninfo['startdate'] = DateTimeField::convertToDBFormat($valueComponents[0]);
                                    $columninfo['enddate'] = DateTimeField::convertToDBFormat($valueComponents[1]);
                                }
                            }
                            $dateFilterResolvedList = $customView->resolveDateFilterValue($columninfo);
                            $startDate = DateTimeField::convertToDBFormat($dateFilterResolvedList['startdate']);
                            $endDate = DateTimeField::convertToDBFormat($dateFilterResolvedList['enddate']);
                            $columninfo['value'] = $value = implode(',', array($startDate, $endDate));
                            $comparator = 'bw';
                        }
                        $valuearray = explode(",", trim($value));
                        $datatype = (isset($selectedfields[4])) ? $selectedfields[4] : "";
                        if (isset($valuearray) && count($valuearray) > 1 && $comparator != 'bw') {

                            $advcolumnsql = "";
                            for ($n = 0; $n < count($valuearray); $n++) {

                                if (($selectedfields[0] == "vtiger_users" . $this->primarymodule || $selectedfields[0] == "vtiger_users" . $this->secondarymodule) && $selectedfields[1] == 'user_name') {
                                    $module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
                                    $advcolsql[] = " (trim($concatSql)" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype) . " or vtiger_groups" . $module_from_tablename . ".groupname " . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype) . ")";
                                    $this->queryPlanner->addTable("vtiger_groups" . $module_from_tablename);
                                } elseif ($selectedfields[1] == 'status') {
                                    if ($selectedfields[2] == 'Calendar_Status') {
                                        $advcolsql[] = "(case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end)" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                    } elseif ($selectedfields[2] == 'HelpDesk_Status') {
                                        $advcolsql[] = "vtiger_troubletickets.status" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                    }
                                } elseif ($selectedfields[1] == 'description') {
                                    if ($selectedfields[0] == 'vtiger_crmentity' . $this->primarymodule) {
                                        $advcolsql[] = "vtiger_crmentity.description" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                    } else {
                                        $advcolsql[] = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                    }
                                } elseif ($selectedfields[2] == 'Quotes_Inventory_Manager') {
                                    $advcolsql[] = ("trim($concatSql)" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype));
                                } else {
                                    $advcolsql[] = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                }
                            }
                            if ($comparator == 'n' || $comparator == 'k') {
                                $advcolumnsql = implode(" and ", $advcolsql);
                            } else {
                                $advcolumnsql = implode(" or ", $advcolsql);
                            }
                            $fieldvalue = " (" . $advcolumnsql . ") ";
                        } elseif ($selectedfields[1] == 'user_name') {
                            if ($selectedfields[0] == "vtiger_users" . $this->primarymodule) {
                                $module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
                                $fieldvalue = " trim(case when (" . $selectedfields[0] . ".last_name NOT LIKE '') then " . $concatSql . " else vtiger_groups" . $module_from_tablename . ".groupname end) " . $this->getAdvComparator($comparator, trim($value), $datatype);
                                $this->queryPlanner->addTable("vtiger_groups" . $module_from_tablename);
                            } else {
                                $secondaryModules = explode(':', $this->secondarymodule);
                                $firstSecondaryModule = "vtiger_users" . $secondaryModules[0];
                                $secondSecondaryModule = "vtiger_users" . $secondaryModules[1];
                                if (($firstSecondaryModule && $firstSecondaryModule == $selectedfields[0]) || ($secondSecondaryModule && $secondSecondaryModule == $selectedfields[0])) {
                                    $module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
                                    $moduleInstance = CRMEntity::getInstance($module_from_tablename);
                                    $fieldvalue = " trim(case when (" . $selectedfields[0] . ".last_name NOT LIKE '') then " . $concatSql . " else vtiger_groups" . $module_from_tablename . ".groupname end) " . $this->getAdvComparator($comparator, trim($value), $datatype);
                                    $this->queryPlanner->addTable("vtiger_groups" . $module_from_tablename);
                                    $this->queryPlanner->addTable($moduleInstance->table_name);
                                }
                            }
                        } elseif ($comparator == 'bw' && count($valuearray) == 2) {
                            if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
                                $fieldvalue = "(" . "vtiger_crmentity." . $selectedfields[1] . " between '" . trim($valuearray[0]) . "' and '" . trim($valuearray[1]) . "')";
                            } else {
                                $fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " between '" . trim($valuearray[0]) . "' and '" . trim($valuearray[1]) . "')";
                            }
                        } elseif ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
                            $fieldvalue = "vtiger_crmentity." . $selectedfields[1] . " " . $this->getAdvComparator($comparator, trim($value), $datatype);
                        } elseif ($selectedfields[2] == 'Quotes_Inventory_Manager') {
                            $fieldvalue = ("trim($concatSql)" . $this->getAdvComparator($comparator, trim($value), $datatype));
                        } elseif ($selectedfields[1] == 'modifiedby') {
                            $module_from_tablename = str_replace("vtiger_crmentity", "", $selectedfields[0]);
                            if ($module_from_tablename != '') {
                                $tableName = 'vtiger_lastModifiedBy' . $module_from_tablename;
                            } else {
                                $tableName = 'vtiger_lastModifiedBy' . $this->primarymodule;
                            }
                            $this->queryPlanner->addTable($tableName);
                            $fieldvalue = getSqlForNameInDisplayFormat(array('last_name' => "$tableName.last_name", 'first_name' => "$tableName.first_name"), 'Users') .
                                $this->getAdvComparator($comparator, trim($value), $datatype);
                        } elseif ($selectedfields[0] == "vtiger_activity" && $selectedfields[1] == 'status') {
                            $fieldvalue = "(case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end)" . $this->getAdvComparator($comparator, trim($value), $datatype);
                        } elseif ($comparator == 'y' || ($comparator == 'e' && (trim($value) == "NULL" || trim($value) == ''))) {
                            if ($selectedfields[0] == 'vtiger_inventoryproductrel') {
                                $selectedfields[0] = 'vtiger_inventoryproductrel' . $moduleName;
                            }
                            $fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " IS NULL OR " . $selectedfields[0] . "." . $selectedfields[1] . " = '')";
                        }  elseif ('ny' === $comparator) {
                            if ('vtiger_inventoryproductrel' === $selectedfields[0]) {
                                $selectedfields[0] = 'vtiger_inventoryproductrel' . $moduleName;
                            }
                            $fieldvalue = sprintf(
                                '(%s.%s IS NOT NULL OR %s.%s != \'\')',
                                $selectedfields[0],
                                $selectedfields[1],
                                $selectedfields[0],
                                $selectedfields[1]
                            );
                        } elseif ($selectedfields[0] == 'vtiger_inventoryproductrel') {
                            if ($selectedfields[1] == 'productid') {
                                $fieldvalue = "vtiger_products$moduleName.productname " . $this->getAdvComparator($comparator, trim($value), $datatype);
                                $this->queryPlanner->addTable("vtiger_products$moduleName");
                            } else {
                                if ($selectedfields[1] == 'serviceid') {
                                    $fieldvalue = "vtiger_service$moduleName.servicename " . $this->getAdvComparator($comparator, trim($value), $datatype);
                                    $this->queryPlanner->addTable("vtiger_service$moduleName");
                                } else {
                                    $selectedfields[0] = 'vtiger_inventoryproductrel' . $moduleName;
                                    $fieldvalue = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, $value, $datatype);
                                }
                            }
                        } elseif ($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype'])) {

                            $comparatorValue = $this->getAdvComparator($comparator, trim($value), $datatype);
                            $fieldSqls = array();
                            $fieldSqlColumns = $this->getReferenceFieldColumnList($moduleName, $fieldInfo);
                            foreach ($fieldSqlColumns as $columnSql) {
                                $fieldSqls[] = $columnSql . $comparatorValue;
                            }
                            $fieldvalue = ' (' . implode(' OR ', $fieldSqls) . ') ';
                        } else {
                            $fieldvalue = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($value), $datatype);
                        }

                        $advfiltergroupsql .= $fieldvalue;
                        if (!empty($columncondition)) {
                            $advfiltergroupsql .= ' ' . $columncondition . ' ';
                        }

                        $this->queryPlanner->addTable($selectedfields[0]);
                    }

                }

                if (trim($advfiltergroupsql) != "") {
                    $advfiltergroupsql = "( $advfiltergroupsql ) ";
                    if (!empty($groupcondition)) {
                        $advfiltergroupsql .= ' ' . $groupcondition . ' ';
                    }

                    $advfiltersql .= $advfiltergroupsql;
                }
            }
        }
        if (trim($advfiltersql) != "") {
            $advfiltersql = '(' . $advfiltersql . ')';
        }

        return $advfiltersql;
    }

    public function getFieldByEMAILMakerLabel($module, $label)
    {
        $cacheLabel = VTCacheUtils::getReportFieldByLabel($module, $label);
        if ($cacheLabel) {
            return $cacheLabel;
        }
        getColumnFields($module);
        $cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
        if (empty($cachedModuleFields)) {
            return null;
        }
        foreach ($cachedModuleFields as $fieldInfo) {
            $fieldName = str_replace(' ', '_', $fieldInfo['fieldname']);
            if ($label == $fieldName) {
                VTCacheUtils::setReportFieldByLabel($module, $label, $fieldInfo);
                return $fieldInfo;
            }
        }
        return null;
    }

    public function getReportsStaticQuery($module) {
        global $current_user;

        switch($module) {
            case 'Leads':
                return 'FROM vtiger_leaddetails 
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_leaddetails.leadid 
                    INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid=vtiger_leaddetails.leadid 
                    INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid=vtiger_leadsubdetails.leadsubscriptionid 
                    INNER JOIN vtiger_leadscf ON vtiger_leaddetails.leadid = vtiger_leadscf.leadid 
                    LEFT JOIN vtiger_groups as vtiger_groupsLeads ON vtiger_groupsLeads.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users as vtiger_usersLeads ON vtiger_usersLeads.id = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid' .
                    $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
                    ' WHERE vtiger_crmentity.deleted=0 and vtiger_leaddetails.converted=0 ';
            case 'Accounts':
                return 'FROM vtiger_account 
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_account.accountid 
                    INNER JOIN vtiger_accountbillads ON vtiger_account.accountid=vtiger_accountbillads.accountaddressid 
                    INNER JOIN vtiger_accountshipads ON vtiger_account.accountid=vtiger_accountshipads.accountaddressid 
                    INNER JOIN vtiger_accountscf ON vtiger_account.accountid = vtiger_accountscf.accountid 
                    LEFT JOIN vtiger_groups as vtiger_groupsAccounts ON vtiger_groupsAccounts.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_account as vtiger_accountAccounts ON vtiger_accountAccounts.accountid = vtiger_account.parentid
                    LEFT JOIN vtiger_users as vtiger_usersAccounts ON vtiger_usersAccounts.id = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid ' .
                    $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
                    ' WHERE vtiger_crmentity.deleted=0 ';
            case 'Contacts':
                return 'FROM vtiger_contactdetails
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid 
                    INNER JOIN vtiger_contactaddress ON vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid 
                    INNER JOIN vtiger_customerdetails ON vtiger_customerdetails.customerid = vtiger_contactdetails.contactid
                    INNER JOIN vtiger_contactsubdetails ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid 
                    INNER JOIN vtiger_contactscf ON vtiger_contactdetails.contactid = vtiger_contactscf.contactid 
                    LEFT JOIN vtiger_groups vtiger_groupsContacts ON vtiger_groupsContacts.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_contactdetails as vtiger_contactdetailsContacts ON vtiger_contactdetailsContacts.contactid = vtiger_contactdetails.reportsto
                    LEFT JOIN vtiger_account as vtiger_accountContacts ON vtiger_accountContacts.accountid = vtiger_contactdetails.accountid 
                    LEFT JOIN vtiger_users as vtiger_usersContacts ON vtiger_usersContacts.id = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid ' .
                    $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
                    ' WHERE vtiger_crmentity.deleted=0 ';
            case 'Potentials':
                return 'FROM vtiger_potential 
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_potential.potentialid 
                    INNER JOIN vtiger_potentialscf ON vtiger_potentialscf.potentialid = vtiger_potential.potentialid
                    LEFT JOIN vtiger_account as vtiger_accountPotentials ON vtiger_potential.related_to = vtiger_accountPotentials.accountid
                    LEFT JOIN vtiger_contactdetails as vtiger_contactdetailsPotentials ON vtiger_potential.related_to = vtiger_contactdetailsPotentials.contactid 
                    LEFT JOIN vtiger_campaign as vtiger_campaignPotentials ON vtiger_potential.campaignid = vtiger_campaignPotentials.campaignid
                    LEFT JOIN vtiger_groups vtiger_groupsPotentials ON vtiger_groupsPotentials.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users as vtiger_usersPotentials ON vtiger_usersPotentials.id = vtiger_crmentity.smownerid  
                    LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid ' .
                    $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
                    ' WHERE vtiger_crmentity.deleted=0 ';
            case 'Products':
                return 'FROM vtiger_products 
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_products.productid 
                    LEFT JOIN vtiger_productcf ON vtiger_products.productid = vtiger_productcf.productid 
                    LEFT JOIN vtiger_users as vtiger_usersProducts ON vtiger_usersProducts.id = vtiger_products.handler 
                    LEFT JOIN vtiger_vendor as vtiger_vendorRelProducts ON vtiger_vendorRelProducts.vendorid = vtiger_products.vendor_id 
                    LEFT JOIN (
                            SELECT vtiger_products.productid, (CASE WHEN (vtiger_products.currency_id = 1 ) THEN vtiger_products.unit_price ELSE (vtiger_products.unit_price / vtiger_currency_info.conversion_rate) END ) AS actual_unit_price
                            FROM vtiger_products
                            LEFT JOIN vtiger_currency_info ON vtiger_products.currency_id = vtiger_currency_info.id
                            LEFT JOIN vtiger_productcurrencyrel ON vtiger_products.productid = vtiger_productcurrencyrel.productid
                            AND vtiger_productcurrencyrel.currencyid = ' . $current_user->currency_id . '
                    ) AS innerProduct ON innerProduct.productid = vtiger_products.productid ' .
                    $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
                    ' WHERE vtiger_crmentity.deleted=0 ';
            case 'HelpDesk':
                return 'FROM vtiger_troubletickets 
                    INNER JOIN vtiger_crmentity  
                    ON vtiger_crmentity.crmid=vtiger_troubletickets.ticketid 
                    INNER JOIN vtiger_ticketcf ON vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid
                    LEFT JOIN vtiger_crmentity as vtiger_crmentityRelHelpDesk ON vtiger_crmentityRelHelpDesk.crmid = vtiger_troubletickets.parent_id
                    LEFT JOIN vtiger_account as vtiger_accountRelHelpDesk ON vtiger_accountRelHelpDesk.accountid=vtiger_crmentityRelHelpDesk.crmid 
                    LEFT JOIN vtiger_contactdetails as vtiger_contactdetailsRelHelpDesk ON vtiger_contactdetailsRelHelpDesk.contactid= vtiger_crmentityRelHelpDesk.crmid
                    LEFT JOIN vtiger_products as vtiger_productsRel ON vtiger_productsRel.productid = vtiger_troubletickets.product_id 
                    LEFT JOIN vtiger_groups as vtiger_groupsHelpDesk ON vtiger_groupsHelpDesk.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users as vtiger_usersHelpDesk ON vtiger_crmentity.smownerid=vtiger_usersHelpDesk.id 
                    LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id ' .
                    $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
                    ' WHERE vtiger_crmentity.deleted=0 ';
            case 'Quotes':
                return 'FROM vtiger_quotes 
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_quotes.quoteid 
                    INNER JOIN vtiger_quotesbillads ON vtiger_quotes.quoteid=vtiger_quotesbillads.quotebilladdressid 
                    INNER JOIN vtiger_quotesshipads ON vtiger_quotes.quoteid=vtiger_quotesshipads.quoteshipaddressid
                    LEFT JOIN vtiger_inventoryproductrel as vtiger_inventoryproductrelQuotes ON vtiger_quotes.quoteid = vtiger_inventoryproductrelQuotes.id
                    LEFT JOIN vtiger_products as vtiger_productsQuotes ON vtiger_productsQuotes.productid = vtiger_inventoryproductrelQuotes.productid  
                    LEFT JOIN vtiger_service as vtiger_serviceQuotes ON vtiger_serviceQuotes.serviceid = vtiger_inventoryproductrelQuotes.productid
                    LEFT JOIN vtiger_quotescf ON vtiger_quotes.quoteid = vtiger_quotescf.quoteid
                    LEFT JOIN vtiger_groups as vtiger_groupsQuotes ON vtiger_groupsQuotes.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users as vtiger_usersQuotes ON vtiger_usersQuotes.id = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users as vtiger_usersRel1 ON vtiger_usersRel1.id = vtiger_quotes.inventorymanager
                    LEFT JOIN vtiger_potential as vtiger_potentialRelQuotes ON vtiger_potentialRelQuotes.potentialid = vtiger_quotes.potentialid
                    LEFT JOIN vtiger_contactdetails as vtiger_contactdetailsQuotes ON vtiger_contactdetailsQuotes.contactid = vtiger_quotes.contactid
                    LEFT JOIN vtiger_account as vtiger_accountQuotes ON vtiger_accountQuotes.accountid = vtiger_quotes.accountid ' .
                    $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
                    ' WHERE vtiger_crmentity.deleted=0 ';
            case 'PurchaseOrder':
                return 'FROM vtiger_purchaseorder 
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_purchaseorder.purchaseorderid 
                    INNER JOIN vtiger_pobillads ON vtiger_purchaseorder.purchaseorderid=vtiger_pobillads.pobilladdressid 
                    INNER JOIN vtiger_poshipads ON vtiger_purchaseorder.purchaseorderid=vtiger_poshipads.poshipaddressid
                    LEFT JOIN vtiger_inventoryproductrel as vtiger_inventoryproductrelPurchaseOrder ON vtiger_purchaseorder.purchaseorderid = vtiger_inventoryproductrelPurchaseOrder.id
                    LEFT JOIN vtiger_products as vtiger_productsPurchaseOrder ON vtiger_productsPurchaseOrder.productid = vtiger_inventoryproductrelPurchaseOrder.productid  
                    LEFT JOIN vtiger_service as vtiger_servicePurchaseOrder ON vtiger_servicePurchaseOrder.serviceid = vtiger_inventoryproductrelPurchaseOrder.productid
                    LEFT JOIN vtiger_purchaseordercf ON vtiger_purchaseorder.purchaseorderid = vtiger_purchaseordercf.purchaseorderid
                    LEFT JOIN vtiger_groups as vtiger_groupsPurchaseOrder ON vtiger_groupsPurchaseOrder.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users as vtiger_usersPurchaseOrder ON vtiger_usersPurchaseOrder.id = vtiger_crmentity.smownerid 
                    LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid 
                    LEFT JOIN vtiger_vendor as vtiger_vendorRelPurchaseOrder ON vtiger_vendorRelPurchaseOrder.vendorid = vtiger_purchaseorder.vendorid 
                    LEFT JOIN vtiger_contactdetails as vtiger_contactdetailsPurchaseOrder ON vtiger_contactdetailsPurchaseOrder.contactid = vtiger_purchaseorder.contactid ' .
                    $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
                    ' WHERE vtiger_crmentity.deleted=0 ';
            case 'Invoice':
                return 'FROM vtiger_invoice 
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_invoice.invoiceid 
                    INNER JOIN vtiger_invoicebillads ON vtiger_invoice.invoiceid=vtiger_invoicebillads.invoicebilladdressid 
                    INNER JOIN vtiger_invoiceshipads ON vtiger_invoice.invoiceid=vtiger_invoiceshipads.invoiceshipaddressid
                    LEFT JOIN vtiger_inventoryproductrel as vtiger_inventoryproductrelInvoice ON vtiger_invoice.invoiceid = vtiger_inventoryproductrelInvoice.id
                    LEFT JOIN vtiger_products as vtiger_productsInvoice ON vtiger_productsInvoice.productid = vtiger_inventoryproductrelInvoice.productid
                    LEFT JOIN vtiger_service as vtiger_serviceInvoice ON vtiger_serviceInvoice.serviceid = vtiger_inventoryproductrelInvoice.productid
                    LEFT JOIN vtiger_salesorder as vtiger_salesorderInvoice ON vtiger_salesorderInvoice.salesorderid=vtiger_invoice.salesorderid
                    LEFT JOIN vtiger_invoicecf ON vtiger_invoice.invoiceid = vtiger_invoicecf.invoiceid 
                    LEFT JOIN vtiger_groups as vtiger_groupsInvoice ON vtiger_groupsInvoice.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users as vtiger_usersInvoice ON vtiger_usersInvoice.id = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_account as vtiger_accountInvoice ON vtiger_accountInvoice.accountid = vtiger_invoice.accountid
                    LEFT JOIN vtiger_contactdetails as vtiger_contactdetailsInvoice ON vtiger_contactdetailsInvoice.contactid = vtiger_invoice.contactid ' .
                    $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
                    ' WHERE vtiger_crmentity.deleted=0 ';
            case 'SalesOrder':
                return 'FROM vtiger_salesorder 
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_salesorder.salesorderid 
                    INNER JOIN vtiger_sobillads ON vtiger_salesorder.salesorderid=vtiger_sobillads.sobilladdressid 
                    INNER JOIN vtiger_soshipads ON vtiger_salesorder.salesorderid=vtiger_soshipads.soshipaddressid
                    LEFT JOIN vtiger_inventoryproductrel as vtiger_inventoryproductrelSalesOrder ON vtiger_salesorder.salesorderid = vtiger_inventoryproductrelSalesOrder.id
                    LEFT JOIN vtiger_products as vtiger_productsSalesOrder ON vtiger_productsSalesOrder.productid = vtiger_inventoryproductrelSalesOrder.productid  
                    LEFT JOIN vtiger_service as vtiger_serviceSalesOrder ON vtiger_serviceSalesOrder.serviceid = vtiger_inventoryproductrelSalesOrder.productid
                    LEFT JOIN vtiger_contactdetails as vtiger_contactdetailsSalesOrder ON vtiger_contactdetailsSalesOrder.contactid = vtiger_salesorder.contactid
                    LEFT JOIN vtiger_quotes as vtiger_quotesSalesOrder ON vtiger_quotesSalesOrder.quoteid = vtiger_salesorder.quoteid				
                    LEFT JOIN vtiger_account as vtiger_accountSalesOrder ON vtiger_accountSalesOrder.accountid = vtiger_salesorder.accountid
                    LEFT JOIN vtiger_potential as vtiger_potentialRelSalesOrder ON vtiger_potentialRelSalesOrder.potentialid = vtiger_salesorder.potentialid 
                    LEFT JOIN vtiger_invoice_recurring_info ON vtiger_invoice_recurring_info.salesorderid = vtiger_salesorder.salesorderid
                    LEFT JOIN vtiger_groups as vtiger_groupsSalesOrder ON vtiger_groupsSalesOrder.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users as vtiger_usersSalesOrder ON vtiger_usersSalesOrder.id = vtiger_crmentity.smownerid 
                    LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid ' .
                    $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
                    ' WHERE vtiger_crmentity.deleted=0 ';
            case 'Campaigns':
                return 'FROM vtiger_campaign
                    INNER JOIN vtiger_campaignscf as vtiger_campaignscf ON vtiger_campaignscf.campaignid=vtiger_campaign.campaignid   
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_campaign.campaignid
                    LEFT JOIN vtiger_products as vtiger_productsCampaigns ON vtiger_productsCampaigns.productid = vtiger_campaign.product_id
                    LEFT JOIN vtiger_groups as vtiger_groupsCampaigns ON vtiger_groupsCampaigns.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users as vtiger_usersCampaigns ON vtiger_usersCampaigns.id = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid ' .
                    $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
                    ' WHERE vtiger_crmentity.deleted=0 ';
        }

        return '';
    }

    public function getReportsQuery($module)
    {
        global $log, $current_user;

        $query = $this->getReportsStaticQuery($module);

        if (empty($query) && !empty($module)) {
            $query = $this->generateReportsQuery($module) . $this->getRelatedModulesQuery($module, $this->secondarymodule);

            switch ($this->secondarymodule) {
                case 'HelpDesk':
                    $query .= ' left join vtiger_ticketcomments on vtiger_ticketcomments.ticketid=vtiger_troubletickets.ticketid ';
                    break;
                case 'Faq':
                    $query .= ' left join vtiger_products as vtiger_productsfaq on vtiger_productsfaq.productid=vtiger_faq.product_id 
                                    left join vtiger_faqcomments on vtiger_faqcomments.faqid=vtiger_faq.id ';
                    break;
            }

            $query .= getNonAdminAccessControlQuery($this->primarymodule, $current_user) . ' WHERE vtiger_crmentity.deleted=0';
        }

        if ('PriceBooks' === $module && 'Products' === $this->secondarymodule) {
            $query = str_replace('left join vtiger_crmentity as vtiger_crmentityProducts', 'inner join vtiger_crmentity as vtiger_crmentityProducts', $query);
        }

        if (false !== strpos($query, 'vtiger_crmentity' . $this->secondarymodule . '.')) {
            $query .= ' AND vtiger_crmentity' . $this->secondarymodule . '.deleted=0 ';
        }

        $query .= " AND vtiger_crmentity.crmid= '" . $this->crmid . "'";

        $log->info('ReportRun :: Successfully returned getReportsQuery' . $module);

        return $query;
    }

    public function getRelatedModulesQuery($module, $secModule)
    {
        global $log, $current_user;
        $query = '';

        if (!empty($secModule)) {
            $secondaryModule = explode(":", $secModule);

            foreach ($secondaryModule as $value) {
                $foc = CRMEntity::getInstance($value);
                $this->queryPlanner->addTable('vtiger_crmentity' . $value);
                $focQuery = $foc->generateReportsSecQuery($module, $value, $this->queryPlanner);

                if ($focQuery) {
                    $query .= $focQuery . getNonAdminAccessControlQuery($value, $current_user, $value);
                }
            }

            $this->updateRelatedModulesQuery($query, $module, $secModule);
        }

        $query = str_replace(array('  ', 'left join as'), array(' ', 'left join'), $query);
        $log->info('ReportRun :: Successfully returned getRelatedModulesQuery' . $secModule);

        return $query;
    }

    public function updateRelatedModulesQuery(&$query, $module, $secModule)
    {
        if ($this->queryPlanner->requireTable('vtiger_contactdetailsHelpDesk') && !$this->hasRequiredTable($query, 'vtiger_contactdetailsHelpDesk')) {
            $query .= ' left join vtiger_contactdetails as vtiger_contactdetailsHelpDesk on vtiger_contactdetailsHelpDesk.contactid=vtiger_troubletickets.contact_id ';
        }

        if ($this->queryPlanner->requireTable('its4you_hotelbookingRelCalendar') && !$this->hasRequiredTable($query, 'its4you_hotelbookingRelCalendar')) {
            $query .= ' left join its4you_hotelbooking as its4you_hotelbookingRelCalendar on its4you_hotelbookingRelCalendar.hotelbookingid=vtiger_crmentityRelCalendar.crmid ';
        }

        if ($this->queryPlanner->requireTable('its4you_multicompany4youRelCalendar') && !$this->hasRequiredTable($query, 'its4you_multicompany4youRelCalendar')) {
            $query .= ' left join its4you_multicompany4you as its4you_multicompany4youRelCalendar on its4you_multicompany4youRelCalendar.companyid=vtiger_crmentityRelCalendar.crmid ';
        }

        if ($this->queryPlanner->requireTable('its4you_salesvisitRelCalendar') && !$this->hasRequiredTable($query, 'its4you_salesvisitRelCalendar')) {
            $query .= ' left join its4you_salesvisit as its4you_salesvisitRelCalendar on its4you_salesvisitRelCalendar.salesvisit_id=vtiger_crmentityRelCalendar.crmid ';
        }
    }

    public function hasRequiredTable($query, $table)
    {
        return false !== stripos($query, 'as ' . $table);
    }

    public function generateReportsQuery($module, $queryPlanner = null)
    {
        $adb = PearDatabase::getInstance();
        $primary = CRMEntity::getInstance($module);

        vtlib_setup_modulevars($module, $primary);
        $moduletable = $primary->table_name;
        $moduleindex = $primary->table_index;
        $modulecftable = $primary->customFieldTable[0];
        $modulecfindex = $primary->customFieldTable[1];

        if (isset($modulecftable)) {
            $cfquery = "inner join $modulecftable as $modulecftable on $modulecftable.$modulecfindex=$moduletable.$moduleindex";
        } else {
            $cfquery = '';
        }
        $query = "from $moduletable $cfquery
                inner join vtiger_crmentity on vtiger_crmentity.crmid=$moduletable.$moduleindex
                left join vtiger_groups as vtiger_groups" . $module . " on vtiger_groups" . $module . ".groupid = vtiger_crmentity.smownerid
                left join vtiger_users as vtiger_users" . $module . " on vtiger_users" . $module . ".id = vtiger_crmentity.smownerid
                left join vtiger_users as vtiger_lastModifiedBy" . $module . " on vtiger_lastModifiedBy" . $module . ".id = vtiger_crmentity.modifiedby
                left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

        $fields_query = $adb->pquery("SELECT vtiger_field.fieldname,vtiger_field.tablename,vtiger_field.fieldid from vtiger_field INNER JOIN vtiger_tab on vtiger_tab.name = ? WHERE vtiger_tab.tabid=vtiger_field.tabid AND vtiger_field.uitype IN (10) and vtiger_field.presence in (0,2)", array($module));

        if ($adb->num_rows($fields_query) > 0) {
            for ($i = 0; $i < $adb->num_rows($fields_query); $i++) {
                $field_name = $adb->query_result($fields_query, $i, 'fieldname');
                $field_id = $adb->query_result($fields_query, $i, 'fieldid');
                $tab_name = $adb->query_result($fields_query, $i, 'tablename');
                $ui10_modules_query = $adb->pquery("SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid=?", array($field_id));

                if ($adb->num_rows($ui10_modules_query) > 0) {
                    $query .= " left join vtiger_crmentity as vtiger_crmentityRel$module$field_id on vtiger_crmentityRel$module$field_id.crmid = $tab_name.$field_name and vtiger_crmentityRel$module$field_id.deleted=0";
                    for ($j = 0; $j < $adb->num_rows($ui10_modules_query); $j++) {
                        $rel_mod = $adb->query_result($ui10_modules_query, $j, 'relmodule');
                        $rel_obj = CRMEntity::getInstance($rel_mod);
                        vtlib_setup_modulevars($rel_mod, $rel_obj);

                        $rel_tab_name = $rel_obj->table_name;
                        $rel_tab_index = $rel_obj->table_index;
                        $query .= " left join $rel_tab_name as " . $rel_tab_name . "Rel$module$field_id on " . $rel_tab_name . "Rel$module$field_id.$rel_tab_index = vtiger_crmentityRel$module$field_id.crmid";
                    }
                }
            }
        }
        return $query;
    }

    public function getAccessPickListValues()
    {
        $adb = PearDatabase::getInstance();
        global $current_user;
        $id = array(getTabid($this->primarymodule));
        if ($this->secondarymodule != '') {
            array_push($id, getTabid($this->secondarymodule));
        }

        $query = 'select fieldname,columnname,fieldid,fieldlabel,tabid,uitype from vtiger_field where tabid in(' . generateQuestionMarks($id) . ') and uitype in (15,33,55)'; //and columnname in (?)';
        $result = $adb->pquery($query, $id); //,$select_column));
        $roleid = $current_user->roleid;
        $subrole = getRoleSubordinates($roleid);
        if (count($subrole) > 0) {
            $roleids = $subrole;
            array_push($roleids, $roleid);
        } else {
            $roleids = $roleid;
        }

        $temp_status = array();
        for ($i = 0; $i < $adb->num_rows($result); $i++) {
            $fieldname = $adb->query_result($result, $i, "fieldname");
            $fieldlabel = $adb->query_result($result, $i, "fieldlabel");
            $tabid = $adb->query_result($result, $i, "tabid");
            $uitype = $adb->query_result($result, $i, "uitype");

            $fieldlabel1 = str_replace(" ", "_", $fieldlabel);
            $keyvalue = getTabModuleName($tabid) . "_" . $fieldlabel1;
            $fieldvalues = array();
            if (count($roleids) > 1) {
                $mulsel = "select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid in (\"" . implode($roleids, "\",\"") . "\") and picklistid in (select picklistid from vtiger_$fieldname) order by sortid asc";
            } else {
                $mulsel = "select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid ='" . $roleid . "' and picklistid in (select picklistid from vtiger_$fieldname) order by sortid asc";
            }
            if ($fieldname != 'firstname') {
                $mulselresult = $adb->pquery($mulsel, array());
            }
            for ($j = 0; $j < $adb->num_rows($mulselresult); $j++) {
                $fldvalue = $adb->query_result($mulselresult, $j, $fieldname);
                if (in_array($fldvalue, $fieldvalues)) {
                    continue;
                }
                $fieldvalues[] = $fldvalue;
            }
            $field_count = count($fieldvalues);
            if ($uitype == 15 && $field_count > 0 && ($fieldname == 'taskstatus' || $fieldname == 'eventstatus')) {
                $temp_count = count($temp_status[$keyvalue]);
                if ($temp_count > 0) {
                    for ($t = 0; $t < $field_count; $t++) {
                        $temp_status[$keyvalue][($temp_count + $t)] = $fieldvalues[$t];
                    }
                    $fieldvalues = $temp_status[$keyvalue];
                } else {
                    $temp_status[$keyvalue] = $fieldvalues;
                }
            }

            if ($uitype == 33) {
                $fieldlists[1][$keyvalue] = $fieldvalues;
            } else {
                if ($uitype == 55 && $fieldname == 'salutationtype') {
                    $fieldlists[$keyvalue] = $fieldvalues;
                } else {
                    if ($uitype == 15) {
                        $fieldlists[$keyvalue] = $fieldvalues;
                    }
                }
            }
        }
        return $fieldlists;
    }

    public function getEMAILMakerFieldValue($report, $picklistArray, $dbField, $valueArray, $fieldName)
    {
        global $current_user, $default_charset;

        $db = PearDatabase::getInstance();
        $value = $valueArray[$fieldName];
        $fld_type = $dbField->type;

        list($module, $fieldLabel) = explode('_', $dbField->name, 2);

        $fieldInfo = $this->getFieldByEMAILMakerLabel($module, $fieldLabel);
        $fieldType = null;
        $fieldValue = $value;

        if (!empty($fieldInfo)) {
            $field = WebserviceField::fromArray($db, $fieldInfo);
            $fieldType = $field->getFieldDataType();
            $fieldName = $field->getFieldName();
        }

        if ('currency' === $fieldType && '' != $value) {
            if (72 === intval($field->getUIType())) {
                $curid_value = explode("::", $value);
                $currency_id = $curid_value[0];
                $currency_value = $curid_value[1];
                $cur_sym_rate = getCurrencySymbolandCRate($currency_id);

                if ('Products_Unit_Price' === $dbField->name) { // need to do this only for Products Unit Price
                    if ($currency_id != 1) {
                        $currency_value = floatval($cur_sym_rate['rate']) * floatval($currency_value);
                    }
                }

                $formattedCurrencyValue = CurrencyField::convertToUserFormat($currency_value, null, true);
                $fieldValue = CurrencyField::appendCurrencySymbol($formattedCurrencyValue, $cur_sym_rate['symbol']);
            } else {
                $currencyField = new CurrencyField($value);
                $fieldValue = $currencyField->getDisplayValue();
            }
        } elseif (in_array($dbField->name, ['PurchaseOrder_Currency', 'SalesOrder_Currency', 'Invoice_Currency', 'Quotes_Currency', 'PriceBooks_Currency'])) {
            if ('' != $value) {
                $fieldValue = getTranslatedCurrencyString($value);
            }
        } elseif (in_array($dbField->name, $this->ui101_fields) && !empty($value)) {
            $entityNames = getEntityName('Users', $value);
            $fieldValue = $entityNames[$value];
        } elseif ('date' === $fieldType && !empty($value)) {
            $fieldValue = DateTimeField::convertToUserFormat($value);
        } elseif ('datetime' === $fieldType && !empty($value)) {
            $date = new DateTimeField($value);
            $fieldValue = $date->getDisplayDateTimeValue();
        } elseif ('time' === $fieldType && !empty($value) && 'duration_hours' !== $fieldName) {
            if (in_array($fieldName, ['time_start', 'time_end'])) {
                $date = new DateTimeField($value);
                $fieldValue = $date->getDisplayTime();
            } else {
                $fieldValue = $value;
            }
        } elseif ('picklist' === $fieldType && !empty($value)) {
            if (is_array($picklistArray)) {
                if (is_array($picklistArray[$dbField->name]) && 'activitytype' !== $fieldName && !in_array($value, $picklistArray[$dbField->name])) {
                    $fieldValue = $this->getTranslatedString('LBL_NOT_ACCESSIBLE', $module);
                } else {
                    $fieldValue = $this->getTranslatedString($value, $module);
                }
            } else {
                $fieldValue = $this->getTranslatedString($value, $module);
            }
        } elseif ('multipicklist' === $fieldType && !empty($value)) {
            $translatedValueList = array();

            if (is_array($picklistArray[1])) {
                $valueList = explode(' |##| ', $value);

                foreach ($valueList as $value) {
                    if (is_array($picklistArray[1][$dbField->name]) && !in_array($value, $picklistArray[1][$dbField->name])) {
                        $translatedValueList[] = $this->getTranslatedString('LBL_NOT_ACCESSIBLE', $module);
                    } else {
                        $translatedValueList[] = $this->getTranslatedString($value, $module);
                    }
                }
            }

            if (!is_array($picklistArray[1]) || !is_array($picklistArray[1][$dbField->name])) {
                $fieldValue = str_replace(' |##| ', ', ', $value);
            } else {
                $fieldValue = implode(', ', $translatedValueList);
            }
        } elseif ($fieldType == 'double') {
            if ($current_user->truncate_trailing_zeros == true) {
                $fieldValue = decimalFormat($fieldValue);
            }
        } elseif ($fieldType == 'image') {
            if (!empty($fieldValue) && isRecordExists($fieldValue)) {
                $recordModel = Vtiger_Record_Model::getInstanceById($fieldValue, $module);
                $images = $recordModel->getImageDetails();
                $url = rtrim(vglobal('site_URL'), '/') . '/' . $images[0]['url'];

                $fieldValue = sprintf('<img src="%s" alt="%s" class="relatedImage" height="50">', $url, $url);
            }
        }

        if ('' == $fieldValue) {
            return '-';
        }

        $fieldValue = str_replace("<", "&lt;", $fieldValue);
        $fieldValue = str_replace(">", "&gt;", $fieldValue);
        $fieldValue = decode_html($fieldValue);

        if (stristr($fieldValue, "|##|") && empty($fieldType)) {
            $fieldValue = str_ireplace(' |##| ', ', ', $fieldValue);
        } elseif ($fld_type == "date" && empty($fieldType)) {
            $fieldValue = DateTimeField::convertToUserFormat($fieldValue);
        } elseif ($fld_type == "datetime" && empty($fieldType)) {
            $date = new DateTimeField($fieldValue);
            $fieldValue = $date->getDisplayDateTimeValue();
        }

        if ($fieldInfo['uitype'] == '19' && $module === 'Documents') {
            return $fieldValue;
        }

        return htmlentities($fieldValue, ENT_QUOTES, $default_charset);
    }

    public function SetEMAILLanguage($language)
    {
        $this->EMAILLanguage = $language;
    }

    public function getEntityImage($ival)
    {
        global $site_URL, $adb;
        $siteurl = trim($site_URL, "/");
        $result = "";
        if ($ival != "") {
            switch ($this->secondarymodule) {
                case "Contacts":
                    $id = $ival;
                    $query = "SELECT vtiger_attachments.*
							FROM vtiger_contactdetails
							INNER JOIN vtiger_seattachmentsrel
								ON vtiger_contactdetails.contactid=vtiger_seattachmentsrel.crmid
							INNER JOIN vtiger_attachments
								ON vtiger_attachments.attachmentsid=vtiger_seattachmentsrel.attachmentsid
							INNER JOIN vtiger_crmentity
								ON vtiger_attachments.attachmentsid=vtiger_crmentity.crmid
							WHERE deleted=0 AND vtiger_contactdetails.contactid=?";

                    $res = $adb->pquery($query, array($id));
                    $num_rows = $adb->num_rows($res);
                    if ($num_rows > 0) {
                        $row = $adb->query_result_rowdata($res);

                        if (!isset($row['storedname']) || empty($row['storedname'])) {
                            $row['storedname'] = $row['name'];
                        }

                        $image_src = $row["path"] . $row["attachmentsid"] . "_" . $row["storedname"];
                        $result = "<img src='" . $siteurl . "/" . $image_src . "' />";
                    }
                    break;
                case "Products":
                    $attid = "";
                    $id = $ival;
                    $saved_sql1 = "SELECT attachmentid FROM vtiger_emakertemplates_images WHERE crmid=?";
                    $result1 = $adb->pquery($saved_sql1, array($id));
                    if ($adb->num_rows($result1) > 0) {
                        $saved_sql = "SELECT vtiger_attachments.*, vtiger_emakertemplates_images.width,
                                             vtiger_emakertemplates_images.height
                                      FROM vtiger_emakertemplates_images
                                      LEFT JOIN vtiger_attachments
                                             ON vtiger_attachments.attachmentsid=vtiger_emakertemplates_images.attachmentid
                                      INNER JOIN vtiger_crmentity
                                              ON vtiger_attachments.attachmentsid=vtiger_crmentity.crmid
                                      WHERE deleted=0 AND vtiger_emakertemplates_images.crmid=?";
                    } else {
                        $saved_sql = "SELECT vtiger_attachments.*, '83' AS width, '' AS height
                                      FROM vtiger_attachments
                                      LEFT JOIN vtiger_seattachmentsrel
                                             ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
                                      INNER JOIN vtiger_crmentity
                                              ON vtiger_attachments.attachmentsid=vtiger_crmentity.crmid
                                      WHERE vtiger_crmentity.deleted=0 AND vtiger_seattachmentsrel.crmid=?
                                      ORDER BY attachmentsid LIMIT 1";
                    }

                    $saved_res = $adb->pquery($saved_sql, array($id));
                    if ($adb->num_rows($saved_res) > 0) {
                        $row = $adb->query_result_rowdata($res);

                        if (!isset($row['storedname']) || empty($row['storedname'])) {
                            $row['storedname'] = $row['name'];
                        }

                        $path = $row["path"];
                        $attid = $row["attachmentsid"];
                        $name = $row["storedname"];
                        $attwidth = $row["width"];
                        $attheight = $row["height"];
                    }

                    if ($attid != "") {
                        if ($attwidth > 0) {
                            $width = " width='" . $attwidth . "' ";
                        }
                        if ($attheight > 0) {
                            $height = " height='" . $attheight . "' ";
                        }
                        $result = "<img src='" . $siteurl . "/" . $path . $attid . "_" . $name . "' " . $width . $height . "/>";
                    }
                    break;
            }
        }
        return $result;
    }

    public function getLstringforReportHeaders($fldname)
    {
        global $modules, $current_language, $current_user, $app_strings;
        $rep_header = ltrim(str_replace($modules, " ", $fldname));
        $rep_header_temp = preg_replace("/\s+/", "_", $rep_header);
        $rep_module = preg_replace("/_$rep_header_temp/", "", $fldname);
        $temp_mod_strings = return_module_language($current_language, $rep_module);
        $rep_header = decode_html($rep_header);
        $curr_symb = "";
        if (in_array($fldname, $this->convert_currency)) {
            $curr_symb = " (" . $app_strings['LBL_IN'] . " " . $current_user->currency_symbol . ")";
        }
        if ($temp_mod_strings[$rep_header] != '') {
            $rep_header = $temp_mod_strings[$rep_header];
        }
        $rep_header .= $curr_symb;
        return $rep_header;
    }
}

class EMAILMaker_ReportRunQueryPlanner
{
    protected static $tempTableCounter = 0;
    protected $disablePlanner = false;
    protected $tables = array();
    protected $tempTables = array();
    protected $allowTempTables = true;
    protected $tempTablePrefix = 'vtiger_reptmptbl_';
    protected $registeredCleanup = false;

    public function addTable($table)
    {
        $this->tables[$table] = $table;
    }

    public function requireTable($table, $dependencies = null)
    {
        if ($this->disablePlanner) {
            return true;
        }

        if (isset($this->tables[$table])) {
            return true;
        }
        if (is_array($dependencies)) {
            foreach ($dependencies as $dependentTable) {
                if (isset($this->tables[$dependentTable])) {
                    return true;
                }
            }
        } else {
            if ($dependencies instanceof EMAILMaker_ReportRunQueryDependencyMatrix) {
                $dependents = $dependencies->getDependents($table);
                if ($dependents) {
                    return count(array_intersect($this->tables, $dependents)) > 0;
                }
            }
        }
        return false;
    }

    public function getTables()
    {
        return $this->tables;
    }

    public function newDependencyMatrix()
    {
        return new EMAILMaker_ReportRunQueryDependencyMatrix();
    }

    public function registerTempTable($query, $keyColumn)
    {
    }

    public function initializeTempTables()
    {
        $adb = PearDatabase::getInstance();
        $this->tempTables = array();
    }

    public function cleanup()
    {
        $adb = PearDatabase::getInstance();

        $oldDieOnError = $adb->dieOnError;
        $adb->dieOnError = false; // To avoid abnormal termination during shutdown...
        foreach ($this->tempTables as $uniqueName => $tempTableInfo) {
            $adb->pquery('DROP TABLE ' . $uniqueName, array());
        }
        $adb->dieOnError = $oldDieOnError;

        $this->tempTables = array();
    }

}

class EMAILMaker_ReportRunQueryDependencyMatrix
{

    protected $matrix = array();
    protected $computedMatrix = null;

    public function addDependency($table, $dependent)
    {
        if (isset($this->matrix[$table]) && !in_array($dependent, $this->matrix[$table])) {
            $this->matrix[$table][] = $dependent;
        } else {
            $this->setDependency($table, array($dependent));
        }
    }

    public function setDependency($table, array $dependents)
    {
        $this->matrix[$table] = $dependents;
    }

    public function getDependents($table)
    {
        $this->computeDependencies();
        return isset($this->computedMatrix[$table]) ? $this->computedMatrix[$table] : array();
    }

    protected function computeDependencies()
    {
        if ($this->computedMatrix !== null) {
            return;
        }

        $this->computedMatrix = array();
        foreach ($this->matrix as $key => $values) {
            $this->computedMatrix[$key] =
                $this->computeDependencyForKey($key, $values);
        }
    }

    protected function computeDependencyForKey($key, $values)
    {
        $merged = array();
        foreach ($values as $value) {
            $merged[] = $value;
            if (isset($this->matrix[$value])) {
                $merged = array_merge($merged, $this->matrix[$value]);
            }
        }
        return $merged;
    }
}