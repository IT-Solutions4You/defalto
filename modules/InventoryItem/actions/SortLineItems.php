<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class InventoryItem_SortLineItems_Action extends Vtiger_Action_Controller
{
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        try {
            $moduleName = $request->getModule();
            $data = $request->get('data');

            if (!empty($request->get('data'))) {
                foreach ($data as $itemData) {
                    $focus = CRMEntity::getInstance($moduleName);
                    $focus->id = $itemData['id'];
                    $focus->mode = 'edit';
                    $focus->retrieve_entity_info($itemData['id'], $moduleName);

                    if ($focus->column_fields['sequence'] != $itemData['sequence']) {
                        $focus->column_fields['sequence'] = $itemData['sequence'];
                        $focus->save($moduleName);
                    }
                }
            }

            $response->setResult('OK');
        } catch (Exception $e) {
            $response->setError($e->getMessage());
        }

        $response->emit();
    }

    public function validateRequest(Vtiger_Request $request) {
        return $request->validateWriteAccess();
    }
}