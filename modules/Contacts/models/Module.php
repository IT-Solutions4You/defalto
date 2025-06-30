<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */
class Contacts_Module_Model extends Vtiger_Module_Model {

	protected string $fontIcon = 'fa-solid fa-user';

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
	 * Function returns query for module record's search
	 * @param <String> $searchValue - part of record name (label column of crmentity table)
	 * @param <Integer> $parentId - parent record id
	 * @param <String> $parentModule - parent module name
	 * @return <String> - query
	 */
	function getSearchRecordsQuery($searchValue, $searchFields, $parentId=false, $parentModule=false) {
        $db = PearDatabase::getInstance();
        if($parentId && $parentModule == 'Accounts') {
			$query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
						INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
						WHERE deleted = 0 AND vtiger_contactdetails.accountid = ? AND label like ?";
            $params = array($parentId, "%$searchValue%");
            $returnQuery = $db->convert2Sql($query, $params);
			return $returnQuery;
		} else if($parentId && $parentModule == 'Potentials') {
			$query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
						INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
						LEFT JOIN vtiger_contpotentialrel ON vtiger_contpotentialrel.contactid = vtiger_contactdetails.contactid
						LEFT JOIN vtiger_potential ON vtiger_potential.contact_id = vtiger_contactdetails.contactid
						WHERE deleted = 0 AND (vtiger_contpotentialrel.potentialid = ? OR vtiger_potential.potentialid = ?)
						AND label like ?";
			$params = array($parentId, $parentId, "%$searchValue%");
            $returnQuery = $db->convert2Sql($query, $params);
            return $returnQuery;
		} else if ($parentId && $parentModule == 'HelpDesk') {
            $query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_troubletickets ON vtiger_troubletickets.contact_id = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_troubletickets.ticketid  = ?  AND label like ?";

            $params = array($parentId, "%$searchValue%");
            $returnQuery = $db->convert2Sql($query, $params);
            return $returnQuery;
        } else if($parentId && $parentModule == 'Campaigns') {
            $query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_campaigncontrel ON vtiger_campaigncontrel.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_campaigncontrel.campaignid = ? AND label like ?";

            $params = array($parentId, "%$searchValue%");
            $returnQuery = $db->convert2Sql($query, $params);
            return $returnQuery;
        } else if($parentId && $parentModule == 'Vendors') {
            $query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_vendorcontactrel ON vtiger_vendorcontactrel.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_vendorcontactrel.vendorid = ? AND label like ?";

            $params = array($parentId, "%$searchValue%");
            $returnQuery = $db->convert2Sql($query, $params);
            return $returnQuery;
        } else if ($parentId && $parentModule == 'Quotes') {
            $query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_quotes ON vtiger_quotes.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_quotes.quoteid  = ?  AND label like ?";

            $params = array($parentId, "%$searchValue%");
            $returnQuery = $db->convert2Sql($query, $params);
            return $returnQuery;
        } else if ($parentId && $parentModule == 'PurchaseOrder') {
            $query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_purchaseorder ON vtiger_purchaseorder.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_purchaseorder.purchaseorderid  = ?  AND label like ?";

            $params = array($parentId, "%$searchValue%");
            $returnQuery = $db->convert2Sql($query, $params);
            return $returnQuery;
        } else if ($parentId && $parentModule == 'SalesOrder') {
            $query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_salesorder ON vtiger_salesorder.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_salesorder.salesorderid  = ?  AND label like ?";

            $params = array($parentId, "%$searchValue%");
            $returnQuery = $db->convert2Sql($query, $params);
            return $returnQuery;
        } else if ($parentId && $parentModule == 'Invoice') {
            $query = "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_invoice ON vtiger_invoice.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_invoice.invoiceid  = ?  AND label like ?";

            $params = array($parentId, "%$searchValue%");
            $returnQuery = $db->convert2Sql($query, $params);
            return $returnQuery;
        }

		return parent::getSearchRecordsQuery($searchValue,$searchFields,$parentId, $parentModule);
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
		if (in_array($sourceModule, array('Campaigns', 'Potentials', 'Vendors', 'Products', 'Services', 'ITS4YouEmails'))
				|| ($sourceModule === 'Contacts' && $field === 'contact_id' && $record)) {
			switch ($sourceModule) {
				case 'Campaigns'	: $tableName = 'vtiger_campaigncontrel';	$fieldName = 'contactid';	$relatedFieldName ='campaignid';	break;
				case 'Potentials'	: $tableName = 'vtiger_contpotentialrel';	$fieldName = 'contactid';	$relatedFieldName ='potentialid';	break;
				case 'Vendors'		: $tableName = 'vtiger_vendorcontactrel';	$fieldName = 'contactid';	$relatedFieldName ='vendorid';		break;
				case 'Products'		: $tableName = 'vtiger_seproductsrel';		$fieldName = 'crmid';		$relatedFieldName ='productid';		break;
			}

            		$db = PearDatabase::getInstance();
		    	$params = array($record);
			if ($sourceModule === 'Services') {
				$condition = " vtiger_contactdetails.contactid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = ? UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = ?) ";
                		$params = array($record , $record);
			} elseif ($sourceModule === 'ITS4YouEmails') {
				$condition = ' vtiger_contactdetails.emailoptout = 0';
			} elseif ($sourceModule === 'Contacts' && $field === 'contact_id') {
				$condition = " vtiger_contactdetails.contactid != ?";
			} else {
				$condition = " vtiger_contactdetails.contactid NOT IN (SELECT $fieldName FROM $tableName WHERE $relatedFieldName = ?)";
			}
            		$condition = $db->convert2Sql($condition, $params);

			$position = stripos($listQuery, 'where');
			if($position) {
				$split = preg_split('/where/i', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery. ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}
    
    public function getDefaultSearchField(){
        return "lastname";
    }

    /**
     * @return bool
     */
    public function isShowMapSupported(): bool
    {
        return true;
    }
}
