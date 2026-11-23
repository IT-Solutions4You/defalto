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
 * Adds a warning to the delete confirmation of an integrated document module when payments are linked to the records.
 * Registered for every active integration as the DeleteWarningsAction modifier (Core_DeleteWarnings_Action).
 */
abstract class Core_PaymentDeleteWarnings_Modifier implements Core_Modifier_Interface
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
     * @inheritDoc
     */
    public function modifyProcess(Vtiger_Viewer $viewer, Vtiger_Request $request): void
    {
    }

    /**
     * Adds the warning about linked payments.
     *
     * @param array          $warnings  Warnings of the delete confirmation
     * @param int[]          $recordIds Records being deleted
     * @param Vtiger_Request $request
     * @return void
     */
    public function modifyGetWarnings(array &$warnings, array $recordIds, Vtiger_Request $request): void
    {
        $counts = $this->getConfig()->getAssociatedModel()->countForDocuments($recordIds);

        if ($counts['payments'] <= 0) {
            return;
        }

        $module = $this->getConfig()::MODULE;
        $warnings[] = count($recordIds) === 1
            ? vtranslate('LBL_DELETE_DOCUMENT_WITH_PAYMENTS', $module, $counts['payments'])
            : vtranslate('LBL_DELETE_DOCUMENTS_WITH_PAYMENTS', $module, $counts['documents'], $counts['payments']);
        $this->addTargetWarning($warnings, $recordIds);
    }

    /**
     * Adds that the payments of deleted source documents (sales orders) no longer count on the documents created
     * from them (invoices). Only the module paying the document module adds it, so it appears once.
     *
     * @param array $warnings  Warnings of the delete confirmation
     * @param int[] $recordIds Records being deleted
     * @return void
     * @throws Exception
     */
    protected function addTargetWarning(array &$warnings, array $recordIds): void
    {
        $documentModule = $recordIds ? (string)getSalesEntityType((int)reset($recordIds)) : '';

        if (!Core_PaymentSourceDocument_Model::isSourceModule($documentModule)
            || Core_PaymentDocumentBalance_Model::getPayingModule($documentModule) !== $this->getConfig()::MODULE) {
            return;
        }

        $numbers = Core_PaymentSourceDocument_Model::getInstance()->getTargetNumbers($documentModule, $recordIds);

        if ($numbers) {
            $warnings[] = vtranslate('LBL_DELETE_SOURCE_WITH_TARGETS', $this->getConfig()::MODULE, implode(', ', $numbers));
        }
    }
}
