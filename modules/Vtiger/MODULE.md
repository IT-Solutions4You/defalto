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
