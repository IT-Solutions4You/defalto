<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Project_Percentage_UIType extends Core_Progress_UIType
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
