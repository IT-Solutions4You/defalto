<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_ParentEntity_Model extends Vtiger_Base_Model
{
    /**
     * Update calculated / total fields in the record.
     * These are known as "computed" in InventoryItem module. If the parent module has a field with the same name, it will be updated.
     *
     * @param int $recordId
     *
     * @return void
     */
    public static function updateTotals(int $recordId)
    {
        $db = PearDatabase::getInstance();
        $changed = false;
        $moduleName = getSalesEntityType($recordId);

        if (!$moduleName) {
            return;
        }

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $recordModel->set('mode', 'edit');
        $focus = $recordModel->getEntity();
        $columnFields = $focus->column_fields;

        $sql = 'SELECT ';

        foreach (InventoryItem_Field_Model::totalFields as $fieldName) {
            if (isset($columnFields[$fieldName])) {
                $sql .= 'SUM(' . $fieldName . ') AS ' . $fieldName . ', ';
            }
        }

        $sql .= ' COUNT(inventoryitemid) AS items_count FROM df_inventoryitem 
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = df_inventoryitem.inventoryitemid
                WHERE vtiger_crmentity.deleted = 0
                AND df_inventoryitem.parentid = ?';
        $res = $db->pquery($sql, [$recordId]);

        if ($db->num_rows($res))
        {
            $row = $db->fetchByAssoc($res);

            foreach (InventoryItem_Field_Model::totalFields as $fieldName) {
                if (isset($row[$fieldName]) && (float)$row[$fieldName] != (float)$recordModel->get($fieldName)) {
                    $recordModel->set($fieldName, $row[$fieldName]);
                    $changed = true;
                }
            }
        }

        if (!$changed) {
            $recordModel->save();
        }
    }
}