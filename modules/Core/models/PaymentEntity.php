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
 * Entity shared by the payment modules. The module class (e.g. IncomingPayment) extends it with its tables and list
 * fields; the payment logic reads the module configuration (<Module>_Config_Helper) from the class name.
 */
abstract class Core_PaymentEntity_Model extends CRMEntity
{
    /**
     * Returns the configuration of the payment module this entity belongs to.
     *
     * @return Core_PaymentConfig_Helper
     * @throws Exception
     */
    protected function getConfig(): Core_PaymentConfig_Helper
    {
        return Core_PaymentConfig_Helper::getForClass(static::class);
    }

    /**
     * Writes the derived payment values and synchronises the documents of the payment.
     * It runs inside saveentity(), so it also covers import (bulk save mode, no events) and workflow field updates,
     * and it runs before the after-save handlers, so workflows and the history see the new values. The currency,
     * the rate and the base amount of the amount (uitype 35) are already stored by saveentity() at this point.
     *
     * @param string $module Module name
     * @return void
     * @throws Exception
     */
    public function save_module(string $module)
    {
        $this->getConfig()->getDocumentSyncModel()->handlePaymentSave((int)$this->id);
    }

    /**
     * Deletes the payment. In bulk save mode (Undo Last Import) the core skips the delete events and runs only its
     * own batch handlers, so the documents of the payment are recalculated here; otherwise the after-delete handler
     * (Core_PaymentDelete_Handler) does it.
     *
     * @param string $module Module name
     * @param int    $id     Payment record id
     * @return void
     * @throws Exception
     */
    public function trash($module, $id)
    {
        parent::trash($module, $id);

        if (self::isBulkSaveMode()) {
            $this->getConfig()->getDocumentSyncModel()->recalculateForPayment((int)$id);
        }
    }

    /**
     * Related list of the documents of one module that a payment is applied to.
     *
     * @param int $id         Payment record id
     * @param int $curTabId   Tab id of this module
     * @param int $relTabId   Tab id of the document module
     * @param int $relationId Related list id
     * @return array ['query' => related list query]
     * @throws Exception
     */
    public function get_associated_documents($id, $curTabId, $relTabId, $relationId): array
    {
        return ['query' => $this->getConfig()->getAssociatedModel()->getDocumentsRelationQuery((int)$id, (string)vtlib_getModuleNameById($relTabId))];
    }

    /**
     * Delegates vtlib lifecycle events (enable, disable, update, uninstall) to the install model.
     *
     * @param string $moduleName Module name
     * @param string $eventType  vtlib event type, e.g. module.enabled
     * @return void
     * @throws Exception
     */
    public function vtlib_handler($moduleName, $eventType): void
    {
        Core_Install_Model::getInstance((string)$eventType, (string)$moduleName)->install();
    }
}
