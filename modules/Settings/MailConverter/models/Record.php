<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_MailConverter_Record_Model extends Settings_Vtiger_Record_Model {

    //Overridden table names and fields
    var $baseTable = 'vtiger_mailscanner';
    var $baseTableIndex = 'scannerid';
    var $fieldName = 'scannername';
    var $scannedFoldersTable = 'vtiger_mailscanner_folders';
    var $scannerRulesTable = 'vtiger_mailscanner_rules';
    var $scannerIdsTable = 'vtiger_message_ids';
    
    
    
	/**
	 * Function to get Id of this record instance
	 * @return <Integer> Id
	 */
	public function getId() {
		return $this->get('scannerid');
	}

	/**
	 * Function to get Name of this record instance
	 * @return <String> Name
	 */
	public function getName() {
		return $this->get('scannername');
	}

	/**
	 * Function to set module
	 * @param <Settings_MailConverter_Model> $moduleModel
	 * @return <Settings_MailConverter_Record_Model>
	 */
	public function setModule($moduleModel) {
		$this->module = $moduleModel;
		return $this;
	}

	/**
	 * Function to get module of this record
	 * @return <Settings_MailConverter_Model>
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Function to check whether rules exist or not for this record
	 * @return <Boolean> true/false
	 */
	public function hasRules() {
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT 1 FROM ".$this->scannerRulesTable." WHERE scannerid = ?", array($this->getId()));
		if ($db->num_rows($result)) {
			return true;
		}
		return false;
	}

	/**
	 * Function to get Default url
	 * @return <String> Url
	 */
	public function getDefaultUrl() {
		$moduleModel = $this->getModule();
		return 'index.php?module=' . $moduleModel->getName() . '&parent=' . $moduleModel->getParentName() . '&record=' . $this->getId();
	}

	public function getListUrl() {
		$moduleModel = $this->getModule();
		return 'index.php?module=' . $moduleModel->getName() . '&parent=' . $moduleModel->getParentName() . '&view=List';
	}

	/**
	 * Function to get Scan url
	 * @return <String> Url
	 */
	public function getScanUrl() {
		$url = $this->getDefaultUrl(). '&action=ScanNow';
		return 'javascript:Settings_MailConverter_List_Js.triggerScan("'.$url.'")';
	}

	/**
	 * Function to get Rules list url
	 * @return <String> Url
	 */
	public function getRulesListUrl() {
		$url = $this->getDefaultUrl(). '&view=RulesList';
		return $url;
	}

	/**
	 * Function to get Editview url
	 * @return <String> Url
	 */
	public function getEditViewUrl() {
		return $this->getDefaultUrl() . '&create=existing&view=Edit';
	}

	public function  getCreateRuleRecordUrl() {
		$moduleModel = $this->getModule();
		return 'index.php?module='. $moduleModel->getName() .'&parent=Settings&scannerId='.$this->getId().'&view=EditRule&mode=step1';
	}

	/**
	 * Function to get Delete url
	 * @return <String> Url
	 *
	 */
	public function getDeleteUrl() {
		return $this->getDefaultUrl(). '&action=DeleteMailBox';
	}

	/**
	 * Function to get record links
	 * @return <Array> List of link models <Vtiger_Link_Model>
	 */
	public function getRecordLinks() {
		$qualifiedModuleName = $this->getModule()->getName(true);
		$recordLinks = array(
			array(
				'linktype' => 'LISTVIEW',
				'linklabel' => vtranslate('LBL_EDIT', $qualifiedModuleName) . ' ' . vtranslate('MAILBOX', $qualifiedModuleName),
				'linkurl' => "javascript:window.location.href = '" . $this->getEditViewUrl() . "&mode=step1'",
				'linkicon' => 'icon-pencil'
			),
			array(
				'linktype' => 'LISTVIEW',
				'linklabel' => vtranslate('LBL_SELECT_FOLDERS', $qualifiedModuleName),
				'linkurl' => "javascript:window.location.href = '" . $this->getEditViewUrl() . "&mode=step2'",
				'linkicon' => 'icon-pencil'
			),
			array(
				'linktype' => 'LISTVIEW',
				'linklabel' => vtranslate('LBL_DELETE', $qualifiedModuleName) . ' ' . vtranslate('MAILBOX', $qualifiedModuleName),
				'linkurl' => 'javascript:Settings_MailConverter_List_Js.triggerDelete("' . $this->getDeleteUrl() . '")',
				'linkicon' => 'icon-trash'
			)
		);

		$links = array();
		if ($this->hasRules()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues(array(
					'linktype' => 'LISTVIEW',
					'linklabel' => vtranslate('LBL_SCAN_NOW', $qualifiedModuleName),
					'linkurl' => $this->getScanUrl(),
					'linkicon' => ''
			));
		}

		foreach($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

    /**
	 * Encrypt/Decrypt input.
	 * @access private
	 */
	function __crypt($password, $encrypt=true) {
		vimport('~~include/utils/encryption.php');
		$cryptobj = new Encryption();
		if($encrypt) return $cryptobj->encrypt(trim($password));
		else return $cryptobj->decrypt(trim($password));
	}

	/**
	 * Functon to delete this record
	 */
	public function delete() {
        vimport('~~modules/Settings/MailConverter/handlers/MailScannerInfo.php');
        $scanner = new Vtiger_MailScannerInfo(trim(decode_html($this->getName())));
		$scanner->delete();
	}

	/**
	 * Function to save this record
	 * @return <Boolean> true/false (Saved/Not Saved)
	 */
	public function save() {
        if($this->isDulpicateScannerName()) {
            $this->set('errorMsg', 'LBL_DUPLICATE_SCANNERNAME_ERROR');
            return false;
        }
        if($this->isDulpicateUserName()) {
            $this->set('errorMsg', 'LBL_DUPLICATE_USERNAME_ERROR');
            return false;
        }
        vimport('~~modules/Settings/MailConverter/handlers/MailScannerInfo.php');
		$scannerLatestInfo = new Vtiger_MailScannerInfo(false, false);
		$fieldsList = $this->getModule()->getFields();
		foreach ($fieldsList as $fieldName => $fieldModel) {
			$scannerLatestInfo->$fieldName = $this->get($fieldName);
		}
		$scannerId = $this->getId();
		if (!empty($scannerId)) {
			$scannerLatestInfo->scannerid = $this->getId();
		}

		$isConnected = true;
		$scannerOldInfo = new Vtiger_MailScannerInfo($this->get('scannerOldName'));

		if(!$scannerOldInfo->compare($scannerLatestInfo)) {
            vimport('~~modules/Settings/MailConverter/handlers/MailBox.php');
			$mailBox = new Vtiger_MailBox($scannerLatestInfo);
			$isConnected = $mailBox->connect();
		}
		if($isConnected) {
			$scannerLatestInfo->connecturl = $mailBox->_imapurl;
			//$scannerLatestInfo->isvalid = $scannerOldInfo->isvalid = $isConnected;
            if(!empty($scannerId)){
                $scannerOldInfo->scannerid = $scannerId;
            }
			$mailServerChanged = $scannerOldInfo->update($scannerLatestInfo);
            if(empty($scannerId)) {
                $this->set('scannerid', $scannerOldInfo->scannerid);
            }

			$rescanFolder = false;
			if ($this->get('searchfor') === 'all') {
				$rescanFolder = true;
			}
			$scannerOldInfo->updateAllFolderRescan($rescanFolder);
		} else {
            $this->set('errorMsg', $mailBox->_imaperror);
        }
		return $isConnected;
	}

	/**
	 * Function to scan this record
	 * @return <Boolean> true/false (Scaned/Not)
	 */
	public function scanNow() {
		$isValid = $this->get('isvalid');
		if ($isValid) {
			vimport('~~modules/Settings/MailConverter/handlers/MailScannerInfo.php');
			vimport('~~modules/Settings/MailConverter/handlers/MailScanner.php');
			$scannerInfo = new Vtiger_MailScannerInfo($this->getName());
			/** Start the scanning. */
			$scanner = new Vtiger_MailScanner($scannerInfo);
			$status = $scanner->performScanNow();
            if(empty($status))
                $this->set('errorMsg', 'LBL_NO_RULES');
			return $status;
		}
        $this->set('errorMsg', 'LBL_MAIL_SCANNER_INACTIVE');
		return false;
	}

	/**
	 * Function to get Folders list of this record
	 * @return <Array> Folders list
	 */
	public function getFoldersList() {
        vimport('~~modules/Settings/MailConverter/handlers/MailBox.php');
		$scannerInfo = new Vtiger_MailScannerInfo($this->getName());
		return $scannerInfo->getFolderInfo();
	}

	/**
	 * Function to get Updated folders list
	 * @return <Array> Folders List
	 */
	public function getUpdatedFoldersList() {
        vimport('~~modules/Settings/MailConverter/handlers/MailBox.php');
		$scannerInfo = new Vtiger_MailScannerInfo($this->getName());
		$mailBox = new Vtiger_MailBox($scannerInfo);

		if($mailBox->connect()) {
			$folders = $mailBox->getFolders();
			$scannerInfo->updateFolderInfo($folders);
		}

		return $scannerInfo->getFolderInfo();
	}

	/**
	 * Function to Save the folders for this record
	 */
	public function saveFolders() {
		$recordId = $this->getId();
		$db = PearDatabase::getInstance();
		$foldersData = $this->get('foldersData');

		$updateQuery = "UPDATE $this->scannedFoldersTable SET enabled = CASE folderid ";
		foreach ($foldersData as $folderId => $enabled) {
			$updateQuery .= " WHEN $folderId THEN $enabled ";
		}
		$updateQuery .= "ELSE 0 END WHERE scannerid = ?";

		$db->pquery($updateQuery, array($this->getId()));
	}

	/**
	 * Function to update sequence of several rules
	 * @param <Array> $sequencesList
	 */
	public function updateSequence($sequencesList) {
		$db = PearDatabase::getInstance();

		$updateQuery = "UPDATE $this->scannerRulesTable SET sequence = CASE";
		foreach ($sequencesList as $sequence => $ruleId) {
			$updateQuery .= " WHEN ruleid = $ruleId THEN $sequence ";
		}
		$updateQuery .= " END";

		$db->pquery($updateQuery, array());
	}

	//Static functions started

	/**
	 * Function to get Clean instance of this record
	 * @return <Settings_MailConverter_Record_Model>
	 */
	public static function getCleanInstance() {
		$recordModel = new self();
		return $recordModel->setModule(Settings_Vtiger_Module_Model::getInstance('Settings:MailConverter'));
	}

	/**
	 * Function to get instance of this record using by recordId
	 * @param <Integer> $recordId
	 * @return <Settings_MailConverter_Record_Model>
	 */
	public static function getInstanceById($recordId) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT * FROM vtiger_mailscanner WHERE scannerid = ", array($recordId));
		if ($db->num_rows($result)) {
			$recordModel = self::getCleanInstance();
			$recordModel->setData($db->query_result_rowdata($result));
                        $recordModel->set('servertype', $recordModel->getServerType($recordModel->get('server')));
                        return $recordModel->set('password', $recordModel->__crypt($recordModel->get('password'),false));
		}
		return false;
	}

	/**
	 * Function to get List of mail scanner records
	 * @return <Array> List of record models <Settings_MailConverter_Record_Model>
	 */
	public static function getAll() {
		$db = PearDatabase::getInstance();
		$moduleModel = Settings_Vtiger_Module_Model::getInstance('Settings:MailConverter');

		$result = $db->pquery('SELECT * FROM vtiger_mailscanner', array());
		$numOfRows = $db->num_rows($result);

		$recordModelsList = array();
		for ($i=0; $i<$numOfRows; $i++) {
			$rowData = $db->query_result_rowdata($result, $i);
			$recordModel = new self();
			$recordModelsList[$rowData['scannerid']] = $recordModel->setData($rowData)->setModule($moduleModel);
		}
		return $recordModelsList;
	}

	public static function getCount() {
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT 1 FROM vtiger_mailscanner", array());
		$numOfRows = $db->num_rows($result);
		return $numOfRows;
	}

	public function getDetailViewFields() {
        $detailViewIgnoredFields = array('scannername');
        $module = $this->getModule();
        $fields = $module->getFields();
        foreach($detailViewIgnoredFields as $ignoreFieldName) {
            unset($fields[$ignoreFieldName]);
        }
        return $fields;
        return array_diff($fields,$detailViewIgnoredFields);

    }

	public function isFieldEditable($fieldModel) {
		return $fieldModel->isEditable();
	}

	public function getDisplayValue($fieldName) {
        $value = $this->get($fieldName);
        if($fieldName == 'isvalid') {
            if($value == 1){
                return 'Enabled';
            }
            return 'Disabled';
        }
        else if($fieldName == 'time_zone') {
            return Settings_MailConverter_Field_Model::$timeZonePickListValues[$value];
        }
        return $value;
    }
    
    function getLastScanTime() {
        $lastScan = $this->get('last_scan');
        if(!empty($lastScan) && $lastScan != '0000-00-00 00:00:00') {
            $dateTime = new DateTimeField($lastScan);
            return $dateTime->getDisplayDate().' '.Vtiger_Time_UIType::getDisplayValue($dateTime->getDisplayTime());
        } else {
            return '';
        }
    }
    
    function getServerType($serverName) {
        $defaultServers = array('imap.gmail.com', 'imap.mail.yahoo.com', 'mail.messagingengine.com');
        if(!in_array($serverName, $defaultServers)) {
            return 'other';
        } else {
            return $serverName;
        }
    }
    
    function isDulpicateUserName() {
        $server = $this->get('server');
        $userName = $this->get('username');
        $scannerId = $this->getId();
        $db = PearDatabase::getInstance();
        $userNameParts = explode('@', $userName);
        $query = "SELECT server, username FROM $this->baseTable WHERE username LIKE ?";
        $params = array('%'.$userNameParts[0].'%');
        if(!empty($scannerId)) {
            $query .= " AND scannerid <> ?";
            array_push($params, $scannerId);
        }
        $result = $db->pquery($query, $params);
        $count = $db->num_rows($result);
        if($count > 0) {
            for($i = 0; $i < $count; $i++) {
                $storedServer = $db->query_result($result, $i, 'server');
                $storedUsername = $db->query_result($result, $i, 'username');
                $storedUsernameParts = explode('@', $storedUsername);
                if(($userName == $storedUsername) || ($userNameParts[0] == $storedUsernameParts[0] && $server == $storedServer)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    function isDulpicateScannerName() {
        $db = PearDatabase::getInstance();
        $scannerId = $this->getId();
        $query = "SELECT 1 FROM $this->baseTable WHERE scannername = ?";
        $params = array($this->getName());
        if(!empty($scannerId)) {
            $query .= " AND scannerid <> ?";
            array_push($params, $scannerId);
        }
        $result = $db->pquery($query, $params);
        if($db->num_rows($result) > 0) {
            return true;
        } else {
            return false;
        }
    }
}