<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

class Google_Utils_Helper
{
    const settings_table_name = 'vtiger_google_sync_settings';

    const fieldmapping_table_name = 'vtiger_google_sync_fieldmapping';

    /**
     * Updates the database with syncronization times
     *
     * @param <sting> $sourceModule module to which sync time should be stored
     * @param <date>  $modifiedTime Max modified time of record that are sync
     */
    public static function updateSyncTime($sourceModule, $modifiedTime = false, $user = false)
    {
        $db = PearDatabase::getInstance();

        if (!$user) {
            $user = Users_Record_Model::getCurrentUserModel();
        }
        if (!$modifiedTime) {
            $modifiedTime = self::getSyncTime($sourceModule, $user);
        }
        if (!self::getSyncTime($sourceModule, $user)) {
            if ($modifiedTime) {
                $db->pquery(
                    'INSERT INTO vtiger_google_sync (googlemodule,user,synctime,lastsynctime) VALUES (?,?,?,?)',
                    [$sourceModule, $user->id, $modifiedTime, date('Y-m-d H:i:s')]
                );
            }
        } else {
            $db->pquery(
                'UPDATE vtiger_google_sync SET synctime = ?,lastsynctime = ? WHERE user=? AND googlemodule=?',
                [$modifiedTime, date('Y-m-d H:i:s'), $user->id, $sourceModule]
            );
        }
    }

    /**
     *  Gets the max Modified time of last sync records
     *
     * @param <sting> $sourceModule modulename to which sync time should return
     *
     * @return <date> max Modified time of last sync records OR <boolean> false when date not present
     */
    public static function getSyncTime($sourceModule, $user = false)
    {
        $db = PearDatabase::getInstance();
        if (!$user) {
            $user = Users_Record_Model::getCurrentUserModel();
        }
        $result = $db->pquery('SELECT synctime FROM vtiger_google_sync WHERE user=? AND googlemodule=?', [$user->id, $sourceModule]);
        if ($result && $db->num_rows($result) > 0) {
            $row = $db->fetch_array($result);

            return $row['synctime'];
        } else {
            return false;
        }
    }

    /**
     *  Gets the last syncronazation time
     *
     * @param <sting> $sourceModule modulename to which sync time should return
     *
     * @return <date> last syncronazation time OR <boolean> false when date not present
     */
    public static function getLastSyncTime($sourceModule)
    {
        $db = PearDatabase::getInstance();
        $user = Users_Record_Model::getCurrentUserModel();
        $result = $db->pquery('SELECT lastsynctime FROM vtiger_google_sync WHERE user=? AND googlemodule=?', [$user->id, $sourceModule]);

        if ($result && $db->num_rows($result) > 0) {
            $row = $db->fetch_array($result);

            return $row['lastsynctime'];
        } else {
            return false;
        }
    }

    /**
     *  Get the callback url for a module
     *
     * @param <object> $request
     * @param <array>  $options any extra parameter add to url
     *
     * @return string callback url
     * @global type    $site_URL
     */
    static function getCallbackUrl($request, $options = [])
    {
        global $site_URL;

        $callback = rtrim($site_URL, '/') . "/index.php?module=" . $request['module'] . "&view=List&sourcemodule=" . $request['sourcemodule'];
        foreach ($options as $key => $value) {
            $callback .= "&" . $key . "=" . $value;
        }

        return $callback;
    }

    /**
     * To get users currently in sync with Google
     * @return type
     * @global type $adb
     */
    public static function getSyncUsers()
    {
        global $adb;
        $users = [];
        $result = $adb->pquery("SELECT DISTINCT userid FROM vtiger_google_oauth2", []);

        if ($result && $adb->num_rows($result)) {
            while ($resultrow = $adb->fetch_array($result)) {
                $users[] = $resultrow['id'];
            }
        }

        return $users;
    }

    static function hasSettingsForUser($userId, $source_module)
    {
        $db = PearDatabase::getInstance();
        $sql = 'SELECT 1 FROM ' . self::settings_table_name . ' WHERE user = ? AND module = ?';
        $result = $db->pquery($sql, [$userId, $source_module]);
        if ($db->num_rows($result) > 0) {
            return true;
        }

        return false;
    }

    static function saveSettings($request)
    {
        $db = PearDatabase::getInstance();
        $user = Users_Record_Model::getCurrentUserModel();
        $userId = $user->getId();
        $source_module = $request->get('sourcemodule');
        $google_group = $request->get('google_group');
        $sync_direction = $request->get('sync_direction');
        if (Google_Utils_Helper::hasSettingsForUser($userId, $source_module)) {
            $sql = 'UPDATE ' . self::settings_table_name . ' SET clientgroup = ?, direction = ? WHERE user = ? AND module = ?';
            $params = [$google_group, $sync_direction, $userId, $source_module];
            $db->pquery($sql, $params);
        } else {
            $sql = 'INSERT INTO ' . self::settings_table_name . '(user,module,clientgroup,direction) VALUES (?,?,?,?)';
            $params = [$userId, $source_module, $google_group, $sync_direction];
            $db->pquery($sql, $params);
        }
    }

