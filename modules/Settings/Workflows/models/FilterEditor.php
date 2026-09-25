<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/** Workflow metadata for the shared filter UI; execution remains workflow-owned. */
class Settings_Workflows_FilterEditor_Model extends Core_FilterEditor_Model
{
    public function getTranslationModule(): string
    {
        return 'Settings:Workflows';
    }

    public function hasDefaultCondition(): bool
    {
        return false;
    }

    public function getColumnName(Vtiger_Field_Model $field): string
    {
        return $field->getWorkFlowFilterColumnName();
    }

    public function getOperators(Vtiger_Field_Model $field): array
    {
        $info = $this->getFieldInfo($field);
        $mapping = Settings_Workflows_Field_Model::getAdvancedFilterOpsByFieldType();
        $labels = Settings_Workflows_Field_Model::getAdvancedFilterOptions();
        $operators = $mapping[$info['type']] ?? [];

        if ($this->isRelatedField($field)) {
            $operators = array_diff($operators, ['has changed', 'has been set or changed',
                'has been set or changed to', 'has been set or changed from']);
        }

        $result = [];

        foreach ($operators as $operator) {
            $result[$operator] = vtranslate($labels[$operator] ?? $operator, $this->getTranslationModule());
        }

        return $result;
    }

    public function getFieldInfo(Vtiger_Field_Model $field): array
    {
        // Workflow owner/reference values retain IDs rather than list-filter names.
        $info = $field->getFieldInfo();

        if ($field->getModule()->get('name') === 'Documents') {
            if ($field->getName() === 'filelocationtype') {
                $info['type'] = 'picklist';
                $info['picklistvalues'] = $field->getFileLocationType();
            } elseif ($field->getName() === 'folderid') {
                $info['type'] = 'picklist';
                $info['picklistvalues'] = $field->getDocumentFolders();
            } elseif ($field->getName() === 'filename') {
                $info['type'] = 'string';
            }
        }

        return $info;
    }

    protected function isRelatedField(Vtiger_Field_Model $field): bool
    {
        return (bool)preg_match('/\((\w+)\) (\w+)/', $this->getColumnName($field));
    }
}
