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

class Settings_Vtiger_Announcement_Model extends Vtiger_Base_Model
{
    const tableName = 'vtiger_announcement';

    public function save()
    {
        $db = PearDatabase::getInstance();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $currentDate = date('Y-m-d H:i:s');
        $checkQuery = 'SELECT 1 FROM ' . self::tableName . ' WHERE creatorid=?';
        $result = $db->pquery($checkQuery, [$currentUser->getId()]);
        if ($db->num_rows($result) > 0) {
            $query = 'UPDATE ' . self::tableName . ' SET announcement=?,time=? WHERE creatorid=?';
            $params = [$this->get('announcement'), $db->formatDate($currentDate, true), $currentUser->getId()];
        } else {
            $query = 'INSERT INTO ' . self::tableName . ' VALUES(?,?,?,?)';
            $params = [$currentUser->getId(), $this->get('announcement'), 'announcement', $db->formatDate($currentDate, true)];
        }
        $db->pquery($query, $params);
    }

    public static function getInstanceByCreator(Users_Record_Model $user)
    {
        $db = PearDatabase::getInstance();
        $query = 'SELECT * FROM ' . self::tableName . ' WHERE creatorid=?';
        $result = $db->pquery($query, [$user->getId()]);
        $instance = new self();
        if ($db->num_rows($result) > 0) {
            $row = $db->query_result_rowdata($result, 0);
            $instance->setData($row);
        }

        return $instance;
    }
}