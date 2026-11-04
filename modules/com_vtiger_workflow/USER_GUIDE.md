# Workflow execution

Workflows are configured in Settings > Workflows (see the Workflows guide for the editor, conditions and action options). This page describes how configured workflows run.

Record-triggered workflows check their conditions when a record is saved, according to the execution option you chose (for example only on the first save, every time the record is saved, or only when it is modified). Scheduled workflows select the records that meet their conditions each time their schedule runs.

Conditions use the same AND/OR grouping as saved lists, and AND takes precedence over OR. To require either A or B together with C, put A OR B in one group and join it with AND to a group containing C.

Change comparisons such as "has changed" depend on a record save. They are skipped when records are selected for a scheduled workflow, so use ordinary value comparisons there. When a record is created, "has been set or changed" (and its "to" variant) matches a field that was filled in, while "has changed", "has changed to" and "has changed from" do not match.

Actions that send email, SMS or notifications, update fields or create records can insert values from the source record using `$field$` variables. Dates are inserted in the record owner's date format. Condition values are comparisons, not templates, and do not expand variables.
