# Settings Module Manager

## Purpose and ownership

- Settings Module Manager owns administrator UI entry points for listing, enabling, disabling, uploading, and confirming modules.
- `Installer_ModulePackage_Model` owns validation and installation of a manually uploaded module ZIP. Keep archive safety, staging, rollback, module-path ownership, and lifecycle behavior in Installer instead of duplicating it in the view or action.
- The legacy `Vtiger_Package` manifest importer remains available for legacy catalog flows, but manually uploaded Defalto module packages must not use its directory remapping.

## Manual package contract

- Accept installable files only below `modules`, `layouts`, and `languages`, either directly at the ZIP root or below one wrapper directory.
- Derive exactly one module identity from `modules/<Module>/<Module>.php`. Require a declared `moduleVersion` and `modules/<Module>/models/Install.php`. `modules/<Module>/manifest.xml` is optional; when present, its name and version must match the module file and its minimum Defalto version is enforced.
- Permit only paths owned by that module: its module and Settings directories, its layout module directories, and its locale language files.
- Keep cron handlers under `modules/<Module>/cron`. Reject a root `cron` directory and every other application root.
- Load and install an optional manifest only from `modules/<Module>/manifest.xml`. Never use a root manifest or use manifest data to remap package paths.
- Re-read and validate the uploaded ZIP in the write action. Never trust hidden browser fields for module identity, type, or version.
- Store pending uploads under `cache/installer/uploads` with random names. Delete the upload after a successful installation.

## Installation lifecycle

- Use `module.postinstall` for a new module and `module.postupdate` for an installed module through `Core_Install_Model`.
- Reject an update whose package version is not newer than the installed module file version.
- Preserve an existing module's sharing permission, regenerate module metadata, clear runtime and template caches, and verify the installed version, active state, and default URL before committing package files.
- On failure, restore copied files where possible and let the original error propagate to the central WebUI exception handler without replacing or hiding it.
- In the ZIP install AJAX failure callback, display the server-provided error in a modal. Tell the administrator to correct the cause and retry or report the exact error to the package publisher, and offer navigation to import another package while keeping the current install/update action available after closing the modal.

## Validation

- Run `php -l` on touched Module Manager and Installer PHP files.
- Run a JavaScript syntax check and the ITS4You ScriptsValidator after changing Module Manager resources.
- Manually check direct-root and one-wrapper packages, install and update flows, invalid roots, cross-module paths, traversal paths, duplicate targets, oversized archives, older versions, and rollback after a lifecycle failure.
