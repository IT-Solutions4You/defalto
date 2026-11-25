<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class Core_Checklist_UIType extends Vtiger_Base_UIType
{
    public function getScriptNames(): array
    {
        return ['modules.Core.resources.Checklist'];
    }

    public function getTemplateName()
    {
        return 'uitypes/Checklist.tpl';
    }

    public function getDetailViewTemplateName()
    {
        return 'uitypes/ChecklistDetailView.tpl';
    }

    public function getRequestValue(mixed $fieldValue): mixed
    {
        if ($fieldValue === null) {
            return null;
        }

        if (!is_string($fieldValue)) {
            throw new Exception(vtranslate('LBL_CHECKLIST_INVALID_TEXT', 'Core'));
        }

        $model = Core_Checklist_Model::getInstance();
        $lines = $model->getLines($fieldValue);

        foreach ($lines as $line) {
            $model->validateText($line);
        }

        $text = implode("\n", $lines);
        $model->validateFieldLength($text);

        return Vtiger_Util_Helper::validateFieldValue($text, $this->getFieldModel());
    }

    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        return $value;
    }

    public function getChecklistContext(int $recordId): array
    {
        $model = Core_Checklist_Model::getInstance();
        $field = $this->getFieldModel();
        $fieldId = (int)$field->getId();

        if (!$recordId) {
            $items = [];

            foreach ($model->getLines(decode_html((string)$field->get('fieldvalue'))) as $index => $line) {
                $items[] = ['id' => $index + 1, 'text' => $line, 'checked' => false];
            }

            return [
                'editable' => $model->isNewFieldEditable($field),
                'checklist' => [
                    'id' => 0, 'title' => vtranslate($field->get('label'), $field->getModuleName()),
                    'items' => $items, 'revision' => hash('sha256', ''), 'completed' => 0, 'total' => count($items), 'percent' => 0,
                ],
            ];
        }

        $context = $model->getContext($recordId, $fieldId);
        $context['checklist'] = $model->getChecklist($recordId, $fieldId);

        return $context;
    }
}
