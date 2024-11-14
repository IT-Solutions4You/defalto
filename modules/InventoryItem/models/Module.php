<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class InventoryItem_Module_Model extends Vtiger_Module_Model
{
    /**
     * @param int $moduleId
     *
     * @return array
     */
    public static function getSelectedFields(int $moduleId, bool $addDefaultFields = true): array
    {
        $return = [];
        $db = PearDatabase::getInstance();

        $query = 'SELECT columnslist FROM its4you_inventoryitemcolumns WHERE tabid = ?';
        $result = $db->pquery($query, [$moduleId]);

        while ($row = $db->fetchByAssoc($result)) {
            $return = explode(',', $row['columnslist']);
        }

        if (empty($return) && $addDefaultFields) {
            $return = self::getDefaultSelectedFields();
        }

        return $return;
    }

    /**
     * @return array
     */
    public static function getDefaultSelectedFields(): array
    {
        return self::getSelectedFields(0);
    }
}