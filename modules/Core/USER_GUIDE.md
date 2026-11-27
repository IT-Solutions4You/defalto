# Record header progress

Project and Project Task headers show Progress as a bar with the percentage visible beside it. Together they occupy 80% of the header field width, leaving space around the indicator. The bar uses the same appearance as the task widget. Zero progress shows an empty track with 0%; an unset value stays blank. Where inline editing is allowed, the pencil still edits the percentage and the bar updates after saving.

# Checklists

In a project's Summary task table, a counter beside the task name shows completed checklist items out of the total, such as **3/4**. It combines the task's Checklist fields you can view; empty checklists show no counter. Refresh the table after checklist changes to update the count.

Each **Checklist** field is one checklist and can be placed in any ordinary block. No special checklist block is needed. Administrators can add this field type through the module Layout Editor. The field contains only the item texts, one item per line; completion states are stored separately.

The field name uses the normal field layout and label style in Details and Edit. In Edit it has the same text color as Description. On small screens, the label appears above the items.

On phones and touch devices, the checkbox and remove icon have larger touch areas while their visual size stays compact. The remove icon is visible without hovering. Tapping the space around a checkbox toggles that checkbox; tapping the item row opens its text editor.

Modules can provide a Summary widget for a specific Checklist field. Project Tasks provides its Checklist widget in the right column by default. Administrators can enable, hide or rearrange it through Settings, under Widget editor. Module updates retain a hidden widget and its saved position. Adding another Checklist field does not automatically register another widget. The widget uses the same controls and permissions as Details and Edit.

Details and editing use the same simple list, similar to Google Keep: a checkbox beside each item and a **+ List item** row below. There is no frame, large heading, percentage counter or progress bar. Enter text and press Enter to add an item and continue with the next one. For a new record, item texts are drafts until the record is saved; completion becomes available after that first save.

Pressing Enter turns the entered text into an item with a checkbox and moves the cursor into the empty text area below it. After confirming a text edit with Enter or the idle timer, the cursor also returns to the add row. Leaving the editor keeps focus where you clicked. Blank Enter presses do not add empty items.

The **+ List item** control is a single-line input. Paste several lines into it to create one checklist item per non-empty line immediately. Their order and repeated entries are retained. Each item can contain up to 1000 characters. If saving fails, the pasted batch is kept for retry; typing in the input replaces it with the newly edited text. This works in Details, Edit and Summary, including drafts on a new record.

Check a box to complete an item; clear it to reopen it. Completed text is crossed out. The small **Hide completed** and **Show completed** control appears when completed items exist and changes only the current display.

A line above the items shows completed items out of the total, for example **3/5**. It updates when items are completed, reopened, added or removed and includes hidden completed items. An empty checklist shows **0/0**. The count is also visible in read-only checklists.

Hover over an item or focus it with the keyboard to highlight its row and reveal the remove icon. The highlighted row identifies the item that the remove icon belongs to. You can also remove that item while editing its text; removal cancels the unfinished text edit.

Select an item's row to edit it, including the empty space beside its text. The checkbox and remove icon retain their own actions. The change saves when you leave the editor or stop typing for two seconds. Enter also saves it; Escape keeps the previous text while the edit is still open. There are no save or cancel buttons. Other items and form fields remain available; selecting another item confirms the current edit and opens the selected one. An empty edit keeps the original item. An item can have up to 1000 characters. Blank lines are ignored. The field remains available after its last item is removed. Additional checklists require additional checklist fields.

For saved records, checklist changes save immediately from either Details or Edit without reloading the page. Wait for a pending save before starting another change, and finish or cancel an item edit before saving the parent form. If saving fails, the entered text stays available for correction or retry. If someone changed the checklist text after you opened it, refresh the record before trying again. The server checks record and field edit access for every operation; locked or read-only records and fields show a read-only checklist in both views.

Editing an item in the widget preserves its completion. If another supported save path changes the field text, unchanged or reordered lines retain their completion; new or changed lines start unchecked. Identical lines are matched in occurrence order. Copying a record copies its checklist text and starts the copied items unchecked.

Checklist completion is independent of the parent record's status and completion percentage. Items have no due dates or assignees. Moving a record to the recycle bin retains its checklist for restoration; permanently deleting the record removes its metadata too.

# Record navigation

Project task and milestone date validation accepts the calendar date format configured in your user preferences. Dates stay on the selected calendar day.

