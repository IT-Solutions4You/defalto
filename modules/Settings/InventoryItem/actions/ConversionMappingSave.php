<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_InventoryItem_ConversionMappingSave_Action extends Settings_Vtiger_Index_Action
{
    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request): void
    {
        $conversion = InventoryItem_Conversion_Model::getConversionById((int)$request->get('conversion'));

        if (empty($conversion)) {
            throw new Exception(vtranslate('LBL_RECORD_NOT_FOUND'));
        }

        (new InventoryItem_ConversionMapping_Model())->saveConversionMapping($conversion, array_values((array)$request->get('rows', [])));

        $response = new Vtiger_Response();
        $response->setResult(true);
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
