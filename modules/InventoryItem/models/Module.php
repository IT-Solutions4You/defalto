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

    /**
     * Fetches inventory items associated with a specific CRM ID.
     *
     * @param int $crmId The CRM ID for which the inventory items should be fetched.
     *
     * @return array An array of inventory items corresponding to the provided CRM ID.
     */
    public static function fetchItemsForId(int $crmId): array
    {
        $db = PearDatabase::getInstance();
        $items = [];
        $sql = 'SELECT df_inventoryitem.*, df_inventoryitemcf.* 
                FROM df_inventoryitem
                    LEFT JOIN df_inventoryitemcf ON df_inventoryitemcf.inventoryitemid = df_inventoryitem.inventoryitemid
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = df_inventoryitem.inventoryitemid AND vtiger_crmentity.deleted = 0
                WHERE parentid = ?   
                    AND productid IS NOT NULL
                    AND productid <> 0';
        $res = $db->pquery($sql, [$crmId]);

        while ($row = $db->fetchByAssoc($res)) {
            $items[] = $row;
        }

        return $items;
    }
}