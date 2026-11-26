<?php
/**
 * This file is part of Defalto CRM, licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for details.
 */

class Project_Chart_Model extends Vtiger_Base_Model
{
    public function getChartData(Project_Record_Model $project): array
    {
        $tasks = $unscheduled = [];
        $projectStart = $this->getProjectDate($project, 'startdate');
        $projectEnd = $this->getProjectDate($project, 'targetenddate');
        $user = Users_Record_Model::getCurrentUserModel();
        $today = (new DateTimeImmutable('now', new DateTimeZone($user->get('time_zone') ?: 'UTC')))->format('Y-m-d');
        $closedValues = (new ProjectTask_ClosedStatus_Model())->getClosedValues();
        $taskModule = Vtiger_Module_Model::getInstance('ProjectTask');
        $statusField = $taskModule->getField('projecttaskstatus');
        $colors = $statusField->getPicklistColors();
        $legacyColors = $project->getStatusColors();

        foreach ($this->getRows('ProjectTask', $project->getId(), ['projecttaskname', 'startdate', 'enddate', 'assigned_user_id', 'projecttaskstatus', 'projecttaskprogress', 'milestoneid']) as $id => $row) {
            $status = $row['projecttaskstatus'] ?? '';
            $progress = $this->getProgress($row['projecttaskprogress'] ?? '');
            $dates = $this->getTaskRange($row['startdate'], $row['enddate'], $projectStart);
            $task = [
                'id' => 'task_' . $id,
                'recordid' => $id,
                'module' => 'ProjectTask',
                'text' => $row['projecttaskname'],
                'start_date' => $dates['start'],
                'end_date' => $dates['end'],
                'owner' => empty($row['assigned_user_id']) ? '' : decode_html(getOwnerName($row['assigned_user_id'])),
                'owner_id' => (string)($row['assigned_user_id'] ?? ''),
                'status_value' => $status,
                'status' => vtranslate($status, 'ProjectTask'),
                'color' => $this->getColor($colors[$status] ?? $legacyColors[$status] ?? ''),
                'readonly' => !$this->isTaskEditable($taskModule, $id),
                'can_edit_record' => Users_Privileges_Model::isPermitted('ProjectTask', 'EditView', $id),
                'type' => 'task',
                'progress' => $progress,
                'progress_visible' => array_key_exists('projecttaskprogress', $row),
                'milestone_id' => (int)($row['milestoneid'] ?? 0),
                'overdue' => $this->isOverdue($row['enddate'], $today, $progress, in_array($status, $closedValues, true)),
                'outside_project' => $this->isOutsideProject($dates['start'], $dates['end'], $projectStart, $projectEnd),
                'dependency_violation' => false,
            ];

            if ($this->hasDateRange($dates['start'], $dates['end'])) {
                $tasks[] = $task;
            } else {
                $task['missing_start'] = $this->isMissingDate($row['startdate']);
                $task['missing_end'] = $this->isMissingDate($row['enddate']);
                $unscheduled[] = $task;
            }
        }

        foreach ($this->getRows('ProjectMilestone', $project->getId(), ['projectmilestonename', 'projectmilestonedate', 'assigned_user_id']) as $id => $row) {
            $deadline = $this->getMilestoneDeadline($row['projectmilestonedate'], $projectEnd);

            if (!$this->hasDateRange($deadline, $deadline)) {
                continue;
            }

            $range = $this->getMilestoneRange((int)$id, $deadline, array_merge($tasks, $unscheduled));
            $tasks[] = [
                'id' => 'milestone_' . $id,
                'recordid' => $id,
                'module' => 'ProjectMilestone',
                'text' => $row['projectmilestonename'],
                'start_date' => $range['start'],
                'end_date' => $deadline,
                'owner' => empty($row['assigned_user_id']) ? '' : decode_html(getOwnerName($row['assigned_user_id'])),
                'owner_id' => (string)($row['assigned_user_id'] ?? ''),
                'status_value' => '',
                'status' => '',
                'readonly' => !$this->isMilestoneDateEditable($id),
                'can_edit_record' => Users_Privileges_Model::isPermitted('ProjectMilestone', 'EditView', $id),
                'type' => $range['has_range'] ? 'task' : 'milestone',
                'is_milestone_range' => $range['has_range'],
                'milestone_violation' => $range['violation'],
                'milestone_conflicts' => $range['conflicts'],
                'color' => '#c45bea',
            ];
        }

        usort($tasks, static fn(array $left, array $right): int => strcmp($left['start_date'], $right['start_date']) ?: strcmp($left['id'], $right['id']));
        $hasRecords = (bool)($tasks || $unscheduled);
        $range = $this->getProjectRange($project, $projectStart, $projectEnd);

        if ($range) {
            array_unshift($tasks, $range);
        }

        $this->setTaskParents($tasks, $range['id'] ?? 0);

        $dependency = new Project_Dependency_Model();
        $dependenciesReady = $dependency->isAvailable();
        $links = $dependenciesReady ? $dependency->getProjectLinks((int)$project->getId()) : [];
        $chartLinks = $this->getChartLinks($tasks, $links);

        $preferences = new Project_ChartPreference_Model();

        return [
            'data' => $tasks,
            'assignees' => (new Core_AssigneeSelect_Model())->getOptions('ProjectTask'),
            'has_records' => $hasRecords,
            'project_start' => $projectStart,
            'project_end' => $projectEnd,
            'today' => $today,
            'preferences_ready' => $preferences->isAvailable(),
            'preferences' => $preferences->getPreferences((int)$project->getId(), (int)$user->getId()),
            'unscheduled' => $unscheduled,
            'links' => $chartLinks,
            'can_edit_dependencies' => $dependenciesReady && Users_Privileges_Model::isPermitted('Project', 'EditView', $project->getId()),
            'dependencies_ready' => $dependenciesReady,
            'has_range_warning' => in_array(true, array_column($tasks, 'outside_project'), true),
            'has_dependency_warning' => in_array(true, array_column($tasks, 'dependency_violation'), true),
            'create_task_url' => $this->getCreateTaskUrl($taskModule, (int)$project->getId()),
        ];
    }

