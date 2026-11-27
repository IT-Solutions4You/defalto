<?php
/** CRM-backed fixture runner. All entity and relation writes use their owning models. */
class GanttDemoRunner
{
    protected static array $moduleNameFields = [
        'Project' => 'projectname',
        'ProjectMilestone' => 'projectmilestonename',
        'ProjectTask' => 'projecttaskname',
    ];
    protected static array $dateFields = ['startdate', 'enddate', 'targetenddate', 'projectmilestonedate'];
    protected static array $relatedModules = ['ProjectTask', 'ProjectMilestone'];
    private array $state;
    private array $settings;
    private array $modules = [];

    public function __construct(private string $path, private string $batch, private array $plan, ?array $manifest)
    {
        $this->settings = require __DIR__ . '/Settings.php';
        $this->state = $manifest ?? [];

        if ($manifest && (($manifest['schema'] ?? 0) !== 1 || $manifest['batch'] !== $batch
            || $manifest['site'] !== rtrim((string)vglobal('site_URL'), '/'))) {
            throw new Exception('Manifest does not belong to this CRM installation/batch.');
        }
    }

    public function processImport(string $ownerArgument): void
    {
        if (($this->state['state'] ?? '') === 'removed') {
            throw new Exception('This batch was removed. Use a new --batch name for a new import.');
        }

        $hash = hash('sha256', json_encode($this->plan, JSON_THROW_ON_ERROR));

        if ($this->state && $this->state['plan_hash'] !== $hash) {
            throw new Exception('Existing batch uses another plan. Restore its parameters or use a new --batch.');
        }

        foreach (array_keys(self::$moduleNameFields) as $moduleName) {
            $module = Vtiger_Module_Model::getInstance($moduleName);

            if (!$module || !$module->isActive() || !Users_Privileges_Model::isPermitted($moduleName, 'CreateView')) {
                throw new Exception('Module creation is not available: ' . $moduleName);
            }

            $this->modules[$moduleName] = $module;
        }

        $dependency = new Project_Dependency_Model();

        if (!$dependency->isAvailable()) {
            throw new Exception('Install the regular system update first: dependency storage is missing.');
        }

        $owners = $this->getOwners($ownerArgument);
        $projectStatus = $this->getPicklistValue('Project', 'projectstatus', 'in progress');
        $openStatus = $this->getPicklistValue('ProjectTask', 'projecttaskstatus', 'Open');
        $priority = $this->getPicklistValue('ProjectTask', 'projecttaskpriority', 'normal');
        $type = $this->getPicklistValue('ProjectTask', 'projecttasktype', 'operative');
        $milestoneStatus = $this->getPicklistValue('ProjectMilestone', 'projectmilestonestatus', 'Planned');
        $milestoneType = $this->getPicklistValue('ProjectMilestone', 'projectmilestonetype', 'operative');
        $statuses = [];

        foreach ($this->plan['tasks'] as $task) {
            $statuses[$task['status']] = $this->getPicklistValue('ProjectTask', 'projecttaskstatus', $task['status']);
            $this->getPicklistValue('ProjectTask', 'projecttaskprogress', $task['progress']);
        }

        // Preflight configured mandatory fields before creating the first record.
        $this->validateMandatoryFields('Project', ['projectname'=>'demo','assigned_user_id'=>$owners[0], 'startdate'=>$this->plan['start'],
            'targetenddate'=>$this->plan['end'], 'projectstatus'=>$projectStatus]);
        $this->validateMandatoryFields('ProjectMilestone', ['projectmilestonename'=>'demo','projectmilestonedate'=>$this->plan['end'],
            'projectid'=>1,'assigned_user_id'=>$owners[0],'projectmilestonestatus'=>$milestoneStatus,'projectmilestonetype'=>$milestoneType]);
        $this->validateMandatoryFields('ProjectTask', ['projecttaskname'=>'demo','projectid'=>1,'assigned_user_id'=>$owners[0],
            'projecttaskstatus'=>$openStatus,'projecttaskpriority'=>$priority,'projecttasktype'=>$type,'projecttaskprogress'=>'10%']);

        if (!$this->state) {
            $this->state = ['schema'=>1,'batch'=>$this->batch,'site'=>rtrim((string)vglobal('site_URL'), '/'),
                'token'=>bin2hex(random_bytes(12)), 'start'=>$this->plan['start'], 'task_count'=>$this->plan['task_count'],
                'plan_hash'=>$hash,'owners'=>$owners,'records'=>[],'links'=>[],'finalized'=>[],'state'=>'importing'];
            $this->saveManifest();
        }

        $project = $this->createRecord('main', 'Project', 'projectname', $this->plan['project_name'],
            ['startdate'=>$this->plan['start'],'targetenddate'=>$this->plan['end'],'assigned_user_id'=>$owners[0], 'projectstatus'=>$projectStatus]);
        $this->createRecord('empty', 'Project', 'projectname', 'Prázdny projekt na overenie prvej úlohy',
            ['startdate'=>$this->plan['start'],'targetenddate'=>$this->plan['end'],'assigned_user_id'=>$owners[0], 'projectstatus'=>$projectStatus]);

        foreach ($this->plan['milestones'] as $index => $milestone) {
            $this->createRecord($milestone['key'], 'ProjectMilestone', 'projectmilestonename', $milestone['name'],
                ['projectid'=>$project,'projectmilestonedate'=>$milestone['deadline'],'assigned_user_id'=>$owners[$index % count($owners)],
                    'projectmilestonestatus'=>$milestoneStatus,'projectmilestonetype'=>$milestoneType]);
        }

        foreach ($this->plan['tasks'] as $index => $task) {
            $this->createRecord($task['key'], 'ProjectTask', 'projecttaskname', $task['name'], ['projectid'=>$project,
                'milestoneid'=>$task['milestone'] ? $this->state['records'][$task['milestone']]['id'] : '',
                'startdate'=>$task['start'],'enddate'=>$task['end'],'assigned_user_id'=>$owners[$task['owner_slot'] % count($owners)],
                'projecttaskstatus'=>$openStatus,'projecttaskpriority'=>$priority,'projecttasktype'=>$type,
                'projecttaskprogress'=>$this->getPicklistValue('ProjectTask', 'projecttaskprogress', $task['progress']),
                'planed_hours'=>(string)(2 + $index % 7 * 2)]);

            if (($index + 1) % 25 === 0) {
                echo 'Tasks: ' . ($index + 1) . '/' . count($this->plan['tasks']) . "\n";
            }
        }

        $existingLinks = $dependency->getProjectLinks($project);

        foreach ($this->plan['links'] as [$source,$target]) {
            $sourceId = $this->state['records'][$source]['id'];
            $targetId = $this->state['records'][$target]['id'];
            $pair = $source . ':' . $target;

            if (isset($this->state['links'][$pair])) {
                continue;
            }

            $matches = array_filter($existingLinks, static fn(array $link): bool => $link['sourceid'] === $sourceId && $link['targetid'] === $targetId);

            if (!$matches) {
                $dependency->createDependency($project, $sourceId, $targetId);
                $existingLinks[] = ['sourceid'=>$sourceId,'targetid'=>$targetId];
            }

            $this->state['links'][$pair] = true;
            $this->saveManifest();
        }

        // Set final statuses after relations exist, respecting ordinary CRM status/field rules.
        foreach ($this->plan['tasks'] as $task) {
            if (!isset($this->state['finalized'][$task['key']])) {
                if ($statuses[$task['status']] !== $openStatus) {
                    $this->saveRecord('ProjectTask', ['record'=>$this->state['records'][$task['key']]['id'],
                        'projecttaskstatus'=>$statuses[$task['status']]]);
                }

                $this->state['finalized'][$task['key']] = true;
                $this->saveManifest();
            }
        }

        $this->state['state'] = 'complete';
        $this->saveManifest();
        echo "Import complete. Existing records were not reset on reruns.\n";
        echo rtrim((string)vglobal('site_URL'), '/') . '/index.php?module=Project&view=Detail&record=' . $project . "&mode=showChart&app=PROJECT\n";
        echo 'Manifest: ' . $this->path . "\n";
    }

