<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/** Presentation contract for the shared advanced-condition templates. */
class Core_FilterEditor_Model extends Vtiger_Base_Model
{
    public static function getInstance(string $sourceModule, string $translationModule = '', string $columnNameApi = 'getCustomViewColumnName'): self
    {
        $instance = new static();
        $instance->set('source_module', $sourceModule);
        $instance->set('translation_module', $translationModule ?: $sourceModule);
        $instance->set('column_name_api', $columnNameApi);
        return $instance;
    }

    public function getSourceModule(): string
    {
        return $this->get('source_module');
    }

    public function getTranslationModule(): string
    {
        return $this->get('translation_module');
    }

    public function hasDefaultCondition(): bool
    {
        return true;
    }

    public function getColumnName(Vtiger_Field_Model $field): string
    {
        $method = $this->get('column_name_api');
        return $field->$method();
    }

    public function getOperators(Vtiger_Field_Model $field): array
    {
        return Core_FilterOperator_Model::getForField($field, $this->getSourceModule());
    }

    public function getFieldInfo(Vtiger_Field_Model $field): array
    {
        $info = $field->getFieldInfo();

        if ($this->hasUserReferenceOptions($field)) {
            $values = [];

            foreach (Users_Record_Model::getCurrentUserModel()->getAccessibleUsers() as $name) {
                $values[$name] = $name;
            }

            $info['picklistvalues'] = $values;
            $info['type'] = 'picklist';
        }

        return $info;
    }

    protected function hasUserReferenceOptions(Vtiger_Field_Model $field): bool
    {
        if ($field->getFieldDataType() !== 'reference') {
            return false;
        }

        $references = $field->getWebserviceFieldObject()->getReferenceList();
        return is_array($references) && in_array('Users', $references, true);
    }
}
