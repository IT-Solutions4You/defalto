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
        $recordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
        $moduleModel = $recordModel->getModule();
        $values = [
            Core_PaymentConfig_Helper::FIELD_RELATION => $sourceRecord,
            'payment_name' => trim(vtranslate('SINGLE_' . $paymentConfig::MODULE, $paymentConfig::MODULE) . ' ' . Core_PaymentValues_Helper::getRecordNumber($recordModel)),
        ];
        $total = self::getFieldAmount($recordModel, (string)($config['total_field'] ?? ''));
        $paid = self::getFieldAmount($recordModel, (string)($config['paid_field'] ?? ''));
        $openAmount = max(0.0, $total - $paid);

        if ($openAmount > 0) {
            $values[Core_PaymentConfig_Helper::FIELD_AMOUNT] = Core_PaymentCalculation_Helper::formatEditAmount($openAmount);
        }

        if ($moduleModel->getField('currency_id') && (int)$recordModel->get('currency_id') > 0) {
            $values[Core_PaymentConfig_Helper::FIELD_AMOUNT_CURRENCY] = (int)$recordModel->get('currency_id');
        }

        $dueDate = Core_PaymentStatus_Helper::normalizeDate($moduleModel->getField('duedate') ? $recordModel->get('duedate') : '');

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
