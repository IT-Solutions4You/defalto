<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

require_once 'modules/com_vtiger_workflow/VTEntityCache.inc';
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';
require_once 'modules/com_vtiger_workflow/VTSimpleTemplate.inc';

class VTCalendarTask extends VTTask
{
    public $executeImmediately = true;
    public string $subject = '';
    public string $description = '';
    public string $calendar_status = '';
    public string $calendar_type = '';
    public string $assigned_to = '';
    public string $start_time = '';
    public string $start_days = '';
    public string $start_direction = '';
    public string $start_field = '';
    public string $end_time = '';
    public string $end_days = '';
    public string $end_direction = '';
    public string $end_field = '';
    public string $is_all_day = '';
    public string $moduleName = 'ITS4YouCalendar';
    public string $sourceModuleName = '';
    public $entityData;
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
    ];

    /**
     * @return array
     */
    public function getFieldNames(): array
    {
        $this->fieldNames = array_merge($this->fieldNames, $this->getOtherFieldNames());

        return $this->fieldNames;
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

    public function getModuleModel()
    {
        return Vtiger_Module_Model::getInstance($this->moduleName);
    }

    public function isOtherField($field): bool
    {
        return !(in_array($field->getName(), $this->ignoredFieldNames) || !$field->isActiveField() || !$field->isEditable() || !$field->isMandatory());
    }

    public function getStatusValues()
    {
        $field = $this->getModuleModel()->getField('calendar_status');

        return $field->getPicklistValues();
    }

    public function getPriorityValues()
    {
        $field = $this->getModuleModel()->getField('calendar_priority');

        return $field->getPicklistValues();
    }

    public function getTypeValues()
    {
        $field = $this->getModuleModel()->getField('calendar_type');

        return $field->getPicklistValues();
    }

    public function getDirectionValues()
    {
        return [
            'before' => vtranslate('Before', $this->moduleName),
            'after' => vtranslate('After', $this->moduleName),
        ];
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

    public function getSourceModuleModel()
    {
        return Vtiger_Module_Model::getInstance($this->getSourceModule());
    }

    public function getSourceModule(): string
    {
        return $this->sourceModuleName;
    }

    public function setSourceModule($module)
    {
        $this->sourceModuleName = $module;
    }

    public function getAssignedToValues(): array
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        return [
            'Special Options' => [
                'copyParentOwner' => vtranslate('Parent Record Owner', $this->moduleName),
            ],
            'Users' => array_filter($currentUser->getAccessibleUsers()),
            'Groups' => array_filter($currentUser->getAccessibleGroups()),
        ];
    }

    /**
     * @param VTWorkflowEntity $entityData
     * @return string
     * @throws Exception
     */
    public function doTask($entityData): string
    {
        $this->entityData = $entityData;

        $recordModel = Vtiger_Record_Model::getCleanInstance($this->moduleName);
        $recordModel->set('subject', $this->subject);
        $recordModel->set('description', $this->description);
        $recordModel->set('calendar_status', $this->calendar_status);
        $recordModel->set('calendar_type', $this->calendar_type);
        $recordModel->set('assigned_to_user', $this->getAssignedTo());
        $recordModel->set('datetime_start', DateTimeField::convertToUserFormat($this->getDateTime(true)));
        $recordModel->set('datetime_end', DateTimeField::convertToUserFormat($this->getDateTime(false)));
        $recordModel->set('is_all_day', $this->is_all_day);

        foreach ($this->getOtherFields() as $fieldName => $fieldModel) {
            $recordModel->set($fieldName, $this->get($fieldName));
        }

        $recordModel->set($this->getRelationField(), $this->entityData->getId());
        $recordModel->save();

        return 'Success';
    }

    public function getAssignedTo()
    {
        $adb = PearDatabase::getInstance();
        $userId = $this->entityData->get('assigned_user_id');

        if (empty($userId)) {
            $userId = vtws_getWebserviceEntityId('Users', Users::getActiveAdminId());
        }

        if (!empty($this->assigned_to)) {
            $userExists = $adb->pquery('SELECT 1 FROM vtiger_users WHERE id = ? AND status = ?', array($this->assigned_to, 'Active'));
            $groupExist = $adb->pquery('SELECT 1 FROM vtiger_groups WHERE groupid = ?', array($this->assigned_to));

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

    public function get($name)
    {
        if (!empty($this->$name)) {
            return $this->$name;
        }

        return null;
    }

    public function getDateTime($isStart)
    {
        $field = $isStart ? $this->start_field : $this->end_field;

        list($date, $time) = explode(' ', $this->entityData->get($field));

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
}