The Key fields card in Summary shows its title without a Details shortcut. Open the Details tab to view all record fields.

Switching tabs opens the selected view of the current record. Tab links can also be opened directly or refreshed while retaining the selected record and view.

The active tab has a thicker blue underline for easier recognition.

The Related records button beside Comments opens a list of related modules with at least one linked record and their counts. Empty relations stay available in the desktop Related modules panel. On mobile, the sidebar and its toggle are hidden; use Related records for populated relations. Adding or removing related records refreshes the shortcuts automatically.

Summary, Details, Updates, Sharing, Comments and related-record lists have no extra outer margin below the navigation. Spacing inside their content is retained.

Record details separate the main record views from linked modules. Use the icon tabs below the record header for Summary, Details, Updates, Sharing and Comments, where available. The bar has a transparent background without a surrounding border or divider. The active view is underlined in blue; on a small screen the entire tab bar, including Related records, scrolls horizontally as one row. Other related modules remain in the Related modules panel on desktop. On mobile, use the Related records dropdown for modules containing linked records. The separate sidebar and its toggle are hidden. Existing module-specific views remain accessible after the main views.

# AI setup

Click **AI** in the top bar to check your Membership license, whether the AI extension is installed, and whether it is enabled and configured. The window shows the next step. Administrators can open Installer, Module Manager or AI settings from the window when needed. If AI is ready and your profile has access, choose **Open AI** and send a message to test the connection.

# Style Guide

Administrators can open `index.php?module=Core&view=StyleGuide` to review the colors and common interface components currently rendered by Defalto. The page includes semantic colors, alerts, buttons, badges, form controls, navigation, cards, lists, tables, progress indicators, loading states, and their common variants. It is a visual reference only and does not change application data or settings.

# External links

Image and file addresses inserted through the editor's file browser and generated short links work with CRM addresses configured with or without a trailing slash, including CRM installations in a subdirectory.

Defalto website, documentation, store, community, social, and release links pass through the CRM redirect page. This keeps destinations consistent across the application and in generated emails or documents. Legacy links to upstream vtiger websites are not shown.

# Read-only records

Administrators can lock and unlock records. A non-administrator can lock a record only when it is assigned directly to that user and the user is allowed to edit it. Unlocking records and installing the read-only workflow remain administrator-only operations. Opening a lock or unlock URL directly does not bypass these rules.

# List filters

Use the funnel icon beside the column selector in a list to narrow the current list. Choose a field, a condition and its value, then select Apply. Text, numbers, selections, owners, related records and dates use the same conditions as the saved-list editor. Fields from directly related records can be used as well. Owner fields group users and groups separately, and related-record fields let you pick the record in a popup.

- Date filters accept a specific date, a range, a named period such as Today, a number of days/hours, or a quantity and unit for Last X periods. Time ranges have a start and end time, both included (for example Between 11:00 and 13:00), in 24-hour or the configured AM/PM format. Checkboxes use Yes or No. Empty/not-empty conditions need no value.
- Apply runs the filter immediately; saving the list is not required. Applied conditions appear inside the dropdown and can be edited or removed.
- Clear all removes the conditions you added. If the list already has saved conditions, Clear additional filters removes only your additional ones. The arrow beside the list name switches saved lists.
- Save as filter opens a draft in the saved-list editor.
- When a saved list already contains conditions, its notice offers an Edit saved conditions button if you have permission to edit the list.
- Modules may tailor the conditions they offer; those rules apply to both quick filters and saved lists.

In the saved-list editor and in workflow conditions, rows are organized in numbered groups. Choose a field after adding a condition, and use AND/OR beside rows and between groups to combine rules. Several groups are evaluated together, including when groups are joined with OR, and normal record visibility restrictions still apply. Empty groups are ignored. Group numbers, delete tooltips and connector labels are shown in your selected language, including groups added while editing. Workflow conditions keep their own comparisons and expressions.

# Product block templates

Administrators can open CRM Settings, choose Product block templates in the Inventory section, and manage the shared templates. The list shows the inventory module for every template and supports creating, editing, and deleting templates. These templates are shared by EMAILMaker and PDFMaker.

Leave the module filter set to All when creating a template that should be available in every inventory module. Select a specific inventory module before creating when the template should be limited to that module.

