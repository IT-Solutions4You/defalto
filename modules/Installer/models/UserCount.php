<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_UserCount_Model extends Core_DatabaseData_Model
{
    protected string $table = 'vtiger_users';
    protected string $tableId = 'id';

    public static function getLicensedUserCount(): int
    {
        $model = (new self())->retrieveDB();
        $db = $model->getDB();
        $result = $db->pquery(
            'SELECT COUNT(*) AS user_count FROM vtiger_users WHERE status=? AND deleted=?',
            ['Active', 0]
        );
        $userCount = $db->query_result($result, 0, 'user_count');

        return max(0, (int)$userCount);
    }
}
