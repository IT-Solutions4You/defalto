# Import

- `Import_ListView_Model` owns imported-record lists and counts. Its factory uses `Core_QueryGenerator_Model` for source-module normalization and outer grouping. Preserve the current user imported-record restriction.
- Import actions own queue/lock data and per-user import tables; `Import_Install_Model` owns tables and cron registration. `Import_Config_Model` keeps immediate imports at or below 5,000 records in the browser-driven flow and schedules larger imports through cron. Immediate processing remains limited to 250 records per request.
- The root extension has no local version override. No schema change is needed.
- Validate list/count grouping including OR and empty groups; lint `models/ListView.php`. For import routing changes, verify the 5,000-record immediate boundary, the 5,001-record scheduled boundary, and PHP request duration with a representative import.
