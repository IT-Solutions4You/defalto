<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Grouping_UIType extends Vtiger_Base_UIType
{
    public function getTemplateName(): string
    {
        return 'uitypes/Grouping.tpl';
    }

    /**
     * @throws Exception
     */
    public function getFieldOptions(string $moduleName, array $labels = []): array
    {
        $fieldOptions = (new Reporting_Fields_UIType())->getFields($moduleName);

        return array_replace($fieldOptions, array_intersect_key(array_filter($labels), $fieldOptions));
    }

    public function getDateFieldNames(string $moduleName): array
    {
        if ('' === $moduleName) {
            return [];
        }

        $module = Vtiger_Module_Model::getInstance($moduleName);

        if (!$module) {
            return [];
        }

        $dateFields = [];

        foreach ($module->getFields() as $field) {
            if (in_array($field->getFieldDataType(), ['date', 'datetime'], true)) {
                $dateFields[] = $field->get('name');
            }
        }

        foreach ($module->getFieldsByType(['reference', 'owner']) as $field) {
            foreach (Reporting_Fields_Model::getFieldModules($field) as $referenceModuleName) {
                $referenceModule = Vtiger_Module_Model::getInstance($referenceModuleName);

                if (!$referenceModule) {
                    continue;
                }

                foreach ($referenceModule->getFields() as $referenceField) {
                    if (in_array($referenceField->getFieldDataType(), ['date', 'datetime'], true)) {
                        $dateFields[] = implode(':', [
                            $field->get('name'),
                            $referenceModuleName,
                            $referenceField->get('name'),
                        ]);
                    }
                }
            }
        }

        return array_values(array_unique($dateFields));
    }

    public function getDateGroupingIntervals(): array
    {
        return [
            '' => vtranslate('LBL_DATE_GROUP_EXACT', 'Reporting'),
            'day' => vtranslate('LBL_DATE_UNIT_DAY'),
            'week' => vtranslate('LBL_DATE_UNIT_WEEK'),
            'month' => vtranslate('LBL_DATE_UNIT_MONTH'),
            'quarter' => vtranslate('LBL_DATE_UNIT_QUARTER'),
            'year' => vtranslate('LBL_DATE_UNIT_YEAR'),
        ];
    }

    public function getRequestValue(mixed $fieldValue): mixed
    {
        if (null === $fieldValue) {
            return null;
        }

        return json_encode(Reporting_Grouping_Model::getConfigurations($fieldValue));
    }

    public function getSelectedValues(mixed $fieldValue): array
    {
        return Reporting_Grouping_Model::getFields($fieldValue);
    }

    public function getSelectedConfigurations(mixed $fieldValue): array
    {
        return Reporting_Grouping_Model::getConfigurations($fieldValue);
    }

    public function getSelectedIntervals(mixed $fieldValue): array
    {
        return Reporting_Grouping_Model::getIntervals($fieldValue);
    }

    /**
     * @throws Exception
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        if (empty($value) || !$recordInstance) {
            return $value;
        }

        $options = $this->getFieldOptions($recordInstance->getPrimaryModule());
        $labels = [];

        foreach (Reporting_Grouping_Model::getConfigurations($value) as $configuration) {
            $fieldName = $configuration['field'];
            $label = $options[$fieldName] ?? $fieldName;

            if ('' !== $configuration['interval']) {
                $intervals = $this->getDateGroupingIntervals();
                $label .= ' (' . ($intervals[$configuration['interval']] ?? $configuration['interval']) . ')';
            }

            $labels[] = $label;
        }

        return implode(', ', $labels);
    }
}
