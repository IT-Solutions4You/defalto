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
 * Daily update of payment statuses that change with the calendar (Waiting → Pending, Waiting → Paid).
 * The core cron PaymentStatus runs it always; without an active payment module it does nothing.
 */
class Core_PaymentStatus_Model
{
    /**
     * Payment modules; the document synchronisation model of each (Core_PaymentConfig_Helper::getDocumentSyncModel())
     * re-saves their open payments.
     */
    public const PAYMENT_MODULES = ['IncomingPayment', 'OutgoingPayment'];

    /**
     * Returns a new instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * Updates the open payments of every active payment module.
     * Runs without the bulk save mode, so the payment handlers update the statuses, the history and the documents.
     *
     * @return void
     */
    public function updateOpenStatuses(): void
    {
        foreach ($this->getActiveStatusModels() as $statusModel) {
            $statusModel->updateOpenStatuses();
        }
    }

    /**
     * Returns the status models of the installed and active payment modules.
     *
     * @return array<string, Core_PaymentDocumentSync_Model>
     * @throws Exception
     */
    public function getActiveStatusModels(): array
    {
        $models = [];

        foreach (self::PAYMENT_MODULES as $moduleName) {
            if (vtlib_isModuleActive($moduleName) && class_exists($moduleName . '_Config_Helper')) {
                $models[$moduleName] = Core_PaymentConfig_Helper::getInstance($moduleName)->getDocumentSyncModel();
            }
        }

        return $models;
    }
}
