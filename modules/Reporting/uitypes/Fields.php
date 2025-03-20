<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Reporting_Fields_UIType extends Core_Data_UIType
{
    /**
     * @throws Exception
     */
    public function getFields($moduleName): array
    {
        $options = [];

        if (empty($moduleName)) {
            return $options;
        }

        $module = Vtiger_Module_Model::getInstance($moduleName);
        $fields = $module->getFields();

        /**
         * @var Vtiger_Field_Model $field
         * @var Vtiger_Field_Model $referenceField
         */
        foreach ($fields as $field) {
            $options[$field->get('name')] = vtranslate($field->get('label'), $field->getModuleName());
        }

        $fields = $module->getFieldsByType(['reference', 'owner']);

        foreach ($fields as $field) {
            $fieldName = $field->get('name');
            $fieldLabel = $field->get('label');
            $referenceModuleNames = Reporting_Fields_Model::getFieldModules($field);

            foreach ($referenceModuleNames as $referenceModuleName) {
                $reference = Vtiger_Module_Model::getInstance($referenceModuleName);
                $referenceFields = $reference->getFields();

                foreach ($referenceFields as $referenceField) {
                    $referenceFieldName = $referenceField->get('name');
                    $referenceFieldLabel = $referenceField->get('label');

                    $options[implode(':', [$fieldName, $referenceModuleName, $referenceFieldName])] = sprintf(
                        '%s (%s) %s',
                        vtranslate($fieldLabel, $field->getModuleName()),
                        vtranslate($referenceModuleName, $referenceModuleName),
                        vtranslate($referenceFieldLabel, $referenceModuleName),
                    );
                }
            }
        }

        return $options;
    }

    public function getFieldOptions($moduleName): array
    {
        $options = [];

        if (empty($moduleName)) {
            return $options;
        }

        return Reporting_Fields_Model::getFieldVariables($moduleName);
    }

    public function getLabelOptions(string $moduleName, array $labels): array
    {
        $options = [];

        if (empty($moduleName)) {
            return $options;
        }

        return array_merge(Reporting_Fields_Model::getFieldLabels($moduleName), array_filter($labels));
    }

    /**
     * @throws Exception
     */
    public function getModuleOptions($moduleName): array
    {
        $options = [
            '' => vtranslate($moduleName, $moduleName),
        ];

        if (empty($moduleName)) {
            return $options;
        }

        $module = Vtiger_Module_Model::getInstance($moduleName);
        $fields = $module->getFieldsByType(['reference', 'owner']);

        /**
         * @var Vtiger_Field_Model $field
         * @var Vtiger_Field_Model $referenceField
         */
        foreach ($fields as $field) {
            $modules = Reporting_Fields_Model::getFieldModules($field);

            foreach ($modules as $module) {
                $options[sprintf('%s:%s', $field->get('name'), $module)] = sprintf('%s (%s)', vtranslate($field->get('label'), $moduleName), vtranslate($module, $module));
            }
        }

        return $options;
    }

    public function getSelectedValue(mixed $fieldValue, $moduleName = ''): array
    {
        $fields = parent::getSelectedValue($fieldValue);

        if (empty($fields) && !empty($moduleName)) {
            $module = Vtiger_Module_Model::getInstance($moduleName);
            $fields = $module->getNameFields();
        }

        return $fields;
    }

    public function getLabelForValue($value, $options)
    {
        return $options[$value];
    }
}