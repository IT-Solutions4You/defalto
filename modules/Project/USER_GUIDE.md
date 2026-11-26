# Projects

Projects keep project details together with their tasks, milestones and related records. Use the Tasks list to manage work and assignees, and Milestones to record important project dates. Task statuses describe the current stage of work.

## Calculated project progress

The optional **Calculate project progress** workflow in Project Tasks updates the project's Progress after a task is saved, moved to another project, deleted or restored. Moving a task recalculates both projects. The result includes all tasks of the project, even tasks hidden from the person saving the record.

Progress is a weighted average: each task's progress is multiplied by its planned hours, then the total is divided by the total planned hours. The final result is rounded to a whole percentage. A completed 80-hour task and an unstarted 8-hour task give 91%. Completed statuses count as 100%; excluded statuses, initially Canceled, do not contribute. Other statuses use the task's Progress, with an empty value counting as zero. A project without included tasks has 0% progress.

Task Progress also uses whole percentages from 0 to 100. The task table, chart and project calculation use the same whole percentages.

When planned hours are missing or zero, the weight uses the inclusive number of calendar days between task start and end, multiplied by Hours per calendar day (initially 8). Without a valid complete date range it uses the fallback hours (initially 8). Actual worked hours do not change the weight. Adding work or changing estimates can reduce the calculated percentage.

Project Progress remains editable and accepts whole percentages from 0 to 100. Progress is stored as a whole number. The next qualifying task operation can overwrite a manual value. Disable the workflow or its action in **Settings → Workflows → Project Tasks** to keep managing progress manually; disabling leaves the last value unchanged. Existing projects are not recalculated in bulk during installation or update.

## Summary task table

In All, the task Status column appears before Progress.

The table uses the same records-per-page limit as standard lists. Only the requested batch is loaded into the table. Select Show more to fetch the next batch from the server without leaving the project. The button disappears when all matching tasks are shown. Switching status tabs or changing filters starts again with one batch; tab counts always include every matching task.

Key Metrics shows four indicators in order: Open Tasks, Progress, Overdue Tasks and Completed Tasks. Priority totals are not displayed here. On phones, indicators appear as compact rectangular cards. Cards keep space between rows on narrow screens without extra spacing below the last row.

Use Filter beside the status tabs to choose statuses, assignees, milestones (including No milestone), priorities or a date range. Select several values within a category to include any of them; different categories must all match. The selected status tab also applies. Changing a filter or status requests the first matching batch from the server and updates the tab counts for all matches. Done closes the menu. Active choices appear as removable chips; Clear filters removes them together. A tab with no matching tasks shows an empty result message.

Date filters include tasks whose start/end period overlaps the chosen dates, including both boundary days. With only one task date, that date is used; tasks without dates are excluded while a date filter is active. Use the standard CRM calendar beside each date field, or type a date in your selected date format. Invalid dates and an end date before the start date are marked for correction. The calendar offers the usual Today and Clear actions. Filters are remembered separately for each user and project in this browser and survive widget refreshes. Only values you may view are offered.

Select a status tab above the task table to show its tasks, or All to show every visible task. Open is the default tab. If there are no open tasks, In Progress opens; if neither status has tasks, All opens. All includes a Status column with a colored dot. Other status tabs hide this column. Each status tab shows a colored dot and the task count; the active status is marked by a thin underline. Your selected status is remembered separately for each user and project in the same browser after refreshing or reopening the project. If that status has no tasks left, the default selection applies. Use arrow keys, Home or End to switch tabs from the keyboard.

Date ranges hide repeated months and years to save space, using your selected date format. A one-day range shows one date. Both years remain visible when the range crosses a year boundary. Hover over a range to see both full dates.

The task widget follows the active Defalto theme, with the same header, action buttons and compact table styling as other application widgets.

Priority appears before the task name: a solid red flag for High, a gray outline flag for Low, and an empty slot for Normal so task names stay aligned. Hover over this slot to see the priority name.

The Summary tab shows all project tasks you may view in the standard side-column widget, organized into status tabs above the table. Select a tab to view tasks in that status. Configured closed statuses appear last, with muted, crossed-out task names. The widget header shows creation actions. Task counts appear in the status tabs. Hidden fields and records are excluded from the display.

