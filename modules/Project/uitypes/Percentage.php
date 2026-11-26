<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class Project_Percentage_UIType extends Vtiger_Percentage_UIType
{
    public function getDisplayValue($value, $record = false, $recordInstance = false): string
    {
        if ($value === '' || $value === null) {
            return '';
        }

        return $value === 0 || $value === '0' ? '0 %' : parent::getDisplayValue($value, $record, $recordInstance);
    }

    public function getRequestValue(mixed $fieldValue): mixed
    {
        $value = parent::getRequestValue($fieldValue);

        if (!Project_ProgressCalculator_Model::isValidOptionalIntegerPercentage($value)) {
            $field = $this->getFieldModel();
            throw new Exception(vtranslate($field->get('label'), $field->getModuleName()) . ': ' . vJSTranslate('JS_PLEASE_ENTER_VALID_VALUE', 'Core'));
        }

        return $value === null || $value === '' ? $value : (int)$value;
    }
}
