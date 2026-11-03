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
 * Recalculates the paid amount, balance and status of an integrated document
 * after it was saved (for example after its total or currency changed).
 */
class IncomingPayment_RelatedSave_Handler extends VTEventHandler
{
    /**
     * Handles the final after-save event of an integrated document module.
     *
     * @param string       $handlerType Event name
     * @param VTEntityData $entityData  Document entity data
     * @return void
     * @throws Exception
     */
    public function handleEvent($handlerType, $entityData)
    {
        $moduleName = (string)$entityData->getModuleName();

        // The events manager calls every active handler for the event, so documents are filtered here
        // before the integration configuration is loaded.
        if ($handlerType !== IncomingPayment_Config_Helper::EVENT_AFTER_SAVE_FINAL || !InventoryItem_Utils_Helper::isInventoryModule($moduleName)) {
            return;
        }

        if (!IncomingPayment_Integration_Model::getInstance()->isActive($moduleName)) {
            return;
        }

        IncomingPayment_DocumentSync_Model::getInstance()->recalculateDocument($moduleName, (int)$entityData->getId());
    }
}
