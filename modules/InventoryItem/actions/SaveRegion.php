<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_SaveRegion_Action extends Vtiger_SaveAjax_Action
{
    public function process(Vtiger_Request $request)
    {
        $regionId = (float)$request->get('region_id');
        $recordId = $request->get('for_record');
        $moduleName = $request->get('for_module');

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $recordModel->set('mode', 'edit');
        $recordModel->set('region_id', $regionId);
        $recordModel->save();
        /*$db = PearDatabase::getInstance();
        $discount = (float)$request->get('overall_discount_percent');

        $sql = 'SELECT df_inventoryitem.*
                FROM df_inventoryitem
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = df_inventoryitem.inventoryitemid AND vtiger_crmentity.deleted = 0
                WHERE parentid = ?
                    AND productid IS NOT NULL
                    AND productid <> 0';
        $res = $db->pquery($sql, [$request->get('for_record')]);

        while ($row = $db->fetchByAssoc($res)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($row['inventoryitemid'], 'InventoryItem');
            $recordModel->set('overall_discount', $discount);
            $recordModel->set('mode', 'edit');
            $recordModel->save();
        }*/
    }
}