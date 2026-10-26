# Record audit ownership

- `ModTrackerHandler.php` handles `vtiger.entity.beforesave`, `aftersave.final`, `beforedelete` and `afterrestore`. Modules with tracking disabled (`vtiger_modtracker_tabs.visible`) are skipped. Delete and restore insert a `vtiger_modtracker_basic` row directly; before-save registers an audit baseline and final-save delegates to `ModTracker_History_Helper::saveHistory()`. Workflow condition deltas remain separate.
- `models/Install.php` registers the handler and owns the schema of `vtiger_modtracker_basic`, `vtiger_modtracker_detail`, `vtiger_modtracker_tabs` and `vtiger_modtracker_relations`. Tables and columns are created or migrated before the handler is registered. `ModTracker.php` sets its own `$moduleVersion` to version this schema independently.
- Basic history stores record, module, user (`whodid`), time, status and nullable `workflow_id`/`task_id`; detail rows store previous and current field values. Old rows keep null provenance.
- `ModTracker_Basic_Model::saveHistory()` is the shared writer and `ModTracker_Detail_Model::saveFieldChange()` writes field details. Deltas come from `VTEntityDelta::getDataDelta()` and ignore `modifiedtime`; an empty delta writes no history. Table-backed models use `getTable($this->table, $this->tableId)` (`getModTrackerBasicTable()`, `getModTrackerDetailTable()`); do not set table metadata in constructors.
- `models/Record.php::getUpdates()` supplies record history (ordered by `changedon DESC, id DESC`) to `Vtiger_Detail_View`, and the update APIs in `ModTracker.php` consume the same data. The Updates UI is `layouts/d1/modules/Vtiger/RecentActivities.tpl`; `layouts/d1/modules/ModTracker/RecentActivities.tpl` is only a placeholder.
- Preserve user identity separately from automation provenance.

## Audit baselines and workflow provenance

- `ModTracker_History_Helper` keeps per-save baselines in a `WeakMap`. A baseline advances after every history write so an outer save does not repeat changes already recorded by a nested save of the same record. `saveRecord()` wraps `saveentity()` without firing workflow events.
- `processTask()` flushes pending initiating saves under their own identity, then runs `$task->doTask()` with the workflow/task context set and restored in `try/finally`. Its callers are `cron/modules/com_vtiger_workflow/com_vtiger_workflow.php` and `WorkFlowScheduler.php`. It runs the task directly when ModTracker is inactive.
- Workflow and task metadata is resolved at display time, not snapshotted. `getWorkflow()` and `getWorkflowTask()` return data only to administrators and cache results (including nulls, via `array_key_exists()`) per model instance; the task lookup checks both task ID and workflow ID. Deleted definitions fall back to the stored IDs.
- `layouts/d1/modules/ModTracker/Workflow.tpl` renders the provenance of create/update entries as a dropdown: the trigger shows `workflowname`, and the menu shows the actor, workflow description (`summary`; the Settings model's legacy `getName()` also returns it), task details and, below a divider, links to the workflow editor (`mode=V7Edit`, as in the workflow list) and to the task (`history_task_id`). The editor's existing task overlay opens after its action list loads, so never link to an overlay-only task view.
- The shared Vtiger history template marks workflow entries with a `fa-cogs` timeline icon instead of the actor avatar.

## Validation

- Lint changed PHP; manually check history, pagination, tracking-disabled modules, workflow/task provenance and update API compatibility.
