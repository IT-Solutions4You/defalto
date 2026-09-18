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

- `Vtiger_Util_Helper::transferListSearchParamsToFilterCondition()` converts list and advanced-search conditions for the query generator. Source group zero uses AND within the group; source group one uses OR. Empty groups must not be emitted, but their source positions must be preserved when choosing the within-group operator.
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
