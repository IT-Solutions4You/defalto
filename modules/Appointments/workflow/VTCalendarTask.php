<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'modules/com_vtiger_workflow/VTTaskManager.inc';
require_once 'modules/com_vtiger_workflow/VTEntityCache.inc';
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';
require_once 'modules/com_vtiger_workflow/VTSimpleTemplate.inc';

class VTCalendarTask extends VTTask
{
    public string $assigned_to = '';
    public string $calendar_status = '';
    public string $calendar_type = '';
    public string $description = '';
    public string $end_days = '';
    public string $end_direction = '';
    public string $end_field = '';
    public string $end_time = '';
    public $entityData;
    public $executeImmediately = true;
    public string $field_value_mapping = '';
    public array $fieldNames = [
        'subject',
        'description',
        'calendar_status',
        'calendar_type',
        'assigned_to',
        'start_time',
        'start_days',
        'start_field',
        'start_direction',
        'end_time',
        'end_days',
        'end_field',
        'end_direction',
        'is_all_day',
        'field_value_mapping',
    ];
    public array $ignoredFieldNames = [
        'subject',
        'description',
        'calendar_status',
        'calendar_type',
        'assigned_user_id',
        'is_all_day',
        'datetime_start',
        'datetime_end',
        'invite_users',
        'parent_id',
        'account_id',
        'contact_id',
        'recurring_type',
        'reminder_time',
    ];
    public string $is_all_day = '';
    public string $moduleName = 'Appointments';
    public string $sourceModuleName = '';
    public string $start_days = '';
    public string $start_direction = '';
    public string $start_field = '';
    public string $start_time = '';
    public string $subject = '';

    /**
     * @param VTWorkflowEntity $entityData
     *
     * @return string
     * @throws Exception
     */
    public function doTask($entityData): string
    {
        global $current_user, $default_timezone;

        $this->entityData = $entityData;
        [$tabId, $recordId] = explode('x', $this->entityData->getId());
        $entityCache = new VTEntityCache($this->entityData->user ?? $current_user);

        $recordModel = Vtiger_Record_Model::getCleanInstance($this->moduleName);
        $recordModel->set('subject', $this->renderTemplateValue($this->subject, $entityCache));
        $recordModel->set('description', $this->renderTemplateValue($this->description, $entityCache));
        $recordModel->set('calendar_status', $this->calendar_status);
        $recordModel->set('calendar_type', $this->calendar_type);
        $recordModel->set('assigned_to_user', $this->getAssignedTo());
        $recordModel->set('is_all_day', $this->is_all_day);

        if ($recordModel->isAllDayEvent()) {
            $recordModel->setAllDayDates(
                DateTimeField::convertToUserFormat($this->getDateTime(true)),
                DateTimeField::convertToUserFormat($this->getDateTime(false))
            );
        } else {
            $recordModel->set('datetime_start', DateTimeField::convertToUserFormat($this->getDateTime(true)));
            $recordModel->set('datetime_end', DateTimeField::convertToUserFormat($this->getDateTime(false)));
        }

        foreach ($this->getOtherFields() as $fieldName => $fieldModel) {
            $recordModel->set($fieldName, $this->renderTemplateValue((string)$this->get($fieldName), $entityCache));
        }

        $mappableFields = $this->getMappableFields();

        foreach ($this->getFieldValueMapping() as $fieldInfo) {
            $fieldName = $fieldInfo['fieldname'] ?? '';
            $fieldModel = $mappableFields[$fieldName] ?? null;

            if (!$fieldModel) {
                continue;
            }

            $value = $this->getMappedFieldValue($fieldInfo, $fieldModel, $entityCache, $default_timezone);
            $recordModel->set($fieldName, $value);
        }

        $recordModel->set($this->getRelationField(), $recordId);
        $recordModel->save();

        return 'Success';
    }

    public function get($name)
    {
        if (!empty($this->$name)) {
            return $this->$name;
        }

        return null;
    }