    public function getTaskRange(string $start, string $end, string $projectStart): array
    {
        if (($this->isMissingDate($start) || $this->isMissingDate($end)) && $this->hasDateRange($projectStart, $projectStart)) {
            return ['start' => $projectStart, 'end' => $projectStart];
        }

        return ['start' => $start, 'end' => $end];
    }

    public function getMilestoneRange(int $milestoneId, string $deadline, array $tasks): array
    {
        $start = $deadline;
        $hasStart = false;
        $violation = false;
        $conflicts = [];

        foreach ($tasks as $task) {
            if (($task['milestone_id'] ?? 0) !== $milestoneId) {
                continue;
            }

            if ($this->hasDateRange($task['start_date'], $task['start_date'])) {
                $start = $hasStart ? min($start, $task['start_date']) : $task['start_date'];
                $hasStart = true;
            }

            $violation = $violation || $task['start_date'] > $deadline
                || $task['end_date'] > $deadline;

            if ($task['start_date'] > $deadline || $task['end_date'] > $deadline) {
                $conflicts[] = ['name' => $task['text'] ?? '', 'date' => max($task['start_date'], $task['end_date']),
                    'deadline' => $deadline];
            }
        }

        $hasRange = $hasStart && $start <= $deadline;
        return ['start' => $hasRange ? $start : $deadline, 'has_range' => $hasRange, 'violation' => $violation, 'conflicts' => $conflicts];
    }

    public function getMilestoneDeadline(string $date, string $projectEnd): string
    {
        return $this->isMissingDate($date) ? $projectEnd : $date;
    }

    public function setTaskParents(array &$tasks, string|int $projectId): void
    {
        $milestones = [];

        foreach ($tasks as $task) {
            if ($task['module'] === 'ProjectMilestone') {
                $milestones[$task['recordid']] = $task['id'];
            }
        }

        foreach ($tasks as &$task) {
            $task['parent'] = !empty($task['is_project_range']) ? 0 : ($milestones[$task['milestone_id'] ?? 0] ?? $projectId);
            $task['open'] = true;
        }

        unset($task);
    }

