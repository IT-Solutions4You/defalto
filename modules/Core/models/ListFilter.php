<?php
/**
 * Shared list filter presentation; query execution remains owned by Vtiger.
 */
class Core_ListFilter_Model extends Vtiger_Base_Model
{
    public function getFieldGroups(Vtiger_Module_Model $module, array $fields): array
    {
        $groups = [];

        foreach ($fields as $name => $field) {
            if (!isset($field['groupLabel'])) {
                continue;
            }

            $key = $field['groupLabel'];
            $groups[$key]['label'] = $field['groupLabel'];
            $groups[$key]['fields'][$name] = $field;
        }

        return array_values($groups);
    }

    public function getFields(Vtiger_Module_Model $module): array
    {
        $fields = [];
        $structure = Vtiger_RecordStructure_Model::getInstanceForModule($module, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_FILTER)->getStructure();

        foreach ($structure as $groupLabel => $groupFields) {
            foreach ($groupFields as $name => $field) {
                if (!$this->isFilterField($field)) {
                    continue;
                }

                $sourceModule = $module->getName();
                $sourceField = $name;
                $fieldModule = $module;
                $fieldLabel = $field->get('label');

                if (preg_match('/^\((\w+) ; \((\w+)\) (\w+)\)$/', $name, $matches)) {
                    $sourceField = $matches[1];
                    $fieldModule = Vtiger_Module_Model::getInstance($matches[2]);
                    $fieldLabel = $field->get('label');
                }

                $metadata = $this->getFieldMetadata($field, $fieldModule, $name);
                $metadata['label'] = vtranslate($fieldLabel, $fieldModule->getName());
                $metadata['groupLabel'] = $groupLabel;
                $metadata['sourceModule'] = $sourceModule;
                $metadata['sourceField'] = $sourceField;
                $fields[$name] = $metadata;
            }
        }

        return $fields;
    }

    public function getFieldMetadata(Vtiger_Field_Model $field, Vtiger_Module_Model $module, string $name): array
    {
        $info = $field->getFieldInfo();
        $type = $field->getFieldDataType();
        $operatorsByType = Vtiger_Field_Model::getAdvancedFilterOpsByFieldType();
        $operatorLabels = Vtiger_Field_Model::getAdvancedFilterOptions();
        $operators = $operatorsByType[$field->getFieldType()] ?? $operatorsByType['V'];
        $operators = array_values(array_intersect($operators, ['e', 'n', 's', 'ew', 'c', 'k', 'l', 'g', 'm', 'h', 'b', 'a', 'bw', 'y', 'ny']));
        $values = $info['picklistvalues'] ?? [];
        $valueGroups = [];
        $referenceModules = [];

        if ($type === 'reference') {
            foreach ($field->getReferenceList() as $referenceModule) {
                $referenceModules[$referenceModule] = vtranslate($referenceModule, $referenceModule);
            }
            $operators = $operatorsByType['V'];
        } elseif ($type === 'owner') {
            $values = [];
            foreach ($info['picklistvalues'] as $groupLabel => $owners) {
                $groupValues = [];
                foreach ($owners as $owner) {
                    $owner = decode_html($owner);
                    $values[$owner] = $owner;
                    $groupValues[$owner] = $owner;
                }
                if ($groupValues) {
                    $valueGroups[] = ['label' => decode_html($groupLabel), 'values' => $groupValues];
                }
            }
        } elseif ($type === 'ownergroup') {
            $values = array_combine(array_map('decode_html', array_values($values)), array_map('decode_html', array_values($values)));
        } elseif ($type === 'boolean') {
            $values = ['0' => vtranslate('LBL_NO'), '1' => vtranslate('LBL_YES')];
        } elseif ($type === 'currencyList') {
            $values = [];
            foreach ($info['currencyList'] ?? [] as $currencyId => $currency) {
                $values[$currencyId] = vtranslate($currency, $module->getName());
            }
            $operators = ['e', 'n', 'y', 'ny'];
        } elseif ($type === 'documentsFolder') {
            $values = [];
            foreach ($info['documentFolders'] ?? [] as $folder) {
                $values[$folder] = vtranslate($folder, $module->getName());
            }
        } elseif ($type === 'datetime') {
            $operators = ['bw', 'y', 'ny'];
        } elseif ($type === 'time') {
            $operators = array_values(array_diff($operators, ['bw']));
        } elseif (!empty($values)) {
            $operators = ['e', 'n', 'y', 'ny'];
        }

        $labels = [];
        foreach ($operators as $operator) {
            $labels[$operator] = vtranslate($operatorLabels[$operator]);
        }

        return [
            'label' => vtranslate($field->get('label'), $module->getName()),
            'type' => $type,
            'column' => decode_html($field->getCustomViewColumnName()),
            'operators' => $labels,
            'values' => $values,
            'valueGroups' => $valueGroups,
            'referenceModules' => $referenceModules,
        ];
    }

    public function isFilterField(Vtiger_Field_Model $field): bool
    {
        return $field->isViewableInFilterView() && in_array((int)$field->getDisplayType(), [
            Vtiger_Field_Model::DISPLAYTYPE_ALL,
            Vtiger_Field_Model::DISPLAYTYPE_DETAIL_AND_LIST,
            Vtiger_Field_Model::DISPLAYTYPE_LIST,
        ], true);
    }
}