Template authors can use `$PS_ROW_NO$` to number only product and service rows continuously (it restarts for each product block), and `$PS_ROW_TYPE$` to distinguish product, service, and text rows. The original inventory sequence variable still includes every document row.

# Record quick preview

Open a record's eye icon or supported reference link to inspect it without leaving the current screen. Record actions apply to the record shown in the preview, even when the underlying page belongs to another module. Existing record permissions still apply.

Deleting requires confirmation. Cancelling or a failed deletion leaves the preview open. After success, a standard list or an integrated custom screen refreshes in place; other screens refresh their current page to update references and widgets. If you delete the record of the underlying full detail page, you return to that module's list.

An unsuccessful preview request shows an error instead of opening an empty window. Previous/next navigation is available for the source module's standard list, not for unrelated module references or custom board ordering.

# Translated messages

Validation messages that the server sends to on-screen notifications are displayed in the current user's language.

# Attachment storage

Administrators can set `$storage_directory` in `config.inc.php` to a relative directory such as `storage/` or an absolute directory such as `/home/storage/`. If it is empty or not configured, the application uses `storage/`.

New persistent attachments from Documents, email, comments, profile images, PDFs, and other CRM upload flows use this directory. Existing attachments keep working from their saved relative or absolute paths. Browser images are delivered through the CRM file endpoint, so the physical directory does not need to be publicly accessible.

# Automated record updates

Background workflows, imports, integrations, scheduled tasks, and custom scripts can load and save records without changing their text. Accented characters, typographic punctuation, and currency symbols in unchanged fields are preserved during these operations.

# Restricted record actions

Some actions on a single record can be restricted, for example by a workflow: editing, deleting, duplicating, creating a sales order or invoice from a quote, editing selected fields, changing a field to selected values, exporting to PDF and sending emails. Restrictions can apply to everyone or only to selected users, groups or roles, and they apply to administrators too.

When an action is restricted for you, the record detail shows a yellow **Restricted actions** box with the action and the reason, and the corresponding buttons are hidden or refuse the action. Locked fields are shown read-only with a lock icon in the edit form and cannot be edited inline. Values you may not change a field to are offered disabled in the list; hover over them to see the reason. If you try a restricted action, the message tells you the reason: opening a restricted edit form, copy or conversion shows the reason on a normal page with the menu, and a form whose save was refused is shown again with your values and the reason above it. Mass edit and mass delete skip restricted records and then show how many were skipped and why. Administrators also see all rules of the record and can remove a rule with **Remove restriction**, or all of them at once with **Remove all restrictions**, after confirming it. When PDF export or sending emails is restricted, the **Export to PDF** or email button is shown disabled with a lock; hover over it to see the reason.

To set up a restriction, add the workflow task **Restrict record actions** to a workflow: choose the actions, the fields for **Edit field** (leave empty to lock all fields; shown after you choose this action), the values for **Change to value** (shown after you choose this action), whether they are denied or allowed, who the rule applies to, and an optional reason shown to users. An allowed rule for a user, group or role cancels a more general denial (for example, deny editing to everyone but allow it to managers); it never grants more than the user's profile allows. In the rules table such a rule without its own reason is shown as **Exception**. Use the task **Clear record action restrictions** to remove the restrictions created by workflows, for selected actions or all of them.
# Repeating calendar appointments

Monthly appointments keep the selected local start time when daylight-saving time begins or ends. A date such as the 31st moves to the last day of shorter months. All-day appointments keep their selected calendar dates. Existing saved occurrences are not automatically repaired by this change.

# Address fields

Address labels in the record edit form use the normal theme text color, including the combined postal code and city label.

The Related records dropdown looks like the other record tabs. When one of its related modules is selected, its label is blue and underlined; clicking it still opens the list of related modules. Menu items use the navigation text size and spacing, and the selected item has bold primary-colored text without a background. In the desktop sidebar, only the currently open module is highlighted using the application's original primary theme colors and bold text. Its count uses a rounded badge, and selection stays synchronized with the dropdown.

Related-record selection lists open above the edit window and return a selected record to that window. Repeated opening retains one selection action.

## User and group selection

Screens using the shared assignee selector let you search permitted users and groups, select several entries and choose Apply. All shows the unfiltered view; My selects your user when available. Clear removes the filter. Closing without applying keeps the previous selection. Photos or initials use fixed-size avatars.
