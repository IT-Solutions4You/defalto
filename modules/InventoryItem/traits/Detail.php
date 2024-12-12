<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

trait InventoryItem_Detail_Trait
{
    protected $excludedFields = ['assigned_user_id', 'description', 'item_text', 'parentid', 'parentitemid', 'sequence'];
    protected $computedFields = ['subtotal', 'total_after_discount', 'overall_discount_amount', 'total_after_overall_discount', 'tax_amount', 'total'];

    public function adaptDetail(Vtiger_Request $request, Vtiger_Viewer $viewer)
    {
        $viewer->assign('ITEM_MODULES', InventoryItem_ItemModules_Model::getItemModules());
        $viewer->assign('EXCLUDED_FIELDS', $this->excludedFields);
        $viewer->assign('COMPUTED_FIELDS', $this->computedFields);
        $viewer->assign('INVENTORY_ITEMS', $this->fetchItems((int)$request->get('record')));
        $selectedFields = InventoryItem_Module_Model::getSelectedFields(gettabid($request->getModule()));

        if (in_array('description', $selectedFields)) {
            $viewer->assign('DESCRIPTION_ALLOWED', true);
            $key = array_search('description', $selectedFields);

            if ($key !== false) {
                unset($selectedFields[$key]);
            }
        }

        $viewer->assign('INVENTORY_ITEM_COLUMNS', $selectedFields);

        $recordModel = Vtiger_Record_Model::getCleanInstance('InventoryItem');
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $recordStructure = [];

        foreach ($recordStructureInstance->getStructure() as $value) {
            foreach ($value as $key2 => $value2) {
                $recordStructure[$key2] = $value2;
            }
        }

        $viewer->assign('INVENTORY_ITEM_RECORD_STRUCTURE', $recordStructure);

    }

    private function fetchItems(int $record): array
    {
        $inventoryItems = [[],];
        $db = PearDatabase::getInstance();
        $sql = 'SELECT df_inventoryitem.*, vtiger_crmentity.description 
            FROM df_inventoryitem
            LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid = df_inventoryitem.inventoryitemid
            WHERE vtiger_crmentity.deleted = 0
            AND df_inventoryitem.parentid = ?
            ORDER BY df_inventoryitem.sequence, vtiger_crmentity.crmid';
        $result = $db->pquery($sql, [$record]);

        while ($row = $db->fetchByAssoc($result)) {
            if (empty($row['productid']) && !empty($row['item_text'])) {
                $row['entityType'] = 'Text';
            } else {
                $row['entityType'] = getSalesEntityType($row['productid']);

                if (empty($row['item_text'])) {
                    $row['item_text'] = getEntityName($row['entityType'], $row['productid'])[$row['productid']];
                }
            }

            $inventoryItems[] = $row;
        }

        unset($inventoryItems[0]);

        return $inventoryItems;
    }
}