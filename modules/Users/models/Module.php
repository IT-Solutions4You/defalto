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

class Users_Module_Model extends Vtiger_Module_Model
{
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
        if ($sourceModule == 'Users' && $field == 'reports_to_id') {
            $overRideQuery = $listQuery;
            if (!empty($record)) {
                $db = PearDatabase::getInstance();
                $condition = $db->convert2Sql(' AND vtiger_users.id != ? ', [$record]);
                $currentUser = Users_Record_Model::getCurrentUserModel();
                $overRideQuery = $overRideQuery . $condition;
                $allSubordinates = $currentUser->getAllSubordinatesByReportsToField($record);
                if (php7_count($allSubordinates) > 0) {
                    $overRideQuery .= " AND vtiger_users.id NOT IN (" . implode(',', $allSubordinates) . ")"; // do not allow the subordinates
                }
            }

            return $overRideQuery;
        }
    }

    /**
     * Function searches the records in the module, if parentId & parentModule
     * is given then searches only those records related to them.
     *
     * @param <String>  $searchValue  - Search value
     * @param <Integer> $parentId     - parent recordId
     * @param <String>  $parentModule - parent module name
     *
     * @return <Array of Users_Record_Model>
     */
    public function searchRecord($searchValue, $parentId = false, $parentModule = false, $relatedModule = false)
    {
        if (empty($searchValue)) {
            return [];
        }

        $db = PearDatabase::getInstance();
        $query = 'SELECT * FROM vtiger_users WHERE userlabel LIKE ? AND status = ?';
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $params = ["%$searchValue%", 'Active'];

        if (!$currentUser->isAdminUser()) {
            $query .= ' AND vtiger_users.id IN (' . implode(',', array_keys($currentUser->getAccessibleUsers())) . ')';
        }

        $result = $db->pquery($query, $params);
        $matchingRecords = [];

        while ($row = $db->fetchByAssoc($result)) {
            $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', 'Users');
            $recordInstance = new $modelClassName();
            $matchingRecords['Users'][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($this);
        }

        return $matchingRecords;
    }

    /**
     * Function returns the default column for Alphabetic search
     * @return <String> columnname
     */
    public function getAlphabetSearchField()
    {
        return 'last_name';
    }

    /**
     * Function to get the url for the Create Record view of the module
     * @return <String> - url
     */
    public function getCreateRecordUrl()
    {
        return 'index.php?module=' . $this->get('name') . '&parent=Settings&view=' . $this->getEditViewName();
    }

    public function checkDuplicateUser($userName)
    {
        $status = false;
        // To check username existence in db
        $db = PearDatabase::getInstance();
        $query = 'SELECT user_name FROM vtiger_users WHERE user_name = ?';
        $result = $db->pquery($query, [$userName]);
        if ($db->num_rows($result) > 0) {
            $status = true;
        }

        return $status;
    }

    /**
     * Function to delete a given record model of the current module
     *
     * @param Vtiger_Record_Model $recordModel
     */
    public function deleteRecord(Vtiger_Record_Model $recordModel)
    {
        $db = PearDatabase::getInstance();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $deleteUserId = $recordModel->getId();
        if ($deleteUserId != 1) {
            $query = "UPDATE vtiger_users SET status=?, date_modified=?, modified_user_id=? WHERE id=?";
            $db->pquery($query, ['Inactive', date('Y-m-d H:i:s'), $currentUser->getId(), $deleteUserId], true, "Error marking record deleted: ");
        }
    }

    /**
     * Function to get the url for list view of the module
     * @return <string> - url
     */
    public function getListViewUrl()
    {
        return 'index.php?module=' . $this->get('name') . '&parent=Settings&view=' . $this->getListViewName();
    }

    /**
     * Function to update Base Currency of Product
     * @param- $_REQUEST array
     */
    public function updateBaseCurrency($currencyName)
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT currency_code, currency_symbol, currency_name FROM vtiger_currencies WHERE currency_name = ?', [$currencyName]);
        $num_rows = $db->num_rows($result);

        if ($num_rows) {
            $currency_code = decode_html($db->query_result($result, 0, 'currency_code'));
            $currency_symbol = decode_html($db->query_result($result, 0, 'currency_symbol'));
            $currencyName = decode_html($db->query_result($result, 0, 'currency_name'));
        } else {
            return;
        }

        $this->updateConfigFile($currencyName);
        //Updating Database
        $query = 'UPDATE vtiger_currency_info SET currency_name = ?, currency_code = ?, currency_symbol = ? WHERE id = ?';
        $params = [$currencyName, $currency_code, $currency_symbol, '1'];
        $db->pquery($query, $params);
    }

    /**
     * Function to update Config file
     * @param- $_REQUEST array
     */
    public function updateConfigFile($currencyName)
    {
        $currencyName = '$currency_name = \'' . $currencyName . '\'';

        //Updating in config inc file
        $filename = 'config.inc.php';
        if (file_exists($filename)) {
            $contents = file_get_contents($filename);
            $currentBaseCurrenyName = $this->getBaseCurrencyName();
            $contents = str_replace('$currency_name = \'' . $currentBaseCurrenyName . '\'', $currencyName, $contents);
            file_put_contents($filename, $contents);
        }
    }

    public function getBaseCurrencyName()
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery("SELECT currency_name FROM vtiger_currency_info WHERE id=1", []);

        return $db->query_result($result, 0, 'currency_name');
    }

    /**
     * Function to get user setup status
     * @return-is First User or not
     */
    public static function insertEntryIntoCRMSetup($userId)
    {
        $db = PearDatabase::getInstance();

        //updating user setup status into database
        $insertQuery = 'INSERT INTO vtiger_crmsetup (userid, setup_status) VALUES (?, ?)';
        $db->pquery($insertQuery, [$userId, '1']);
    }

    /**
     * Function to store the login history
     *
     * @param type $username
     */
    public function saveLoginHistory($username)
    {
        $adb = PearDatabase::getInstance();

        $userIPAddress = $_SERVER['REMOTE_ADDR'];
        $loginTime = date("Y-m-d H:i:s");
        $query = "INSERT INTO vtiger_loginhistory (user_name, user_ip, logout_time, login_time, status) VALUES (?,?,?,?,?)";
        $params = [$username, $userIPAddress, $loginTime, $loginTime, 'Signed in'];
        //Mysql 5.7 doesn't support invalid date in Timestamp field
        //$params = array($username, $userIPAddress, '0000-00-00 00:00:00',  $loginTime, 'Signed in');
        $adb->pquery($query, $params);
    }

    /**
     * @param string $username
     *
     * @return bool
     * @throws Exception
     */
    public function isFirstLoginHistory(string $username): bool
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT count(login_id) as counts FROM vtiger_loginhistory WHERE user_name=?', [$username]);

        return 1 === (int)$adb->query_result($result, 0, 'counts');
    }

    /**
     * Function to store the logout history
     *
     * @param type $username
     */
    public function saveLogoutHistory()
    {
        $adb = PearDatabase::getInstance();

        $userRecordModel = Users_Record_Model::getCurrentUserModel();
        $userIPAddress = $_SERVER['REMOTE_ADDR'];
        $outtime = date("Y-m-d H:i:s");

        $loginIdQuery = "SELECT MAX(login_id) AS login_id FROM vtiger_loginhistory WHERE user_name=? AND user_ip=?";
        $result = $adb->pquery($loginIdQuery, [$userRecordModel->get('user_name'), $userIPAddress]);
        $loginid = $adb->query_result($result, 0, "login_id");

        if (!empty($loginid)) {
            $query = "UPDATE vtiger_loginhistory SET logout_time =?, status=? WHERE login_id = ?";
            $result = $adb->pquery($query, [$outtime, 'Signed off', $loginid]);
        }
    }

    /**
     * Function to save packages info
     *
     * @param <type> $packagesList
     */
    public static function savePackagesInfo($packagesList)
    {
        $adb = PearDatabase::getInstance();
        $packagesListFromDB = Users_CRMSetup::getPackagesList();
        $disabledModulesList = [];

        foreach ($packagesListFromDB as $packageName => $packageInfo) {
            if (!$packagesList[$packageName]) {
                $disabledModulesList = array_merge($disabledModulesList, array_keys($packageInfo['modules']));
            }
        }

        if ($disabledModulesList) {
            $updateQuery = 'UPDATE vtiger_tab SET presence = CASE WHEN name IN (' . generateQuestionMarks($disabledModulesList) . ') THEN 1 ';
            $updateQuery .= 'ELSE 0 END WHERE presence != 2 ';
        } else {
            $updateQuery = 'UPDATE vtiger_tab SET presence = 0 WHERE presence != 2';
        }

        $adb->pquery($updateQuery, $disabledModulesList);
    }

    /**
     * Function to save a given record model of the current module
     *
     * @param Vtiger_Record_Model $recordModel
     */
    public function saveRecord(Vtiger_Record_Model $recordModel)
    {
        $moduleName = $this->get('name');
        $focus = CRMEntity::getInstance($moduleName);
        $fields = $focus->column_fields;
        foreach ($fields as $fieldName => $fieldValue) {
            $fieldValue = $recordModel->get($fieldName);
            if (is_array($fieldValue)) {
                $focus->column_fields[$fieldName] = $fieldValue;
            } elseif ($fieldValue !== null) {
                $focus->column_fields[$fieldName] = decode_html($fieldValue);
            }
        }

        $focus->mode = $recordModel->get('mode');
        $focus->id = $recordModel->getId();
        $focus->save($moduleName);

        return $recordModel->setId($focus->id);
    }

    /**
     * @return an array with the list of currencies which are available in source
     */
    public function getCurrenciesList()
    {
        $adb = PearDatabase::getInstance();

        $currency_query = 'SELECT currency_name, currency_code, currency_symbol FROM vtiger_currencies ORDER BY currency_name';
        $result = $adb->pquery($currency_query, []);
        $num_rows = $adb->num_rows($result);
        for ($i = 0; $i < $num_rows; $i++) {
            $currencyname = decode_html($adb->query_result($result, $i, 'currency_name'));
            $currencycode = decode_html($adb->query_result($result, $i, 'currency_code'));
            $currencysymbol = decode_html($adb->query_result($result, $i, 'currency_symbol'));
            $currencies[$currencyname] = [$currencycode, $currencysymbol];
        }

        return $currencies;
    }

    /**
     * @return an array with the list of time zones which are availables in source
     */
    public function getTimeZonesList()
    {
        $adb = PearDatabase::getInstance();

        $timezone_query = 'SELECT time_zone FROM vtiger_time_zone';
        $result = $adb->pquery($timezone_query, []);
        $num_rows = $adb->num_rows($result);
        for ($i = 0; $i < $num_rows; $i++) {
            $time_zone = decode_html($adb->query_result($result, $i, 'time_zone'));
            $time_zones_list[$time_zone] = $time_zone;
        }

        return $time_zones_list;
    }

    /**
     * @return an array with the list of languages which are available in source
     */
    public function getLanguagesList()
    {
        $adb = PearDatabase::getInstance();

        $language_query = 'SELECT prefix, label FROM vtiger_language';
        $result = $adb->pquery($language_query, []);
        $num_rows = $adb->num_rows($result);
        for ($i = 0; $i < $num_rows; $i++) {
            $lang_prefix = decode_html($adb->query_result($result, $i, 'prefix'));
            $label = decode_html($adb->query_result($result, $i, 'label'));
            $languages_list[$lang_prefix] = $label;
        }
        asort($languages_list);

        return $languages_list;
    }

    /*
     * Function to get change owner url for Users
     */
    public function getChangeOwnerUrl()
    {
        return 'javascript:Settings_Users_List_Js.showTransferOwnershipForm()';
    }

    /**
     * Function to get active block name of module
     * @return type
     */
    public function getSettingsActiveBlock($viewName)
    {
        $blocksList = [
            'Edit'           => ['block' => 'LBL_USER_MANAGEMENT', 'menu' => 'LBL_USERS'],
            'Calendar'       => ['block' => 'LBL_MY_PREFERENCES', 'menu' => 'Calendar Settings'],
            'PreferenceEdit' => ['block' => 'LBL_MY_PREFERENCES', 'menu' => 'My Preferences']
        ];

        return $blocksList[$viewName];
    }

    /**
     * Function to get Module Header Links (for Vtiger7)
     * @return array
     */
    public function getModuleBasicLinks()
    {
        $basicLinks = [];
        $moduleName = $this->getName();

        $currentUser = Users_Record_Model::getCurrentUserModel();
        if ($currentUser->isAdminUser() && Users_Privileges_Model::isPermitted($moduleName, 'CreateView')) {
            $basicLinks[] = [
                'linktype'    => 'BASIC',
                'linklabel'   => 'LBL_ADD_RECORD',
                'linkurl'     => $this->getCreateRecordUrl(),
                'linkicon'    => 'fa-plus',
                'style_class' => Vtiger_Link_Model::PRIMARY_STYLE_CLASS,
            ];

            if (Users_Privileges_Model::isPermitted($moduleName, 'Import')) {
                $basicLinks[] = [
                    'linktype'  => 'BASIC',
                    'linklabel' => 'LBL_IMPORT',
                    'linkurl'   => $this->getImportUrl(),
                    'linkicon'  => 'fa-download'
                ];
            }
        }

        return $basicLinks;
    }

    /**
     * Function to get Settings links
     * @return <Array>
     */
    public function getSettingLinks()
    {
        $settingsLinks = [];
        $moduleName = $this->getName();

        $currentUser = Users_Record_Model::getCurrentUserModel();
        if ($currentUser->isAdminUser() && Users_Privileges_Model::isPermitted($moduleName, 'DetailView')) {
            $settingsLinks[] = [
                'linktype'  => 'LISTVIEW',
                'linklabel' => 'LBL_EXPORT',
                'linkurl'   => 'index.php?module=Users&source_module=Users&action=ExportData',
                'linkicon'  => ''
            ];
        }

        return $settingsLinks;
    }

    public function getImportableFieldModels()
    {
        $focus = CRMEntity::getInstance($this->getName());
        $importableFields = $focus->getImportableFields();

        $importableFieldModels = [];
        foreach ($importableFields as $fieldName => $fieldInstance) {
            $importableFieldModels[$fieldName] = $this->getField($fieldName);
        }

        return $importableFieldModels;
    }
}