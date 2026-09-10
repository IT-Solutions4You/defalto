# Workflow management

- This Settings module manages definitions executed by `modules/com_vtiger_workflow`; it is not the task execution owner.
- `models/Record.php::getEditViewUrl()` provides the base workflow edit URL. The current editor requires `mode=V7Edit`, as appended by `ListViewContents.tpl`; without a mode, the controller enters the legacy `step1()` flow. Audit UI links must include this mode and enforce Settings access on the server.
- Workflow definitions and task persistence use the legacy workflow manager and task manager. Execution entry points and save-event boundaries are documented in `modules/com_vtiger_workflow/MODULE.md`.
- `workflowname` is the workflow name and `summary` is its description; the legacy record model `getName()` returns `summary`. Task `summary` is its user-supplied title/description; its task type provides the action label.
- `history_task_id` deep links are handled by `Edit.js::showHistoryTask()` after the action container loads. Only an existing task link in the current workflow can open, through the existing overlay handler and its V7 URL; the task opens once per editor instance.
- `resources/Edit.js::loadFieldSpecificUi()` uses date display values only for `rawtext`. `Workflows_Date_Field_Js` in `resources/AdvanceFilter.js` displays expressions and field references from their serialized mapping value and assigns input values through the DOM; never date-format expression source or prefer an empty display value over it. Template input values must be HTML-escaped to preserve quoted expressions.
