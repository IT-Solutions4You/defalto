# Settings Module Manager

## Purpose and ownership

- Settings Module Manager owns the administrator UI: the module list (enable/disable checkbox via `Basic::updateModuleStatus`, per-module Settings links) and the ZIP upload/confirmation screens.
- `Installer_ModulePackage_Model` owns validation and installation of a manually uploaded module ZIP. Keep archive safety, staging, rollback, module-path ownership, and lifecycle behavior in Installer instead of duplicating it in the view or action.
- Flow: `ModuleImport` view modes `importUserModuleStep1` (disclaimer and upload form) and `importUserModuleStep2` (stores the upload, shows the confirmation), then `Basic` action modes `importUserModuleStep3` / `updateUserModuleStep3` (both call `installUserModulePackage()`).
- The legacy `Vtiger_Package` manifest importer remains only for the legacy extension flow (`ModuleImport` `step2` / `step3`); manually uploaded Defalto module packages must not use its directory remapping.

## Manual package contract

- Accept installable files only below `modules`, `layouts`, and `languages`, either directly at the ZIP root or below one wrapper directory.
- Derive exactly one module identity from `modules/<Module>/<Module>.php`. Require a declared `moduleVersion` and `modules/<Module>/models/Install.php`. `modules/<Module>/manifest.xml` is optional; when present, its name and version must match the module file and its minimum Defalto version is enforced. Never use a root manifest or manifest data to remap package paths.
- Read an optional `modules/<Module>/CHANGELOG.md` from the validated ZIP, require its newest version heading to match the package version, and show at most its newest three versions (`Installer_Changelog_Model::MAX_DISPLAYED_VERSIONS`) on the confirmation screen. Legacy packages without a changelog stay installable.
- Permit only paths owned by that module: `modules/<Module>/`, `modules/Settings/<Module>/`, `layouts/<Layout>/modules/<Module>/` and its Settings equivalent, and `languages/<Locale>/<Module>.php` or `languages/<Locale>/Settings/<Module>.php`. Keep cron handlers under `modules/<Module>/cron`; a root `cron` directory and every other application root are rejected.
- Re-read and validate the uploaded ZIP in the write action. Never trust hidden browser fields for module identity, type, or version; the action accepts only an upload name matching `usermodule_<24 hex>.zip`.
- Store pending uploads under `cache/installer/uploads` with random names. Delete the upload after a successful installation.

## Installation lifecycle

- Use `module.postinstall` for a new module and `module.postupdate` for an installed module through `Core_Install_Model`.
- Reject an update whose package version is not newer than the installed module file version.
- Preserve an existing module's sharing permission, regenerate module metadata, clear runtime and template caches, and verify the installed version, active state, and default URL before committing package files.
- On failure, restore copied files where possible and let the original error propagate to the central WebUI exception handler without replacing or hiding it.
- `Index.js::showPackageInstallError()` (the install AJAX failure callback) shows the server-provided error in a modal with guidance to fix the cause and retry or report the exact error to the package publisher. The modal offers navigation to import another package; the install/update button stays available after closing it.

## Validation

- Run `php -l` on touched Module Manager and Installer PHP files.
- Run a JavaScript syntax check and the ITS4You ScriptsValidator after changing Module Manager resources.
- Manually check direct-root and one-wrapper packages, install and update flows, invalid roots, cross-module paths, traversal paths, duplicate targets, oversized archives, older versions, and rollback after a lifecycle failure.
