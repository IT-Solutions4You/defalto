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
 * Configuration of the payment modules whose records the Payments list shows: IncomingPayment and OutgoingPayment.
 * A source may limit its module by a condition (CONDITION_* constants) and treat fields as missing; both are unused
 * now and stay for further payment modules.
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
        'payment_amount' => 'payment_amount_base',
    ];

    /**
     * Default columns of the All filter.
     */
    public const LIST_FIELDS = [
        'payment_no',
        'payment_name',
        'payment_amount',
        'payment_date',
        'due_date',
        'payment_status',
        'related_to',
        'assigned_user_id',
    ];

    /**
     * Field holding the document of a payment; it separates the two prototype sources.
     */
    public const DOCUMENT_FIELD = 'reference_id';

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
                'condition' => null,
                'missing_fields' => [],
            ],
            'outgoing' => [
                'module' => 'OutgoingPayment',
                'direction' => self::OUTGOING,
                'condition' => null,
                'missing_fields' => [],
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
     * Returns the module whose query generator stands in for Payments, where the list framework needs one:
     * the first source module the current user may open, otherwise the first active one, otherwise the first
     * installed one. The payment modules are installed separately, so any of them may be missing.
     *
     * @return string
     */
    public static function getPrimaryModule(): string
    {
        $modules = self::getSourceModules();

        foreach ($modules as $moduleName) {
            if (self::isAccessibleModule($moduleName)) {
                return $moduleName;
            }
        }

        foreach ($modules as $moduleName) {
            if (vtlib_isModuleActive($moduleName)) {
                return $moduleName;
            }
        }

        foreach ($modules as $moduleName) {
            if (getTabid($moduleName)) {
                return $moduleName;
            }
        }

        return $modules[0];
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
