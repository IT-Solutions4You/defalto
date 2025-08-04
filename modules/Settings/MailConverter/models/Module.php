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

class Settings_MailConverter_Module_Model extends Settings_Vtiger_Module_Model
{
    var $name = 'MailConverter';

    /**
     * Function to get Create record url
     * @return <String> Url
     */
    public function getCreateRecordUrl()
    {
        return 'index.php?module=MailConverter&parent=Settings&view=Edit&mode=step1&create=new';
    }

    /**
     * Function to get List of fields for mail converter record
     * @return <Array> List of fields
     */
    public function getFields()
    {
        $fields = [
            'scannername'         => ['name' => 'scannername', 'typeofdata' => 'V~M', 'label' => 'Scanner Name', 'datatype' => 'string'],
            'server'              => ['name' => 'server', 'typeofdata' => 'V~M', 'label' => 'Server', 'datatype' => 'string'],
            'username'            => ['name' => 'username', 'typeofdata' => 'V~M', 'label' => 'User Name', 'datatype' => 'string'],
            'password'            => ['name' => 'password', 'typeofdata' => 'V~M', 'label' => 'Password', 'datatype' => 'password'],
            'client_id'           => ['name' => 'client_id', 'typeofdata' => 'V~M', 'label' => 'Client Id', 'datatype' => 'string'],
            'client_secret'       => ['name' => 'client_secret', 'typeofdata' => 'V~M', 'label' => 'Client Secret', 'datatype' => 'password'],
            'client_token'        => ['name' => 'client_token', 'typeofdata' => 'V~M', 'label' => 'Client Token', 'datatype' => 'token'],
            'client_access_token' => ['name' => 'client_access_token', 'typeofdata' => 'V~M', 'label' => 'Client Access Token', 'datatype' => 'password'],
            'protocol'            => ['name' => 'protocol', 'typeofdata' => 'C~O', 'label' => 'Protocol', 'datatype' => 'radio'],
            'ssltype'             => ['name' => 'ssltype', 'typeofdata' => 'C~O', 'label' => 'SSL Type', 'datatype' => 'radio'],
            'sslmethod'           => ['name' => 'sslmethod', 'typeofdata' => 'C~O', 'label' => 'SSL Method', 'datatype' => 'radio'],
            'connecturl'          => ['name' => 'connecturl', 'typeofdata' => 'V~O', 'label' => 'Connect URL', 'datatype' => 'string', 'isEditable' => false],
            'searchfor'           => ['name' => 'searchfor', 'typeofdata' => 'V~O', 'label' => 'Look For', 'datatype' => 'picklist'],
            'markas'              => ['name' => 'markas', 'typeofdata' => 'V~O', 'label' => 'After Scan', 'datatype' => 'picklist'],
            'isvalid'             => ['name' => 'isvalid', 'typeofdata' => 'C~O', 'label' => 'Status', 'datatype' => 'boolean'],
            'time_zone'           => ['name' => 'time_zone', 'typeofdata' => 'V~O', 'label' => 'Time Zone', 'datatype' => 'picklist']
        ];

        $fieldsList = [];
        foreach ($fields as $fieldName => $fieldInfo) {
            $fieldModel = new Settings_MailConverter_Field_Model();
            foreach ($fieldInfo as $key => $value) {
                $fieldModel->set($key, $value);
            }
            $fieldsList[$fieldName] = $fieldModel;
        }

        return $fieldsList;
    }

    /**
     * Function to get the field of setup Rules
     * @return <Array> List of setup rule fields
     */

    public function getSetupRuleFields()
    {
        $ruleFields = [
            'fromaddress' => ['name' => 'fromaddress', 'label' => 'LBL_FROM', 'datatype' => 'email'],
            'toaddress'   => ['name' => 'toaddress', 'label' => 'LBL_TO', 'datatype' => 'email'],
            'cc'          => ['name' => 'cc', 'label' => 'LBL_CC', 'datatype' => 'email'],
            'bcc'         => ['name' => 'bcc', 'label' => 'LBL_BCC', 'datatype' => 'email'],
            'subject'     => ['name' => 'subject', 'label' => 'LBL_SUBJECT', 'datatype' => 'picklist'],
            'body'        => ['name' => 'body', 'label' => 'LBL_BODY', 'datatype' => 'picklist'],
            'matchusing'  => ['name' => 'matchusing', 'label' => 'LBL_MATCH', 'datatype' => 'radio'],
            'action'      => ['name' => 'action', 'label' => 'LBL_ACTION', 'datatype' => 'picklist']
        ];
        $ruleFieldsList = [];
        foreach ($ruleFields as $fieldName => $fieldInfo) {
            $fieldModel = new Settings_MailConverter_RuleField_Model();
            foreach ($fieldInfo as $key => $value) {
                $fieldModel->set($key, $value);
            }
            $ruleFieldsList[$fieldName] = $fieldModel;
        }

        return $ruleFieldsList;
    }

