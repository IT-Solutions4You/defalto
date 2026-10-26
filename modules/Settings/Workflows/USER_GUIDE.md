# Workflows

Workflows run configured actions when records meet the selected trigger and conditions. Open a workflow to review its name, description, execution settings, conditions and actions before saving. Conditions decide which records qualify; actions decide what happens to those records or to related records.

## Conditions

The condition editor works like the saved-list filter editor. Use Add condition to add a blank rule to a group and choose its field, comparison and value. Use Group to add another group, and the trash buttons to remove a rule or a group other than the first. Some comparisons, such as "is empty" or "has changed", need no value. For inventory documents, the main record blocks are listed first and the Item Details fields last.

Choose AND or OR beside each condition to connect it to the next one, and use the connector between groups to connect whole groups. AND takes precedence over OR: A OR B AND C means A OR (B AND C). Use separate groups when you need (A OR B) AND C. Saving and reopening a workflow preserves these choices. The same grouping applies to record-triggered and scheduled workflows.

Comparisons that look at a change, such as "has changed" and "has been set or changed", need a record save. They are skipped when records are selected for a scheduled workflow, and for workflows that run only when a record is created some of them are hidden. If a saved condition is not allowed for the chosen trigger, the editor shows an error and you must correct it before saving. Comparisons on related-record fields do not offer change options.

## Actions

Action names, comparison operators, deletion options, creator details and validation messages are shown in the selected application language. This includes the sharing actions and extension actions such as creating a calendar record, sending an EMAIL Maker message and making a record read-only.

In **Update Fields**, **Create Entity** and **Create Calendar Record**, open a mapped value to combine text with one or more source-record variables. Inserted variables use the `$field$` format; existing `$field` values keep working. Use the pencil button when the field normally displays a select. To copy a value into a date, number, currency or similar field without conversion, use the Field Name value type instead of raw text. Condition values are literal comparisons and do not offer variables.

**Create Calendar Record** can also fill additional standard and custom Appointments fields: use **Add field**, choose the destination field, and enter fixed text, source-record variables, a source field or an expression.

**Move to Recycle Bin** processes the record through the workflow queue. Its **Deletion mode** is either Move to Recycle Bin, so the record can be restored, or Delete permanently, which cannot be undone.

## Workflow list

The list can be filtered by module and searched. It shows each workflow's name with its description below, a summary of its conditions (All and Any), its actions, and, in the last column, the creator with the creation date and time in your time zone and hour format. Workflows created before this information was recorded show the administrator and a dash instead of a time.
