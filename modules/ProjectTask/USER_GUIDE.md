# Project Tasks

The record header shows Progress as a compact bar matching the project task widget. Hover over or focus it to see the percentage. Zero shows an empty track; an unset value stays blank. Use the pencil when available to edit the percentage; the bar refreshes after saving.

Project tasks describe work within a project, with an assignee, status, priority, progress and optional start and end dates. A task may also be associated with a project milestone. Use the task list or record detail to review and update the work.

Enter Progress as a whole percentage from 0 to 100, for example 33; the screen displays the percent sign. You can enter any whole percentage instead of choosing ten-percent steps. Fractional values are rejected. Progress is stored as a whole number. The project task table, chart and project calculation use the same whole percentage.

## Updating project progress

The optional **Calculate project progress** workflow uses Planned Hours as each task's weight in the project's overall progress. Enter an estimate for the most useful result. When an estimate is missing, the calculation uses the task's calendar duration, then fallback hours if dates are incomplete or invalid. Worked Hours are not used as the weight.

Administrators can configure the workflow action in **Settings → Workflows → Project Tasks**: choose the statuses that count as completed (100%), statuses excluded from the calculation, hours per calendar day and fallback hours. Both hour settings must be positive; both initially use 8. If a status is in both groups, exclusion takes precedence. Update these selections if status values are renamed.

Select **How project progress is calculated** above the action settings to expand a short explanation of estimates, task statuses, automatic updates, manual changes and disabling the calculation. It is collapsed initially and uses spaced paragraphs and bold key points without a notification box.

Each setting also has a short explanation directly below its label: when hours per calendar day apply, what the fallback hours represent, which statuses count as finished and which are excluded.

The default workflow runs after every task save. Deletion also recalculates the project when the task matches an enabled every-save or on-modify workflow; restoration uses the normal workflow trigger. Moving a task recalculates both its old and new project. Conditions select which task operations trigger a recalculation; the aggregate itself always includes all eligible tasks of the project. Avoid restrictive trigger conditions if every change should update progress.

Disable the action or entire workflow to stop automatic updates, including deletion updates. Project Progress stays editable; while automation is enabled, later task operations can overwrite a manual value. Re-enabling automation takes effect on the next qualifying operation and does not bulk-update existing projects.

## Checklists

Administrators can add **Checklist** to task Summary through Settings → Widget editor → Project Tasks. The Summary widget uses the same checklist controls and task edit permissions as Details and Edit. The field label follows the normal layout in each screen; in Edit it has the same text color as Description.

Installing or updating the module registers a missing Checklist widget in the right Summary column. If an administrator has hidden or moved it, that choice remains in place during module updates.

The **Checklist** field represents one checklist. It appears in the ordinary **Description** block by default and can be moved to another block by an administrator. Its value contains only item texts, one per line; completion states are stored separately. Details and Edit show the same simple list: a checkbox and item text on each row, followed by **+ List item**. There is no frame, percentage counter or progress bar. Press Enter to add an item and continue with the next one. Select item text to edit it; hover or use keyboard focus to reveal its remove icon.

For saved tasks, checklist changes save immediately in either Details or Edit without saving the whole task or reloading the page. The server checks task and field edit permission for every change; read-only locks also apply. For a new task, prepare item texts before saving; completion controls become available after the first save. Complete or cancel an item edit before saving the task form. Checklist completion does not change the task's status or progress, and items do not include due dates or assignees. A copied task retains item text with all items unchecked. More checklists require more checklist fields, which administrators can add through Layout Editor without creating a special block.

## Dates and the project chart

Start and end dates are calendar dates. Enter them in the date format selected in your user preferences; the chart uses the same format when saving a dragged task. The end cannot precede the start; equal dates describe a one-day task. A task with either date missing remains in the project's **Unscheduled** panel. Fill in a valid range and save to place it on the timeline.

Both known task dates must fall within the project's start and planned end dates. If a milestone is selected, it must belong to the same project and neither task date may be after the milestone deadline. Project boundaries and the milestone date are inclusive. Missing project boundaries or an undated milestone do not add a limit; unscheduled tasks may keep missing dates.

The edit form checks dates when they or their project/milestone selection change, and again before saving. This works in full editing, Quick Create and the chart's task edit window. Errors appear through the normal field validation controls; invalid dates prevent saving. The same rules apply to chart moves and server-side saves. Dates are not changed automatically. A rejected chart move restores the previous dates. Existing tasks outside the project period remain visible but their dates must be corrected before saving.

From the project's Chart tab, double-click an editable task or select its name in Unscheduled to open the standard CRM edit overlay, matching projects and milestones. Both date fields are available when your permissions allow them. Locked fields are displayed without an editing control. Read-only tasks can be viewed but cannot be dragged or changed.

The task edit form prepares its controls before appearing. Repeated opening clicks share one window; saving again while a save is pending does not send another request.

Dragging a task moves both dates while preserving its duration. Dragging an edge changes that boundary. Each task is locked against another drag while its save is in progress. A failed save restores the original dates and displays the error. Successful saves reload the project's chart data without reloading the page.

Task saves use the same validation and record rules as normal editing. They do not automatically move milestones, change project dates or move dependent tasks. The project chart marks tasks outside the project's planned period and warns when a successor begins before its predecessor is finished.

The edit window supports the normal CRM relationship selector, including searching for and selecting a related record. Milestone suggestions and the selection list show milestones of the selected project. Saving does not check for changes made by another user since opening the task. Changes use the normal task history.

Administrators can open **Closed task statuses** from the Project Tasks settings menu and select the statuses treated as closed in the chart. Closed tasks and tasks recorded as 100% complete do not show an overdue warning. Task colors still follow CRM status settings; the fill shows the stored completion percentage. Changing the percentage does not automatically change the status. Installation, module updates and re-enabling the module add the default closed statuses Completed and Canceled only when no closed statuses are stored. Any nonempty selection is preserved; an empty selection receives the defaults at the next registration.

Saving or cancelling an edit opened from Chart returns to that same chart. Saving refreshes its data and preserves the current display.

Task bars show completion through their fill without a numeric percentage label. The stored percentage remains editable in the task form.

The milestone date is the last allowed day for a linked task. The chart calculates the milestone start from its earliest task start. Saving a task cannot move the milestone deadline automatically.

## Responsibility

The **Assignee** field (Riešiteľ in Slovak) identifies the user or group responsible for the task. Select the responsible person or group when creating or editing a task; existing assignments and access rules stay the same.
