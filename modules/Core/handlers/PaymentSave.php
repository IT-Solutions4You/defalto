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
 * Checks the reference documents of a payment before it is saved.
 * The read-only values and the document synchronisation are handled by the payment entity (Core_PaymentEntity_Model::save_module()).
 */
abstract class Core_PaymentSave_Handler extends VTEventHandler
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
     * Handles the before-save event of a payment.
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

        if ($handlerType === Core_PaymentConfig_Helper::EVENT_BEFORE_SAVE) {
            Core_PaymentValues_Helper::validateReferences($entityData);
        }
    }
}