    private function getOwners(string $argument): array
    {
        $user = Users_Record_Model::getCurrentUserModel();
        $users = $user->getAccessibleUsersForModule('ProjectTask');
        $groups = $user->getAccessibleGroupForModule('ProjectTask');
        $available = array_unique([...array_keys($users), ...array_keys($groups)]);
        $requested = $argument !== '' ? explode(',', $argument) : ($this->state['owners'] ??
            [...array_slice(array_keys($users), 0, 4), ...array_slice(array_keys($groups), 0, 1)]);
        $owners = [];

        foreach ($requested as $id) {
            if (!ctype_digit((string)$id) || !in_array((int)$id, array_map('intval', $available), true)) {
                throw new Exception('Owner is not available for ProjectTask: ' . $id);
            }

            $owners[] = (int)$id;
        }

        $owners = array_values(array_unique($owners));

        if (!$owners || ($this->state && $owners !== $this->state['owners'])) {
            throw new Exception('Supply at least one owner; resumed batches must keep their original owner list.');
        }

        return $owners;
    }

    private function getPicklistValue(string $module, string $field, string $value): string
    {
        $mapped = $this->settings['picklists'][$module][$field][$value] ?? $value;
        $available = $this->modules[$module]->getField($field)->getPicklistValues() ?? [];

        if (!array_key_exists($mapped, $available)) {
            throw new Exception("Missing picklist $module.$field: $mapped. Map it in tests/gantt/Settings.php. Available: " . implode(', ', array_keys($available)));
        }

        return $mapped;
    }

