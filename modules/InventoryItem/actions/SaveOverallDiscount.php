<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_SaveOverallDiscount_Action extends Vtiger_SaveAjax_Action
{
    public function process(Vtiger_Request $request)
    {
        /*$recordModel = $this->getRecordModelFromRequest($request);
        $recordModel->saveOverallDiscount();*/
        show($request->getAll());
        $db = PearDatabase::getInstance();
//        $db->setDebug(true);
        $discount = (float)$request->get('overall_discount_percent');

        $sql = 'SELECT df_inventoryitem.* 
                FROM df_inventoryitem
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = df_inventoryitem.inventoryitemid AND vtiger_crmentity.deleted = 0
                WHERE parentid = ?   
                    AND productid IS NOT NULL
                    AND productid <> 0';
        $res = $db->pquery($sql, [$request->get('for_record')]);

        while ($row = $db->fetchByAssoc($res)) {
            show($row);
            $recordModel = Vtiger_Record_Model::getInstanceById($row['inventoryitemid'], 'InventoryItem');
            $recordModel->getEntity()->mode = 'edit';
            $recordModel->set('overall_discount', $discount);
            $recordModel->save();
        }
    }
}