On narrow screens, scroll the table horizontally to see every column. Columns show the task name, progress bar, linked milestone, assignee avatar and start/end dates. The assignee is shown only as initials in an avatar; hover over it to see the full name. This leaves more room for task names. Task progress uses a 56px bar in a 110px column; hover over the bar to see its percentage in a tooltip. Unset progress is shown as —. Project progress is available in Key Metrics; the task progress column contains only its bar. The task-name column heading is blank. Open tasks past their deadline show red dates and a calendar-day overdue count in your time zone. Empty values are identified without inventing dates, owners or milestones. Task names use the normal dark text color and remain clickable links. Select a task or milestone name to open its record quick preview over the project. Closing the preview returns to the same project view.

Use the menu at the far right of a task row to change its status when you have permission. Locked fields and denied status choices remain unavailable. A successful save refreshes the groups and counts without leaving the project. Change task progress in the task form.

Add Project Milestone and Add Project Task appear in the header according to module availability, creation permission and the supported relation actions. They open the standard Quick Create form with the current project preselected, and refresh the task widget after saving. If Quick Create is unavailable, the normal full form opens. Task creation is available only in the header; there is no duplicate footer button. The separate milestone summary widget and former status/progress filter dropdowns are replaced by this grouped overview; milestones remain available through their related tab and task links.

## Chart

Open a project and select **Chart** to see its scheduled tasks and milestones on a shared timeline. The left table shows the task or milestone name, start date and number of calendar days. Task assignees appear as initials beside their task name, with the full name on hover. Task colors follow the task status colors configured in CRM; milestones appear as ranges, or deadline diamonds when no task start is available.

The project row shows its planned date range. Expand or collapse the tree to see milestones and their linked tasks; tasks without a visible dated milestone appear directly under the project. The list icon hides or shows the task table. Today has a blue marker and a highlighted column; weekly and monthly scales highlight the period containing today.

Names in the left table are vertically centered. Status colors and completion fill appear on the timeline; numeric percentages remain in tooltips, without a second line beneath names in the table.

Refreshing the chart keeps one set of controls and actions active. Repeated clicks while a task edit window is loading do not open additional windows.

After moving a task or milestone, the chart refreshes in place and retains your view. If refreshed data cannot be loaded, the existing chart stays visible and an error is shown.

After each refresh you can continue moving the same task or other records without reopening the page.

The chart remembers filters, table visibility, selection and scroll position separately for each project. Fresh openings choose daily columns for projects up to 30 calendar days, weekly columns for 31–56 days, and years/months for longer projects. In-place refreshes retain the working scale. On the first opening it shows today when today falls within the project period, otherwise the project start. The filled portion of a task bar represents its recorded completion percentage. Completed tasks are not marked overdue; administrators can configure which task statuses mean completed or closed.

Use **+** and **−** to zoom between daily, weekly and monthly scales. Tasks missing either date appear as one calendar day at the project start when that date is available; milestones without their own deadline use the project end. Start and end dates are inclusive, so a task starting and ending on the same day lasts one day.

Tasks missing one or both dates appear on the timeline at the project start with a duration of one calendar day. This display default does not write dates merely by opening the chart. Moving or resizing the task saves its new dates through normal CRM validation. The default start also participates in the linked milestone range. Complete valid task dates stay unchanged. Invalid reversed ranges, or missing dates when the project start is unavailable, remain available for correction in Unscheduled. Read-only tasks keep their normal access restrictions.

An empty chart explains how to start planning. When no tasks or dated milestones are available, users allowed to create project tasks can select **Add Project Task**; the new task is linked to the current project.

Double-click an editable task, the project row or a milestone to open the same standard CRM edit overlay; selecting the row and pressing Enter also works. Save updates the chart without leaving it, while Cancel keeps the existing chart. Read-only records open their details. Project dates can be edited in the project form; the project bar cannot be dragged. Milestone diamonds can be dragged when you can edit their dates, without moving linked tasks. A milestone deadline cannot be moved before any linked task date, including tasks hidden by filters.

Drag a task to move its dates, or drag its edge to change its duration. Changes are saved to the related project task. Editing requires permission to edit the task and its date fields.

A task linked to a dated milestone cannot be saved with dates after that milestone deadline. The same day is allowed. Rejected chart moves restore the previous dates and explain the restriction; the rule also applies in the task edit form.

Task dates must also stay within the project's start and planned end dates. The task edit form rechecks dates after changing the project or milestone. A milestone from another project cannot be assigned. These checks also apply to dragging; the chart does not adjust project or milestone dates to accommodate an invalid task range.

