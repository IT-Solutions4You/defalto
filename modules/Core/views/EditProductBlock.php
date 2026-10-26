<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_EditProductBlock_View extends Core_InventoryItemsBlock_View
{
    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request): void
    {
        if (!$request->get('record') && $request->get('tplid')) {
            $request->set('record', $request->get('tplid'));
        }

        $request->set('mode', 'edit');
        parent::process($request);
    }
}
