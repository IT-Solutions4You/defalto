<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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

    public function validateRequest(Vtiger_Request $request)
    {
        return $request->validateWriteAccess();
    }
}