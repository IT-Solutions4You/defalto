<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Fields_Model extends Vtiger_Base_Model
{
    public static array $moduleLabels = [];
    public static array $moduleVariables = [];
    public static array $moduleCurrencyFields = [];
    public static array $moduleFieldDataTypes = [];

    public static array $customFields = [
        'id' => 'Record Id',
        'label' => 'Record Label',
    ];

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public static function isCustomField(string $fieldName): bool
    {
        return !empty(self::$customFields[$fieldName]);
    }

    /**
     * @param object $record
     * @param string $fieldName
     * @param mixed  $fieldValue
     *
     * @return mixed
     */
    public static function getCustomFieldValue(object $record, string $fieldName, mixed $fieldValue): mixed
    {
        return $record->get($fieldName);
    }

    public static function getFieldLabels(string $moduleName): array
    {
        if ('' === $moduleName) {
            return [];
        }

        $cacheKey = self::getPermissionCacheKey($moduleName);

        if (isset(self::$moduleLabels[$cacheKey])) {
            return self::$moduleLabels[$cacheKey];
        }

        $options = [];
        $module = Vtiger_Module_Model::getInstance($moduleName);

        if (!$module || !self::isModuleViewable($module)) {
            return [];
        }

        $fields = array_merge(self::getViewableFields($module), self::getCustomFieldsForModule($module));

        /**
         * @var Vtiger_Field_Model $field
         * @var Vtiger_Field_Model $referenceField
         */
        foreach ($fields as $field) {
            $options[$field->get('name')] = self::getTranslatedFieldLabel($field);
        }

        $fields = self::getViewableFieldsByType($module, ['reference', 'owner']);

        foreach ($fields as $field) {
            $fieldName = $field->get('name');
            $fieldLabel = $field->get('label');
            $referenceModuleNames = self::getFieldModules($field);

            foreach ($referenceModuleNames as $referenceModuleName) {
                $reference = Vtiger_Module_Model::getInstance($referenceModuleName);

                if (!$reference) {
                    continue;
                }

                $referenceFields = self::isModuleViewable($reference)
                    ? array_merge(self::getViewableFields($reference), self::getCustomFieldsForModule($reference))
                    : [];

                foreach ($referenceFields as $referenceField) {
                    $referenceFieldName = $referenceField->get('name');

                    $options[implode(':', [$fieldName, $referenceModuleName, $referenceFieldName])] =
                        vtranslate($fieldLabel, $moduleName) . ' - ' . self::getTranslatedFieldLabel($referenceField);
                }
            }
        }

        self::$moduleLabels[$cacheKey] = $options;

        return $options;
    }

    public static function getAllViewFields(string $moduleName): array
    {
        if ('' === $moduleName) {
            return [];
        }

        $module = Vtiger_Module_Model::getInstance($moduleName);

        if (!$module || !self::isModuleViewable($module)) {
            return [];
        }

        $availableFields = self::getViewableFields($module);
        $allView = CustomView_Record_Model::getAllFilterByModule($moduleName);
        $fields = [];

        foreach ($allView->getSelectedFields() as $columnName) {
            $columnParts = explode(':', decode_html((string)$columnName));
            $fieldName = $columnParts[2] ?? '';

            if ('' !== $fieldName && isset($availableFields[$fieldName]) && !in_array($fieldName, $fields, true)) {
                $fields[] = $fieldName;
            }
        }

        if (!empty($fields)) {
            return $fields;
        }

        foreach ($module->getNameFields() as $fieldName) {
            $field = $availableFields[$fieldName] ?? false;

            if ($field && !in_array($field->getName(), $fields, true)) {
                $fields[] = $field->getName();
            }
        }

        return $fields;
    }

    public static function getCustomFields(): array
    {
        $fieldNames = self::$customFields;
        $fields = [];

        foreach ($fieldNames as $fieldName => $fieldLabel) {
            $field = new Vtiger_Field_Model();
            $field->set('name', $fieldName);
            $field->set('label', $fieldLabel);

            $fields[$fieldName] = $field;
        }

        return $fields;
    }

    public static function getCustomFieldsForModule(Vtiger_Module_Model $module): array
    {
        if (!self::isModuleViewable($module)) {
            return [];
        }

        $fields = self::getCustomFields();

        if (!self::isRecordLabelViewable($module)) {
            unset($fields['label']);
        }

        return $fields;
    }

    protected static function isRecordLabelViewable(Vtiger_Module_Model $module): bool
    {
        $fieldNames = $module->getNameFields();

        if (empty($fieldNames)) {
            return false;
        }

        foreach ($fieldNames as $fieldName) {
            $field = $module->getField($fieldName);

            if (!$field || !$field->isViewable()) {
                return false;
            }
        }

        return true;
    }

    public static function getViewableFields(Vtiger_Module_Model $module): array
    {
        if (!self::isModuleViewable($module)) {
            return [];
        }

        return array_filter(
            $module->getFields(),
            static fn(Vtiger_Field_Model $field): bool => $field->isViewable()
        );
    }

    public static function isModuleViewable(Vtiger_Module_Model $module): bool
    {
        return Users_Privileges_Model::isPermitted($module->getName(), 'DetailView');
    }

    public static function getViewableFieldsByType(Vtiger_Module_Model $module, array|string $types): array
    {
        $types = is_array($types) ? $types : [$types];

        return array_filter(
            self::getViewableFields($module),
            static fn(Vtiger_Field_Model $field): bool => in_array($field->getFieldDataType(), $types, true)
        );
    }

    protected static function getPermissionCacheKey(string $moduleName): string
    {
        return Users_Record_Model::getCurrentUserModel()->getId() . ':' . $moduleName;
    }

    public static function getField(Vtiger_Module_Model $module, string $fieldName): Vtiger_Field_Model|bool
    {
        if (!self::isModuleViewable($module)) {
            return false;
        }

        if (self::isCustomField($fieldName)) {
            return self::getCustomFieldsForModule($module)[$fieldName] ?? false;
        }

        $field = $module->getField($fieldName);

        return $field && $field->isViewable() ? $field : false;
    }

    public static function getTranslatedFieldLabel(Vtiger_Field_Model $field): string
    {
        $moduleName = self::isCustomField($field->get('name')) ? 'Reporting' : $field->getModuleName();

        return vtranslate($field->get('label'), $moduleName);
    }

    public static function getFieldVariables($moduleName): array
    {
        if (empty($moduleName)) {
            return [];
        }

        $cacheKey = self::getPermissionCacheKey($moduleName);

        if (isset(self::$moduleVariables[$cacheKey])) {
            return self::$moduleVariables[$cacheKey];
        }

        $options = [];
        $module = Vtiger_Module_Model::getInstance($moduleName);

        if (!$module || !self::isModuleViewable($module)) {
            return [];
        }

        $fields = array_merge(self::getViewableFields($module), self::getCustomFieldsForModule($module));

        /**
         * @var Vtiger_Field_Model $field
         * @var Vtiger_Field_Model $referenceField
         */
        foreach ($fields as $field) {
            $options['default'][$field->get('name')] = vtranslate(($field->block->label ?? ''), $field->getModuleName()) . '##' . self::getTranslatedFieldLabel($field);
        }

        $fields = self::getViewableFieldsByType($module, ['reference', 'owner']);

        foreach ($fields as $field) {
            $fieldName = $field->get('name');
            $fieldLabel = $field->get('label');
            $referenceModuleNames = Reporting_Fields_Model::getFieldModules($field);

            foreach ($referenceModuleNames as $referenceModuleName) {
                $reference = Vtiger_Module_Model::getInstance($referenceModuleName);

                if (!$reference) {
                    continue;
                }

                $referenceFields = self::isModuleViewable($reference)
                    ? array_merge(self::getViewableFields($reference), self::getCustomFieldsForModule($reference))
                    : [];

                foreach ($referenceFields as $referenceField) {
                    $referenceFieldName = $referenceField->get('name');

                    $options[implode(':', [$fieldName, $referenceModuleName])][implode(':', [$fieldName, $referenceModuleName, $referenceFieldName])] = vtranslate(
                            ($referenceField->block->label ?? ''),
                            $referenceField->getModuleName()
                        ) . '##' . self::getTranslatedFieldLabel($referenceField);
                }
            }
        }

        self::$moduleVariables[$cacheKey] = $options;

        return $options;
    }

    public static function getCurrencyFields(string $moduleName): array
    {
        if ('' === $moduleName) {
            return [];
        }

        $cacheKey = self::getPermissionCacheKey($moduleName);

        if (isset(self::$moduleCurrencyFields[$cacheKey])) {
            return self::$moduleCurrencyFields[$cacheKey];
        }

        $currencyFields = [];
        $module = Vtiger_Module_Model::getInstance($moduleName);

        if (!$module || !self::isModuleViewable($module)) {
            return [];
        }

        foreach (self::getViewableFields($module) as $field) {
            if ('currency' === $field->getFieldDataType()) {
                $currencyFields[] = $field->get('name');
            }
        }

        foreach (self::getViewableFieldsByType($module, ['reference', 'owner']) as $field) {
            foreach (self::getFieldModules($field) as $referenceModuleName) {
                $referenceModule = Vtiger_Module_Model::getInstance($referenceModuleName);

                if (!$referenceModule) {
                    continue;
                }

                foreach (self::getViewableFields($referenceModule) as $referenceField) {
                    if ('currency' === $referenceField->getFieldDataType()) {
                        $currencyFields[] = implode(':', [
                            $field->get('name'),
                            $referenceModuleName,
                            $referenceField->get('name'),
                        ]);
                    }
                }
            }
        }

        return self::$moduleCurrencyFields[$cacheKey] = array_values(array_unique($currencyFields));
    }

    public static function getFieldDataTypes(string $moduleName): array
    {
        if ('' === $moduleName) {
            return [];
        }

        $cacheKey = self::getPermissionCacheKey($moduleName);

        if (isset(self::$moduleFieldDataTypes[$cacheKey])) {
            return self::$moduleFieldDataTypes[$cacheKey];
        }

        $module = Vtiger_Module_Model::getInstance($moduleName);

        if (!$module || !self::isModuleViewable($module)) {
            return [];
        }

        $dataTypes = [];

        foreach (self::getViewableFields($module) as $field) {
            $dataTypes[$field->get('name')] = (string)$field->getFieldDataType();
        }

        foreach (array_keys(self::getCustomFieldsForModule($module)) as $fieldName) {
            $dataTypes[$fieldName] = 'string';
        }

        foreach (self::getViewableFieldsByType($module, ['reference', 'owner']) as $field) {
            foreach (self::getFieldModules($field) as $referenceModuleName) {
                $referenceModule = Vtiger_Module_Model::getInstance($referenceModuleName);

                if (!$referenceModule) {
                    continue;
                }

                foreach (self::getViewableFields($referenceModule) as $referenceField) {
                    $dataTypes[implode(':', [
                        $field->get('name'),
                        $referenceModuleName,
                        $referenceField->get('name'),
                    ])] = (string)$referenceField->getFieldDataType();
                }

                foreach (array_keys(self::getCustomFieldsForModule($referenceModule)) as $referenceFieldName) {
                    $dataTypes[implode(':', [
                        $field->get('name'),
                        $referenceModuleName,
                        $referenceFieldName,
                    ])] = 'string';
                }
            }
        }

        return self::$moduleFieldDataTypes[$cacheKey] = $dataTypes;
    }

    public static function getFieldsByDataTypes(string $moduleName, array $dataTypes): array
    {
        return array_keys(array_filter(
            self::getFieldDataTypes($moduleName),
            static fn(string $dataType): bool => in_array($dataType, $dataTypes, true)
        ));
    }

    public static function getFieldModules($field)
    {
        if ('owner' === $field->getFieldDataType()) {
            $modules = ['Users'];
        } else {
            $modules = $field->getReferenceList();
        }

        return $modules;
    }
}
