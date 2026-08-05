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
        if (!empty(self::$moduleLabels[$moduleName])) {
            return self::$moduleLabels[$moduleName];
        }

        $options = [];
        $module = Vtiger_Module_Model::getInstance($moduleName);
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
        if (!empty(self::$moduleVariables[$moduleName])) {
            return self::$moduleVariables[$moduleName];
        }

        $options = [];
        $module = Vtiger_Module_Model::getInstance($moduleName);
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