    public function getPrintView(array $data, array $options): array
    {
        $all = ($options['scope'] ?? '') === 'all';
        $filters = $all ? ['search' => '', 'owner' => '', 'status' => '']
            : ['search' => (string)($options['search'] ?? ''), 'owner' => (string)($options['owner'] ?? ''), 'status' => (string)($options['status'] ?? '')];
        $filterLabels = ['search' => $filters['search'] ?: vtranslate('LBL_ALL'), 'owner' => vtranslate('LBL_ALL'),
            'status' => $filters['status'] !== '' ? vtranslate($filters['status'], 'ProjectTask') : vtranslate('LBL_ALL')];

        $ownerLabels = [];

        foreach (array_merge($data['data'], $data['unscheduled']) as $task) {
            if ($filters['owner'] !== '' && in_array($task['owner_id'], explode(',', $filters['owner']), true)) {
                $ownerLabels[$task['owner_id']] = $task['owner'];
            }
        }

        if ($ownerLabels) {
            $filterLabels['owner'] = implode(', ', $ownerLabels);
        }
        $from = !$all && $this->hasDateRange((string)($options['from'] ?? ''), (string)($options['to'] ?? '')) ? $options['from'] : '';
        $to = $from !== '' ? $options['to'] : '';
        $byId = array_column($data['data'], null, 'id');
        $visible = [];

        foreach ($data['data'] as $task) {
            $inPeriod = $from === '' || ($task['start_date'] <= $to && $task['end_date'] >= $from);

            if (!empty($task['is_project_range']) || ($inPeriod && $this->matchesPrintFilters($task, $filters))) {
                $visible[$task['id']] = true;
            }
        }

        foreach (array_keys($visible) as $id) {
            $parent = $byId[$id]['parent'] ?? 0;

            while ($parent && isset($byId[$parent]) && !isset($visible[$parent])) {
                $visible[$parent] = true;
                $parent = $byId[$parent]['parent'] ?? 0;
            }
        }

        $data['data'] = array_values(array_filter($data['data'], static fn(array $task): bool => isset($visible[$task['id']])));
        $data['links'] = array_values(array_filter($data['links'], static fn(array $link): bool => isset($visible[$link['source']], $visible[$link['target']])));
        $data['unscheduled'] = array_values(array_filter($data['unscheduled'], fn(array $task): bool => $this->matchesPrintFilters($task, $filters)));

        if ($from === '' && $data['data']) {
            $from = min(array_column($data['data'], 'start_date'));
            $to = max(array_column($data['data'], 'end_date'));
        }

        return ['data' => $data, 'filters' => $filters, 'filter_labels' => $filterLabels, 'from' => $from, 'to' => $to,
            'scale' => $all ? 1 : max(0, min(2, (int)($options['scale'] ?? 1)))];
    }

    protected function matchesPrintFilters(array $task, array $filters): bool
    {
        return ($filters['search'] === '' || preg_match('/' . preg_quote($filters['search'], '/') . '/iu', $task['text']) === 1)
            && ($filters['owner'] === '' || in_array($task['owner_id'], explode(',', $filters['owner']), true))
            && ($filters['status'] === '' || $task['status_value'] === $filters['status']);
    }

    public function getProgress(string $value): float
    {
        return Project_ProgressCalculator_Model::getIntegerPercentage($value) / 100;
    }

    public function isOverdue(string $end, string $today, float $progress, bool $closed): bool
    {
        return !$closed && $progress < 1 && $this->hasDateRange($end, $end) && $end < $today;
    }

    protected function getProjectRange(Project_Record_Model $project, string $start, string $end): ?array
    {
        if (!$this->hasDateRange($start, $end)) {
            return null;
        }

        $nameField = $project->getModule()->getField('projectname');
        $name = $nameField && $nameField->isViewable() ? decode_html((string)$project->get('projectname')) : '';

        return ['id' => 'project_' . $project->getId(), 'recordid' => (int)$project->getId(), 'module' => 'Project',
            'text' => $name !== '' ? $name : vtranslate('SINGLE_Project', 'Project'),
            'start_date' => $start, 'end_date' => $end, 'type' => 'task', 'is_project_range' => true,
            'readonly' => true, 'can_edit_record' => Users_Privileges_Model::isPermitted('Project', 'EditView', $project->getId()),
            'owner' => '', 'owner_id' => '', 'status' => '', 'status_value' => '', 'color' => '#64748b'];
    }

    protected function getProjectDate(Project_Record_Model $project, string $name): string
    {
        $field = $project->getModule()->getField($name);
        $value = (string)$project->get($name);

        return $field && $field->isViewable() && $this->hasDateRange($value, $value) ? $value : '';
    }

    public function isOutsideProject(string $start, string $end, string $projectStart, string $projectEnd): bool
    {
        return $this->hasDateRange($start, $end)
            && (($projectStart !== '' && $start < $projectStart) || ($projectEnd !== '' && $end > $projectEnd));
    }

