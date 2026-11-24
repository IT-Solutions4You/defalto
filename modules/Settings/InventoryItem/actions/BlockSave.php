<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_InventoryItem_BlockSave_Action extends Settings_Vtiger_Index_Action
{
    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request): void
    {
        $simplified = 'simplified' === $request->get('productBlockType');
        InventoryItem_Module_Model::saveSetting(InventoryItem_Module_Model::SETTING_SIMPLIFIED_BLOCK, $simplified ? '1' : '0');

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
