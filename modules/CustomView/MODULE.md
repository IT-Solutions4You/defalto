# Ownership and contracts

- CustomView owns saved list views, selected columns, standard/advanced criteria, sort order and sharing. `EditAjax` renders the editor (`layouts/d1/modules/CustomView/EditView.tpl`, `resources/CustomView.js`); `Save` requires DetailView permission on `source_module` and delegates persistence to `CustomView_Record_Model`. The built-in component has no independent install model or explicit module version; shared application changes use `version.php`.
- Saved-list column selection has no maximum field count: `CustomView.js` initializes Select2 without a selection limit, and `CustomView_Record_Model` persists every selected column in order in `vtiger_cvcolumnlist`.
- Editor footer: standard `modal-footer` spacing with `container-fluid p-0`. Preserve the `modal-overlay-footer`, `customViewSubmit`, `saveButton` and `cancelLink` hooks; avoid extra padding utilities or nonbreaking spaces around the actions.

## Criteria readback

- `transformToNewAdvancedFilter()` (advanced criteria readback and Save as filter drafts) preserves each non-empty `column_condition`, including mixed AND/OR rows. Only missing connectors use the group's legacy fallback (first non-empty connector, otherwise `and`); the final row is always connector-free. Do not flatten row connectors to the first operator.
- Empty advanced criteria normalize to a group with `columns: []` and connector metadata. The shared `AdvanceFilter.tpl` checks the columns to show a single initial blank row; do not remove the metadata to control presentation.
- Operator presentation for saved views and the quick list filter delegates through `Core_FilterOperator_Model` to the source module's `Core_Filter_Model`. `EditAjax` uses `Vtiger_Field_Model::getDisplayDateFilterTypes()` so date labels and period units are translated consistently. `Core_FilterOperator_Model::isCalendarValue()` excludes relative day/hour quantities and `lastperiod` from date-format conversion in persistence and readback. Readback stays in user display format; both query generators then pass criteria through the same module filter's `getQueryCondition()` as quick filtering. Time criteria keep their existing persistence/display conversion and the `time_start`/`time_end` special handling.
- `getSavedConditionCount()` reads the same standard and advanced definitions as the list query generator (one for a configured standard condition plus non-empty advanced columns). It is presentation metadata only; never merge it into temporary search parameters or let the toolbar clear it.

## Save as filter drafts

- The Core list toolbar opens a draft through `EditAjax` with `source_viewname` and `list_search_params`. `isListFilterSource()` requires the source to belong to the requested module and be list-accessible to the current user. The source model is cloned: columns and criteria are kept, but name (reset to empty), sharing (private), default and metrics flags are not inherited. Opening a draft changes no saved view; saving goes through the normal editor and action.
- `getCriteriaWithListSearch()` merges the temporary list-search groups (positional 0 = AND, 1 = OR) into the source criteria, keeping non-empty groups in order. Fields and operators are validated against `Core_ListFilter_Model::getFields()`; invalid input throws `LBL_INVALID_FILTER`.
- The Core toolbar and this editor share `Vtiger_FilterRecordStructure_Model` as the field source. Relation conditions keep the `(parent ; (Module) field)` key, so temporary relation filters convert to the same advanced-filter columns.

## Validation

- Lint changed PHP and load views with their actual parent classes. Check draft creation from shared views, column preservation, group logic, duplicate names, private defaults, save/reopen of time ranges and relative periods, and dates/currencies end to end on a configured instance.
