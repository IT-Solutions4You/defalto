# List filtering

- `Documents_List_View` overrides list initialization to support document folders (`folder_id` and `folder_value` are passed to `Documents_ListView_Model`); `Documents_ListAjax_View` inherits it. Query execution and folder restrictions stay in `Documents_ListView_Model`; filter metadata and editor assets belong to Core. Documents list JavaScript inherits the shared Vtiger search pipeline.
- On every initial and AJAX render, resolve `CURRENT_CV_MODEL` from the current user's accessible `CUSTOM_VIEWS` by the active view ID. The Core toolbar uses it for saved-condition counts and edit links; it is null when no accessible view matches.
- Call `Vtiger_List_View::setListFilterData()` before adding the legacy field-name keys to the search parameters, so the Core toolbar receives fields, block groups and positional AND/OR conditions on both renders.
- Validate list ordering through `Documents_Module_Model`: current list field names and legacy detail/quick-preview order columns must exactly match active Documents field metadata, and directions must normalize to `ASC` or `DESC` before query generation. `Documents_ListView_Model::retrieveOrderBy()` owns the current list boundary; the legacy getters own folder-specific navigation before `ListViewSession` rebuilds SQL.
- Field installation and default columns belong to `Documents_Install_Model`; the installable version is `Documents::$moduleVersion`.
- Validate field selection, applying and removing conditions, folder switching and AJAX paging. Lint `views/List.php` and `Documents.php` after changes.

# List record actions

- The record-action template uses the shared `Vtiger_List_Js::registerEditLink()` handler. A link with the `editLink` class must supply `data-url` with the record edit URL and app context; the handler reads it and adds the list return parameters. Keep `href` for native navigation.

# Document storage

- Uploaded files use `Core_Storage_Model::initAttachmentFileDirectory()`. The root is `$storage_directory`; an unset or empty value falls back to `storage/`. Absolute roots outside the web directory are supported.
- `CRMEntity::getAttachmentUploadPath()` is the shared upload hook and delegates to `decideFilePath()`, so Documents follows the same storage policy as other persisted attachments.
- The dated `<year>/<month name>/week<N>` directory (week 1-5) is created recursively and written to `vtiger_attachments.path`. Downloads resolve files through `Core_Storage_Model::getAttachmentFilePath()` from that stored path, so existing documents keep their original location when `$storage_directory` changes. Relative stored paths resolve against `root_directory`.
