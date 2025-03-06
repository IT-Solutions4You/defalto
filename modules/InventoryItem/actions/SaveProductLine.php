<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_SaveProductLine_Action extends Vtiger_SaveAjax_Action
{
    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        try {
            $rowIdentifier = $request->get('rowNum');
            $data = $request->get('data');
            $focus = CRMEntity::getInstance('InventoryItem');

            if ($data['lineItemId' . $rowIdentifier]) {
                $focus->id = $data['lineItemId' . $rowIdentifier];
                $focus->mode = 'edit';
                $focus->retrieve_entity_info($focus->id, 'InventoryItem');
            }

            foreach ($focus->column_fields as $fieldName => $fieldValue) {
                if (isset($data[$fieldName . $rowIdentifier]) && $data[$fieldName . $rowIdentifier] != $focus->column_fields[$fieldName]) {
                    $focus->column_fields[$fieldName] = $data[$fieldName . $rowIdentifier];
                }
            }

            $focus->column_fields['parentid'] = $request->get('for_record');
            $focus->save('InventoryItem');
            $focus->saveTaxId((int)$data['taxid']);

            InventoryItem_ParentEntity_Model::updateTotals((int)$focus->column_fields['parentid']);

            $response->setResult($focus->id);
        } catch (Exception $e) {
            $response->setError($e->getMessage());
        }

        $response->emit();
    }
}