    static function saveFieldMappings($sourceModule, $fieldMappings)
    {
        $db = PearDatabase::getInstance();
        $user = Users_Record_Model::getCurrentUserModel();
        $sql = 'SELECT 1 FROM ' . self::fieldmapping_table_name . ' WHERE user = ?';
        $res = $db->pquery($sql, [$user->getId()]);
        $sqlParams = [];
        if ($db->num_rows($res)) {
            $sql = 'DELETE FROM ' . self::fieldmapping_table_name . ' WHERE user = ?';
            $db->pquery($sql, [$user->getId()]);
        }
        $sql = 'INSERT INTO ' . self::fieldmapping_table_name . ' (vtiger_field,google_field,google_field_type,google_custom_label,user) VALUES ';
        foreach ($fieldMappings as $fieldMap) {
            $fieldMap['user'] = $user->getId();
            $values = '(' . generateQuestionMarks($fieldMap) . '), ';
            $params = [];
            foreach ($fieldMap as $field) {
                $params[] = $field;
            }
            $sqlParams = array_merge($sqlParams, $params);
            $sql .= $values;
        }
        $sql = rtrim($sql, ', ');
        $db->pquery($sql, $sqlParams);
    }

    static function getSelectedContactGroupForUser($user = false)
    {
        if (!$user) {
            $user = Users_Record_Model::getCurrentUserModel();
        }
        $userId = $user->getId();
        if (!Google_Utils_Helper::hasSettingsForUser($userId, 'Contacts')) {
            return ''; // defaults to all - other contacts groups
        } else {
            $db = PearDatabase::getInstance();
            $sql = 'SELECT clientgroup FROM ' . self::settings_table_name . ' WHERE user = ? AND module = ?';
            $result = $db->pquery($sql, [$userId, 'Contacts']);

            return $db->query_result($result, 0, 'clientgroup');
        }
    }

    static function getSyncDirectionForUser($user = false, $module = 'Contacts')
    {
        if (!$user) {
            $user = Users_Record_Model::getCurrentUserModel();
        }
        if (!Google_Utils_Helper::hasSettingsForUser($user->getId(), $module)) {
            return '11'; // defaults to bi-directional sync
        } else {
            $db = PearDatabase::getInstance();
            $sql = 'SELECT direction FROM ' . self::settings_table_name . ' WHERE user = ? AND module = ?';
            $result = $db->pquery($sql, [$user->getId(), $module]);

            return $db->query_result($result, 0, 'direction');
        }
    }

    static function getFieldMappingForUser($user = false)
    {
        if (!$user) {
            $user = Users_Record_Model::getCurrentUserModel();
        }
        $db = PearDatabase::getInstance();
        $fieldmapping = [
            'salutationtype' => [
                'google_field_name'   => 'gd:namePrefix',
                'google_field_type'   => '',
                'google_custom_label' => ''
            ],
            'firstname'      => [
                'google_field_name'   => 'gd:givenName',
                'google_field_type'   => '',
                'google_custom_label' => ''
            ],
            'lastname'       => [
                'google_field_name'   => 'gd:familyName',
                'google_field_type'   => '',
                'google_custom_label' => ''
            ],
            'title'          => [
                'google_field_name'   => 'gd:orgTitle',
                'google_field_type'   => '',
                'google_custom_label' => ''
            ],
            'account_id'     => [
                'google_field_name'   => 'gd:orgName',
                'google_field_type'   => '',
                'google_custom_label' => ''
            ],
            'birthday'       => [
                'google_field_name'   => 'gContact:birthday',
                'google_field_type'   => '',
                'google_custom_label' => ''
            ],
            'email'          => [
                'google_field_name'   => 'gd:email',
                'google_field_type'   => 'home',
                'google_custom_label' => ''
            ],
            'secondaryemail' => [
                'google_field_name'   => 'gd:email',
                'google_field_type'   => 'work',
                'google_custom_label' => ''
            ],
            'mobile'         => [
                'google_field_name'   => 'gd:phoneNumber',
                'google_field_type'   => 'mobile',
                'google_custom_label' => ''
            ],
            'phone'          => [
                'google_field_name'   => 'gd:phoneNumber',
                'google_field_type'   => 'work',
                'google_custom_label' => ''
            ],
            'mailingaddress' => [
                'google_field_name'   => 'gd:structuredPostalAddress',
                'google_field_type'   => 'home',
                'google_custom_label' => ''
            ],
            'otheraddress'   => [
                'google_field_name'   => 'gd:structuredPostalAddress',
                'google_field_type'   => 'work',
                'google_custom_label' => ''
            ],
            'description'    => [
                'google_field_name'   => 'content',
                'google_field_type'   => '',
                'google_custom_label' => ''
            ]
        ];
        $sql = 'SELECT vtiger_field,google_field,google_field_type,google_custom_label FROM ' . self::fieldmapping_table_name . ' WHERE user = ?';
        $result = $db->pquery($sql, [$user->getId()]);
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $row = $db->fetch_row($result);

            if (in_array($row['google_field'], ['gd:website', 'content'])) {
                continue;
            }

            $fieldmapping[$row['vtiger_field']] = [
                'google_field_name'   => $row['google_field'],
                'google_field_type'   => $row['google_field_type'],
                'google_custom_label' => $row['google_custom_label']
            ];
        }

