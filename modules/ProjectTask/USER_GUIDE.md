# Project Tasks

Project tasks describe work within a project, with an assignee, status, priority, progress and optional start and end dates. A task may also be associated with a project milestone. Use the task list or record detail to review and update the work.

## Dates and the project chart

Start and end dates are calendar dates. Enter them in the date format selected in your user preferences; the chart uses the same format when saving a dragged task. The end cannot precede the start; equal dates describe a one-day task. A task with either date missing remains in the project's **Unscheduled** panel. Fill in a valid range and save to place it on the timeline.

Both known task dates must fall within the project's start and planned end dates. If a milestone is selected, it must belong to the same project and neither task date may be after the milestone deadline. Project boundaries and the milestone date are inclusive. Missing project boundaries or an undated milestone do not add a limit; unscheduled tasks may keep missing dates.

The edit form checks dates when they or their project/milestone selection change, and again before saving. This works in full editing, Quick Create and the chart's task edit window. Errors appear through the normal field validation controls; invalid dates prevent saving. The same rules apply to chart moves and server-side saves. Dates are not changed automatically. A rejected chart move restores the previous dates. Existing tasks outside the project period remain visible but their dates must be corrected before saving.

From the project's Chart tab, double-click an editable task or select its name in Unscheduled to open the standard CRM edit overlay, matching projects and milestones. Both date fields are available when your permissions allow them. Locked fields are displayed without an editing control. Read-only tasks can be viewed but cannot be dragged or changed.

The task edit form prepares its controls before appearing. Repeated opening clicks share one window; saving again while a save is pending does not send another request.

Dragging a task moves both dates while preserving its duration. Dragging an edge changes that boundary. Each task is locked against another drag while its save is in progress. A failed save restores the original dates and displays the error. Successful saves reload the project's chart data without reloading the page.

Task saves use the same validation and record rules as normal editing. They do not automatically move milestones, change project dates or move dependent tasks. The project chart marks tasks outside the project's planned period and warns when a successor begins before its predecessor is finished.

The edit window supports the normal CRM relationship selector, including searching for and selecting a related record. Milestone suggestions and the selection list show milestones of the selected project. Saving does not check for changes made by another user since opening the task. Changes use the normal task history.

Administrators can open **Closed task statuses** from the Project Tasks settings menu and select the statuses treated as closed in the chart. Closed tasks and tasks recorded as 100% complete do not show an overdue warning. Task colors still follow CRM status settings; the fill shows the stored completion percentage. Changing the percentage does not automatically change the status.

Saving or cancelling an edit opened from Chart returns to that same chart. Saving refreshes its data and preserves the current display.

Task bars show completion through their fill without a numeric percentage label. The stored percentage remains editable in the task form.

The milestone date is the last allowed day for a linked task. The chart calculates the milestone start from its earliest task start. Saving a task cannot move the milestone deadline automatically.
