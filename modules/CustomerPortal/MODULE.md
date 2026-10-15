# Customer Portal

- `modules/CustomerPortal/api.php` authenticates portal requests and dispatches API controllers. Portal contacts run web-service queries through the active CRM administrator, so CustomerPortal must enforce contact and account isolation before executing a query.
- Fetch and export equality filters are built only by `CustomerPortal_Utils::getVtqlFilterConditions()`. Field names must be active portal fields, values must be scalar VTQL literals, and only `AND` is supported because the flat VTQL grammar cannot safely scope `OR` conditions beneath mandatory relation or publication predicates.
- Filter literals use doubled apostrophes for VTQL and reject backslashes to prevent differences between VTQL and database string parsing from restoring query syntax.
- Customer Portal image encoding and file downloads resolve `vtiger_attachments` rows through `Core_Storage_Model::getAttachmentFilePath()`.
- Both legacy relative attachment paths and configured absolute storage paths are supported. Never prepend `root_directory` directly to an attachment path without first checking whether it is absolute.
- Validate changes with `php -l` on touched PHP files and exercise FetchRecords and ExportRecords with ordinary filters, apostrophes, rejected backslashes, and rejected non-`AND` operators.
