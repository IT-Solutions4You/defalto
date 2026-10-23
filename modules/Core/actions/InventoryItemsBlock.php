<?php
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
        $sourceModule = $request->get('source_module') ?: $moduleName;
        $recordId = $request->getRecord();

        if ($recordId) {
            $instance = Core_InventoryItemsBlock_Model::getInstanceById($recordId, $sourceModule);
        } else {
            $instance = Core_InventoryItemsBlock_Model::getInstance($sourceModule);
        }

        $instance->retrieveFromRequest($request);
        $instance->save();

        if ($moduleName === $sourceModule) {
            $redirectUrl = $instance->getEditViewUrl();
        } else {
            $redirectUrl = $instance->getManagementEditViewUrl($moduleName);
        }

        header('location:' . $redirectUrl);
    }
}
