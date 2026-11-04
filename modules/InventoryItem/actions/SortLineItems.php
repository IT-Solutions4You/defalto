<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_SortLineItems_Action extends Core_Controller_Action
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);

        $data = $request->get('data');
        $itemIds = is_array($data) ? array_column($data, 'id') : [];

        if (!is_array($data) || count($itemIds) !== count($data)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $itemParentIds = InventoryItem_Module_Model::fetchParentIds($itemIds);

        if (count($itemParentIds) !== count(array_unique(array_map('intval', $itemIds)))) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        foreach (array_unique($itemParentIds) as $parentId) {
            InventoryItem_ParentEntity_Model::requireEditableParent($parentId);
        }

        return true;
    }

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

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}