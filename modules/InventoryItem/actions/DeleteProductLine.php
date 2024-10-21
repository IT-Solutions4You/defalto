<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class InventoryItem_DeleteProductLine_Action extends Vtiger_Delete_Action
{
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        try {
            $moduleName = $request->getModule();
            $recordId = $request->get('lineItemId');
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $recordModel->delete();
            $response->setResult('OK');
        } catch (Exception $e) {
            $response->setError($e->getMessage());
        }

        $response->emit();
    }
}