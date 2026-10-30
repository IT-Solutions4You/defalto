# Header menu

The header menu contains Defalto settings available to administrators, the current user's preferences and sign-out action, and links registered by installed extensions. It does not show links to upstream vtiger feedback, documentation, videos, or marketing pages. Defalto website and help links use the application's redirect page so their destinations stay centrally managed.

# Record actions

Related-record widgets on a record use the language of the related module for their title and add action. If a custom widget label exists only for the displayed record's module, that label is used instead, so users never see a technical name. For example, the Appointments widget keeps its translated Appointments label even on another module's record. A record with no summary widgets shows empty widget columns without an error.

Quick-preview actions apply to the displayed record, not the screen underneath. Deleting asks for one confirmation and refreshes the source screen afterwards; if an error occurs, the preview stays open. Editable fields in the preview header have the same controls as the record detail header: use the pencil, the checkbox switch or the Assigned To selector to update the record without leaving the list. Read-only fields and fields you cannot edit stay unchanged. See the Core user guide for the shared preview workflow.

Quick edits and full record saves validate required, email, numeric and dropdown values on the server. Required fields cannot be cleared, and dropdown fields accept only available options. If a value is invalid, the record stays unchanged and the field error is shown.

Opening, paging through or changing roll-up comments and related records from a quick preview loads the requested content only inside that preview. The summary comments, related content and browser address of the record underneath stay unchanged. When the preview has no matching navigation tab, the link opens the displayed record's own destination instead.

Record-header action icons show a label while the mouse is over them, and the label disappears when the mouse leaves, also after clicking an action or cancelling its confirmation. The buttons also have labels for assistive technology.

# Dropdown fields

Searchable dropdowns, including Assigned To, hide values that are already selected. The current value stays visible in the field; choose another value to replace it. In fields that allow several values, remove a selected item to make it available again.

# Lists and filters

Column settings allow any number of fields. Add the fields you need, arrange their order and save; at least one field must remain selected.

Use the list selector to choose a saved view and the funnel beside the columns to add temporary conditions. The quick filter and the saved-list editor offer the same conditions for each field type. Use a date range for a fixed interval, a named period for a moving interval, or a number of days or hours for relative comparisons. Calculated dates and ranges are shown as read-only fields but are still applied and saved; change the selected period to change the range, or choose a custom range to enter dates yourself.

After applying conditions, check the result count and use sorting, paging or export as usual. Clear additional filters returns to the saved list's own criteria. Save as filter opens the full editor, where you can name and save the combined criteria.

Export keeps the selected list sorting in ascending or descending order. Invalid sorting directions are rejected instead of being included in the export query.

Each open list keeps its saved view and temporary conditions in its own address. You can leave All open in one tab and build conditions in another without changing the first tab, also after a refresh. Switching to another saved view clears the temporary conditions. Copy the current address to reopen the same view and conditions later.

# Condition editor

Saved lists, advanced search and workflow conditions share one editor.

- A new saved list or advanced search starts with one empty condition. Choose a field, a comparison and a value. In advanced search, selecting another module starts with a fresh condition for that module.
- Use Add condition inside any group, including a newly added group, to add a condition to it without reopening the editor. Use Group to add a group; each click adds one group with an empty condition.
- Choose AND or OR beside a condition to connect it to the next one; the last condition has no connector. You can mix connectors in one group, and AND takes precedence over OR. Use separate groups to make the grouping explicit. The connector between groups joins the whole groups.
- Empty groups are ignored. The connector after a populated group joins it to the next populated group, even if an empty group lies between them.
- When you reopen a saved list, every group after the first has a delete button. The first group stays so you can keep adding conditions.
- For inventory documents, the field lists show the main record blocks first and the Item Details fields last; the record's edit and detail screens keep their normal layout.
- Time ranges, and date-only ranges on date-and-time fields, are prepared the same way whether you apply them immediately or load them from a saved list. Module-specific condition options apply in both editors.

The available comparisons depend on the field and on whether you are editing a list filter or a workflow.

# Mass-email recipient fields

When starting a mass email, choose which available email fields should receive the message. If you enable the option to remember the selection, the same fields are restored the next time you open the dialog for that module. The preference is saved per user and module, and resetting the selection removes it.

# Attachments

Record images, previews and downloads keep working when an administrator stores persistent attachments outside the public web directory. Access still follows the usual record and file permissions.

# Dashboard

Dashboard tabs and notebook widgets belong to the user who created them. You can rename, reorder and delete your own tabs and edit your own notes; another user's tab or note cannot be changed by entering its identifier. Tab names and notebook text are displayed as text, including when they contain characters used by HTML.
