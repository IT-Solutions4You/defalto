# Shared filter processing

- `Vtiger_Util_Helper::transferListSearchParamsToFilterCondition()` converts list and advanced-search conditions for the query generator. Source group zero uses AND within the group; source group one uses OR. Empty groups must not be emitted, but their source positions must be preserved when choosing the within-group operator.
- `Vtiger_ListAjax_View::showSearchResults()` loads filtered entries and separately requests the total count through `Vtiger_List_View::getListViewCount()`. Both paths must receive valid conditions without a trailing group connector.
- Filter creation persists through CustomView; applying a top-bar filter uses the shared Vtiger list search flow.
- Verify empty and populated filter groups manually; lint touched PHP files.
