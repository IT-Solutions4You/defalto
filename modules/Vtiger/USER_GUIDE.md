# Shared record lists

Column settings allow more than 15 fields with no maximum count. Add the fields you need, arrange their order, and save. At least one field must remain selected.

Advanced search starts with one condition row after a module is selected. Choose a field, comparison and value; use Add condition or Group to expand the search. Selecting another module starts with a fresh condition row for that module.

A new saved-list filter starts with one empty condition ready for field selection. Choose a field, comparison and value, then add more conditions as needed.

Each open list keeps its selected saved view and temporary conditions in its own address. You can leave All open in one tab and build conditions in another without changing the first tab, including after a refresh. Switching to another saved view clears temporary conditions. Copy the current address to reopen the same view and conditions.

Use Add condition inside any group, including a newly added group, to add another condition to that group without reopening the editor.

Use the outlined Group button below the conditions to add a group when creating or editing a saved list.

The small group delete button appears beside the group heading, separated by a short gap.

When reopening a saved filter, the second and subsequent groups also have a delete button. The first group remains available for adding conditions.

Condition groups have a shaded background to distinguish them from the surrounding editor.

Add and delete buttons have white backgrounds. The AND/OR selector between groups uses the standard control size; the group delete button remains compact.

In the shared condition editor, each click on the add-group button adds one group with an empty condition. Choose AND or OR beside each condition to connect it to the next row; the last row has no connector. You can combine different connectors in one group. Use separate groups to make the intended grouping explicit. The connector between groups sits directly below the preceding group.

Use the list selector to choose a saved view and the funnel beside the columns to add temporary conditions. The quick filter and saved-list editor offer the same conditions for each field type. Use a date range for a fixed interval, a named period for a moving interval, or a quantity of days/hours for relative comparisons.

After applying conditions, check the result count and use sorting, paging or export as usual. Clear additional filters to return to the saved list's criteria. Save as filter opens the full editor for naming and saving the combined criteria.

Read-only filter values, including automatically calculated dates and ranges, appear in grey Bootstrap fields. Their values are still included when applying or saving a filter. Change the selected period to change the range, or choose a custom range to enter dates yourself. Editable dates and day/hour quantities use normal input fields.

Module-specific condition options apply in both editors. Time ranges and date-only ranges on datetime fields use shared preparation when filtering records, whether applied immediately or loaded from a saved list.

The advanced filter layout is shared with workflow conditions. Groups, AND/OR selectors and add/remove buttons behave consistently. Available comparisons still depend on the field and on whether you are editing a list filter or a workflow.
Empty condition groups are ignored. The connector after a populated group joins it to the next populated group, even with an empty group between them.
