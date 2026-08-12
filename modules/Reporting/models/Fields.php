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

        if (!empty(self::$moduleLabels[$moduleName])) {
            return self::$moduleLabels[$moduleName];
        }

        $options = [];
        $module = Vtiger_Module_Model::getInstance($moduleName);

        if (!$module) {
            return [];
        }

        $fields = array_merge($module->getFields(), self::getCustomFields());

        /**
         * @var Vtiger_Field_Model $field
         * @var Vtiger_Field_Model $referenceField
         */
        foreach ($fields as $field) {
            $options[$field->get('name')] = self::getTranslatedFieldLabel($field);
        }

        $fields = $module->getFieldsByType(['reference', 'owner']);

        foreach ($fields as $field) {
            $fieldName = $field->get('name');
            $fieldLabel = $field->get('label');
            $referenceModuleNames = self::getFieldModules($field);

            foreach ($referenceModuleNames as $referenceModuleName) {
                $reference = Vtiger_Module_Model::getInstance($referenceModuleName);

                if (!$reference) {
                    continue;
                }

                $referenceFields = array_merge($reference->getFields(), self::getCustomFields());

                foreach ($referenceFields as $referenceField) {
                    $referenceFieldName = $referenceField->get('name');

                    $options[implode(':', [$fieldName, $referenceModuleName, $referenceFieldName])] =
                        vtranslate($fieldLabel, $moduleName) . ' - ' . self::getTranslatedFieldLabel($referenceField);
                }
            }
        }

        self::$moduleLabels[$moduleName] = $options;

        return $options;
    }

    public static function getAllViewFields(string $moduleName): array
    {
        if ('' === $moduleName) {
            return [];
        }

        $module = Vtiger_Module_Model::getInstance($moduleName);

        if (!$module) {
            return [];
        }

        $availableFields = $module->getFields();
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

        foreach ($module->getNameFields() as $columnName) {
            $field = $module->getFieldByColumn($columnName);

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

    protected static function getTranslatedFieldLabel(Vtiger_Field_Model $field): string
    {
        $moduleName = self::isCustomField($field->get('name')) ? 'Reporting' : $field->getModuleName();

        return vtranslate($field->get('label'), $moduleName);
    }

    public static function getFieldVariables($moduleName): array
    {
        if (empty($moduleName)) {
            return [];
        }

        if (!empty(self::$moduleVariables[$moduleName])) {
            return self::$moduleVariables[$moduleName];
        }

        $options = [];
        $module = Vtiger_Module_Model::getInstance($moduleName);

        if (!$module) {
            return [];
        }

        $fields = array_merge($module->getFields(), self::getCustomFields());

        /**
         * @var Vtiger_Field_Model $field
         * @var Vtiger_Field_Model $referenceField
         */
        foreach ($fields as $field) {
            $options['default'][$field->get('name')] = vtranslate(($field->block->label ?? ''), $field->getModuleName()) . '##' . self::getTranslatedFieldLabel($field);
        }

        $fields = $module->getFieldsByType(['reference', 'owner']);

        foreach ($fields as $field) {
            $fieldName = $field->get('name');
            $fieldLabel = $field->get('label');
            $referenceModuleNames = Reporting_Fields_Model::getFieldModules($field);

            foreach ($referenceModuleNames as $referenceModuleName) {
                $reference = Vtiger_Module_Model::getInstance($referenceModuleName);

                if (!$reference) {
                    continue;
                }

                $referenceFields = array_merge($reference->getFields(), self::getCustomFields());

                foreach ($referenceFields as $referenceField) {
                    $referenceFieldName = $referenceField->get('name');

                    $options[implode(':', [$fieldName, $referenceModuleName])][implode(':', [$fieldName, $referenceModuleName, $referenceFieldName])] = vtranslate(
                            ($referenceField->block->label ?? ''),
                            $referenceField->getModuleName()
                        ) . '##' . self::getTranslatedFieldLabel($referenceField);
                }
            }
        }

        self::$moduleVariables[$moduleName] = $options;

        return $options;
    }

    public static function getCurrencyFields(string $moduleName): array
    {
        if ('' === $moduleName) {
            return [];
        }

        if (isset(self::$moduleCurrencyFields[$moduleName])) {
            return self::$moduleCurrencyFields[$moduleName];
        }

        $currencyFields = [];
        $module = Vtiger_Module_Model::getInstance($moduleName);

        if (!$module) {
            return [];
        }

        foreach ($module->getFields() as $field) {
            if ('currency' === $field->getFieldDataType()) {
                $currencyFields[] = $field->get('name');
            }
        }

        foreach ($module->getFieldsByType(['reference', 'owner']) as $field) {
            foreach (self::getFieldModules($field) as $referenceModuleName) {
                $referenceModule = Vtiger_Module_Model::getInstance($referenceModuleName);

                if (!$referenceModule) {
                    continue;
                }

                foreach ($referenceModule->getFields() as $referenceField) {
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

        return self::$moduleCurrencyFields[$moduleName] = array_values(array_unique($currencyFields));
    }

    public static function getFieldDataTypes(string $moduleName): array
    {
        if ('' === $moduleName) {
            return [];
        }

        if (isset(self::$moduleFieldDataTypes[$moduleName])) {
            return self::$moduleFieldDataTypes[$moduleName];
        }

        $module = Vtiger_Module_Model::getInstance($moduleName);

        if (!$module) {
            return [];
        }

        $dataTypes = [];

        foreach ($module->getFields() as $field) {
            $dataTypes[$field->get('name')] = (string)$field->getFieldDataType();
        }

        foreach (array_keys(self::$customFields) as $fieldName) {
            $dataTypes[$fieldName] = 'string';
        }

        foreach ($module->getFieldsByType(['reference', 'owner']) as $field) {
            foreach (self::getFieldModules($field) as $referenceModuleName) {
                $referenceModule = Vtiger_Module_Model::getInstance($referenceModuleName);

                if (!$referenceModule) {
                    continue;
                }

                foreach ($referenceModule->getFields() as $referenceField) {
                    $dataTypes[implode(':', [
                        $field->get('name'),
                        $referenceModuleName,
                        $referenceField->get('name'),
                    ])] = (string)$referenceField->getFieldDataType();
                }

                foreach (array_keys(self::$customFields) as $referenceFieldName) {
                    $dataTypes[implode(':', [
                        $field->get('name'),
                        $referenceModuleName,
                        $referenceFieldName,
                    ])] = 'string';
                }
            }
        }

        return self::$moduleFieldDataTypes[$moduleName] = $dataTypes;
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
