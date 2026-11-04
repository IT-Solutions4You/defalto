<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_InventoryItem_ConversionsSave_Action extends Settings_Vtiger_Index_Action
{
    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request): void
    {
        $settings = (array)$request->get('conversions', []);

        foreach (InventoryItem_Conversion_Model::getAllConversions() as $conversion) {
            $conversionId = $conversion->getId();

            if (isset($settings[$conversionId])) {
                $conversion->saveSettings((array)$settings[$conversionId]);
            }
        }

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