    private function getFieldData(string $module, array $values): array
    {
        $data = [];

        foreach ($this->modules[$module]->getFields() as $name => $field) {
            if ($field->isEditable() && !in_array($field->getDefaultFieldValue(), ['', null], true)) {
                $data[$name] = $field->getDefaultFieldValue();
            }
        }

        return array_replace($data, $this->settings['fields'][$module] ?? [], $values);
    }

    private function validateMandatoryFields(string $module, array $values): void
    {
        $data = $this->getFieldData($module, $values);

        foreach ($this->modules[$module]->getMandatoryFieldModels() as $field) {
            if ($field->isEditable() && (int)$field->get('uitype') !== 4) {
                Vtiger_Util_Helper::validateFieldValue($data[$field->getName()] ?? '', $field);
            }
        }
    }

    private function createRecord(string $key, string $module, string $nameField, string $name, array $values): int
    {
        if (isset($this->state['records'][$key])) {
            $entry = $this->state['records'][$key];
            $this->validateRecord($entry);

            return $entry['id'];
        }

        $query = Core_QueryGenerator_Model::getInstance($module);
        $query->setFields(['id']);
        $query->addFieldColumnCondition('description', '=', $this->getRecordDescription($key));

        if (isset($values['projectid'])) {
            $query->addFieldColumnCondition('projectid', '=', $values['projectid']);
        }

        $matches = $query->getProjectedRows();

        if (count($matches) > 1) {
            throw new Exception('Ambiguous fixture record: ' . $name);
        }

        if ($matches) {
            $id = (int)array_key_first($matches);
        } else {
            $data = $this->getFieldData($module, [$nameField=>$name, ...$values,
                'description'=>$this->getRecordDescription($key)]);
            $id = (int)$this->saveRecord($module, $data)->getId();
        }

        $entry = ['id'=>$id,'module'=>$module,'projectid'=>(int)($values['projectid'] ?? 0)];
        $this->validateRecord($entry);
        $this->state['records'][$key] = $entry;
        $this->saveManifest();

        return $id;
    }

    private function saveRecord(string $module, array $data): Vtiger_Record_Model
    {
        foreach (self::$dateFields as $field) {
            if (!empty($data[$field])) {
                $data[$field] = DateTimeField::convertToUserFormat($data[$field]);
            }
        }

        $actionClass = Vtiger_Loader::getComponentClassName('Action', 'Save', $module);

        return (new $actionClass())->saveRecord(new Vtiger_Request(['module'=>$module, ...$data], []));
    }

