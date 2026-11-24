# Navigation from filtered lists

When opening a record from a filtered list, previous and next record navigation retains the list's conditions, including calendar date ranges. Changing filter values refreshes the available navigation records.

# Header menu

The header menu contains Defalto settings available to administrators, the current user's preferences and sign-out action, and links registered by installed extensions. It does not show links to upstream vtiger feedback, documentation, videos, or marketing pages. Defalto website and help links use the application's redirect page so their destinations stay centrally managed.

# Record actions

Deleting a record from a list or search result asks for confirmation once per click, including after refreshing the displayed records.

Beside Comments, the Related records button opens a dropdown of related modules that contain records, including their counts. These shortcuts appear or disappear as related records are added or removed. The desktop Related modules panel retains the full list, including empty relations. On mobile, the sidebar and its toggle are hidden; populated relations remain available through Related records.

Summary, Details, Updates, Sharing, Comments and related-record lists start directly below the record navigation without an extra outer margin. The related-modules panel aligns with the content. Internal spacing between content sections remains unchanged.

The key-fields summary block has no Details shortcut. Use the Details tab above the summary to open all record fields.

Records in modules with image fields show their picture above the title and key fields on narrow screens. From the two-column key-field layout onward, the picture appears beside the name in a separate column. If no picture is stored, the module icon is shown.

In the record header, Assigned To appears first when the key fields wrap on a narrower screen. In the wide, single-row layout it remains the last field.

Full record details show an icon navigation bar below the record header: Summary, Details, Updates, Sharing and Comments, when supported and available. The current view is underlined. Module-specific views, such as project charts, remain available in this bar. On narrow screens, scroll the whole bar horizontally to reach more views and the Related records button; all items stay on one row.

The Related modules panel contains the other linked modules and their record counts. The panel is available only on desktop. On mobile, use the Related records dropdown beside the top record tabs. The main record views and Comments are not repeated there. Open a related module to view its records; use the top bar to return to Summary or Details. When no other related modules are available, the content uses the full width. The comment composer in Summary remains available as before.

Click the **AI** icon beside the comment box to check the license, installation and setup. If AI is ready and you have access, select **Open AI** to work with your current draft in AI chat. The comment stays in the editor until you post it.

When you click Add in a record's summary widget, Quick Create fills the related-record field with the record you are viewing. For example, a task or milestone added from a project's summary starts with that project selected.

An empty related-record summary widget shows the translated module name in its message, such as Project Tasks or Project Milestones, using your selected language.

Administrators can lock or unlock a record. A non-administrator can lock a record only when it is assigned directly to that user and the user has permission to edit it; unlocking remains available only to administrators.

Related-record widgets on a record use the language of the related module for their title and add action. If a custom widget label exists only for the displayed record's module, that label is used instead, so users never see a technical name. For example, the Appointments widget keeps its translated Appointments label even on another module's record. A record with no summary widgets shows empty widget columns without an error.

Quick-preview actions apply to the displayed record, not the screen underneath. Deleting asks for one confirmation and refreshes the source screen afterwards; if an error occurs, the preview stays open. Editable fields in the preview header have the same controls as the record detail header: use the pencil, the checkbox switch or the Assigned To selector to update the record without leaving the list. Read-only fields and fields you cannot edit stay unchanged. See the Core user guide for the shared preview workflow.

Quick edits and full record saves validate required, email, numeric and dropdown values on the server. Required fields cannot be cleared, and dropdown fields accept only available options. If a value is invalid, the record stays unchanged and the field error is shown.

Opening, paging through or changing roll-up comments and related records from a quick preview loads the requested content only inside that preview. The summary comments, related content and browser address of the record underneath stay unchanged. When the preview has no matching navigation tab, the link opens the displayed record's own destination instead.

Record-header action icons show a label while the mouse is over them, and the label disappears when the mouse leaves, also after clicking an action or cancelling its confirmation. The buttons also have labels for assistive technology.

# Dropdown fields

When you open a dropdown, a colored line on the left marks an option with a configured color. Option text and backgrounds follow the theme. On saved records, colors still appear as labels in the list, detail and related lists.

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

Key Metrics and MiniList display saved-filter names in your selected language when a translation is available. Custom names without a translation retain their saved text. Clicking a metric still opens its saved list.

Dashboard tabs and notebook widgets belong to the user who created them. You can rename, reorder and delete your own tabs and edit your own notes; another user's tab or note cannot be changed by entering its identifier. Tab names and notebook titles are displayed as text.

Choose the Notebook widget, enter a name and write your note in the editor. The compact toolbar offers bold, italic, underline, strikethrough, subscript, superscript, links, remove formatting and undo/redo. Use subscript for H₂O or superscript for m², and Link/Unlink to add or remove a clickable link. List and quotation buttons are not available; existing supported formatting remains displayed. Save adds the note to the current dashboard tab. The dialog keeps labels and fields aligned and gives the editor room to write.

Choose Edit on an existing note to open the standard editor with its toolbar above the editing area. Choose Save to update it without reloading the page and return to the note display. Each note has its own editor. The create and edit editors have no bottom status panel. A failed save keeps your draft available to retry. Existing plain-text notes keep their text and line breaks; saving them through the editor enables formatting. Active content and unsupported formatting are removed from formatted notes.

Saved formatted notes appear as HTML content without an input border. Bulleted, numbered and nested lists keep their markers and indentation and follow the note's width rather than the dashboard grid's width. Text and long links wrap to fit the widget's width. Content that still cannot fit has a horizontal scrollbar inside the note, keeping the widget within its dashboard column.

# Record forms

Field labels in the full record edit form use the normal theme text color for easier reading, including address labels. This also applies when the same form creates or duplicates a record. Required fields retain their red asterisk, and restricted fields retain their lock indicator.

The Related records dropdown looks like the other record tabs. When one of its related modules is selected, its label is blue and underlined; clicking it still opens the list of related modules. Menu items use the navigation text size and spacing, and the selected item has bold primary-colored text without a background. In the desktop sidebar, only the currently open module is highlighted using the application's original primary theme colors and bold text. Its count uses a rounded badge, and selection stays synchronized with the dropdown.

Relationship selectors in edit windows use the record being edited. Select a result to fill the relationship, or clear it to search again. Suggestions remain visible above the edit window. Reopening a selection list does not multiply selection actions.

Screens using the shared user/group selector show fixed-size avatars, searchable users and groups, and All/My shortcuts. Apply confirms checkbox changes; closing discards the draft.
