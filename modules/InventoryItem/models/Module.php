<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

        $query = 'SELECT columnslist FROM df_inventoryitemcolumns WHERE tabid = ?';
        $result = $db->pquery($query, [$moduleId]);

        while ($row = $db->fetchByAssoc($result)) {
            if (!empty($row['columnslist'])) {
                $return = explode(',', $row['columnslist']);
            }
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