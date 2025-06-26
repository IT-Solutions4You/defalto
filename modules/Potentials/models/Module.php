<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class Potentials_Module_Model extends Vtiger_Module_Model {

    protected string $fontIcon = 'fa-solid fa-sack-dollar';
	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$parentQuickLinks = parent::getSideBarLinks($linkParams);

		$quickLink = array(
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_DASHBOARD',
			'linkurl' => $this->getDashBoardUrl(),
			'linkicon' => '',
		);
		
		//Check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if($permission) {
			$parentQuickLinks['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}
		
		return $parentQuickLinks;
	}

	/**
	 * Function returns number of Open Potentials in each of the sales stage
	 * @param <Integer> $owner - userid
	 * @return <Array>
	 */
	public function getPotentialsCountBySalesStage($owner, $dateFilter) {
		$db = PearDatabase::getInstance();

		if (!$owner) {
			$currenUserModel = Users_Record_Model::getCurrentUserModel();
			$owner = $currenUserModel->getId();
		} else if ($owner === 'all') {
			$owner = '';
		}

		$params = array();
		if(!empty($owner)) {
			$ownerSql =  ' AND smownerid = ? ';
			$params[] = $owner;
		}
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND closingdate BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
        $picklistvaluesmap = getAllPickListValues("sales_stage");
        unset($picklistvaluesmap['Closed Won']);unset($picklistvaluesmap['Closed Lost']);
        foreach($picklistvaluesmap as $picklistValue) {
            $params[] = $picklistValue;
        }
        
		$result = $db->pquery('SELECT COUNT(*) count, vtiger_potential.sales_stage FROM vtiger_potential
						INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
						INNER JOIN vtiger_sales_stage ON vtiger_potential.sales_stage = vtiger_sales_stage.sales_stage 
                        AND deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()). $ownerSql . $dateFilterSql . ' AND vtiger_potential.sales_stage IN ('.  generateQuestionMarks($picklistvaluesmap).') 
					    GROUP BY sales_stage ORDER BY vtiger_sales_stage.sortorderid', $params);
		
		$response = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
            // Dashboard showing UTF8 characters as encoded values
			$saleStage = decode_html($db->query_result($result, $i, 'sales_stage'));
			$response[$i][0] = vtranslate($saleStage, $this->getName());
			$response[$i][1] = $db->query_result($result, $i, 'count');
			$response[$i][2] = vtranslate($saleStage, $this->getName());
            $response[$i]['link'] = $saleStage;
		}
		return $response;
	}

	/**
	 * Function returns number of Open Potentials for each of the sales person
	 * @param <Integer> $owner - userid
	 * @return <Array>
	 */
	public function getPotentialsCountBySalesPerson() {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$params = array();
        $picklistvaluesmap = getAllPickListValues("sales_stage");
        foreach($picklistvaluesmap as $picklistValue) {
            $params[] = $picklistValue;
        }
        
		$result = $db->pquery('SELECT COUNT(*) AS count, vtiger_users.userlabel as last_name, vtiger_potential.sales_stage, vtiger_groups.groupname FROM vtiger_potential
						INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0
						LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid AND vtiger_users.status="ACTIVE"
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid'.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).'
						INNER JOIN vtiger_sales_stage ON vtiger_potential.sales_stage =  vtiger_sales_stage.sales_stage 
                        WHERE vtiger_potential.sales_stage IN ('.  generateQuestionMarks($picklistvaluesmap).') GROUP BY smownerid, sales_stage ORDER BY vtiger_sales_stage.sortorderid', $params);

		$response = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$lastName = decode_html($row['last_name']);
			if(!$lastName) {
				$lastName = decode_html($row['groupname']);
			}
            $response[$i]['count'] = $row['count'];
            $response[$i]['last_name'] = $lastName;
            $response[$i]['link'] = decode_html($row['sales_stage']);
            $response[$i]['sales_stage'] = vtranslate(decode_html($row['sales_stage']),  $this->getName());
            //$response[$i][2] = $row['']
        }
		return $response;
	}

	/**
	 * Function returns Potentials Amount for each Sales Person
	 * @return <Array>
	 */
	function getPotentialsPipelinedAmountPerSalesPerson() {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$params = array();
        $picklistvaluesmap = getAllPickListValues("sales_stage");
        unset($picklistvaluesmap['Closed Won']);unset($picklistvaluesmap['Closed Lost']);
        foreach($picklistvaluesmap as $picklistValue) $params[] = $picklistValue;
        
		$result = $db->pquery('SELECT sum(amount) AS amount, vtiger_users.userlabel as last_name, vtiger_potential.sales_stage FROM vtiger_potential
						INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid AND vtiger_users.status="ACTIVE"
						AND vtiger_crmentity.deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).
						'INNER JOIN vtiger_sales_stage ON vtiger_potential.sales_stage =  vtiger_sales_stage.sales_stage 
						WHERE vtiger_potential.sales_stage IN ('.generateQuestionMarks($picklistvaluesmap).') 
						GROUP BY smownerid, sales_stage ORDER BY vtiger_sales_stage.sortorderid', $params);
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
            $row['link'] = decode_html($row['sales_stage']);
			$row['amount'] = CurrencyField::convertToUserFormat($row['amount'], null, false, true);
            $row['last_name'] = decode_html($row['last_name']);
            $row['sales_stage'] = vtranslate(decode_html($row['sales_stage']),  $this->getName());
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * Function returns Total Revenue for each Sales Person
	 * @return <Array>
	 */
	function getTotalRevenuePerSalesPerson($dateFilter) {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$params = array();
		$params[] = 'Closed Won';
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//appended time frame and converted to db time zone in showwidget.php
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
		
		$result = $db->pquery('SELECT sum(amount) amount, vtiger_users.userlabel as last_name,vtiger_users.id as id,DATE_FORMAT(closingdate, "%d-%m-%Y") AS closingdate  FROM vtiger_potential
						INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid AND vtiger_users.status="ACTIVE"
						AND vtiger_crmentity.deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).'WHERE sales_stage = ? '.' '.$dateFilterSql.' GROUP BY smownerid', $params);
		$data = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$row['amount'] = CurrencyField::convertToUserFormat($row['amount'], null, false, true);
                        $row['last_name'] = decode_html($row['last_name']);
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * Function returns Top Potentials
	 * @return <Array of Vtiger_Record_Model>
	 */
	function getTopPotentials($pagingModel) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();
		$query = "SELECT crmid, amount, potentialname, related_to FROM vtiger_potential
						INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
							AND deleted = 0 ".Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName())."
						WHERE sales_stage NOT IN ('Closed Won', 'Closed Lost') AND amount > 0
						ORDER BY amount DESC LIMIT ".$pagingModel->getStartIndex().", ".$pagingModel->getPageLimit()."";
		$result = $db->pquery($query, array());

		$models = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$modelInstance = Vtiger_Record_Model::getCleanInstance('Potentials');
			$modelInstance->setId($db->query_result($result, $i, 'crmid'));
			$modelInstance->set('amount', $db->query_result($result, $i, 'amount'));
			$modelInstance->set('potentialname', $db->query_result($result, $i, 'potentialname'));
			$modelInstance->set('related_to', $db->query_result($result, $i, 'related_to'));
			$models[] = $modelInstance;
		}
		return $models;
	}

	/**
	 * Function returns Potentials Amount for each Sales Stage
	 * @return <Array>
	 */
	function getPotentialTotalAmountBySalesStage() {
		//$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

        $picklistValues = getAllPickListValues("sales_stage");
		$data = array();
		foreach ($picklistValues as $key => $picklistValue) {
			$result = $db->pquery('SELECT SUM(amount) AS amount FROM vtiger_potential
								   INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
								   AND deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).' WHERE sales_stage = ?', array($picklistValue));
			$num_rows = $db->num_rows($result);
			for($i=0; $i<$num_rows; $i++) {
				$values = array();
				$amount = $db->query_result($result, $i, 'amount');
				if(!empty($amount)){
					$values[0] = CurrencyField::convertToUserFormat($db->query_result($result, $i, 'amount'), null, false, true);
					$values[1] = vtranslate($picklistValue, $this->getName());
                    $values['link'] = $picklistValue;
					$data[] = $values;
				}
				
			}
		}
		return $data;
	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if (in_array($sourceModule, array('Products', 'Services'))) {
            		$db = PearDatabase::getInstance();
		    	$params = array($record);
			if ($sourceModule === 'Products') {
				$condition = " vtiger_potential.potentialid NOT IN (SELECT crmid FROM vtiger_seproductsrel WHERE productid = ?)";
			} elseif ($sourceModule === 'Services') {
				$condition = " vtiger_potential.potentialid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = ? UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = ?) ";
                		$params = array($record, $record);
			}
			$condition = $db->convert2Sql($condition, $params);

			$pos = stripos($listQuery, 'where');
			if ($pos) {
				$split = preg_split('/where/i', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function returns query for module record's search
	 * @param <String> $searchValue - part of record name (label column of crmentity table)
	 * @param <Integer> $parentId - parent record id
	 * @param <String> $parentModule - parent module name
	 * @return <String> - query
	 */
	public function getSearchRecordsQuery($searchValue,$searchFields, $parentId=false, $parentModule=false) {
        $db = PearDatabase::getInstance();
		if($parentId && in_array($parentModule, array('Accounts', 'Contacts'))) {
			$query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
						INNER JOIN vtiger_potential ON vtiger_potential.potentialid = vtiger_crmentity.crmid
						WHERE deleted = 0 AND vtiger_potential.related_to = ? AND label like ?";
			$params = array($parentId, "%$searchValue%");
            $returnQuery = $db->convert2Sql($query, $params);
            return $returnQuery;
		}
		return parent::getSearchRecordsQuery($parentId, $parentModule);
	}
    
    /**
	 * Function returns Settings Links
	 * @return Array
	 */
	public function getSettingLinks() {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$settingLinks = parent::getSettingLinks();
		
		if($currentUserModel->isAdminUser()) {
			$settingLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_CUSTOM_FIELD_MAPPING',
					'linkurl' => 'index.php?parent=Settings&module=Potentials&view=MappingDetail',
					'linkicon' => '');
			
		}
		return $settingLinks;
	}
    
    /*
     * Function to get supported utility actions for a module
     */
    function getUtilityActionsNames() {
        return array('Import', 'Export', 'DuplicatesHandling');
    }
}
