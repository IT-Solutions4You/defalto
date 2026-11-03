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
 * Removes a deleted integrated document from its incoming payments. Restoring the document does not link it again.
 */
class IncomingPayment_RelatedDelete_Handler extends VTEventHandler
{
    /**
     * Handles the after-delete event of an integrated document module.
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
        if ($handlerType !== IncomingPayment_Config_Helper::EVENT_AFTER_DELETE || !InventoryItem_Utils_Helper::isInventoryModule($moduleName)) {
            return;
        }

        if (!IncomingPayment_Integration_Model::getInstance()->isActive($moduleName)) {
            return;
        }

        IncomingPayment_DocumentSync_Model::getInstance()->handleDocumentDelete((int)$entityData->getId());
    }
}
