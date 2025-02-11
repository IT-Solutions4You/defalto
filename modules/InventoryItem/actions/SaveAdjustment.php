<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_SaveAdjustment_Action extends Vtiger_SaveAjax_Action
{
    public function process(Vtiger_Request $request)
    {
        $adjustment = (float)$request->get('adjustment');
        $recordId = $request->get('for_record');
        $moduleName = $request->get('for_module');

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $recordModel->set('mode', 'edit');
        $recordModel->set('txtAdjustment', $adjustment);
        $recordModel->save();
    }
}