        return $fieldmapping;
    }

    public static function getSelectedCalendarForUser($user = false)
    {
        if (!$user) {
            $user = Users_Record_Model::getCurrentUserModel();
        }
        $userId = $user->getId();
        if (!Google_Utils_Helper::hasSettingsForUser($userId, 'Calendar')) {
            return 'primary'; // defaults to primary calendar
        } else {
            $db = PearDatabase::getInstance();
            $sql = 'SELECT clientgroup FROM ' . self::settings_table_name . ' WHERE user = ? AND module = ?';
            $result = $db->pquery($sql, [$userId, 'Calendar']);

            return $db->query_result($result, 0, 'clientgroup');
        }
    }

    public static function errorLog()
    {
        $i = 0;
        $debug = debug_backtrace();
        array_shift($debug);
        foreach ($debug as $value) {
            $error .= "\t#" . $i++ . '  File : ' . $value['file'] . ' || Line : ' . $value['line'] . ' || Class : ' . $value['class'] . ' || Function : ' . $value['function'] . "\n";
        }
        $fp = fopen('logs/googleErrorLog.txt', 'a+');
        fwrite($fp, "Debug traced ON " . date('Y-m-d H:i:s') . "\n\n");
        fwrite($fp, $error);
        fwrite($fp, "\n\n");
        fclose($fp);
    }

    static function toGoogleXml($string)
    {
        $string = str_replace('&', '&amp;amp;', $string);
        $string = str_replace('<', '&amp;lt;', $string);
        $string = str_replace('>', '&amp;gt;', $string);

        return $string;
    }

    static function saveSyncSettings($request)
    {
        $db = PearDatabase::getInstance();
        $user = Users_Record_Model::getCurrentUserModel();
        $userId = $user->getId();
        $source_module = $request->get('sourcemodule');
        $google_group = $request->get('google_group');
        $sync_direction = $request->get('sync_direction');
        if ($request->get('enabled') == 'on' || $request->get('enabled') == 1) {
            $enabled = 1;
        } else {
            $enabled = 0;
        }
        if (Google_Utils_Helper::hasSettingsForUser($userId, $source_module)) {
            $sql = 'UPDATE ' . self::settings_table_name . ' SET clientgroup = ?, direction = ?, enabled = ? WHERE user = ? AND module = ?';
            $params = [$google_group, $sync_direction, $enabled, $userId, $source_module];
            $db->pquery($sql, $params);
        } else {
            $sql = 'INSERT INTO ' . self::settings_table_name . ' VALUES (?,?,?,?,?)';
            $params = [$userId, $source_module, $google_group, $sync_direction, $enabled];
            $db->pquery($sql, $params);
        }
    }

    /**
     * Function to check if the sync is enabled for a module and for user given
     *
     * @param <string >            $module
     * @param <Users_Record_Model> $user
     *
     * @return <boolean> true/false
     */
    static function checkSyncEnabled($module, $user = false)
    {
        if (!$user) {
            $user = Users_Record_Model::getCurrentUserModel();
        }
        $userId = $user->getId();
        if (!Google_Utils_Helper::hasSettingsForUser($userId, $module)) {
            return true; // defaults to enabled
        } else {
            $db = PearDatabase::getInstance();
            $sql = 'SELECT enabled FROM ' . self::settings_table_name . ' WHERE user = ? AND module = ?';
            $result = $db->pquery($sql, [$userId, $module]);
            $enabled = $db->query_result($result, 0, 'enabled');
        }

        if ($enabled == 1) {
            return true;
        }

        return false;
    }
}