<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_InventoryItemsBlock_Action extends Core_RelatedBlock_Action
{
    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();

        if ($recordId) {
            $instance = Core_InventoryItemsBlock_Model::getInstanceById($recordId, $moduleName);
        } else {
            $instance = Core_InventoryItemsBlock_Model::getInstance($moduleName);
        }

        $instance->retrieveFromRequest($request);
        $instance->save();

        header('location:' . $instance->getEditViewUrl());
    }
}