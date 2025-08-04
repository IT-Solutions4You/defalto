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

class Settings_LoginHistory_Record_Model extends Settings_Vtiger_Record_Model
{
    /**
     * Function to get the Id
     * @return <Number> Profile Id
     */
    public function getId()
    {
        return $this->get('login_id');
    }

    /**
     * Function to get the Profile Name
     * @return <String>
     */
    public function getName()
    {
        return $this->get('user_name');
    }

    public function getAccessibleUsers()
    {
        $adb = PearDatabase::getInstance();
        $usersListArray = [];

        $query = 'SELECT user_name, first_name, last_name FROM vtiger_users';
        $result = $adb->pquery($query, []);
        while ($row = $adb->fetchByAssoc($result)) {
            $usersListArray[$row['user_name']] = getFullNameFromArray('Users', $row);
        }

        return $usersListArray;
    }

    /**
     * Function to retieve display value for a field
     *
     * @param <String> $fieldName - field name for which values need to get
     *
     * @return <String>
     */
    public function getDisplayValue($fieldName, $recordId = false)
    {
        $fieldValue = $this->get($fieldName);

        if ($fieldName == 'login_time') {
            if ($fieldValue != '0000-00-00 00:00:00') {
                $fieldValue = Vtiger_Datetime_UIType::getDateTimeValue($fieldValue);
            } else {
                $fieldValue = '---';
            }
        } elseif ($fieldName == 'logout_time') {
            if ($fieldValue != '0000-00-00 00:00:00' && $this->get('status') != 'Signed in') {
                $fieldValue = Vtiger_Datetime_UIType::getDateTimeValue($fieldValue);
            } else {
                $fieldValue = '---';
            }
        }

        return $fieldValue;
    }
}