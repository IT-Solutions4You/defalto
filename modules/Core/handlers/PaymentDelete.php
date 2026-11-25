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
 * Recalculates the documents of a payment after the payment was deleted or restored.
 */
abstract class Core_PaymentDelete_Handler extends VTEventHandler
{
    /**
     * Returns the configuration of the payment module this class belongs to.
     *
     * @return Core_PaymentConfig_Helper
     * @throws Exception
     */
    protected function getConfig(): Core_PaymentConfig_Helper
    {
        return Core_PaymentConfig_Helper::getForClass(static::class);
    }

    /**
     * Handles the after-delete and after-restore events of a payment. Deletes in bulk save mode have no events;
     * Core_PaymentEntity_Model::trash() recalculates the documents then.
     *
     * @param string       $handlerType Event name
     * @param VTEntityData $entityData  Payment entity data
     * @return void
     * @throws Exception
     */
    public function handleEvent($handlerType, $entityData)
    {
        if ($entityData->getModuleName() !== $this->getConfig()::MODULE) {
            return;
        }

        $paymentId = (int)$entityData->getId();
        $documentSync = $this->getConfig()->getDocumentSyncModel();

        // The document and the calendar may have changed while the payment was deleted.
        if ($handlerType === Core_PaymentConfig_Helper::EVENT_AFTER_RESTORE) {
            $documentSync->updateReadonlyValues(Vtiger_Record_Model::getInstanceById($paymentId, $this->getConfig()::MODULE));
        }

        $events = [Core_PaymentConfig_Helper::EVENT_AFTER_DELETE, Core_PaymentConfig_Helper::EVENT_AFTER_RESTORE];

        if (in_array($handlerType, $events, true)) {
            $documentSync->recalculateForPayment($paymentId);
        }
    }
}
