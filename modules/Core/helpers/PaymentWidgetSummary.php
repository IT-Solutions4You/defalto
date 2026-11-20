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
 * Prepares the data of the payments widget shown in the summary view of a document.
 */
class Core_PaymentWidgetSummary_Helper
{
    /**
     * Returns the payments of a document with formatted amounts, and the document totals.
     *
     * @param Core_PaymentConfig_Helper $paymentConfig Configuration of the payment module
     * @param string                    $sourceModule  Document module name
     * @param int                       $sourceRecord  Document record id
     * @return array payments, total, paid, balance, isIntegrated, addUrl
     * @throws Exception
     */
    public static function getSummary(Core_PaymentConfig_Helper $paymentConfig, string $sourceModule, int $sourceRecord): array
    {
        $summary = [
            'payments' => [],
            'total' => '',
            'paid' => '',
            'balance' => '',
            'isIntegrated' => false,
            'addUrl' => self::getAddUrl($paymentConfig::MODULE, $sourceModule, $sourceRecord),
        ];

        if ($sourceModule === '' || $sourceRecord <= 0 || !isRecordExists($sourceRecord)) {
            return $summary;
        }

        $integration = $paymentConfig->getIntegrationModel();
        $config = $integration->retrieveByModule($sourceModule);
        $recordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
        $moduleModel = $recordModel->getModule();
        $currencyId = (int)$recordModel->get('currency_id');
        $summary['isIntegrated'] = $integration->isActive($sourceModule);

        foreach ($paymentConfig->getDocumentSyncModel()->fetchDocumentPayments($sourceRecord) as $row) {
            $summary['payments'][] = [
                'id' => (int)$row['id'],
                'number' => (string)$row['payment_no'],
                'name' => (string)$row['payment_name'],
                'date' => $row['payment_date'] ? DateTimeField::convertToUserFormat((string)$row['payment_date']) : '',
                'status' => (string)$row['payment_status'],
                'isReceived' => in_array((string)$row['payment_status'], Core_PaymentConfig_Helper::getPaidStatuses(), true),
                'amount' => Core_PaymentCalculation_Helper::formatAmount((float)$row['partial_amount'], (int)$row['currency_id']),
                'url' => 'index.php?module=' . $paymentConfig::MODULE . '&view=Detail&record=' . (int)$row['id'],
            ];
        }

        $amounts = [
            'total' => (string)($config['total_field'] ?? ''),
            'paid' => (string)($config['paid_field'] ?? ''),
            'balance' => (string)($config['balance_field'] ?? ''),
        ];

        foreach ($amounts as $key => $fieldName) {
            if ($fieldName !== '' && $moduleModel->getField($fieldName)) {
                $value = Core_PaymentCalculation_Helper::parseAmount($recordModel->get($fieldName));
                $summary[$key] = Core_PaymentCalculation_Helper::formatAmount($value, $currencyId);
            }
        }

        return $summary;
    }

    /**
     * Returns the URL of the edit view that creates a payment for the document.
     *
     * @param string $paymentModule Payment module name
     * @param string $sourceModule  Document module name
     * @param int    $sourceRecord  Document record id
     * @return string
     */
    public static function getAddUrl(string $paymentModule, string $sourceModule, int $sourceRecord): string
    {
        return 'index.php?module=' . $paymentModule . '&view=Edit&sourceModule=' . $sourceModule
            . '&sourceRecord=' . $sourceRecord . '&relationOperation=1';
    }
}