    public function getAssignedTo()
    {
        $adb = PearDatabase::getInstance();
        $userId = $this->entityData->get('assigned_user_id');

        if (empty($userId)) {
            $userId = vtws_getWebserviceEntityId('Users', Users::getActiveAdminId());
        }

        if (!empty($this->assigned_to)) {
            $userExists = $adb->pquery('SELECT 1 FROM vtiger_users WHERE id = ? AND status = ?', [$this->assigned_to, 'Active']);
            $groupExist = $adb->pquery('SELECT 1 FROM vtiger_groups WHERE groupid = ?', [$this->assigned_to]);

            if ($adb->num_rows($userExists)) {
                $assignedUserId = vtws_getWebserviceEntityId('Users', $this->assigned_to);
                $userId = $assignedUserId;
            } elseif ($adb->num_rows($groupExist)) {
                $assignedGroupId = vtws_getWebserviceEntityId('Groups', $this->assigned_to);
                $userId = $assignedGroupId;
            } elseif ('copyParentOwner' === $this->assigned_to) {
                $userId = $this->entityData->get('assigned_user_id');
            }
        }

        return $userId;
    }

    public function getAssignedToValues(): array
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        return [
            'Special Options' => [
                'copyParentOwner' => vtranslate('Parent Record Owner', $this->moduleName),
            ],
            'Users'           => array_filter($currentUser->getAccessibleUsers()),
            'Groups'          => array_filter($currentUser->getAccessibleGroups()),
        ];
    }

    public function getDateTime($isStart)
    {
        $field = $isStart ? $this->start_field : $this->end_field;

        [$date, $time] = explode(' ', $this->entityData->get($field));

        if (empty(trim($date, '-'))) {
            $date = date('Y-m-d');
        }

        $plusDays = '';
        $direction = $isStart ? $this->start_direction : $this->end_direction;
        $days = $isStart ? $this->start_days : $this->end_days;

        if (!empty($direction) && !empty($days)) {
            $directionValue = 'after' === $direction ? ' + ' : ' - ';
            $plusDays = $directionValue . abs($days) . ' days';
        }

        $time = $isStart ? $this->start_time : $this->end_time;

        return date('Y-m-d', strtotime(trim($date . $plusDays))) . ' ' . Vtiger_Time_UIType::getTimeValueWithSeconds($time);
    }

    public function getDateTimeValues()
    {
        $fields = $this->getSourceModuleModel()->getFieldsByType(['date', 'datetime']);
        $values = [];

        foreach ($fields as $field) {
            $values[$field->getName()] = vtranslate($field->label, $this->getSourceModule());
        }

        return $values;
    }

    public function getDirectionValues()
    {
        return [
            'before' => vtranslate('Before', $this->moduleName),
            'after'  => vtranslate('After', $this->moduleName),
        ];
    }

    /**
     * @return array
     */
    public function getFieldNames(): array
    {
        $this->fieldNames = array_values(array_unique(array_merge($this->fieldNames, $this->getOtherFieldNames())));

        return $this->fieldNames;
    }

    public function getFieldInfo(Vtiger_Field_Model $fieldModel): array
    {
        return $fieldModel->getFieldInfo();
    }

    public function getFieldValueMapping(): array
    {
        if (empty($this->field_value_mapping)) {
            return [];
        }

        $mapping = Zend_Json::decode($this->field_value_mapping);

        return is_array($mapping) ? $mapping : [];
    }

    protected function getMappedFieldValue(
        array $fieldInfo,
        Vtiger_Field_Model $fieldModel,
        VTEntityCache $entityCache,
        string $defaultTimezone
    ): mixed {
        global $current_user;

        $value = trim((string)($fieldInfo['value'] ?? ''));
        $valueType = $fieldInfo['valuetype'] ?? 'rawtext';

        if ($valueType === 'fieldname') {
            $value = $this->renderTemplateValue('$' . ltrim($value, '$'), $entityCache);
        } elseif ($valueType === 'expression') {
            require_once 'modules/com_vtiger_workflow/expression_engine/include.inc';
            date_default_timezone_set($current_user->time_zone);

            $parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($value)));
            $expression = $parser->expression();
            $evaluator = new VTFieldExpressionEvaluater($expression);
            $evaluator->fieldInstance = $fieldModel;
            $value = $evaluator->evaluate($this->entityData);

            date_default_timezone_set($defaultTimezone);
        } else {
            $value = $this->renderTemplateValue($value, $entityCache);
        }

        if (preg_match('/([^:]+):boolean$/', (string)$value, $matches)) {
            $value = $matches[1] === 'true' ? '1' : '0';
        }

        if ($fieldModel->getFieldDataType() === 'multipicklist') {
            $value = implode(' |##| ', array_map('trim', explode(',', (string)$value)));
        }

        if (in_array($fieldModel->getFieldDataType(), ['owner', 'reference'], true)) {
            $value = $this->getReferenceValue($fieldModel, $value);
        }

        return $value;
    }

    public function getMappableFields(): array
    {
        $fields = [];

        foreach ($this->getModuleModel()->getFields() as $field) {
            if ($this->isMappableField($field)) {
                $fields[$field->getName()] = $field;
            }
        }

        return $fields;
    }

    protected function getReferenceValue(Vtiger_Field_Model $fieldModel, mixed $value): mixed
    {
        if (empty($value)) {
            return '';
        }

        if (is_string($value) && preg_match('/^\d+x\d+$/', $value)) {
            $value = vtws_getIdComponents($value)[1];
        }

        if (is_numeric($value)) {
            return $value;
        }

        if ($fieldModel->getFieldDataType() === 'owner') {
            $userId = getUserId_Ol($value);
            $groupId = getGrpId($value);

            return $userId ?: $groupId;
        }

        $referenceModules = $fieldModel->getReferenceList();

        foreach ($referenceModules as $referenceModule) {
            $recordId = Vtiger_Util_Helper::getRecordId($value, $referenceModule, true);

            if ($recordId) {
                return $recordId;
            }
        }

        return '';
    }

    public function getModuleModel()
    {
        return Vtiger_Module_Model::getInstance($this->moduleName);
    }

    public function getOtherFieldNames(): array
    {
        $otherFields = [];
        $fields = $this->getModuleModel()->getFields();

        /** @var Vtiger_Field_Model $field */
        foreach ($fields as $field) {
            if ($this->isOtherField($field)) {
                $otherFields[] = $field->getName();
            }
        }

        return $otherFields;
    }

    public function getOtherFields(): array
    {
        $otherFields = [];
        $fields = $this->getModuleModel()->getFields();

        /** @var Vtiger_Field_Model $field */
        foreach ($fields as $field) {
            if ($this->isOtherField($field)) {
                $otherFields[$field->getName()] = $field;
            }
        }

        return $otherFields;
    }

    public function getPriorityValues()
    {
        $field = $this->getModuleModel()->getField('calendar_priority');

        return $field->getPicklistValues();
    }

    public function getRelationField(): string
    {
        switch ($this->entityData->getModuleName()) {
            case 'Accounts':
                return 'account_id';
            case 'Contacts':
                return 'contact_id';
        }

        return 'parent_id';
    }

    public function getSourceModule(): string
    {
        return $this->sourceModuleName;
    }

    public function getSourceModuleModel()
    {
        return Vtiger_Module_Model::getInstance($this->getSourceModule());
    }

    public function getStatusValues()
    {
        $field = $this->getModuleModel()->getField('calendar_status');

        return $field->getPicklistValues();
    }

    public function getTypeValues()
    {
        $field = $this->getModuleModel()->getField('calendar_type');

        return $field->getPicklistValues();
    }

    public function isOtherField($field): bool
    {
        return !(in_array($field->getName(), $this->ignoredFieldNames) || !$field->isActiveField() || !$field->isEditable() || !$field->isMandatory());
    }

    public function isMappableField(Vtiger_Field_Model $field): bool
    {
        return !in_array($field->getName(), $this->ignoredFieldNames, true)
            && $field->isActiveField()
            && $field->isEditable()
            && !$field->isMandatory();
    }

    protected function renderTemplateValue(string $value, VTEntityCache $entityCache): string
    {
        return (new VTSimpleTemplate($value))->render($entityCache, $this->entityData->getId());
    }

    public function setSourceModule($module)
    {
        $this->sourceModuleName = $module;
    }
}
