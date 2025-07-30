<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_ParentEntity_Model extends Vtiger_Base_Model
{
    /**
     * Update calculated / total fields in the parent record.
     * These are known as "computed" in InventoryItem module. If the parent module has a field with the same name, it will be updated.
     *
     * @param int $parentId
     *
     * @return void
     */
    public static function updateTotals(int $parentId)
    {
        if (!$parentId) {
            return;
        }

        $parentModuleName = getSalesEntityType($parentId);
        $parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);

        $moduleInstance = Vtiger_Module_Model::getInstance('InventoryItem');
        $fieldsInModule = Vtiger_Field_Model::getAllForModule($moduleInstance);
        $fields = [];

        foreach ($fieldsInModule as $fieldsInBlock) {
            foreach ($fieldsInBlock as $fieldModel) {
                if ($fieldModel->get('uitype') == 71) {
                    $fieldName = $fieldModel->get('name');
                    $controlField = Vtiger_Field::getInstance($fieldName, $parentModuleModel);

                    if ($controlField) {
                        $fields[] = $fieldName;
                    }
                }
            }
        }

        if (!empty($fields)) {
            $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $parentModuleModel);
            $changed = false;
            $db = PearDatabase::getInstance();
            $sql = 'SELECT ';

            foreach ($fields as $fieldName) {
                $sql .= ' SUM(' . $fieldName . ') AS ' . $fieldName . ', ';
            }

            $sql = trim($sql, ', ');

            $sql .= ' FROM df_inventoryitem 
                LEFT JOIN df_inventoryitemcf ON df_inventoryitemcf.inventoryitemid = df_inventoryitem.inventoryitemid
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = df_inventoryitem.inventoryitemid 
                WHERE parentid = ? AND vtiger_crmentity.deleted = 0';

            $result = $db->pquery($sql, [$parentId]);

            if ($db->num_rows($result)) {
                $row = $db->fetchByAssoc($result);

                foreach ($row as $fieldName => $value) {
                    if ((float)$parentRecordModel->get($fieldName) !== (float)$value) {
                        $parentRecordModel->set($fieldName, $value);
                        $changed = true;
                    }
                }

                $adjustment = (float)$parentRecordModel->get('adjustment');
                $priceTotal = (float)$parentRecordModel->get('price_total');
                $grandTotal = (float)$parentRecordModel->get('grand_total');

                if ($grandTotal !== $priceTotal + $adjustment) {
                    $parentRecordModel->set('grand_total', $priceTotal + $adjustment);
                    $changed = true;
                }

                if ($changed) {
                    $parentRecordModel->set('mode', 'edit');
                    $parentRecordModel->save();
                }
            }
        }
    }
}