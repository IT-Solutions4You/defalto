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
 * Prepares the field choices shown on the integration Settings page for every available document module.
 */
class Core_PaymentIntegrationFields_Helper
{
    /**
     * Returns one row per available document module with its configuration and the selectable fields.
     *
     * @param Core_PaymentConfig_Helper $paymentConfig Configuration of the payment module
     * @return array rows with module, label, config, refundWithoutPaying (an active refund row of a document module
     *               no other active payment module pays), statusFields, picklists, referenceFields, amountFields
     * @throws Exception
     */
    public static function getRows(Core_PaymentConfig_Helper $paymentConfig): array
    {
        $rows = [];

        foreach ($paymentConfig->getIntegrationModel()->retrieveAll() as $module => $config) {
            $moduleModel = Vtiger_Module_Model::getInstance($module);

            if (!$moduleModel) {
                continue;
            }

            $statusFields = self::getStatusFields($moduleModel);
            $picklists = self::getPicklists($moduleModel, array_keys($statusFields));
            $rows[] = [
                'module' => $module,
                'label' => vtranslate($module, $module),
                'config' => $config,
                'refundWithoutPaying' => !empty($config['active']) && !empty($config[Core_PaymentConfig_Helper::INTEGRATION_REFUND])
                    && '' === Core_PaymentDocumentBalance_Model::getPayingModule($module, [$paymentConfig::MODULE]),
                'statusFields' => $statusFields,
                'picklists' => $picklists,
                'picklistsJson' => Vtiger_Functions::jsonEncode($picklists),
                'referenceFields' => self::getReferenceFields($paymentConfig, $moduleModel),
                'amountFields' => self::getAmountFields($moduleModel),
            ];
        }

        return $rows;
    }

    /**
     * Returns the default integration configuration of a document module, derived from its fields,
     * so any inventory module registered in the future starts with a usable, inactive configuration.
     *
     * @param Core_PaymentConfig_Helper $paymentConfig Configuration of the payment module
     * @param string                    $module        Document module name
     * @return array integration values
     */
    public static function getDefaultConfig(Core_PaymentConfig_Helper $paymentConfig, string $module): array
    {
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $defaults = [
            'active' => 0,
            'status_field' => self::getDefaultStatusField($moduleModel),
            'status_value' => '',
            'status_value_back' => '',
            'relatedto_field' => self::getDefaultRelatedToField($paymentConfig, $moduleModel),
            'total_field' => $moduleModel->getField(Core_PaymentConfig_Helper::DEFAULT_TOTAL_FIELD) ? Core_PaymentConfig_Helper::DEFAULT_TOTAL_FIELD : '',
            'paid_field' => self::getDefaultPaidField($paymentConfig, $moduleModel),
            'balance_field' => Core_PaymentConfig_Helper::DEFAULT_BALANCE_FIELD,
            Core_PaymentConfig_Helper::INTEGRATION_REFUND => self::getDefaultRefund($paymentConfig, $moduleModel),
        ];

        return array_merge($defaults, $paymentConfig->getIntegrationOverrides()[$module] ?? []);
    }

    /**
     * Returns whether the payments of the module are refunds of a document module by default: a document with
     * a vendor (purchase order) is paid by outgoing payments, any other document by incoming payments; payments of
     * the opposite direction are refunds.
     *
     * @param Core_PaymentConfig_Helper $paymentConfig Configuration of the payment module
     * @param Vtiger_Module_Model       $moduleModel   Document module
     * @return int 1 for refunds, 0 for payments
     */
    public static function getDefaultRefund(Core_PaymentConfig_Helper $paymentConfig, Vtiger_Module_Model $moduleModel): int
    {
        $payingDirection = Core_PaymentConfig_Helper::DIRECTION_INCOMING;

        foreach ($moduleModel->getFieldsByType('reference') as $fieldModel) {
            if (in_array('Vendors', (array)$fieldModel->getReferenceList(false, false), true)) {
                $payingDirection = Core_PaymentConfig_Helper::DIRECTION_OUTGOING;
                break;
            }
        }

        return $paymentConfig::DIRECTION === $payingDirection ? 0 : 1;
    }

    /**
     * Returns the first picklist field named like a document status or stage; empty when there is none.
     *
     * @param Vtiger_Module_Model $moduleModel Document module
     * @return string
     */
    private static function getDefaultStatusField(Vtiger_Module_Model $moduleModel): string
    {
        foreach (array_keys(self::getStatusFields($moduleModel)) as $fieldName) {
            if (preg_match('/(status|stage)$/', $fieldName)) {
                return $fieldName;
            }
        }

        return '';
    }

