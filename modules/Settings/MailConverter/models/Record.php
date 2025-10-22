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

class Settings_MailConverter_Record_Model extends Settings_Vtiger_Record_Model
{
    protected $module;

    /**
     * Function to get Id of this record instance
     * @return <Integer> Id
     */
    public function getId(): int
    {
        return (int)$this->get('scannerid');
    }

    /**
     * Function to get Name of this record instance
     * @return <String> Name
     */
    public function getName()
    {
        return $this->get('scannername');
    }

    /**
     * Function to set module
     *
     * @param <Settings_MailConverter_Model> $moduleModel
     *
     * @return <Settings_MailConverter_Record_Model>
     */
    public function setModule($moduleModel)
    {
        $this->module = $moduleModel;

        return $this;
    }

    /**
     * Function to get module of this record
     * @return <Settings_MailConverter_Model>
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Function to check whether rules exist or not for this record
     * @return <Boolean> true/false
     */
    public function hasRules()
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT 1 FROM vtiger_mailscanner_rules WHERE scannerid = ?', [$this->getId()]);
        if ($db->num_rows($result)) {
            return true;
        }

        return false;
    }

    /**
     * Function to get Default url
     * @return <String> Url
     */
    public function getDefaultUrl()
    {
        $moduleModel = $this->getModule();

        return 'index.php?module=' . $moduleModel->getName() . '&parent=' . $moduleModel->getParentName() . '&record=' . $this->getId();
    }

    public function getListUrl()
    {
        $moduleModel = $this->getModule();

        return 'index.php?module=' . $moduleModel->getName() . '&parent=' . $moduleModel->getParentName() . '&view=List';
    }

    /**
     * Function to get Scan url
     * @return <String> Url
     */
    public function getScanUrl()
    {
        $url = $this->getDefaultUrl() . '&action=ScanNow';

        return 'javascript:Settings_MailConverter_List_Js.triggerScan("' . $url . '")';
    }

    /**
     * Function to get Rules list url
     * @return <String> Url
     */
    public function getRulesListUrl()
    {
        $url = $this->getDefaultUrl() . '&view=RulesList';

        return $url;
    }

    /**
     * Function to get Editview url
     * @return <String> Url
     */
    public function getEditViewUrl()
    {
        return $this->getDefaultUrl() . '&create=existing&view=Edit';
    }

    public function getCreateRuleRecordUrl()
    {
        $moduleModel = $this->getModule();
        $url = 'index.php?module=' . $moduleModel->getName() . '&parent=Settings&scannerId=' . $this->getId() . '&view=EditRule';

        return 'javascript:Settings_MailConverter_Index_Js.triggerRuleEdit("' . $url . '")';
    }

    /**
     * Function to get Delete url
     * @return <String> Url
     *
     */
    public function getDeleteUrl()
    {
        return $this->getDefaultUrl() . '&action=DeleteMailBox';
    }

    /**
     * Function to get record links
     * @return <Array> List of link models <Vtiger_Link_Model>
     */
    public function getRecordLinks()
    {
        $qualifiedModuleName = $this->getModule()->getName(true);
        $recordLinks = [
            [
                'linktype'  => 'LISTVIEW',
                'linklabel' => vtranslate('LBL_EDIT', $qualifiedModuleName) . ' ' . vtranslate('MAILBOX', $qualifiedModuleName),
                'linkurl'   => "javascript:window.location.href = '" . $this->getEditViewUrl() . "&mode=step1'",
                'linkicon'  => 'icon-pencil'
            ],
            [
                'linktype'  => 'LISTVIEW',
                'linklabel' => vtranslate('LBL_SELECT_FOLDERS', $qualifiedModuleName),
                'linkurl'   => "javascript:window.location.href = '" . $this->getEditViewUrl() . "&mode=step2'",
                'linkicon'  => 'icon-pencil'
            ],
            [
                'linktype'  => 'LISTVIEW',
                'linklabel' => vtranslate('LBL_DELETE', $qualifiedModuleName) . ' ' . vtranslate('MAILBOX', $qualifiedModuleName),
                'linkurl'   => 'javascript:Settings_MailConverter_List_Js.triggerDelete("' . $this->getDeleteUrl() . '")',
                'linkicon'  => 'icon-trash'
            ]
        ];

        $links = [];
        if ($this->hasRules()) {
            $links[] = Vtiger_Link_Model::getInstanceFromValues([
                'linktype'  => 'LISTVIEW',
                'linklabel' => vtranslate('LBL_SCAN_NOW', $qualifiedModuleName),
                'linkurl'   => $this->getScanUrl(),
                'linkicon'  => ''
            ]);
        }

        foreach ($recordLinks as $recordLink) {
            $links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
        }

        return $links;
    }

    /**
     * Encrypt/Decrypt input.
     * @access private
     */
    function __crypt($password, $encrypt = true)
    {
        vimport('~~include/utils/encryption.php');
        $cryptobj = new Encryption();
        if ($encrypt) {
            return $cryptobj->encrypt(trim($password));
        } else {
            return $cryptobj->decrypt(trim($password));
        }
    }

    /**
     * Functon to delete this record
     * @throws Exception
     */
    public function delete(): void
    {
        $scanner = Settings_MailConverter_MailScannerInfo_Handler::getInstance(trim($this->getName()));
        $scanner->delete();
    }

    /**
     * Function to save this record
     * @return bool true/false (Saved/Not Saved)
     * @throws Exception
     */
    public function save(): bool
    {
        $scannerLatestInfo = Settings_MailConverter_MailScannerInfo_Handler::getInstance(false, false);
        $fieldsList = $this->getModule()->getFields();

        foreach ($fieldsList as $fieldName => $fieldModel) {
            $scannerLatestInfo->$fieldName = $this->get($fieldName);
        }

        if (!$this->isEmpty('scannerOldName')) {
            $scannerOldInfo = Settings_MailConverter_MailScannerInfo_Handler::getInstance($this->get('scannerOldName'));
            $scannerOldId = $scannerOldInfo->getId();

            if ($scannerOldId) {
                $this->setId($scannerOldInfo->getId());
            }
        }

        $scannerId = $this->getId();

        if ($scannerId) {
            $scannerLatestInfo->setId($scannerId);
        }

        //Checking Scanner Name
        $scannerName = $this->getName();

        if ($scannerName && !validateAlphanumericInput($scannerName)) {
            $this->set('save_error_message', 'LBL_REQUERED_ALPHANUMERIC_SCANNER_NAME');

            return false;
        }

        //Checking Server
        $server = $this->get('server');

        if ($server && !validateServerName($server)) {
            $this->set('save_error_message', 'LBL_INVALID_SERVER_NAME');

            return false;
        }

        $this->set('save_error_message', 'LBL_CONNECTION_TO_MAILBOX_FAILED');
        $mailBox = new Settings_MailConverter_MailBox_Handler($scannerLatestInfo);
        $isConnected = $mailBox->connect();

        if ($isConnected) {
            $scannerLatestInfo->save();

            $this->set('scannerid', $scannerLatestInfo->getId());

            $rescanFolder = false;

            if ($this->get('searchfor') === 'all') {
                $rescanFolder = true;
            }

            $scannerLatestInfo->updateAllFolderRescan($rescanFolder);
        }

        return $isConnected;
    }

    public function setId(int $value): void
    {
        $this->set('scannerid', $value);
    }

    /**
     * Function to scan this record
     * @return <Boolean> true/false (Scaned/Not)
     * @throws Exception
     */
    public function scanNow(): bool
    {
        $isValid = $this->get('isvalid');

        if ($isValid) {
            $scannerInfo = Settings_MailConverter_MailScannerInfo_Handler::getInstance($this->getName());
            /** Start the scanning. */
            $scanner = new Settings_MailConverter_MailScanner_Handler($scannerInfo);

            return $scanner->performScanNow();
        }

        return false;
    }

    /**
     * Function to get Folders list of this record
     * @return <Array> Folders list
     * @throws Exception
     */
    public function getFoldersList()
    {
        $scannerInfo = Settings_MailConverter_MailScannerInfo_Handler::getInstance($this->getName());

        return $scannerInfo->getFolderInfo();
    }

    /**
     * Function to get Updated folders list
     * @return <Array> Folders List
     * @throws Exception
     */
    public function getUpdatedFoldersList()
    {
        $scannerInfo = Settings_MailConverter_MailScannerInfo_Handler::getInstance($this->getName());
        $mailBox = new Settings_MailConverter_MailBox_Handler($scannerInfo);

        if ($mailBox->connect()) {
            $folders = $mailBox->getFolders();
            $scannerInfo->updateFolderInfo($folders);
        }

        return $scannerInfo->getFolderInfo();
    }

    /**
     * Function to Save the folders for this record
     */
    public function saveFolders()
    {
        $recordId = $this->getId();
        $db = PearDatabase::getInstance();
        $foldersData = $this->get('foldersData');

        $updateQuery = "UPDATE vtiger_mailscanner_folders SET enabled = CASE folderid ";
        foreach ($foldersData as $folderId => $enabled) {
            $updateQuery .= " WHEN $folderId THEN $enabled ";
        }
        $updateQuery .= "ELSE 0 END WHERE scannerid = ?";

        $db->pquery($updateQuery, [$this->getId()]);
    }

    /**
     * Function to update sequence of several rules
     *
     * @param <Array> $sequencesList
     */
    public function updateSequence($sequencesList)
    {
        $db = PearDatabase::getInstance();

        $updateQuery = "UPDATE vtiger_mailscanner_rules SET sequence = CASE";
        foreach ($sequencesList as $sequence => $ruleId) {
            $updateQuery .= " WHEN ruleid = $ruleId THEN $sequence ";
        }
        $updateQuery .= " END";

        $db->pquery($updateQuery, []);
    }

    //Static functions started

    /**
     * Function to get Clean instance of this record
     * @return <Settings_MailConverter_Record_Model>
     */
    public static function getCleanInstance()
    {
        $recordModel = new self();

        return $recordModel->setModule(Settings_Vtiger_Module_Model::getInstance('Settings:MailConverter'));
    }

    /**
     * Function to get instance of this record using by recordId
     *
     * @param <Integer> $recordId
     *
     * @return <Settings_MailConverter_Record_Model>
     */
    public static function getInstanceById($recordId)
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT * FROM vtiger_mailscanner WHERE scannerid = ?', [$recordId]);

        if ($db->num_rows($result)) {
            $recordModel = self::getCleanInstance();
            $recordModel->setData($db->query_result_rowdata($result));
            $recordModel->set('password', $recordModel->__crypt($recordModel->get('password'), false));

            return $recordModel;
        }

        return false;
    }

    /**
     * Function to get List of mail scanner records
     * @return <Array> List of record models <Settings_MailConverter_Record_Model>
     */
    public static function getAll()
    {
        $db = PearDatabase::getInstance();
        $moduleModel = Settings_Vtiger_Module_Model::getInstance('Settings:MailConverter');

        $result = $db->pquery('SELECT * FROM vtiger_mailscanner', []);
        $numOfRows = $db->num_rows($result);

        $recordModelsList = [];
        for ($i = 0; $i < $numOfRows; $i++) {
            $rowData = $db->query_result_rowdata($result, $i);
            $recordModel = new self();
            $recordModelsList[$rowData['scannerid']] = $recordModel->setData($rowData)->setModule($moduleModel);
        }

        return $recordModelsList;
    }

    public static function getCount()
    {
        $db = PearDatabase::getInstance();
        $moduleModel = Settings_Vtiger_Module_Model::getInstance('Settings:MailConverter');

        $result = $db->pquery('SELECT 1 FROM vtiger_mailscanner', []);
        $numOfRows = $db->num_rows($result);

        return $numOfRows;
    }

    public function getDetailViewFields()
    {
        $detailViewIgnoredFields = ['scannername'];
        $module = $this->getModule();
        $fields = $module->getFields();
        foreach ($detailViewIgnoredFields as $ignoreFieldName) {
            unset($fields[$ignoreFieldName]);
        }

        return $fields;

        return array_diff($fields, $detailViewIgnoredFields);
    }

    public function isFieldEditable($fieldModel)
    {
        return $fieldModel->isEditable();
    }

    public function getDisplayValue($fieldName)
    {
        $value = $this->get($fieldName);
        if ($fieldName == 'isvalid') {
            if ($value == 1) {
                return 'Enabled';
            }

            return 'Disabled';
        } elseif ($fieldName == 'time_zone') {
            return Settings_MailConverter_Field_Model::$timeZonePickListValues[$value];
        }

        return $value;
    }
}