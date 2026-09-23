# Dashboard layout persistence

- Home inherits the shared Vtiger dashboard view. `DashBoard.js` initializes Gridster from widget position/size attributes rendered by `DashBoardTabContents.tpl`; pass saved column and row to `add_widget()` for positioned widgets. Automatic placement is only for new widgets or positions outside the current viewport.
- Initialization must not save positions: responsive reflow must not overwrite the stored layout. Drag and resize completion save all affected widget positions.
- `saveWidgetLayout()` shows the shared saving indicator and one translated result notification after all position/size requests finish. A failed request must prevent a success notification for that operation.
- Drag/resize start hides the changed widget content through all layout writes, then calls `loadWidget(widget, true)` to regenerate the full server-rendered widget with the existing post-load initialization and chart sizing. Replacement destroys old Chart.js instances, clears cached widget instances and lifecycle listeners, and preserves the Gridster node and resize handle. Failed saves or reload requests preserve existing content and report the error. The ordinary refresh button continues to use the widget-specific refresh flow.
- The hidden content is accompanied by a Bootstrap utility-based status placeholder with moving/resizing, saving and loading states. Labels come from the shared Core language files; restore removes the placeholder on success and failure.
- `SaveWidgetPositions` delegates to `Vtiger_Widget_Model::updateWidgetPosition()`. Updates to `vtiger_module_dashboard_widgets` must be scoped by current user and dashboard tab, including ordinary widgets identified by link ID. MiniList, Notebook and chart widget IDs use a compound DOM ID.
- Iterate the native `positionsmap` request array. `vtlib_array()` returns an ArrayAccess-only `Vtiger_GuardedArray` with private storage, so foreach over that wrapper silently processes no positions. Reject malformed maps and missing tab IDs instead of reporting a successful no-op.
- This is shared Vtiger runtime behavior, with no standalone Vtiger module version or schema migration. Validate drag/refresh, resize/refresh, independent tabs containing the same widget, and narrow/wide viewport reloads; run JavaScript syntax and ITS4You ScriptsValidator checks and PHP lint.

# Shared filter processing

- Group delete buttons use Bootstrap `btn-sm` to match the `form-select-sm` operator control, both in `AdvanceFilter.tpl` and in the dynamically generated markup in `AdvanceFilter.js`.

- `AdvanceFilter.tpl` uses the connector's `py-2` for spacing between groups, without an additional group bottom margin. The add-group button owns its separate top margin; dynamically cloned groups retain the same spacing.

- `Vtiger_AdvanceFilter_Js::init()` must retain only `.filterContainer` elements when the supplied collection also contains editor wrappers. CustomView passes nested `.filterConditionsDiv` elements; binding delegated handlers to both wrapper and filter processes one bubbling click twice and adds duplicate groups.

- `AdvanceFilter_Field_Js::getUiTypeSpecificHtml()` normalizes root and nested text-like inputs and textareas to Bootstrap `form-control`, and gives readonly controls `bg-body-secondary text-body-secondary`. Hidden inputs, checkboxes, radio buttons and buttons are excluded. Calculated date previews remain `readonly`, not disabled, so saved-view serialization and quick-filter submission still include their values. Editable dates and offsets retain normal input styling; selects keep their own Select2/Bootstrap rendering.

- `AdvanceFilterCondition.tpl` supplies per-field operator labels from `Core_FilterOperator_Model`; `AdvanceFilter.js` consumes those labels, retaining the legacy mapping fallback for other templates. The Core quick filter reuses the shared date/datetime field renderer. The time range renderer serializes two picker inputs through one named hidden value.
- List conversion only maps positional conditions to advanced criteria. ListView (including popup), ExportData and MiniList instantiate `Core_QueryGenerator_Model`; its `parseAdvFilterList()` obtains the source module's `Core_Filter_Model` and normalizes conditions before delegating to the unchanged Enhanced parser. The common model normalizes time endpoints and date-only datetime ranges while preserving symbolic periods, empty operators and monetary precision. Storage and timezone conversion remain in their existing owners.

- `getTimeValueWithSeconds()` splits AM/PM from the clock before defaulting missing seconds, preserving noon/midnight for both save and list-filter conversion. Accept literal AM/PM case-insensitively; editable values must not contain translated suffixes.
- `AdvanceFilter_Currencylist_Field_Js` emits currency IDs as option values, matching the Core toolbar and numeric currency query contract. Restore selection by either ID or legacy currency name, so older saved filters migrate to IDs when saved again.

