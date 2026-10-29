<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Workflow tasks of record action permissions: registration of the task types and the member options of their editor.
 * Members are stored as "All", "Users:<id>", "Groups:<id>" or "Roles:<roleid>" (Settings_Groups_Member_Model ids).
 */
class Core_RecordActionPermissionTask_Helper
{
    public const TASK_RESTRICT = 'VTRestrictRecordActions';
    public const TASK_CLEAR = 'VTClearRecordActions';

    public const MEMBER_ALL = 'All';

    /** Member types of Settings_Groups_Member_Model => rule scope */
    public const MEMBER_SCOPES = [
        Settings_Groups_Member_Model::MEMBER_TYPE_USERS  => Core_RecordActionPermission_Model::SCOPE_USER,
        Settings_Groups_Member_Model::MEMBER_TYPE_GROUPS => Core_RecordActionPermission_Model::SCOPE_GROUP,
        Settings_Groups_Member_Model::MEMBER_TYPE_ROLES  => Core_RecordActionPermission_Model::SCOPE_ROLE,
    ];

    /**
     * Registers the workflow task types once; called from Core_RecordActionPermission_Model::createTables().
     *
     * @return void
     */
    public static function registerTaskTypes(): void
    {
        require_once 'modules/com_vtiger_workflow/VTTaskManager.inc';

        $taskTypes = [
            self::TASK_RESTRICT => 'Restrict record actions',
            self::TASK_CLEAR    => 'Clear record action restrictions',
        ];

        foreach ($taskTypes as $name => $label) {
            if (VTTaskType::getInstanceFromTaskType($name)->get('name')) {
                continue;
            }

            VTTaskType::registerTaskType([
                'name'         => $name,
                'label'        => $label,
                'sourcemodule' => '',
                'classname'    => $name,
                'classpath'    => sprintf('modules/com_vtiger_workflow/tasks/%s.inc', $name),
                'templatepath' => sprintf('modules/Settings/Workflows/Tasks/%s.tpl', $name),
                'modules'      => ['include' => [], 'exclude' => []],
            ]);
        }
    }

    /**
     * Returns the members offered in the task editor: everyone, users, groups and roles.
     *
     * @return array member type => [member id => name]
     */
    public static function getMemberOptions(): array
    {
        $options = [self::MEMBER_ALL => [self::MEMBER_ALL => vtranslate('LBL_RECORD_ACTION_SCOPE_ALL', 'Core')]];
        $members = Settings_Groups_Member_Model::getAll(false);

        foreach (array_keys(self::MEMBER_SCOPES) as $memberType) {
            foreach ($members[$memberType] ?? [] as $memberId => $member) {
                // Names are stored HTML-encoded; the template escapes them once
                $options[$memberType][$memberId] = trim(decode_html((string)$member->get('name')));
            }
        }

        return $options;
    }

    /**
     * Returns the editable fields of a module offered for the action "Edit field".
     *
     * @param Vtiger_Module_Model $moduleModel
     *
     * @return array field name => translated label
     */
    public static function getFieldOptions(Vtiger_Module_Model $moduleModel): array
    {
        $options = [];

        foreach ($moduleModel->getFields() as $fieldName => $fieldModel) {
            if ($fieldModel->isEditable()) {
                $options[$fieldName] = vtranslate($fieldModel->get('label'), $moduleModel->getName());
            }
        }

        asort($options);

        return $options;
    }

    /**
     * Returns the values of the picklist fields of a module offered for the action "Change to value".
     *
     * @param Vtiger_Module_Model $moduleModel
     *
     * @return array field name => ['label' => translated field label, 'values' => [StatusChange action value
     *               ("field=value") => translated value]]
     */
    public static function getStatusOptions(Vtiger_Module_Model $moduleModel): array
    {
        $options = [];

        foreach ($moduleModel->getFields() as $fieldName => $fieldModel) {
            if (!$fieldModel->isEditable() || 'picklist' !== $fieldModel->getFieldDataType()) {
                continue;
            }

            $options[$fieldName] = ['label' => vtranslate($fieldModel->get('label'), $moduleModel->getName()), 'values' => []];

            foreach ((array)$fieldModel->getPicklistValues() as $value => $valueLabel) {
                $options[$fieldName]['values'][Core_RecordActionPermission_Model::getStatusValue($fieldName, $value)] = decode_html((string)$valueLabel);
            }
        }

        return $options;
    }

    /**
     * Returns the action values of the rules a task writes for an action: fields for "Edit field" (none selected =
     * every field), field values for "Change to value" (none selected = no rule), an empty value otherwise.
     *
     * @param string $action
     * @param array  $fields       selected fields
     * @param array  $statusValues selected StatusChange values
     *
     * @return array
     */
    public static function getActionValues(string $action, array $fields, array $statusValues): array
    {
        return match ($action) {
            Core_RecordActionPermission_Model::ACTION_FIELD_EDIT => $fields ?: [''],
            Core_RecordActionPermission_Model::ACTION_STATUS_CHANGE => $statusValues,
            default => [''],
        };
    }

    /**
     * Converts a stored member to the scope of a rule.
     *
     * @param string $member "All" or a Settings_Groups_Member_Model id such as "Users:5"
     *
     * @return array [scope type, scope id], empty for an unsupported member
     */
    public static function getMemberScope(string $member): array
    {
        if (self::MEMBER_ALL === $member) {
            return [Core_RecordActionPermission_Model::SCOPE_ALL, ''];
        }

        [$memberType, $memberId] = array_pad(explode(':', $member, 2), 2, '');

        if (!isset(self::MEMBER_SCOPES[$memberType]) || '' === $memberId) {
            return [];
        }

        return [self::MEMBER_SCOPES[$memberType], $memberId];
    }
}
