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
 * Removes a deleted integrated document from its incoming payments. Restoring the document does not link it again,
 * it only recalculates the paid amount, balance and status of the document from the payments still linked to it.
 */
class IncomingPayment_RelatedDelete_Handler extends VTEventHandler
{
    /**
     * Handles the after-delete and after-restore events of an integrated document module.
     *
     * @param string       $handlerType Event name
     * @param VTEntityData $entityData  Document entity data
     * @return void
     * @throws Exception
     */
    public function handleEvent($handlerType, $entityData)
    {
        $moduleName = (string)$entityData->getModuleName();
        $events = [IncomingPayment_Config_Helper::EVENT_AFTER_DELETE, IncomingPayment_Config_Helper::EVENT_AFTER_RESTORE];

        // The events manager calls every active handler for the event, so documents are filtered here
        // before the integration configuration is loaded.
        if (!in_array($handlerType, $events, true) || !InventoryItem_Utils_Helper::isInventoryModule($moduleName)) {
            return;
        }

        if (!IncomingPayment_Integration_Model::getInstance()->isActive($moduleName)) {
            return;
        }

        $documentSync = IncomingPayment_DocumentSync_Model::getInstance();
        $recordId = (int)$entityData->getId();

        if ($handlerType === IncomingPayment_Config_Helper::EVENT_AFTER_RESTORE) {
            // The delete removed the links, so the stored paid amount of the restored document is stale.
            $documentSync->recalculateDocument($moduleName, $recordId);
        } else {
            $documentSync->handleDocumentDelete($recordId);
        }
    }
}
