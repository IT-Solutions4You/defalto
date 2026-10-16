# Workflow execution

Configure workflows in Settings > Workflows. Choose when a workflow runs, the conditions records must meet, and the actions to perform. Record triggers evaluate conditions when a record is saved; scheduled workflows select records when their configured schedule runs.

Conditions use the same AND/OR grouping as saved lists. A connector beside a condition joins it to the next condition; a connector between groups joins whole groups. AND takes precedence over OR. To require either A or B together with C, put A OR B in one group and join it with AND to a group containing C.

Change comparisons depend on a record save and are skipped when selecting records for scheduled workflows. Use ordinary value comparisons for scheduled selection. Review the trigger, conditions and actions together before saving.

Workflow action templates use source-record variables in `$field$` format. This format is available in email, SMS, notification, Update Fields, Create Entity, and Create Calendar Record actions. Raw text field mappings can combine literal text with multiple variables; existing `$field` variables remain compatible. Condition values are comparisons rather than templates and do not expand these variables.
