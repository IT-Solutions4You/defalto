<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Keeps the items reviewed in the conversion modal until the target record is created.
 */
class InventoryItem_ConversionStore_Action extends Core_Controller_Action
{
    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $sourceModule = (string)$request->get('sourceModule');
        $targetModule = (string)$request->get('targetModule');
        $sourceRecord = (int)$request->get('sourceRecord');
        $conversion = InventoryItem_Conversion_Model::getConversion($sourceModule, $targetModule);

        if (empty($conversion)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $conversion->checkAccess($sourceRecord);

        $items = $conversion->validateItems($sourceRecord, (array)$request->get('items', []));

        InventoryItem_Conversion_Model::storeItems($targetModule, $sourceRecord, $items);

        $response = new Vtiger_Response();
        $response->setResult(['stored' => count($items)]);
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