    /**
     * Function to get Default url for this module
     * @return <String> Url
     */
    public function getDefaultUrl()
    {
        return 'index.php?module=' . $this->getName() . '&parent=' . $this->getParentName() . '&view=List';
    }

    public function isPagingSupported()
    {
        return false;
    }

    public static function MailBoxExists()
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery("SELECT COUNT(*) AS count FROM vtiger_mailscanner", []);
        $response = $db->query_result($result, 0, 'count');
        if ($response == 0) {
            return false;
        }

        return true;
    }

    public static function getDefaultId()
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery("SELECT MIN(scannerid) AS id FROM vtiger_mailscanner", []);
        $id = $db->query_result($result, 0, 'id');

        return $id;
    }

    public static function getMailboxes()
    {
        $mailBox = [];
        $db = PearDatabase::getInstance();
        $result = $db->pquery("SELECT scannerid, scannername FROM vtiger_mailscanner", []);
        $numOfRows = $db->num_rows($result);
        for ($i = 0; $i < $numOfRows; $i++) {
            $mailBox[$i]['scannerid'] = $db->query_result($result, $i, 'scannerid');
            $mailBox[$i]['scannername'] = $db->query_result($result, $i, 'scannername');
        }

        return $mailBox;
    }

    public static function getScannedFolders($id)
    {
        $folders = [];
        $db = PearDatabase::getInstance();
        $result = $db->pquery("SELECT foldername FROM vtiger_mailscanner_folders WHERE scannerid=? AND enabled=1", [$id]);
        $numOfRows = $db->num_rows($result);
        for ($i = 0; $i < $numOfRows; $i++) {
            $folders[$i] = $db->query_result($result, $i, 'foldername');
        }

        return $folders;
    }

    /**
     * @throws Exception
     */
    public static function getFolders($id): bool|array
    {
        $scannerName = Settings_MailConverter_Module_Model::getScannerName($id);
        $scannerInfo = Settings_MailConverter_MailScannerInfo_Handler::getInstance($scannerName);
        $mailBox = new Settings_MailConverter_MailBox_Handler($scannerInfo);

        $isConnected = $mailBox->connect();

        if ($isConnected) {
            $allFolders = $mailBox->getFolders();
            $folders = [];
            $selectedFolders = Settings_MailConverter_Module_Model::getScannedFolders($id);
            if (is_array($allFolders)) {
                foreach ($allFolders as $a) {
                    if (in_array($a, $selectedFolders)) {
                        $folders[$a] = 'checked';
                    } else {
                        $folders[$a] = '';
                    }
                }

                return $folders;
            } else {
                return $allFolders;
            }
        }

        return false;
    }

    public static function getScannerName($id)
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery("SELECT scannername FROM vtiger_mailscanner WHERE scannerid=?", [$id]);
        $scannerName = $db->query_result($result, 0, 'scannername');

        return $scannerName;
    }

    /**
     * @throws Exception
     */
    public static function updateFolders($scannerId, $folders): void
    {
        $db = PearDatabase::getInstance();
        $scannerName = Settings_MailConverter_Module_Model::getScannerName($scannerId);
        $scannerInfo = Settings_MailConverter_MailScannerInfo_Handler::getInstance($scannerName);
        $lastScan = $scannerInfo->dateBasedOnMailServerTimezone('d-M-Y');
        $db->pquery("DELETE FROM vtiger_mailscanner_folders WHERE scannerid=?", [$scannerId]);

        foreach ($folders as $folder) {
            $db->pquery("INSERT INTO vtiger_mailscanner_folders VALUES(?,?,?,?,?,?)", ['', $scannerId, $folder, $lastScan, '0', '1']);
        }
    }

    public function hasCreatePermissions()
    {
        $permissions = false;
        $recordsCount = Settings_MailConverter_Record_Model::getCount();

        global $max_mailboxes;
        if ($recordsCount < $max_mailboxes) {
            $permissions = true;
        }

        return $permissions;
    }
}