# Workflows

Workflows run configured actions when records meet the selected trigger and conditions. Open a workflow to review its name, description, execution settings, conditions and actions before saving changes.

The condition editor organizes rules into numbered groups, like saved lists. Choose AND or OR beside each condition to connect it to the next condition, and use the connector between groups to connect whole groups. The last condition has no connector. AND takes precedence over OR: A OR B AND C means A OR (B AND C). Use separate groups when you need (A OR B) AND C. Saving and reopening preserves these choices. The same grouping applies to record-triggered and scheduled workflows; change comparisons still require a record save and are skipped during scheduled selection.

The editor shares its layout and controls with saved-list filters. Use Add condition with the plus icon to add a blank rule to the current group, then choose its field. Workflow comparisons and expressions remain available for the fields that support them. Use the matching Group button with the plus icon to add another group, and the trash icon beside a condition to remove that rule. Any group after the first can be removed using the trash button beside its title. These buttons and the spacing of condition rows match the saved-list condition editor. The value control fills the available width, with the trash button at the right edge. Select a field and comparison; enter a value when the comparison requires one. Event comparisons such as a comment being added may not need a value.

Each condition group has its own outline, without an enclosing frame around the whole editor. The Group button appears below the condition groups.

Review the execution frequency and configured actions before saving. Conditions determine which records qualify; actions determine what the workflow does to those records or related records.

When reopening an existing workflow, the selected fields and comparisons should remain populated, including related-record fields. If field names appear blank after an update, reload after the application update has finished before editing or saving its conditions.
