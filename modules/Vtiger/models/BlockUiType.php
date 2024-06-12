<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Vtiger_BlockUiType_Model extends Vtiger_Base_Model
{
    /**
     * Retrieves the name associated with the given UI type from the database.
     *
     * @param int $uiType The UI type to retrieve the name for. Defaults to 1.
     *
     * @return string The name associated with the given UI type.
     */
    public static function getNameForUIType(int $uiType = 1): string
    {
        $db = PearDatabase::getInstance();
        $query = 'SELECT name FROM vtiger_blockuitype WHERE blockuitype = ?';
        $result = $db->pquery($query, [$uiType]);

        if (!$db->num_rows($result)) {
            return 'Base';
        }

        return ucfirst($db->query_result($result, 0, 'name'));
    }
}