    /**
     * Returns the reference field that holds the payer of a document; empty when there is none.
     *
     * @param Core_PaymentConfig_Helper $paymentConfig Configuration of the payment module
     * @param Vtiger_Module_Model       $moduleModel   Document module
     * @return string
     */
    private static function getDefaultRelatedToField(Core_PaymentConfig_Helper $paymentConfig, Vtiger_Module_Model $moduleModel): string
    {
        $referenceFields = $moduleModel->getFieldsByType('reference');

        foreach ($paymentConfig::RELATED_TO_MODULES as $relatedModule) {
            foreach ($referenceFields as $fieldName => $fieldModel) {
                if (in_array($relatedModule, (array)$fieldModel->getReferenceList(false, false), true)) {
                    return $fieldName;
                }
            }
        }

        return '';
    }

    /**
     * Returns the existing paid amount field of a module, or the name of the field the integration will create.
     *
     * @param Core_PaymentConfig_Helper $paymentConfig Configuration of the payment module
     * @param Vtiger_Module_Model       $moduleModel   Document module
     * @return string
     */
    private static function getDefaultPaidField(Core_PaymentConfig_Helper $paymentConfig, Vtiger_Module_Model $moduleModel): string
    {
        foreach ($paymentConfig::DEFAULT_PAID_FIELDS as $fieldName) {
            if ($moduleModel->getField($fieldName)) {
                return $fieldName;
            }
        }

        return $paymentConfig::DEFAULT_PAID_FIELDS[0];
    }

    /**
     * Returns the picklist fields of a module that can hold the document status.
     *
     * @param Vtiger_Module_Model $moduleModel Document module
     * @return array [field name => translated label]
     */
    public static function getStatusFields(Vtiger_Module_Model $moduleModel): array
    {
        $fields = [];

        foreach ($moduleModel->getFieldsByType('picklist') as $fieldName => $fieldModel) {
            if (in_array((int)$fieldModel->get('uitype'), [15, 16], true) && $fieldName !== 'taxtype') {
                $fields[$fieldName] = vtranslate($fieldModel->get('label'), $moduleModel->getName());
            }
        }

        return $fields;
    }

    /**
     * Returns the values of the given picklist fields with their translations.
     *
     * @param Vtiger_Module_Model $moduleModel Document module
     * @param array               $fieldNames  Picklist field names
     * @return array [field name => [value => translated label]]
     */
    public static function getPicklists(Vtiger_Module_Model $moduleModel, array $fieldNames): array
    {
        $picklists = [];

        foreach ($fieldNames as $fieldName) {
            $picklists[$fieldName] = [];

            foreach ((array)Vtiger_Util_Helper::getPickListValues($fieldName) as $value) {
                $picklists[$fieldName][$value] = vtranslate($value, $moduleModel->getName());
            }
        }

        return $picklists;
    }

    /**
     * Returns the reference fields of a module that point to organisations or vendors.
     *
     * @param Core_PaymentConfig_Helper $paymentConfig Configuration of the payment module
     * @param Vtiger_Module_Model       $moduleModel   Document module
     * @return array [field name => translated label]
     */
    public static function getReferenceFields(Core_PaymentConfig_Helper $paymentConfig, Vtiger_Module_Model $moduleModel): array
    {
        $fields = [];

        foreach ($moduleModel->getFieldsByType('reference') as $fieldName => $fieldModel) {
            $referenceList = (array)$fieldModel->getReferenceList(false, false);

            if (array_intersect($paymentConfig::RELATED_TO_MODULES, $referenceList)) {
                $fields[$fieldName] = vtranslate($fieldModel->get('label'), $moduleModel->getName());
            }
        }

        return $fields;
    }

    /**
     * Returns the amount fields of a module usable as total, paid or balance.
     *
     * @param Vtiger_Module_Model $moduleModel Document module
     * @return array [field name => translated label]
     */
    public static function getAmountFields(Vtiger_Module_Model $moduleModel): array
    {
        $fields = [];

        foreach ($moduleModel->getFieldsByType(['currency', 'double']) as $fieldName => $fieldModel) {
            $fields[$fieldName] = vtranslate($fieldModel->get('label'), $moduleModel->getName());
        }

        return $fields;
    }
}