    private function getMarker(): string
    {
        return '[GANTT-DEMO:' . $this->state['token'] . ']';
    }

    private function getRecordDescription(string $key): string
    {
        return $this->getMarker() . "\nGenerated Gantt demonstration: " . $key;
    }

    public function processNameCleanup(): void
    {
        if (!$this->state || ($this->state['state'] ?? '') === 'removed') {
            throw new Exception('Name cleanup requires an existing fixture manifest.');
        }

        foreach ($this->state['records'] as $entry) {
            $this->validateRecord($entry);
        }

        $prefix = '[GANTT ' . $this->batch . '] ';
        $count = 0;

        foreach ($this->state['records'] as $entry) {
            $field = self::$moduleNameFields[$entry['module']];
            $record = Vtiger_Record_Model::getInstanceById($entry['id'], $entry['module']);
            $name = decode_html((string)$record->get($field));

            if (str_starts_with($name, $prefix)) {
                if (!Users_Privileges_Model::isPermitted($entry['module'], 'EditView', $entry['id'])) {
                    throw new Exception('Name editing is not permitted: ' . $entry['id']);
                }

                $this->saveRecord($entry['module'], ['record'=>$entry['id'], $field=>substr($name, strlen($prefix))]);
                $count++;
            }
        }

        echo 'Fixture name prefixes removed: ' . $count . ".\n";
    }

    private function validateRecord(array $entry): void
    {
        if (!isRecordExists($entry['id']) || getSalesEntityType($entry['id']) !== $entry['module']) {
            throw new Exception('Fixture record is missing or changed type: ' . $entry['id']);
        }

        $record = Vtiger_Record_Model::getInstanceById($entry['id'], $entry['module']);

        if (!str_starts_with(decode_html((string)$record->get('description')), $this->getMarker())
            || ($entry['projectid'] && (int)$record->get('projectid') !== $entry['projectid'])) {
            throw new Exception('Record no longer belongs to this fixture batch: ' . $entry['id']);
        }
    }

    public function processCleanup(): void
    {
        if (!$this->state) {
            throw new Exception('Cleanup requires the original manifest. No records were removed.');
        }

        $known = array_column($this->state['records'], 'id');

        // Check every live record and every child before deleting anything.
        foreach ($this->state['records'] as $entry) {
            if (!isRecordExists($entry['id'])) {
                continue;
            }

            $this->validateRecord($entry);

            if (!Users_Privileges_Model::isPermitted($entry['module'], 'Delete', $entry['id'])) {
                throw new Exception('Deletion is not permitted: ' . $entry['id']);
            }

            if ($entry['module'] === 'Project') {
                foreach (self::$relatedModules as $module) {
                    $query = Core_QueryGenerator_Model::getInstance($module);
                    $query->setFields(['id']);
                    $query->addFieldColumnCondition('projectid', '=', $entry['id']);

                    foreach (array_keys($query->getProjectedRows()) as $child) {
                        if (!in_array($child, $known, true)) {
                            throw new Exception('Project contains an untracked record; cleanup stopped: ' . $child);
                        }
                    }
                }
            }
        }

        foreach (array_reverse($this->state['records']) as $entry) {
            if (isRecordExists($entry['id'])) {
                Vtiger_Record_Model::getInstanceById($entry['id'], $entry['module'])->delete();
                echo 'Removed ' . $entry['module'] . ' ' . $entry['id'] . "\n";
            }
        }

        $this->state['state'] = 'removed';
        $this->saveManifest();
        echo "Fixture records moved through normal CRM deletion. Keep the manifest.\n";
    }

    private function saveManifest(): void
    {
        if (!is_dir(dirname($this->path)) && !mkdir(dirname($this->path), 0770, true)) {
            throw new Exception('Cannot create manifest directory.');
        }

        $temporary = $this->path . '.tmp';
        $json = json_encode($this->state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        if (file_put_contents($temporary, $json) === false || !rename($temporary, $this->path)) {
            throw new Exception('Cannot write manifest.');
        }
    }
}
