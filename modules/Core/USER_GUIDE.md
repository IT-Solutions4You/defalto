# List filters

Multiple condition groups are evaluated together, including when groups are joined with OR. Normal record visibility restrictions still apply to all matching groups.

When a saved list already contains conditions, its notice offers a white Edit saved conditions button with a pen icon if you have permission to edit the list. Use it to change the saved conditions.

The saved-list editor displays numbered condition groups and delete tooltips in your selected language, including groups added while editing. The connectors between conditions and between groups also use translated labels.

Use the funnel icon beside the column selector to narrow the current list. Choose a field, a condition and its value, then select Apply. Text, numbers, selections, owners, related records and dates use the same conditions as the saved-list editor.

Date filters accept a specific date, a range, a named period such as Today, a number of days/hours, or a quantity and unit for Last X periods. Time ranges have a start and end time. Empty/not-empty conditions do not need a value. Checkboxes use Yes or No.

Applied conditions appear inside the dropdown and can be edited or removed. Clear removes additional conditions, not conditions already stored in the selected list. Save as filter opens a draft in the saved-list editor. The arrow beside the list name switches saved lists.

Modules may tailor their available conditions. Those module rules apply to both quick filters and saved lists. Quick and saved conditions pass through the same module-specific preparation before querying records.

For a time condition such as Between 11:00 and 13:00, both boundaries are included. Apply runs the range immediately; saving the list is not required. Both 24-hour values and the configured 12-hour AM/PM format are supported.

Saved-list and workflow condition editors use the same group layout, condition rows and add/remove controls. Workflow filters retain their own comparisons and expressions. Choose a field after adding a condition; use AND/OR beside rows and between groups to combine rules.
Empty condition groups are ignored. The connector after a populated group joins it to the next populated group, even with an empty group between them.
