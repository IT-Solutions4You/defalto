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
 * Configuration of the payment modules whose records the Payments list shows.
 *
 * Phase 0 (prototype): the only finished payment module is IncomingPayment. The second source is IncomingPayment
 * again, limited to payments without a document and labelled as outgoing, so that the union of two sources can be
 * tested before OutgoingPayment exists. It also treats one field as missing to test conditions on such fields.
 */
class Payments_Sources_Helper
{
    public const MODULE = 'Payments';
    public const INCOMING = 'Incoming';
    public const OUTGOING = 'Outgoing';
    public const CONDITION_WITH_DOCUMENT = 'with_document';
    public const CONDITION_WITHOUT_DOCUMENT = 'without_document';

    /**
     * Fields sorted by another column, e.g. an amount by its value in the base currency.
     */
    public const SORT_FIELDS = [
        'paymentamount' => 'payamount_main',
    ];

    /**
     * Default columns of the All filter.
     */
    public const LIST_FIELDS = [
        'payment_no',
        'payment_name',
        'paymentamount',
        'paymentdate',
        'due_date',
        'payment_status',
        'relatedto',
        'assigned_user_id',
    ];

    /**
     * Field holding the document of a payment; it separates the two prototype sources.
     */
    public const DOCUMENT_FIELD = 'relationid';

    /**
     * Returns the configured sources keyed by source key.
     * Each source: module, direction, condition (null or a CONDITION_* constant), missing_fields.
     *
     * @return array<string, array{module: string, direction: string, condition: ?string, missing_fields: array<string>}>
     */
    public static function getSources(): array
    {
        return [
            'incoming' => [
                'module' => 'IncomingPayment',
                'direction' => self::INCOMING,
                'condition' => self::CONDITION_WITH_DOCUMENT,
                'missing_fields' => [],
            ],
            'prototype_outgoing' => [
                'module' => 'IncomingPayment',
                'direction' => self::OUTGOING,
                'condition' => self::CONDITION_WITHOUT_DOCUMENT,
                'missing_fields' => ['transactionid'],
            ],
        ];
    }

    /**
     * Returns the sources whose module is active and which the current user may see.
     *
     * @return array<string, array{module: string, direction: string, condition: ?string, missing_fields: array<string>}>
     */
    public static function getAccessibleSources(): array
    {
        $sources = [];

        foreach (self::getSources() as $sourceKey => $source) {
            if (self::isAccessibleModule($source['module'])) {
                $sources[$sourceKey] = $source;
            }
        }

        return $sources;
    }

    /**
     * Returns the source modules without duplicates, in source order.
     *
     * @return array<string>
     */
    public static function getSourceModules(): array
    {
        return array_values(array_unique(array_column(self::getSources(), 'module')));
    }

    /**
     * Returns the module whose query generator stands in for Payments, where the list framework needs one.
     *
     * @return string
     */
    public static function getPrimaryModule(): string
    {
        return self::getSourceModules()[0];
    }

    /**
     * Tells whether a payment module is active and the current user may open its list.
     *
     * @param string $moduleName Payment module name
     * @return bool
     */
    public static function isAccessibleModule(string $moduleName): bool
    {
        return vtlib_isModuleActive($moduleName) && Users_Privileges_Model::isPermitted($moduleName, 'index');
    }

    /**
     * Tells whether a field is treated as missing in a source.
     *
     * @param array  $source    Source configuration
     * @param string $fieldName Field name
     * @return bool
     */
    public static function isMissingField(array $source, string $fieldName): bool
    {
        return in_array($fieldName, $source['missing_fields'], true);
    }

    /**
     * Returns the field whose column orders the list instead of the given field.
     *
     * @param string $fieldName Field chosen for ordering
     * @return string
     */
    public static function getSortFieldName(string $fieldName): string
    {
        return self::SORT_FIELDS[$fieldName] ?? $fieldName;
    }
}
