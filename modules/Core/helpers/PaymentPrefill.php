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
 * Prepares the values of a new payment created from a document (open amount, currency, due date, organisation, contact).
 */
class Core_PaymentPrefill_Helper
{
    /**
     * Sets the values of a payment of the given document on the request; values already in the request stay.
     *
     * @param Core_PaymentConfig_Helper $paymentConfig Configuration of the payment module
     * @param Vtiger_Request            $request       Request of the edit or quick create view
     * @param string                    $sourceModule  Document module name
     * @param int                       $sourceRecord  Document record id
     * @return void
     * @throws Exception
     */
    public static function setRequestValues(Core_PaymentConfig_Helper $paymentConfig, Vtiger_Request $request, string $sourceModule, int $sourceRecord): void
    {
        foreach (self::getValues($paymentConfig, $sourceModule, $sourceRecord) as $fieldName => $value) {
            if ($request->isEmpty($fieldName)) {
                $request->set($fieldName, $value);
            }
        }
    }

    /**
     * Returns the edit-view values for a payment of the given document, in the user's display format.
     *
     * @param Core_PaymentConfig_Helper $paymentConfig Configuration of the payment module
     * @param string                    $sourceModule  Document module name
     * @param int                       $sourceRecord  Document record id
     * @return array [field name => value]
     * @throws Exception
     */
    public static function getValues(Core_PaymentConfig_Helper $paymentConfig, string $sourceModule, int $sourceRecord): array
    {
        if ($sourceModule === '' || $sourceRecord <= 0 || !isRecordExists($sourceRecord)
            || (string)getSalesEntityType($sourceRecord) !== $sourceModule
            || !Users_Privileges_Model::isPermitted($sourceModule, 'DetailView', $sourceRecord)) {
            return [];
        }

        $config = $paymentConfig->getIntegrationModel()->retrieveByModule($sourceModule);
        $isRefund = $paymentConfig->isRefundFor($sourceModule);
        $recordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
        $moduleModel = $recordModel->getModule();
        $namePrefix = $isRefund
            ? vtranslate(Core_PaymentConfig_Helper::LBL_REFUND, $paymentConfig::MODULE)
            : vtranslate('SINGLE_' . $paymentConfig::MODULE, $paymentConfig::MODULE);
        $values = [
            Core_PaymentConfig_Helper::FIELD_RELATION => $sourceRecord,
            'payment_name' => trim($namePrefix . ' ' . Core_PaymentValues_Helper::getRecordNumber($recordModel)),
        ];
        $amount = self::getPrefillAmount($recordModel, $isRefund);

        if ($amount > Core_PaymentCalculation_Helper::EPSILON) {
            $values[Core_PaymentConfig_Helper::FIELD_AMOUNT] = Core_PaymentCalculation_Helper::formatEditAmount($amount);
        }

        if ($moduleModel->getField('currency_id') && (int)$recordModel->get('currency_id') > 0) {
            $values[Core_PaymentConfig_Helper::FIELD_AMOUNT_CURRENCY] = (int)$recordModel->get('currency_id');
        }

        $dueDateField = Core_PaymentIntegrationFields_Helper::getFirstField($moduleModel, Core_PaymentConfig_Helper::DOCUMENT_DUE_DATE_FIELDS);
        $dueDate = Core_PaymentStatus_Helper::normalizeDate($dueDateField !== '' ? $recordModel->get($dueDateField) : '');

        if ($dueDate !== '') {
            $values['due_date'] = DateTimeField::convertToUserFormat($dueDate);
        }

        $relatedToField = (string)($config['relatedto_field'] ?? '');

        if ($relatedToField !== '' && $moduleModel->getField($relatedToField) && (int)$recordModel->get($relatedToField) > 0) {
            $values[Core_PaymentConfig_Helper::FIELD_RELATED_TO] = (int)$recordModel->get($relatedToField);
        }

        if ($moduleModel->getField('contact_id') && (int)$recordModel->get('contact_id') > 0) {
            $values[Core_PaymentConfig_Helper::FIELD_CONTACT] = (int)$recordModel->get('contact_id');
        }

        return $values;
    }

    /**
     * Returns the amount a new payment of the document is prefilled with, in the document currency, as Cashflow
     * does: for a payment the total minus all payments and plus all refunds regardless of their status, so waiting
     * payments are not asked for twice; for a refund the paid amount (received payments minus received refunds).
     * The paying module provides the total field. The result may be zero or negative; then nothing is prefilled.
     *
     * @param Vtiger_Record_Model $recordModel Document record
     * @param bool                $isRefund    true for a refund, false for a payment
     * @return float
     * @throws Exception
     */
    private static function getPrefillAmount(Vtiger_Record_Model $recordModel, bool $isRefund): float
    {
        $documentModule = $recordModel->getModuleName();
        $payingModule = Core_PaymentDocumentBalance_Model::getPayingModule($documentModule);

        if ($payingModule === '') {
            return 0.0;
        }

        $balanceModel = Core_PaymentDocumentBalance_Model::getInstance();

        if ($isRefund) {
            return $balanceModel->getNetAmount($recordModel, true);
        }

        $config = Core_PaymentConfig_Helper::getInstance($payingModule)->getIntegrationModel()->retrieveByModule($documentModule);

        return self::getFieldAmount($recordModel, (string)($config['total_field'] ?? '')) - $balanceModel->getNetAmount($recordModel, false);
    }

    /**
     * Returns an amount field of the document as float; 0 when the field is not configured or missing.
     *
     * @param Vtiger_Record_Model $recordModel Document record
     * @param string              $fieldName   Field name
     * @return float
     */
    private static function getFieldAmount(Vtiger_Record_Model $recordModel, string $fieldName): float
    {
        if ($fieldName === '' || !$recordModel->getModule()->getField($fieldName)) {
            return 0.0;
        }

        return Core_PaymentCalculation_Helper::parseAmount($recordModel->get($fieldName));
    }
}
