# List filtering

- Resolve `CURRENT_CV_MODEL` from the current user's accessible `CUSTOM_VIEWS` using the active view ID on each initial/AJAX render. The Core toolbar uses it for saved-condition counts and edit links; clear it to null when no accessible match exists.

- `Documents_List_View` overrides list initialization to support document folders; `Documents_ListAjax_View` inherits it. Call the shared `Vtiger_List_View::setListFilterData()` before adding legacy field-name keys to search parameters so the Core toolbar receives fields, block groups, and positional AND/OR conditions on initial and AJAX renders.
- Query execution and folder restrictions remain in `Documents_ListView_Model`; filter metadata and editor assets belong to Core. Documents list JavaScript inherits the shared Vtiger search pipeline. Field installation and default columns belong to `Documents_Install_Model`, and the installable version is `Documents::$moduleVersion`.
- Validate field selection, applying/removing conditions, folder switching and AJAX paging. Lint `views/List.php` and `Documents.php` after changes.

# List record actions

- The Documents record-action template uses the shared `Vtiger_List_Js::registerEditLink()` handler. Links with `editLink` must supply `data-url` with the record edit URL and app context; the handler reads that attribute and adds list return parameters. Retain `href` for native link navigation.
