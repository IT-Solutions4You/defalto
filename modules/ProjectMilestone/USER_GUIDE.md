# Project Milestones

Milestones mark important project dates and may group related project tasks. Set the project, name, date and assignee in the milestone record. Link tasks to the milestone through their Milestone field.

Linked tasks must use the same project. Their known dates must stay on or before the milestone deadline and within the project's planned period; the task form checks these limits when its dates or relations change.

## Editing from the project chart

Open the project's Chart tab. Dated milestones appear as diamonds and their linked tasks appear beneath them in the expandable table. Double-click a milestone, or select it and press Enter, to open its CRM edit window when you have edit permission. You can edit its date, name and other available fields without leaving the chart.

Save applies normal CRM validation and refreshes the chart. Cancel closes the window without saving. Errors leave the window open for correction. Read-only milestones open their record details. Moving a task does not move its milestone.

Drag an editable milestone diamond to change its calendar date. The milestone cannot be moved before any known date of a linked task; the same date is allowed. This restriction also applies in the edit form. Task dates stay unchanged. While saving, another move of that milestone is disabled; a failed save restores its original date and shows the error. Field locks and read-only permissions also apply to dragging.

When assigning a milestone to a task, the selection list and name suggestions are limited to milestones of the task's selected project. Tasks, milestones and the project use the same edit overlay from Chart; save and cancel keep that chart open.

The milestone date is its inclusive deadline. Linked tasks must finish by that date. The chart derives the milestone range start from the earliest accessible linked task start; this calculated value is not stored in the milestone record. Moving a range or its right edge updates only the deadline. Milestones without task starts remain diamonds. Existing tasks beyond a deadline are shown with a warning.

Without its own date, a milestone is shown at the planned project end, keeping its date field empty. You can connect milestones to other milestones with Finish → Start links. Overlapping dates produce a warning; changes never automatically move successors.
