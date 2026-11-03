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
 * Fills the editable derived values of a payment before it is saved.
 * The read-only values and the document synchronisation are handled by IncomingPayment::save_module().
 */
class IncomingPayment_PaymentSave_Handler extends VTEventHandler
{
    /**
     * Handles the before-save event of an incoming payment.
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

        if ($handlerType === IncomingPayment_Config_Helper::EVENT_BEFORE_SAVE) {
            IncomingPayment_PaymentValues_Helper::setEditableValues($entityData);
        }
    }
}