- Standalone time fields are wall-clock values without timezone conversion. `Time.tpl` passes the same user hour format to `getEditViewDisplayValue()` and the timepicker's `data-format`. Editable 12-hour values use literal AM/PM; translated suffixes belong only in display output. Other callers fall back to the current user's format. Fix edit-value preparation rather than expanding save parsing to accept translated display labels. Shared time validation enforces 12/24-hour ranges with optional seconds.
- The list view assigns both the flat `LIST_FILTER_FIELDS` metadata and Core-owned `LIST_FILTER_FIELD_GROUPS` for the toolbar's block-grouped field selector.
- `Vtiger_List_View::setListFilterData()` supplies toolbar state and field metadata for the base list and specialized initializers such as Documents. Call it before adding legacy field-name keys to the positional search groups.

- Standard `ListViewContents.tpl` uses the Core-owned Bootstrap filter toolbar instead of the column search row. `List.php` assigns untouched `LIST_FILTER_PARAMS` before adding legacy field-name keys to `SEARCH_DETAILS`. `List.js::getListSearchParams()` reads the toolbar state when present, retaining the legacy path for specialized list templates. Resetting a saved view must clear both states; post-load rendering restores chips from the server response.
- The filter dropdown toggle is included beside `listColumnFilterContainer` in the first header cell; applied conditions and their actions stay inside the dropdown, not in the table header. Its empty owner form is outside `listedit`, with HTML `form` attributes associating the header controls; this also preserves form ownership when floatThead moves the header.
- `loadListViewRecords()` rejects failed requests without replacing the table and resolves successful requests after the new list content is installed. Filter changes reset paging and selection only after success. Export and mass actions continue to consume `getListSearchParams()`.

- `Vtiger_Util_Helper::transferListSearchParamsToFilterCondition()` converts list and advanced-search conditions for the query generator. Legacy source group zero uses AND within the group and source group one uses OR; modern groups carry their own within-group operator. Empty groups must not be emitted, but their source positions must be preserved when choosing legacy defaults.
- List advanced filters preserve an explicit `condition` connector on every non-empty group, allowing more than the legacy two groups. Missing connectors retain the historical first-group AND / second-group OR defaults.
- `Vtiger_ListAjax_View::showSearchResults()` loads filtered entries and separately requests the total count through `Vtiger_List_View::getListViewCount()`. Both paths must receive valid conditions without a trailing group connector.
- Filter creation persists through CustomView; applying a top-bar filter uses the shared Vtiger list search flow.
- Verify empty and populated filter groups manually; lint touched PHP files.

# Field content format

- `Vtiger_Field_Model::isHtmlField()` checks the current field name against the public instance array `$htmlFields` (empty by default). Owning module field models override the list; callers can configure an individual object with `set('htmlFields', ['field_name'])`. This is runtime configuration, not persisted metadata, and does not enable an editor or sanitize HTML. Do not infer HTML semantics from textarea uitypes or value contents.
- `getCleanInstance($fieldName, $moduleName)` resolves the owning field model without database hydration and retains its default HTML field list. Workflow substitution uses that definition; changes to an unrelated field instance do not configure newly created instances.

# Record update history

- `RecentActivities.tpl` uses the ModTracker-owned `Workflow.tpl` partial for create/update provenance. History persistence, workflow lookup, and administrator-only links belong to ModTracker.
- For workflow create/update entries, the heading contains only the ModTracker workflow dropdown. The actor appears inside that dropdown; ordinary entries retain their actor and action text.
- Workflow create/update entries use the `fa-cogs` timeline icon in place of the actor icon or uploaded avatar.
- `VTEntityDelta::getDataDelta()` exposes the existing field comparison semantics for ModTracker's independent audit snapshots; workflow condition deltas retain their own original save baseline.
- `Vtiger_Text_UIType::getDisplayValue()` owns HTML-versus-text formatting via the owning field model's isHtmlField() and configurable htmlFields list. HTML values pass through unchanged; commentcontent retains its legacy pass-through. Other text fields preserve the existing removeTags/sanitization order and use Core_SimpleHtmlDom_Helper::convertNewlinesToHtml(). Field-model getDisplayValue() delegates here for text fields.
