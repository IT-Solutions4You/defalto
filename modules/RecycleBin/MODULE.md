# RecycleBin

- `RecycleBin_ListView_Model` owns deleted-record lists and counts. Its factory uses `Core_QueryGenerator_Model` for shared normalization and outer grouping. The existing list/count flow changes the deleted predicate to select deleted records; preserve this and source permissions. Restore/delete actions belong to `RecycleBinAjax`. `RecycleBin_Install_Model` registers a standard module without owned tables. The root extension has no local version override. Validate filtered list/count consistency and exclusion of active records; lint `models/ListView.php`.
