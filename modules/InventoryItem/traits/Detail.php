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
    public function adaptDetail(Vtiger_Request $request, Vtiger_Viewer $viewer)
    {
        $productModuleModel = Vtiger_Module_Model::getInstance('Products');
        $viewer->assign('PRODUCT_ACTIVE', $productModuleModel->isActive());

        $serviceModuleModel = Vtiger_Module_Model::getInstance('Services');
        $viewer->assign('SERVICE_ACTIVE', $serviceModuleModel->isActive());

        $viewer->assign('INVENTORY_ITEMS', $this->fetchItems((int)$request->get('record')));
        $viewer->assign('INVENTORY_ITEM_COLUMNS', InventoryItem_Module_Model::getSelectedFields(gettabid($request->getModule())));

        /*$record = $request->get('record');
        $recordModel = Vtiger_Record_Model::getInstanceById($record, 'InventoryItem');
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $recordStructure = [];

        foreach ($recordStructureInstance->getStructure() as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $recordStructure[$key2] = $value2;
            }
        }

        foreach($recordStructure as $key => $value) {
            show($key, $value->get('fieldvalue'));
        }*/

    }

    private function fetchItems(int $record): array
    {
        $inventoryItems = [[],];
        $db = PearDatabase::getInstance();
        $sql = 'SELECT df_inventoryitem.* 
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