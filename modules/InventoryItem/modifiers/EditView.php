<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_EditView_Modifier implements Core_Modifier_Interface
{
    /**
     * @inheritDoc
     */
    public function modifyProcess(Vtiger_Viewer $viewer, Vtiger_Request $request): void
    {
    }

    /**
     * Returns array of .js files that should be loaded so that the InventoryItem block could provide its functionality
     *
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function modifyGetHeaderScripts(Vtiger_Request $request): array
    {
        return [
            'modules.InventoryItem.resources.InventoryItemEdit',
        ];
    }
}