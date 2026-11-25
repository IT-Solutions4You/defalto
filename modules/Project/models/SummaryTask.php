<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class Project_SummaryTask_Model extends Vtiger_Base_Model
{
    public function getHeaders(): array
    {
        $module = Vtiger_Module_Model::getInstance('ProjectTask');

        if (!$module || !$module->isActive() || !Users_Privileges_Model::isPermitted('ProjectTask', 'DetailView')) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $headers = [];

        foreach (['projecttaskname', 'projecttaskprogress', 'milestoneid', 'assigned_user_id', 'startdate', 'enddate', 'projecttaskpriority', 'projecttaskstatus'] as $name) {
            $field = $module->getField($name);

            if ($field && $field->isViewableInDetailView()) {
                $headers[$name] = $field;
            }
        }

        return $headers;
    }

    public function getSummaryData(Project_Record_Model $project): array
    {
        $headers = $this->getHeaders();
        $module = Vtiger_Module_Model::getInstance('ProjectTask');
        $query = Core_QueryGenerator_Model::getInstance('ProjectTask');
        $query->setFields(['id', ...array_keys($headers)]);
        $query->addFieldColumnCondition('projectid', '=', $project->getId());
        $rows = $query->getProjectedRows();
        $permissions = Users_Privileges_Model::getDetailViewPermissions('ProjectTask', array_keys($rows));
        $closedValues = isset($headers['projecttaskstatus']) ? (new ProjectTask_ClosedStatus_Model())->getClosedValues() : [];
        $colors = isset($headers['projecttaskstatus']) ? $headers['projecttaskstatus']->getPicklistColors() : [];
        $statusOrder = isset($headers['projecttaskstatus']) ? array_flip(array_keys($headers['projecttaskstatus']->getPicklistValues())) : [];
        $milestones = isset($headers['milestoneid']) ? $this->getMilestones($project->getId()) : [];
        $today = (new DateTimeImmutable('now', new DateTimeZone(Users_Record_Model::getCurrentUserModel()->get('time_zone') ?: 'UTC')))->format('Y-m-d');
        $groups = [];
        $activeCount = $overdueCount = $count = 0;

        foreach ($rows as $id => $values) {
            if (empty($permissions[$id])) {
                continue;
            }

            $record = Vtiger_Record_Model::getCleanInstance('ProjectTask');
            $record->setData($values)->setModuleFromInstance($module)->setId($id);
            $status = (string)($values['projecttaskstatus'] ?? '');
            $closed = in_array($status, $closedValues, true);
            $daysOverdue = $this->getDaysOverdue((string)($values['enddate'] ?? ''), $today, $closed);
            $groupKey = 'status_' . sha1($status);

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'id' => $groupKey,
                    'label' => $status === '' ? vtranslate('LBL_SUMMARY_NO_STATUS', 'Project') : $headers['projecttaskstatus']->getDisplayValue($status),
                    'color' => $this->getStatusColor($colors[$status] ?? ''),
                    'closed' => $closed,
                    'order' => $statusOrder[$status] ?? PHP_INT_MAX,
                    'rows' => [],
                ];
            }

            $fields = array_map(static fn($field) => clone $field, $headers);
            $structure = Core_RecordActionPermission_Model::getInstance()->applyLockedFields(['summary' => $fields], $id);
            $statusField = $structure['summary']['projecttaskstatus'] ?? null;
            $owner = empty($values['assigned_user_id']) ? '' : decode_html(getOwnerName($values['assigned_user_id']));
            $priority = (string)($values['projecttaskpriority'] ?? '');
            $groups[$groupKey]['rows'][] = [
                'id' => $id,
                'url' => $record->getDetailViewUrl(),
                'name' => (string)($values['projecttaskname'] ?? ''),
                'progress' => max(0, min(100, (int)($values['projecttaskprogress'] ?? 0))),
                'progress_set' => ($values['projecttaskprogress'] ?? '') !== '',
                'owner' => $owner,
                'owner_key' => (string)($values['assigned_user_id'] ?? '0'),
                'milestone_key' => isset($milestones[(int)($values['milestoneid'] ?? 0)]) ? (string)$values['milestoneid'] : '0',
                'priority_key' => $priority,
                'start_date' => isset($headers['startdate']) ? Core_DateOnly_Helper::convertToDBFormat((string)($values['startdate'] ?? '')) : '',
                'end_date' => isset($headers['enddate']) ? Core_DateOnly_Helper::convertToDBFormat((string)($values['enddate'] ?? '')) : '',
                'milestone' => $milestones[(int)($values['milestoneid'] ?? 0)] ?? null,
                'dates' => $this->getDateRange($record, $headers),
                'dates_title' => $this->getDateRange($record, $headers, false),
                'overdue_days' => $daysOverdue,
                'priority' => isset($headers['projecttaskpriority']) ? $headers['projecttaskpriority']->getDisplayValue($priority) : '',
                'priority_type' => in_array($priority, ['high', 'low'], true) ? $priority : 'normal',
                'status_field' => $statusField,
                'status' => $status,
                'can_edit_status' => $this->isStatusEditable($record, $statusField),
            ];
            $count++;
            $activeCount += isset($headers['projecttaskstatus']) && !$closed ? 1 : 0;
            $overdueCount += $daysOverdue > 0 ? 1 : 0;
        }

        uasort($groups, static fn(array $first, array $second): int => ((int)$first['closed'] <=> (int)$second['closed']) ?: ($first['order'] <=> $second['order']));
        $columns = array_intersect_key($headers, array_flip(['projecttaskname', 'projecttaskprogress', 'milestoneid', 'assigned_user_id']));

        if (isset($headers['startdate']) || isset($headers['enddate'])) {
            $columns['dates'] = true;
        }

        if (isset($headers['projecttaskpriority']) && !isset($columns['projecttaskname'])) {
            $columns = ['projecttaskname' => true] + $columns;
        }

        $progressField = $project->getModule()->getField('progress');

        return [
            'groups' => $groups,
            'filters' => $this->getFilterDefinitions($headers, $groups),
            'columns' => $columns,
            'count' => $count,
            'active_count' => $activeCount,
            'status_visible' => isset($headers['projecttaskstatus']),
            'overdue_count' => $overdueCount,
            'project_progress' => $progressField && $progressField->isViewableInDetailView() ? max(0, min(100, (int)$project->get('progress'))) : null,
        ];
    }

    protected function getFilterDefinitions(array $headers, array $groups): array
    {
        $filters = [];

        foreach (['status' => 'projecttaskstatus', 'owner' => 'assigned_user_id', 'milestone' => 'milestoneid', 'priority' => 'projecttaskpriority'] as $key => $field) {
            if (isset($headers[$field])) {
                $filters[$key] = ['label' => vtranslate($headers[$field]->get('label'), in_array($key, ['status', 'priority'], true) ? 'Core' : 'ProjectTask'), 'options' => []];
            }
        }

        foreach ($groups as $group) {
            if (isset($filters['status'])) {
                $filters['status']['options'][$group['id']] = $group['label'];
            }

            foreach ($group['rows'] as $row) {
                if (isset($filters['owner'])) {
                    $filters['owner']['options'][$row['owner_key']] = $row['owner'] ?: vtranslate('LBL_SUMMARY_UNASSIGNED', 'Project');
                }

                if (isset($filters['milestone'])) {
                    $filters['milestone']['options'][$row['milestone_key']] = $row['milestone']['name'] ?? vtranslate('LBL_SUMMARY_NO_MILESTONE', 'Project');
                }

                if (isset($filters['priority'])) {
                    $filters['priority']['options'][$row['priority_key']] = $row['priority'] ?: vtranslate('LBL_NONE', 'Core');
                }
            }
        }

        if (isset($headers['startdate']) || isset($headers['enddate'])) {
            $filters['dates'] = ['label' => vtranslate('LBL_DATE', 'Core'), 'options' => []];
        }

        return $filters;
    }

    protected function getMilestones(int $projectId): array
    {
        $module = Vtiger_Module_Model::getInstance('ProjectMilestone');
        $field = $module ? $module->getField('projectmilestonename') : null;

        if (!$this->isMilestoneNameViewable($module, $field)) {
            return [];
        }

        $query = Core_QueryGenerator_Model::getInstance('ProjectMilestone');
        $query->setFields(['id', 'projectmilestonename']);
        $query->addFieldColumnCondition('projectid', '=', $projectId);
        $rows = $query->getProjectedRows();
        $permissions = Users_Privileges_Model::getDetailViewPermissions('ProjectMilestone', array_keys($rows));
        $milestones = [];

        foreach ($rows as $id => $row) {
            if (!empty($permissions[$id])) {
                $milestones[$id] = ['name' => $row['projectmilestonename'], 'url' => 'index.php?module=ProjectMilestone&view=Detail&record=' . $id];
            }
        }

        return $milestones;
    }

    protected function isMilestoneNameViewable($module, $field): bool
    {
        return $module && $module->isActive() && $field && $field->isViewableInDetailView()
            && Users_Privileges_Model::isPermitted('ProjectMilestone', 'DetailView');
    }

    public function getCreateActions(Project_Record_Model $project): array
    {
        $actions = [];

        foreach (['ProjectMilestone', 'ProjectTask'] as $name) {
            $module = Vtiger_Module_Model::getInstance($name);

            if (!$project->getModule()->isModuleRelated($name)) {
                continue;
            }

            if ($module && $module->isActive() && Users_Privileges_Model::isPermitted($name, 'CreateView')) {
                $relationList = Vtiger_RelationListView_Model::getInstance($project, $name);

                if (!$relationList->getRelationModel()->isAddActionSupported()) {
                    continue;
                }

                $actions[$name] = [
                    'label' => vtranslate('LBL_ADD_RECORD', $name),
                    'quick_url' => $module->isQuickCreateSupported() ? $module->getQuickCreateUrl() . '&projectid=' . $project->getId() : '',
                    'url' => $relationList->getCreateViewUrl(),
                ];
            }
        }

        return $actions;
    }

    protected function getDateRange(Vtiger_Record_Model $record, array $headers, bool $compact = true): string
    {
        $dates = [];
        $values = [];

        foreach (['startdate', 'enddate'] as $name) {
            $value = isset($headers[$name]) ? Core_DateOnly_Helper::convertToDBFormat((string)$record->get($name)) : '';
            $values[] = $value;

            if ($value) {
                $dates[] = $record->getDisplayValue($name);
            } else {
                $dates[] = '—';
            }
        }

        if ($compact && $values[0] && $values[1]) {
            return $this->getCompactDateRange($values[0], $values[1], Users_Record_Model::getCurrentUserModel()->get('date_format'));
        }

        return $dates === ['—', '—'] ? '' : implode(' – ', $dates);
    }

    protected function getCompactDateRange(string $start, string $end, string $format): string
    {
        $startValues = ['yyyy' => substr($start, 0, 4), 'mm' => substr($start, 5, 2), 'dd' => substr($start, 8, 2)];
        $endValues = ['yyyy' => substr($end, 0, 4), 'mm' => substr($end, 5, 2), 'dd' => substr($end, 8, 2)];
        $fullStart = DateTimeField::__convertToUserFormat($start, $format);
        $fullEnd = DateTimeField::__convertToUserFormat($end, $format);

        if ($start === $end) {
            return $fullStart;
        }

        if (!preg_match('/^(dd|mm|yyyy)([-.\/])(dd|mm|yyyy)\2(dd|mm|yyyy)$/', $format, $matches)
            || $startValues['yyyy'] !== $endValues['yyyy']) {
            return $fullStart . ' – ' . $fullEnd;
        }

        $separator = $matches[2];
        $tokens = [$matches[1], $matches[3], $matches[4]];
        $startTokens = $endTokens = $tokens;

        if ($tokens[0] === 'yyyy') {
            $endTokens = array_values(array_diff($endTokens, ['yyyy']));
        } else {
            $startTokens = array_values(array_diff($startTokens, ['yyyy']));
        }

        if ($startValues['mm'] === $endValues['mm']) {
            if (array_search('dd', $tokens, true) < array_search('mm', $tokens, true)) {
                $startTokens = array_values(array_diff($startTokens, ['mm']));
            } else {
                $endTokens = array_values(array_diff($endTokens, ['mm']));
            }
        }

        return implode($separator, array_map(static fn(string $token): string => $startValues[$token], $startTokens))
            . ' – ' . implode($separator, array_map(static fn(string $token): string => $endValues[$token], $endTokens));
    }

    protected function getDaysOverdue(string $date, string $today, bool $closed): int
    {
        $date = Core_DateOnly_Helper::convertToDBFormat($date);

        if ($closed || $date === null || $date === '' || $date >= $today) {
            return 0;
        }

        // Calendar-day distance, independent of the user's DST offset.
        $zone = new DateTimeZone('UTC');

        return (int)(new DateTimeImmutable($date, $zone))->diff(new DateTimeImmutable($today, $zone))->days;
    }

    protected function isStatusEditable(Vtiger_Record_Model $record, ?Vtiger_Field_Model $field): bool
    {
        return $field && $record->isEditable() && $field->isEditable() && $field->isAjaxEditable();
    }

    protected function getStatusColor(string $color): string
    {
        return preg_match('/^#[0-9a-f]{6}$/i', $color) ? $color : '#85809f';
    }

}
