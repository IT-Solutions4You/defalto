<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_Module_Model extends Vtiger_Module_Model
{
    public function getFontIcon()
    {
        return 'fa fa-cubes';
    }

    public const SETTING_SIMPLIFIED_BLOCK = 'simplified_block';

    /**
     * Simplified product block: items are added and edited directly in the edit view of the document with only
     * the item name, quantity, unit, price and total. The standard block edits items in the detail view.
     *
     * @return bool
     */
    public static function isSimplifiedBlock(): bool
    {
        return '1' === self::getSetting(self::SETTING_SIMPLIFIED_BLOCK);
    }

    /**
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    public static function getSetting(string $name, string $default = ''): string
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT value FROM df_inventoryitem_settings WHERE name = ?', [$name]);

        if ($db->num_rows($result)) {
            return (string)$db->query_result($result, 0, 'value');
        }

        return $default;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    public static function saveSetting(string $name, string $value): void
    {
        $db = PearDatabase::getInstance();
        $db->pquery(
            'INSERT INTO df_inventoryitem_settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)',
            [$name, $value]
        );
    }

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
     * @param int  $crmId       The CRM ID for which the inventory items should be fetched.
     * @param bool $includeText decides whether to include text lines or not; default false
     *
     * @return array An array of inventory items corresponding to the provided CRM ID.
     */
    public static function fetchItemsForId(int $crmId, bool $includeText = false): array
    {
        $db = PearDatabase::getInstance();
        $items = [];
        $sql = 'SELECT df_inventoryitem.*, df_inventoryitemcf.*, df_taxes_records.tax_id 
                FROM df_inventoryitem
                    LEFT JOIN df_inventoryitemcf ON df_inventoryitemcf.inventoryitemid = df_inventoryitem.inventoryitemid
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = df_inventoryitem.inventoryitemid AND vtiger_crmentity.deleted = 0
                    LEFT JOIN df_taxes_records ON df_taxes_records.record_id = df_inventoryitem.inventoryitemid
                WHERE parentid = ? ';

        if (!$includeText) {
            $sql .= ' AND productid IS NOT NULL
                    AND productid <> 0 ';
        }

        $sql .= ' ORDER BY sequence';
        $res = $db->pquery($sql, [$crmId]);

        while ($row = $db->fetchByAssoc($res)) {
            $items[] = $row;
        }

        return $items;
    }

    /**
     * @param array $itemIds
     *
     * @return array parent record id by item id; ids that are not items are missing
     */
    public static function fetchParentIds(array $itemIds): array
    {
        $itemIds = array_values(array_unique(array_filter(array_map('intval', $itemIds))));

        if (empty($itemIds)) {
            return [];
        }

        $db = PearDatabase::getInstance();
        $result = $db->pquery(
            'SELECT inventoryitemid, parentid FROM df_inventoryitem WHERE inventoryitemid IN (' . generateQuestionMarks($itemIds) . ')',
            $itemIds
        );
        $parentIds = [];

        while ($row = $db->fetchByAssoc($result)) {
            $parentIds[(int)$row['inventoryitemid']] = (int)$row['parentid'];
        }

        return $parentIds;
    }
}
