# Import

- `Import_ListView_Model` owns imported-record lists and counts. Its factory uses `Core_QueryGenerator_Model` for source-module normalization and outer grouping. Preserve the current user imported-record restriction. Import actions own queue/lock data and per-user import tables; `Import_Install_Model` owns tables and cron registration. The root extension has no local version override. No schema change is needed. Validate list/count grouping including OR and empty groups; lint `models/ListView.php`.