Select **Print** to open the chart in a separate read-only page, then use its **Print** button. The printed chart includes scheduled tasks, milestones and the Unscheduled panel available to you. For changes to assignees, statuses or milestone dates, use the corresponding task or milestone record.

## Filters and saving

Search by task or milestone name, or filter by assignee and task status. The filters apply together to the left table, timeline and Unscheduled panel. Milestones remain as context for their matching tasks. The print link carries the current period and filters into the print view, which includes the project name and generation date. Clear a filter to show all matching records again.

Select a task and press Enter to open it, or double-click it. Use the edit overlay to change dates without dragging. Saving does not check for concurrent changes by other users.

Moving a task preserves its calendar duration. Resizing an edge changes only that boundary, with a minimum of one day. A task cannot be dragged again while its save is pending. Failed saves restore its original dates and display the error. After a successful save, the Chart tab reloads the dates, statuses and colors stored in CRM while retaining your filters.

A warning symbol and dashed outline identify tasks outside the project's planned start/target-end range. This is an alert for review: neither the project's dates nor milestone dates are changed automatically.

## Dependencies

After the system database update installs dependency storage, users with permission to edit the project and both records can connect tasks to tasks or milestones to milestones using Finish → Start. The successor should begin after the predecessor's final calendar day. Links remain stored when you close and reopen the project.

Only **Finish → Start** links are supported. A task or milestone cannot link to itself, the same directed link cannot be added twice, and circular chains are rejected. Double-click a link and confirm to remove it. Dependencies do not move dates automatically. When timing violates a dependency, the successor and its link show a warning. Links to tasks you cannot view are not displayed.

Saving or cancelling the overlay keeps the current project Chart tab open, with its filters, scroll position and selected record. Validation errors keep the form open for correction. Assignee filters use the current CRM assignees.

Task bars show completion through their fill without a numeric percentage label. The stored percentage remains editable in the task form.

The project name appears without a Project prefix in both the table and timeline. Search and filters are on the left of the chart toolbar, with zoom, table and print controls on the right. On narrow screens the toolbar wraps.

Chart filter names appear inside their controls to keep the toolbar compact. Search has a placeholder, the assignee selector starts with All Users, and the status selector starts with Status: All.

The chart automatically opens at a daily, weekly or monthly scale based on the planned project duration. Both boundary days count. Without a complete valid project range it opens with years and months. Use + or − to change the scale; saving an edit keeps your working zoom. The assignee filter supports several users or groups, name search, My, All and Apply. Filtering and printing use the same selection. The information icon in the toolbar explains task dependencies. The project name is centered in its range bar.

Milestones span from the earliest accessible linked task start to the deadline stored in the milestone record. Changing task starts recalculates the displayed start. Moving the range or its right edge changes only the deadline; its calculated start stays fixed. With no task start, a diamond marks the deadline. Existing tasks past the deadline show a warning and need correction before saving; invalid reversed ranges remain deadline diamonds until corrected.

The information menu separates milestone rules from task-link instructions. It explains the calculated start, stored deadline, task deadline limit and marker fallback.
In the chart table, the assigned person’s initials appear in a round avatar before each task name. Hover over the avatar to see the full assignee name. The tree remains expandable and task names still open the edit overlay.

The day count includes both the start and end day, so a one-day task shows 1. Weekends count as calendar days. Point milestones show 0; dated milestone ranges use the same inclusive counting. Hover over the number to see the full date range.
Task avatars have no empty expander slot in front of them; the project and milestone hierarchy remains expandable without leading indentation.

Milestones may depend on other milestones. A successor should start after the preceding milestone ends; an overlap shows a warning without automatic rescheduling. A milestone without its own date uses the planned project end for display. The date field stays empty until you set it.



Click the warning button beside the day count or on the timeline bar to open a compact, scrollable warning window. It lists every exact cause and what to change: dates, a wrong dependency, a milestone deadline, or a completed task status when appropriate. Dependency warnings name the predecessor and conflicting dates; milestone warnings identify the task past its deadline. Details appear only in this window, without banner summaries or hover warnings. The button also works with Enter or Space; closing the window returns focus to it. Warnings do not automatically change any record.

## Responsibility

The **Project manager** field identifies the user or group responsible for the project. Select the responsible person or group when creating or editing a project; existing assignments and access rules stay the same.
