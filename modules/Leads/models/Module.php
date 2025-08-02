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

class Leads_Module_Model extends Vtiger_Module_Model
{
    protected string $fontIcon = 'fa-solid fa-address-card';

    /**
     * Function to get the Quick Links for the module
     *
     * @param <Array> $linkParams
     *
     * @return <Array> List of Vtiger_Link_Model instances
     */
    public function getSideBarLinks($linkParams)
    {
        $links = parent::getSideBarLinks($linkParams);

        $quickLink = [
            'linktype'  => 'SIDEBARLINK',
            'linklabel' => 'LBL_DASHBOARD',
            'linkurl'   => $this->getDashBoardUrl(),
            'linkicon'  => '',
        ];

        //Check profile permissions for Dashboards
        $moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
        if ($permission) {
            $links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
        }

        return $links;
    }

    /**
     * Function returns Settings Links
     * @return Array
     */
    public function getSettingLinks()
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $settingLinks = parent::getSettingLinks();

        if ($currentUserModel->isAdminUser()) {
            $settingLinks[] = [
                'linktype'  => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_CUSTOM_FIELD_MAPPING',
                'linkurl'   => 'index.php?parent=Settings&module=Leads&view=MappingDetail',
                'linkicon'  => ''
            ];
        }

        return $settingLinks;
    }

    /**
     * Function returns deleted records condition
     */
    public function getDeletedRecordCondition()
    {
        return 'vtiger_crmentity.deleted = 0 AND vtiger_leaddetails.converted = 0';
    }

    /**
     * Function to get the list of recently visisted records
     *
     * @param <Number> $limit
     *
     * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
     */
    public function getRecentRecords($limit = 10)
    {
        $db = PearDatabase::getInstance();

        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $deletedCondition = $this->getDeletedRecordCondition();
        $query = 'SELECT * FROM vtiger_crmentity 
		    INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
            WHERE setype=? AND ' . $deletedCondition . ' AND modifiedby = ? ORDER BY modifiedtime DESC LIMIT ?';
        $params = [$this->get('name'), $currentUserModel->id, $limit];
        $result = $db->pquery($query, $params);
        $noOfRows = $db->num_rows($result);

        $recentRecords = [];
        for ($i = 0; $i < $noOfRows; ++$i) {
            $row = $db->query_result_rowdata($result, $i);
            $row['id'] = $row['crmid'];
            $recentRecords[$row['id']] = $this->getRecordFromArray($row);
        }

        return $recentRecords;
    }

    /**
     * Function returns the Number of Leads created per week
     *
     * @param type $data
     *
     * @return <Array>
     */
    public function getLeadsCreated($owner, $dateFilter)
    {
        $db = PearDatabase::getInstance();

        $ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
        if (!empty($ownerSql)) {
            $ownerSql = ' AND ' . $ownerSql;
        }

        $params = [];
        if (!empty($dateFilter)) {
            $dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
            //appended time frame and converted to db time zone in showwidget.php
            $params[] = $dateFilter['start'];
            $params[] = $dateFilter['end'];
        }

        $result = $db->pquery(
            'SELECT COUNT(*) AS count, date(createdtime) AS time FROM vtiger_leaddetails
						INNER JOIN vtiger_crmentity ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
						AND deleted=0 ' . Users_Privileges_Model::getNonAdminAccessControlQuery(
                $this->getName()
            ) . $ownerSql . ' ' . $dateFilterSql . ' AND converted = 0 GROUP BY week(createdtime)',
            $params
        );

        $response = [];
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $row = $db->query_result_rowdata($result, $i);
            $response[$i][0] = $row['count'];
            $response[$i][1] = $row['time'];
        }

        return $response;
    }

    /**
     * Function returns Leads grouped by Status
     *
     * @param type $data
     *
     * @return <Array>
     */
    public function getLeadsByStatus($owner, $dateFilter)
    {
        $db = PearDatabase::getInstance();

        $ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
        if (!empty($ownerSql)) {
            $ownerSql = ' AND ' . $ownerSql;
        }

        $dateFilterSql = '';
        $params = [];
        if (!empty($dateFilter)) {
            $dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
            //appended time frame and converted to db time zone in showwidget.php
            $params[] = $dateFilter['start'];
            $params[] = $dateFilter['end'];
        }
        $picklistvaluesmap = getAllPickListValues("leadstatus");
        foreach ($picklistvaluesmap as $picklistValue) {
            $params[] = $picklistValue;
        }

        $result = $db->pquery(
            'SELECT COUNT(*) as count, CASE WHEN vtiger_leadstatus.leadstatus IS NULL OR vtiger_leadstatus.leadstatus = "" THEN "" ELSE 
						vtiger_leadstatus.leadstatus END AS leadstatusvalue FROM vtiger_leaddetails 
						INNER JOIN vtiger_crmentity ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
						AND deleted=0 AND converted = 0 ' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . $ownerSql . ' ' . $dateFilterSql .
            'INNER JOIN vtiger_leadstatus ON vtiger_leaddetails.leadstatus = vtiger_leadstatus.leadstatus 
                        WHERE vtiger_leaddetails.leadstatus IN (' . generateQuestionMarks($picklistvaluesmap) . ') 
						GROUP BY leadstatusvalue ORDER BY vtiger_leadstatus.sortorderid',
            $params
        );

        $response = [];

        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $row = $db->query_result_rowdata($result, $i);
            $response[$i][0] = $row['count'];
            $leadStatusVal = $row['leadstatusvalue'];
            if ($leadStatusVal == '') {
                $leadStatusVal = 'LBL_BLANK';
            }
            $response[$i][1] = vtranslate($leadStatusVal, $this->getName());
            $response[$i][2] = $leadStatusVal;
        }

        return $response;
    }

    /**
     * Function returns Leads grouped by Source
     *
     * @param type $data
     *
     * @return <Array>
     */
    public function getLeadsBySource($owner, $dateFilter)
    {
        $db = PearDatabase::getInstance();

        $ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
        if (!empty($ownerSql)) {
            $ownerSql = ' AND ' . $ownerSql;
        }

        $params = [];
        if (!empty($dateFilter)) {
            $dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
            //appended time frame and converted to db time zone in showwidget.php
            $params[] = $dateFilter['start'];
            $params[] = $dateFilter['end'];
        }
        $picklistvaluesmap = getAllPickListValues("leadsource");
        foreach ($picklistvaluesmap as $picklistValue) {
            $params[] = $picklistValue;
        }

        $result = $db->pquery(
            'SELECT COUNT(*) as count, CASE WHEN vtiger_leaddetails.leadsource IS NULL OR vtiger_leaddetails.leadsource = "" THEN "" 
						ELSE vtiger_leaddetails.leadsource END AS leadsourcevalue FROM vtiger_leaddetails 
						INNER JOIN vtiger_crmentity ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
						AND deleted=0 AND converted = 0 ' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . $ownerSql . ' ' . $dateFilterSql .
            'INNER JOIN vtiger_leadsource ON vtiger_leaddetails.leadsource = vtiger_leadsource.leadsource 
                        WHERE vtiger_leaddetails.leadsource IN (' . generateQuestionMarks($picklistvaluesmap) . ') 
						GROUP BY leadsourcevalue ORDER BY vtiger_leadsource.sortorderid',
            $params
        );

        $response = [];
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $row = $db->query_result_rowdata($result, $i);
            $response[$i][0] = $row['count'];
            $leadSourceVal = $row['leadsourcevalue'];
            if ($leadSourceVal == '') {
                $leadSourceVal = 'LBL_BLANK';
            }
            $response[$i][1] = vtranslate($leadSourceVal, $this->getName());
            $response[$i][2] = $leadSourceVal;
        }

        return $response;
    }

    /**
     * Function returns Leads grouped by Industry
     *
     * @param type $data
     *
     * @return <Array>
     */
    public function getLeadsByIndustry($owner, $dateFilter)
    {
        $db = PearDatabase::getInstance();

        $ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
        if (!empty($ownerSql)) {
            $ownerSql = ' AND ' . $ownerSql;
        }

        $params = [];
        if (!empty($dateFilter)) {
            $dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
            //appended time frame and converted to db time zone in showwidget.php
            $params[] = $dateFilter['start'];
            $params[] = $dateFilter['end'];
        }
        $picklistvaluesmap = getAllPickListValues("industry");
        foreach ($picklistvaluesmap as $picklistValue) {
            $params[] = $picklistValue;
        }

        $result = $db->pquery(
            'SELECT COUNT(*) as count, CASE WHEN vtiger_leaddetails.industry IS NULL OR vtiger_leaddetails.industry = "" THEN "" 
						ELSE vtiger_leaddetails.industry END AS industryvalue FROM vtiger_leaddetails 
						INNER JOIN vtiger_crmentity ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
						AND deleted=0 AND converted = 0 ' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . $ownerSql . ' ' . $dateFilterSql . '
						INNER JOIN vtiger_industry ON vtiger_leaddetails.industry = vtiger_industry.industry 
                        WHERE vtiger_leaddetails.industry IN (' . generateQuestionMarks($picklistvaluesmap) . ') 
						GROUP BY industryvalue ORDER BY vtiger_industry.sortorderid',
            $params
        );

        $response = [];
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $row = $db->query_result_rowdata($result, $i);
            $response[$i][0] = $row['count'];
            $industyValue = $row['industryvalue'];
            if ($industyValue == '') {
                $industyValue = 'LBL_BLANK';
            }
            $response[$i][1] = vtranslate($industyValue, $this->getName());
            $response[$i][2] = $industyValue;
        }

        return $response;
    }

    /**
     * Function to get Converted Information for selected records
     *
     * @param <array> $recordIdsList
     *
     * @return <array> converted Info
     */
    public static function getConvertedInfo($recordIdsList = [])
    {
        $convertedInfo = [];
        if ($recordIdsList) {
            $db = PearDatabase::getInstance();
            $result = $db->pquery("SELECT converted FROM vtiger_leaddetails WHERE leadid IN (" . generateQuestionMarks($recordIdsList) . ")", $recordIdsList);
            $numOfRows = $db->num_rows($result);

            for ($i = 0; $i < $numOfRows; $i++) {
                $convertedInfo[$recordIdsList[$i]] = $db->query_result($result, $i, 'converted');
            }
        }

        return $convertedInfo;
    }

    /**
     * Function to get list view query for popup window
     *
     * @param <String>  $sourceModule Parent module
     * @param <String>  $field        parent fieldname
     * @param <Integer> $record       parent id
     * @param <String>  $listQuery
     *
     * @return <String> Listview Query
     */
    public function getQueryByModuleField($sourceModule, $field, $record, $listQuery)
    {
        if (in_array($sourceModule, ['Campaigns', 'Products', 'Services', 'ITS4YouEmails'])) {
            switch ($sourceModule) {
                case 'Campaigns'    :
                    $tableName = 'vtiger_campaignleadrel';
                    $fieldName = 'leadid';
                    $relatedFieldName = 'campaignid';
                    break;
                case 'Products'        :
                    $tableName = 'vtiger_seproductsrel';
                    $fieldName = 'crmid';
                    $relatedFieldName = 'productid';
                    break;
            }

            $db = PearDatabase::getInstance();
            $params = [$record];
            if ($sourceModule === 'Services') {
                $condition = " vtiger_leaddetails.leadid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = ? UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = ?) ";
                $params = [$record, $record];
            } elseif ($sourceModule === 'ITS4YouEmails') {
                $condition = ' vtiger_leaddetails.emailoptout = 0';
                $params = [];
            } else {
                $condition = " vtiger_leaddetails.leadid NOT IN (SELECT $fieldName FROM $tableName WHERE $relatedFieldName = ?)";
            }
            $condition = $db->convert2Sql($condition, $params);

            $position = stripos($listQuery, 'where');
            if ($position) {
                $split = preg_split('/where/i', $listQuery);
                $overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
            } else {
                $overRideQuery = $listQuery . ' WHERE ' . $condition;
            }

            return $overRideQuery;
        }
    }

    public function getDefaultSearchField()
    {
        return "lastname";
    }

    /*
     * Function to get supported utility actions for a module
     */
    public function getUtilityActionsNames()
    {
        return ['Import', 'Export', 'Merge', 'ConvertLead', 'DuplicatesHandling'];
    }

    /**
     * @return bool
     */
    public function isShowMapSupported(): bool
    {
        return true;
    }
}