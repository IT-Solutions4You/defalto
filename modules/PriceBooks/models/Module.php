<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class PriceBooks_Module_Model extends Vtiger_Module_Model
{
    /**
     * @var array
     */
    public static array $customFieldLabels = [
        'listprice' => 'Selling Price',
        'unit_price' => 'Unit Price',
    ];

	/**
	 * Function returns query for PriceBook-Product relation
	 * @param <Vtiger_Record_Model> $recordModel
	 * @param <Vtiger_Record_Model> $relatedModuleModel
	 * @return <String>
	 */
	function get_pricebook_products($recordModel, $relatedModuleModel) {
		$query = 'SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.productcode, vtiger_products.commissionrate,
						vtiger_products.qty_per_unit, vtiger_products.unit_price, vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
						vtiger_pricebookproductrel.listprice
				FROM vtiger_products
				INNER JOIN vtiger_pricebookproductrel ON vtiger_products.productid = vtiger_pricebookproductrel.productid
				INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_products.productid
				INNER JOIN vtiger_pricebook on vtiger_pricebook.pricebookid = vtiger_pricebookproductrel.pricebookid
				INNER JOIN vtiger_productcf on vtiger_productcf.productid = vtiger_products.productid
				LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid '
				. Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleModel->getName()) .'
				WHERE vtiger_pricebook.pricebookid = '.$recordModel->getId().' and vtiger_crmentity.deleted = 0';
		return $query;
	}


	/**
	 * Function returns query for PriceBooks-Services Relationship
	 * @param <Vtiger_Record_Model> $recordModel
	 * @param <Vtiger_Record_Model> $relatedModuleModel
	 * @return <String>
	 */
	function get_pricebook_services($recordModel, $relatedModuleModel) {
		$query = 'SELECT vtiger_service.serviceid, vtiger_service.servicename, vtiger_service.service_no, vtiger_service.commissionrate,
					vtiger_service.qty_per_unit, vtiger_service.unit_price, vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
					vtiger_pricebookproductrel.listprice
			FROM vtiger_service
			INNER JOIN vtiger_pricebookproductrel on vtiger_service.serviceid = vtiger_pricebookproductrel.productid
			INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_service.serviceid
			INNER JOIN vtiger_pricebook on vtiger_pricebook.pricebookid = vtiger_pricebookproductrel.pricebookid
			INNER JOIN vtiger_servicecf on vtiger_servicecf.serviceid = vtiger_service.serviceid
			LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid '
			. Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleModel->getName()) .'
			WHERE vtiger_pricebook.pricebookid = '.$recordModel->getId().' and vtiger_crmentity.deleted = 0';
		return $query;
	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery, $currencyId = false) {
		$relatedModulesList = array('Products', 'Services');
		if (in_array($sourceModule, $relatedModulesList)) {
			$pos = stripos($listQuery, ' where ');
            		$db = PearDatabase::getInstance();
			if ($currencyId && in_array($field, array('productid', 'serviceid'))) {
				$condition = " vtiger_pricebook.pricebookid IN (SELECT pricebookid FROM vtiger_pricebookproductrel WHERE productid = ?)
								AND vtiger_pricebook.currency_id = $currencyId AND vtiger_pricebook.active = 1";
			} else if($field == 'productsRelatedList') {
				$condition = "vtiger_pricebook.pricebookid NOT IN (SELECT pricebookid FROM vtiger_pricebookproductrel WHERE productid = ?)
								AND vtiger_pricebook.active = 1";
			}
            		$condition = $db->convert2Sql($condition, array($record));
			if ($pos) {
				$split = preg_split('/ where /i', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}
	
	/**
	 * Funtion that returns fields that will be showed in the record selection popup
	 * @return <Array of fields>
	 */
	public function getPopupViewFieldsList() {
		$popupFileds = $this->getSummaryViewFieldsList();
		$reqPopUpFields = array('Currency' => 'currency_id'); 
		foreach ($reqPopUpFields as $fieldLabel => $fieldName) {
			$fieldModel = Vtiger_Field_Model::getInstance($fieldName,$this); 
			if ($fieldModel->getPermissions('readwrite')) { 
				$popupFileds[$fieldName] = $fieldModel; 
			}
		}
		return array_keys($popupFileds);
	}
    
    /**
	* Function is used to give links in the All menu bar
	*/
	public function getQuickMenuModels() {
		if($this->isEntityModule()) {
			$moduleName = $this->getName();
			$listViewModel = Vtiger_ListView_Model::getCleanInstance($moduleName);
			$basicListViewLinks = $listViewModel->getBasicLinks();
		}
        
		if($basicListViewLinks) {
			foreach($basicListViewLinks as $basicListViewLink) {
				if(is_array($basicListViewLink)) {
					$links[] = Vtiger_Link_Model::getInstanceFromValues($basicListViewLink);
				} else if(is_a($basicListViewLink, 'Vtiger_Link_Model')) {
					$links[] = $basicListViewLink;
				}
			}
		}
		return $links;
	}

	/*
     * Function to get supported utility actions for a module
	 */
	function getUtilityActionsNames() {
        return array('Import', 'Export');
    }

	/**
	 * Function returns export query - deprecated
	 * @param <String> $where
	 * @return <String> export query
	 */
	public function getExportQuery($focus, $query) {
		$baseTableName = $focus->table_name;
		$splitQuery = preg_split('/ FROM /i', $query, 2);
		$columnFields = explode(',', $splitQuery[0]);
		foreach ($columnFields as &$value) {
			if(trim($value) == "$baseTableName.currency_id") {
				$value = ' vtiger_currency_info.currency_name AS currency_id';
			}
		}
		array_push($columnFields, "vtiger_pricebookproductrel.productid as Relatedto", "vtiger_pricebookproductrel.listprice as ListPrice");
		$joinSplit = preg_split('/ WHERE /i',$splitQuery[1], 2);
		$joinSplit[0] .= " LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = $baseTableName.currency_id "
				."LEFT JOIN vtiger_pricebookproductrel on vtiger_pricebook.pricebookid = vtiger_pricebookproductrel.pricebookid ";
		$splitQuery[1] = $joinSplit[0] . ' WHERE ' .$joinSplit[1];
		$query = implode(', ', $columnFields).' FROM ' . $splitQuery[1];
		return $query;
	}

	public function getAdditionalImportFields() {
		if (!$this->importableFields) {
			$fieldHeaders = array(
								'relatedto'=> array('label'=>'Related To', 'uitype'=>10),//For relation field
								'listprice'=> array('label'=>'ListPrice', 'uitype'=>83)//For related field currency
				);

			$this->importableFields = array();
			foreach ($fieldHeaders as $fieldName => $fieldInfo) {
				$fieldModel = new Vtiger_Field_Model();
				$fieldModel->name = $fieldName;
				$fieldModel->label = $fieldInfo['label'];
				$fieldModel->column = $fieldName;
				$fieldModel->uitype = $fieldInfo['uitype'];
				$webServiceField = $fieldModel->getWebserviceFieldObject();
				$webServiceField->setFieldDataType($fieldModel->getFieldDataType());
				$fieldModel->webserviceField = $webServiceField;
				$this->importableFields[$fieldName] = $fieldModel;
			}
		}
		return $this->importableFields;
	}


    /**
     * @throws AppException
     */
    public static function getCustomField(string $fieldName, string $moduleName): object
    {
        $fieldModel = Vtiger_Field_Model::getCleanInstance($fieldName, $moduleName);
        $fieldModel->label = self::$customFieldLabels[$fieldName];
        $fieldModel->uitype = 71;

        return $fieldModel;
    }

    /**
     * @throws AppException
     */
    public function getField($fieldName)
    {
        if (in_array($fieldName, array_keys(self::$customFieldLabels))) {
            return self::getCustomField($fieldName, $this->getName());
        }

        return parent::getField($fieldName);
    }

    public static function retrieveHeaderFieldListPrice($headerFields)
    {
        $newHeaderFields = [];

        foreach ($headerFields as $headerField) {
            $newHeaderFields[$headerField->getName()] = $headerField;

            if (1 === count($newHeaderFields)) {
                //Added to support List Price
                $field = new Vtiger_Field_Model();
                $field->set('name', 'listprice');
                $field->set('column', 'listprice');
                $field->set('label', 'List Price');

                $newHeaderFields['listprice'] = $field;
            }
        }

        return $newHeaderFields;
    }
}
