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
class IncomingPayment_DeleteWarnings_Modifier implements Core_Modifier_Interface
{
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
        $counts = IncomingPayment_Associated_Model::getInstance()->countForDocuments($recordIds);

        if ($counts['payments'] <= 0) {
            return;
        }

        $module = IncomingPayment_Config_Helper::MODULE;
        $warnings[] = count($recordIds) === 1
            ? vtranslate('LBL_DELETE_DOCUMENT_WITH_PAYMENTS', $module, $counts['payments'])
            : vtranslate('LBL_DELETE_DOCUMENTS_WITH_PAYMENTS', $module, $counts['documents'], $counts['payments']);
    }
}
