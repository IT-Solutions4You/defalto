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
 * Prepares the data of the payments widgets in the summary view of a document: the widget of one payment module and
 * the common widget of the Payments list with the payments of every direction integrated on the document.
 */
class Core_PaymentWidgetSummary_Helper
{
    /**
     * Returns the payments of a document in one payment module, the document totals and the add URL.
     *
     * @param Core_PaymentConfig_Helper $paymentConfig Configuration of the payment module
     * @param string                    $sourceModule  Document module name
     * @param int                       $sourceRecord  Document record id
     * @return array payments, total, paid, balance, expected, isIntegrated, addUrl
     * @throws Exception
     */
    public static function getSummary(Core_PaymentConfig_Helper $paymentConfig, string $sourceModule, int $sourceRecord): array
    {
        $summary = self::getDocumentSummary([$paymentConfig::MODULE], $sourceModule, $sourceRecord);
        $summary['addUrl'] = self::getAddUrl($paymentConfig::MODULE, $sourceModule, $sourceRecord);

        return $summary;
    }

    /**
     * Returns the payments of a document in the given payment modules, newest payment date first, and the totals:
     * total, paid and balance from the document fields, expected as the sum of the payments not received yet.
     * Payments the current user may not see are left out; the totals still count them.
     *
     * @param string[] $paymentModules Payment module names
     * @param string   $sourceModule   Document module name
     * @param int      $sourceRecord   Document record id
     * @return array payments, total, paid, balance, expected, isIntegrated
     * @throws Exception
     */
    public static function getDocumentSummary(array $paymentModules, string $sourceModule, int $sourceRecord): array
    {
        $summary = [
            'payments' => [],
            'total' => '',
            'paid' => '',
            'balance' => '',
            'expected' => '',
            'isIntegrated' => false,
        ];

        if ($sourceModule === '' || $sourceRecord <= 0 || !isRecordExists($sourceRecord)) {
            return $summary;
        }

        $recordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
        $currencyId = (int)$recordModel->get('currency_id');
        $amountFields = [];
        $expected = 0.0;

        foreach (array_intersect(Core_PaymentConfig_Helper::getActiveModules(), $paymentModules) as $paymentModule) {
            $paymentConfig = Core_PaymentConfig_Helper::getInstance($paymentModule);
            $syncModel = $paymentConfig->getDocumentSyncModel();
            array_push($summary['payments'], ...self::getPayments($paymentConfig, $sourceRecord));
            $integration = $paymentConfig->getIntegrationModel();

            if (!$integration->isActive($sourceModule)) {
                continue;
            }

            $summary['isIntegrated'] = true;
            $amountFields = $amountFields ?: $integration->retrieveByModule($sourceModule);
            $expected += $syncModel->sumOpenAmount($sourceRecord, $currencyId, $syncModel->getDocumentRate($recordModel));
        }

        usort($summary['payments'], static function (array $first, array $second): int {
            return [$second['sortDate'], $second['id']] <=> [$first['sortDate'], $first['id']];
        });

        $moduleModel = $recordModel->getModule();
        $amounts = [
            'total' => (string)($amountFields['total_field'] ?? ''),
            'paid' => (string)($amountFields['paid_field'] ?? ''),
            'balance' => (string)($amountFields['balance_field'] ?? ''),
        ];

        foreach ($amounts as $key => $fieldName) {
            if ($fieldName !== '' && $moduleModel->getField($fieldName)) {
                $value = Core_PaymentCalculation_Helper::parseAmount($recordModel->get($fieldName));
                $summary[$key] = Core_PaymentCalculation_Helper::formatAmount($value, $currencyId);
            }
        }

        if (!Core_PaymentCalculation_Helper::isSameAmount($expected, 0.0)) {
            $summary['expected'] = Core_PaymentCalculation_Helper::formatAmount($expected, $currencyId);
        }

        return $summary;
    }

    /**
     * Returns the active payment modules with an active integration on a document module.
     *
     * @param string $sourceModule Document module name
     * @return string[]
     * @throws Exception
     */
    public static function getIntegratedModules(string $sourceModule): array
    {
        $modules = [];

        foreach (Core_PaymentConfig_Helper::getActiveModules() as $paymentModule) {
            if (Core_PaymentConfig_Helper::getInstance($paymentModule)->getIntegrationModel()->isActive($sourceModule)) {
                $modules[] = $paymentModule;
            }
        }

        return $modules;
    }

    /**
     * Returns the add buttons of the common payments widget: one per payment module integrated on the document
     * in which the current user may create payments. The label names the direction (LBL_ADD_INCOMING_PAYMENT,
     * LBL_ADD_OUTGOING_PAYMENT) and is translated in the Payments language file.
     *
     * @param string $sourceModule Document module name
     * @param int    $sourceRecord Document record id
     * @return array<int, array{module: string, label: string, icon: string, url: string}>
     * @throws Exception
     */
    public static function getAddLinks(string $sourceModule, int $sourceRecord): array
    {
        $links = [];

        foreach (self::getIntegratedModules($sourceModule) as $paymentModule) {
            if (!Users_Privileges_Model::isPermitted($paymentModule, 'CreateView')) {
                continue;
            }

            $paymentConfig = Core_PaymentConfig_Helper::getInstance($paymentModule);
            $links[] = [
                'module' => $paymentModule,
                'label' => 'LBL_ADD_' . strtoupper($paymentConfig::DIRECTION) . '_PAYMENT',
                'icon' => $paymentConfig->getIcon(),
                'url' => self::getAddUrl($paymentModule, $sourceModule, $sourceRecord),
            ];
        }

        return $links;
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

    /**
     * Returns the payments of one payment module applied to a document that the current user may see.
     *
     * @param Core_PaymentConfig_Helper $paymentConfig Configuration of the payment module
     * @param int                       $sourceRecord  Document record id
     * @return array<int, array>
     * @throws Exception
     */
    protected static function getPayments(Core_PaymentConfig_Helper $paymentConfig, int $sourceRecord): array
    {
        $paymentModule = $paymentConfig::MODULE;
        $payments = [];

        foreach ($paymentConfig->getDocumentSyncModel()->fetchDocumentPayments($sourceRecord) as $row) {
            $paymentId = (int)$row['id'];

            if (!Users_Privileges_Model::isPermitted($paymentModule, 'DetailView', $paymentId)) {
                continue;
            }

            $payments[] = [
                'id' => $paymentId,
                'module' => $paymentModule,
                'direction' => $paymentConfig::DIRECTION,
                'isIncoming' => Core_PaymentConfig_Helper::DIRECTION_INCOMING === $paymentConfig::DIRECTION,
                'number' => (string)$row['payment_no'],
                'name' => (string)$row['payment_name'],
                'sortDate' => (string)$row['payment_date'],
                'date' => $row['payment_date'] ? DateTimeField::convertToUserFormat((string)$row['payment_date']) : '',
                'status' => (string)$row['payment_status'],
                'isReceived' => in_array((string)$row['payment_status'], Core_PaymentConfig_Helper::getPaidStatuses(), true),
                'amount' => Core_PaymentCalculation_Helper::formatAmount((float)$row['partial_amount'], (int)$row['currency_id']),
                'url' => 'index.php?module=' . $paymentModule . '&view=Detail&record=' . $paymentId,
            ];
        }

        return $payments;
    }
}