    public function getChartLinks(array &$tasks, array $links): array
    {
        $indices = [];
        $result = [];

        foreach ($tasks as $index => $task) {
            if (in_array($task['module'], ['ProjectTask', 'ProjectMilestone'], true)) {
                $tasks[$index]['dependency_violation'] = false;
                $tasks[$index]['dependency_conflicts'] = [];
                $indices[$task['recordid']] = $index;
            }
        }

        foreach ($links as $link) {
            if (!isset($indices[$link['sourceid']], $indices[$link['targetid']])) {
                continue;
            }

            $source = $tasks[$indices[$link['sourceid']]];
            $targetIndex = $indices[$link['targetid']];
            $violated = $tasks[$targetIndex]['start_date'] <= $source['end_date'];

            if ($violated) {
                $tasks[$targetIndex]['dependency_violation'] = true;
                $tasks[$targetIndex]['dependency_conflicts'][] = ['source_name' => $source['text'] ?? '',
                    'source_end' => $source['end_date'], 'target_start' => $tasks[$targetIndex]['start_date']];
            }

            $result[] = ['id' => 'link_' . $link['id'], 'source' => $source['id'],
                'target' => $tasks[$targetIndex]['id'], 'type' => '0', 'violated' => $violated];
        }

        return $result;
    }

    protected function getCreateTaskUrl(Vtiger_Module_Model $module, int $projectId): string
    {
        if (!$module->isActive() || !$module->isPermitted('CreateView')) {
            return '';
        }

        return $module->getCreateRecordUrl() . '&projectid=' . $projectId
            . '&returnmodule=Project&returnview=Detail&returnmode=showChart&returnrecord=' . $projectId;
    }

    public function isMissingDate(string $date): bool
    {
        return $date === '' || $date === '0000-00-00';
    }

    protected function getRows(string $moduleName, int $projectId, array $fields): array
    {
        $module = Vtiger_Module_Model::getInstance($moduleName);

        if (!$module || !$module->isActive() || !Users_Privileges_Model::isPermitted($moduleName, 'DetailView')) {
            return [];
        }

        $visibleFields = ['id'];

        foreach ($fields as $name) {
            $field = $module->getField($name);

            if ($field && $field->isViewable()) {
                $visibleFields[] = $name;
            } elseif (!in_array($name, ['assigned_user_id', 'projecttaskstatus', 'projecttaskprogress', 'milestoneid'], true)) {
                return [];
            }
        }

        $query = Core_QueryGenerator_Model::getInstance($moduleName);
        $query->setFields($visibleFields);
        $query->addFieldColumnCondition('projectid', '=', $projectId);

        $rows = $query->getProjectedRows();
        $permissions = Users_Privileges_Model::getDetailViewPermissions($moduleName, array_keys($rows));
        $rows = array_filter($rows, static fn(int $id): bool => $permissions[$id] ?? false, ARRAY_FILTER_USE_KEY);

        if ($moduleName === 'ProjectTask') {
            $snapshots = ProjectTask_Record_Model::getRecordSnapshots(array_keys($rows));

            foreach ($rows as $id => &$row) {
                $snapshot = $snapshots[$id] ?? null;

                if (!$snapshot || (int)$snapshot['values']['projectid'] !== $projectId) {
                    unset($rows[$id]);
                    continue;
                }

                foreach (array_keys($row) as $field) {
                    $column = $field === 'id' ? 'crmid' : $module->getField($field)->get('column');
                    $row[$field] = decode_html((string)($snapshot['values'][$column] ?? ''));
                }
            }

            unset($row);
        }

        return $rows;
    }

    public function isMilestoneDateEditable(int $recordId): bool
    {
        $field = Vtiger_Module_Model::getInstance('ProjectMilestone')->getField('projectmilestonedate');

        return Users_Privileges_Model::isPermitted('ProjectMilestone', 'EditView', $recordId)
            && $field->isEditable()
            && Core_RecordActionPermission_Model::getInstance()->getDenyReason(
                $recordId, Core_RecordActionPermission_Model::ACTION_FIELD_EDIT, 'projectmilestonedate'
            ) === null;
    }

    public function isTaskEditable(Vtiger_Module_Model $module, int $recordId): bool
    {
        $rules = Core_RecordActionPermission_Model::getInstance();

        return Users_Privileges_Model::isPermitted('ProjectTask', 'EditView', $recordId)
            && $module->getField('startdate')->isEditable()
            && $module->getField('enddate')->isEditable()
            && $rules->getDenyReason($recordId, Core_RecordActionPermission_Model::ACTION_FIELD_EDIT, 'startdate') === null
            && $rules->getDenyReason($recordId, Core_RecordActionPermission_Model::ACTION_FIELD_EDIT, 'enddate') === null;
    }

    public function hasDateRange(string $start, string $end): bool
    {
        foreach ([$start, $end] as $date) {
            if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $parts)
                || !checkdate((int)$parts[2], (int)$parts[3], (int)$parts[1])) {
                return false;
            }
        }

        return $start <= $end;
    }

    protected function getColor(string $color): string
    {
        return preg_match('/^#[0-9a-f]{6}$/i', $color) ? $color : '#5b8def';
    }
}
