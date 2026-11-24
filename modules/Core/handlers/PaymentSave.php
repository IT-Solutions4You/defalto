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
 * Before-save hook of a payment. It checks nothing today: a payment may reference any number of documents. It is the
 * place for edition checks (for example a limit of documents per payment) once the payment modules are split into
 * a free and a paid edition. The read-only values, the split of the amount over the documents and the document
 * synchronisation are handled by the payment entity (Core_PaymentEntity_Model::save_module()).
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
        if ($entityData->getModuleName() !== $this->getConfig()::MODULE || $handlerType !== Core_PaymentConfig_Helper::EVENT_BEFORE_SAVE) {
            return;
        }
    }
}
