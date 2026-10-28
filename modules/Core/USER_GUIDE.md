# Style Guide

Administrators can open `index.php?module=Core&view=StyleGuide` to review the colors and common interface components currently rendered by Defalto. The page includes semantic colors, alerts, buttons, badges, form controls, navigation, cards, lists, tables, progress indicators, loading states, and their common variants. It is a visual reference only and does not change application data or settings.

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
