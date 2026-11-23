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
 * after it was saved (for example after its total or currency changed), and the other documents sharing
 * a source document with it (the invoices of its sales order).
 */
abstract class Core_PaymentRelatedSave_Handler extends VTEventHandler
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
        if ($handlerType !== Core_PaymentConfig_Helper::EVENT_AFTER_SAVE_FINAL || !Core_PaymentConfig_Helper::isDocumentModule($moduleName)) {
            return;
        }

        if (!$this->getConfig()->getIntegrationModel()->isActive($moduleName)) {
            return;
        }

        $recordId = (int)$entityData->getId();
        $this->getConfig()->getDocumentSyncModel()->recalculateDocument($moduleName, $recordId);
        // An invoice may have got or lost a sales order, which changes how the order payments are split
        Core_PaymentSourceDocument_Model::getInstance()->recalculateSiblings($moduleName, $recordId);
    }
}
