# Ownership and contracts

- Saved-list column selection has no maximum field count. `CustomView.js` initializes Select2 without a selection limit; `CustomView_Record_Model` persists every selected column in `vtiger_cvcolumnlist` in order.

- The saved-list editor footer uses standard `modal-footer` spacing with `container-fluid p-0`, matching the shared modal footer. Preserve `modal-overlay-footer`, `customViewSubmit`, `saveButton` and `cancelLink` hooks; avoid extra padding utilities or nonbreaking spaces around the actions.

- Advanced criteria readback and Save as filter drafts preserve each nonempty `column_condition`, including mixed AND/OR rows. Only missing connectors use the group's legacy fallback; the final row is always connector-free. Do not flatten all row connectors to the first operator when preparing the editor.

- Empty advanced criteria normalize to a group containing `columns: []` and connector metadata. The shared Vtiger template checks the columns to show a single initial blank row; do not remove the normalized metadata to control presentation.

- Saved-view and quick-list operator presentation delegates through `Core_FilterOperator_Model` to the source module's `Core_Filter_Model` instance. `EditAjax` uses `Vtiger_Field_Model::getDisplayDateFilterTypes()` so date labels and period units are translated consistently. `Core_FilterOperator_Model::isCalendarValue()` excludes relative day/hour quantities and `lastperiod` from date-format conversion in persistence and readback. Readback remains in user display format; both query generators then use the same module filter's `getQueryCondition()` as quick filtering.
- Time criteria retain their existing persistence/display conversion and special handling for time_start/time_end. Validate save/reopen of time ranges and relative periods as well as immediate quick-filter execution.

- CustomView owns saved list views, selected columns, standard/advanced criteria, sort order and sharing. `EditAjax` renders the existing editor; `Save` validates write access and delegates persistence to `CustomView_Record_Model`. The built-in component has no independent install model or explicit module version; shared application changes use `version.php`.
- The Core list toolbar opens a new draft through `EditAjax` with `source_viewname` and `list_search_params`. The source must belong to the requested module and be accessible to the current user. Clone the source model; preserve its columns and existing criteria, but do not inherit its name, sharing, default or metrics settings. No saved view is changed by opening a draft.
- `getCriteriaWithListSearch()` combines temporary list-search conditions with the source criteria while preserving dynamically ordered non-empty groups. Fields/operators are checked against the Core list metadata. Actual saving remains in the existing editor and action.
- The Core toolbar and this editor use `Vtiger_FilterRecordStructure_Model` as the common field source. Relation conditions retain the `(parent ; (Module) field)` key, so temporary relation filters can be converted into the same advanced-filter columns when a draft is saved.
- UI assets live under `layouts/d1/modules/CustomView`. Validate draft creation from shared views, column preservation, group logic, duplicate names, private defaults and save/reopen. Lint changed PHP and load views with their actual parent classes; verify dates/currencies end to end on a configured instance.

- `getSavedConditionCount()` reads the same standard and advanced definitions as the list query generator, counting a configured standard condition and nonempty advanced columns. This is presentation metadata only; it must never be merged into temporary search parameters or cleared by the toolbar.
