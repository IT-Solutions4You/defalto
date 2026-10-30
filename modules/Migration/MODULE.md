# Migration runtime

## Application version ownership

- `Migration_Module_Model` owns the migration version comparison and the update of `vtiger_version` after a successful migration.
- The latest source version comes from `$defalto_current_version`, which is defined by `version.php`. Do not use the removed `vtiger_current_version` global.
- `getDBVersion()` reads the installed database version, while `getLatestSourceVersion()` returns the running application version.
- Links shown by the migration wizard use `Core/Redirect` modes for Defalto documentation, community, videos, social pages, and release notes. Do not add direct upstream vtiger URLs to templates or translations.

## Validation

- Run `php -l modules/Migration/models/Module.php` after changing the migration model.
