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
class IncomingPayment_PaymentDelete_Handler extends VTEventHandler
{
    /**
     * Handles the after-delete and after-restore events of an incoming payment.
     *
     * @param string       $handlerType Event name
     * @param VTEntityData $entityData  Payment entity data
     * @return void
     * @throws Exception
     */
    public function handleEvent($handlerType, $entityData)
    {
        if ($entityData->getModuleName() !== IncomingPayment_Config_Helper::MODULE) {
            return;
        }

        $paymentId = (int)$entityData->getId();
        $documentSync = IncomingPayment_DocumentSync_Model::getInstance();

        // The document and the calendar may have changed while the payment was deleted.
        if ($handlerType === IncomingPayment_Config_Helper::EVENT_AFTER_RESTORE) {
            $documentSync->updateReadonlyValues(Vtiger_Record_Model::getInstanceById($paymentId, IncomingPayment_Config_Helper::MODULE));
        }

        $events = [IncomingPayment_Config_Helper::EVENT_AFTER_DELETE, IncomingPayment_Config_Helper::EVENT_AFTER_RESTORE];

        if (in_array($handlerType, $events, true)) {
            $documentSync->recalculateForPayment($paymentId);
        }
    }
}
