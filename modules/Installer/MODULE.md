# Installer Module

## Purpose and Ownership

- Treat this file as durable, verified design memory for `modules/Installer` and its D1 layout assets. Update it when Installer behavior or invariants change; do not append task history or speculative notes.
- Keep membership and product-license state, activation rules, entitlement selection, API communication, licensed-user counting, update metadata, package installation, Installer notifications, and Installer requirement diagnostics owned by this module.
- Keep reusable module installation lifecycle behavior in `Core_Install_Model`. The initial `Install` module may collect license keys, but it must delegate activation to `Installer_License_Model` and must not write `df_licenses` directly.
- Keep purchase redirects owned by `modules/Core/views/Redirect.php`; Installer renders only the branding returned by `Installer_SystemInstall_Model::getMembershipBranding()`.
- Keep database access in Installer models. Views and actions validate requests, select models, and render or return responses without embedding SQL.

## Entry Points and Authorization

- Installer management index, AJAX, and requirements views/actions are administrator-only. Preserve their existing permission checks when adding modes. `Installer_PremiumModal_View` is a reusable read-only footer modal for extension users, not an administrator management endpoint; do not add privileged mutations to it.
- `Installer_Index_View::installer()` may perform only the existing debounced, non-forced `check(false)` for each stored license during page rendering.
- License activation and reactivation enter only through `Installer_IndexAjax_Action::licenseSave()`, which delegates to `Installer_License_Model::activate()`.
- Explicit deactivation enters only through `Installer_IndexAjax_Action::licenseDelete()`. Call the remote deactivation first and delete local state only when that operation permits it.
- Forced checks belong only to Installer-owned manual check/update flows, immediately before a requested protected system or extension installation, and the daily Installer cron. Never move them into application startup, login, Users lifecycle events, unrelated handlers, or unrelated cron tasks.
- Keep extension uninstall as a confirmed, CSRF-protected write operation. Permit it only for custom-source modules and delegate removal to the Core install lifecycle.

## License Contract and State

- `Installer_License_Model` is the contract and persistence owner for `df_licenses`; `Installer_Api_Model` owns HTTP communication; `Installer_UserCount_Model` owns the current count of active, non-deleted users.
- Preserve multiple independently validated licenses. Membership and product-specific licenses may coexist, and an extension is active only when a valid license explicitly entitles that extension.
- A license is active only when the API contract is complete: `success === true`, `license === "valid"`, `users_valid === true`, required expiration data exists, and the current local user count does not exceed a positive limit. A zero limit means unlimited.
- Persist a newly entered key only after the complete activation contract succeeds. On later validation or transport failures, retain the stored key and persist the inactive/error state so the administrator can see the error and retry.
- Preserve the API v2 metadata required for display and entitlement checks, including product identity, expiration, canonical user limit, user count, and extension entitlements. Never expose the license key in logs or debug payloads.
- Keep the non-forced license check debounce and per-request/process locking. Clear the cached license collection and Installer system/extension metadata when license state changes.
- Use `Installer_Cache_Model` for request-local license collection and ID hydration caches. Invalidate the affected license entry and collection after persistence, and clear all Installer runtime caches when license state changes.
- In license rows, keep the details icon after the action buttons and right-aligned. Render it as a borderless icon, green without an error and red with an error. Its dropdown must show the last successful check, either the actual error or the successful activation message, and all entitled modules; retain a minimum width of `30vw` with a viewport-safe maximum.

## API and Metadata Boundaries

- Use the Installer API endpoints through `Installer_Api_Model`: `/license/v1` for license actions, `/extension/v1` for protected extension metadata, and `/system/v1` for system metadata.
- Normalize the installation URL and send the current licensed-user count through the existing request builders. Keep timeouts, transport-error classification, JSON validation, and sensitive-value redaction centralized in the API model.
- Without an active Membership license, the system endpoint may return public update status. Preserve only `version` and `label`, and blank or remove download URLs, package folders, checksums, and every other protected package field.
- Retrieve extension package metadata only for valid licenses. Tag each returned extension with its originating `installer_license_id` so the install flow revalidates the correct license.
- `Installer_SystemInstall_Model` and `Installer_ExtensionInstall_Model` own their session caches. Reuse these caches instead of adding duplicate API calls, and clear them after forced checks or successful state-changing operations.

## System Update Flow

- Require an active Membership license and force-check it immediately before a protected system update. Reject the operation if the refreshed license is invalid or protected download metadata is absent.
- Before extracting a system package, add every extension entitled by the Membership license to the ZIP skip lists so the system update cannot overwrite extension-owned module and layout files.
- Apply an API-provided SHA-256 checksum when present, run the package download/extraction and Composer update, apply database migrations, and commit staged files only after every step succeeds.
- Roll back staged files when download, extraction, Composer, or migration work fails. Do not bypass `Installer_Download_Model` or commit files before database work finishes.
- Preserve the System block layout: show Membership status in its header, render version/update state in the content column, and render update/download actions in the Actions column. When Membership is inactive, show `Installer_SystemInstall_Model::getBranding()` directly below the action for every system-update row. Keep the public SourceForge download fallback when protected download metadata is unavailable.
- Continue showing whether Defalto is current or has a newer version even when no license is active. When the public API status identifies the current release, render the normal up-to-date state rather than an empty update message.

## Extension Install Flow

- Validate module names before lookup or installation and bind each protected package to the license identified by `installer_license_id`. Force-check that license immediately before installation.
- Merge API extension metadata with installed module state in `Installer_ExtensionInstall_Model`; do not duplicate this reconciliation in a view or template.
- Keep installed custom extensions visible in the Installer module list even when no license is active. Do not list ordinary Core CRM modules merely because they are installed.
- Offer an extension install/update action only when protected download metadata exists and its originating license is currently valid and explicitly entitles that module. Stale metadata must never expose an update action.
- Install extension packages only under the allowed roots `modules`, `layouts`, `languages`, and `cron`. Require the standard writable paths and, for a new module, the module metadata and privilege paths needed by the install lifecycle.
- Require `<Module>_Install_Model`, validate entity-module table metadata, and use the Core `postinstall` or `postupdate` lifecycle. Preserve and restore an existing module's sharing permission and regenerate module metadata through the existing Core mechanism.
- Before commit, verify that the module exists and is active, its installed version matches the package metadata, and its default URL is usable. Clear extension metadata after successful installation.
- If any later step fails, roll back copied files, restore sharing state where possible, restore database error settings, and rethrow the original failure with any rollback problem logged separately.

## Package Safety and Recovery

- Use `Installer_Download_Model` for every system or extension package. Preserve its isolated `cache/installer` workspace, package lock, HTTP(S)-only URL validation, TLS peer verification, redirect limit, timeouts, ZIP signature check, 200 MiB download limit, and optional SHA-256 verification.
- Use `Installer_ZipArchive_Model` for extraction. Preserve the 10,000-entry and 200 MiB uncompressed limits, allowed-root enforcement, duplicate-target detection, unsafe path rejection, destination containment checks, protected file/folder skip lists, staging, backups, commit, and rollback.
- Do not broaden allowed roots, writable paths, or protected-file exceptions without tracing both system and extension update behavior and adding focused regression coverage.
- Keep progress and diagnostic logs free of raw license keys, credentials, unsafe markup, and attacker-controlled unescaped values.

## Installation, Notifications, and Requirements

- `Installer_Install_Model` owns the `df_licenses` and `df_notifications` schema, Installer settings links, and registration of the daily `InstallerLicenses` cron in `modules/Installer/cron/UpdateLicenses.php`.
- The cron updates all licenses and then reconciles Installer notifications. Keep its daily interval unless the requirement explicitly changes.
- `Installer_Notification_Model` owns license, system, and extension notifications. Reconcile only Installer-managed notification names, remove stale duplicates, and emit at most the latest relevant system-version notification.
- `Installer_Requirements_Model` owns server, PHP, database, file-permission, and version compatibility checks. Unknown future Defalto versions must use the current safe requirement profile.
- `Installer_ModuleRequirements_Model` diagnoses registered module assets, cron entries, custom links, event handlers, and related lists. Keep it diagnostic; module setup changes belong to the module's install model and Core install APIs.

## Frontend and Languages

- Keep Installer templates in `layouts/d1/modules/Installer` and module assets in `layouts/d1/modules/Installer/resources`.
- Load `resources/Index.js` through `Installer_Index_View::getHeaderScripts()` using `modules.Installer.resources.Index`. Load `resources/Index.css` through the owning view's CSS path; do not add inline style blocks to templates.
- Keep user-facing labels in `languages/<locale>/Installer.php`. Templates must translate labels rather than hardcode localized copy.
- A full page reload after license activation/deletion or completed extension installation is intentional only where license, menu, module, or resource state cannot be synchronized safely in place. Prefer targeted UI updates for smaller AJAX state changes.

## Validation

- For Installer PHP changes, run `php -l` on every touched PHP file.
- Run the focused standalone tests relevant to the change under `tests/unit/Installer*Test.php`, especially license/API, multi-license, package installation/rollback, optimization/cache, and requirements-version coverage.
- After changing `layouts/d1/modules/Installer/resources/*.js`, run a JavaScript syntax check and the application scripts validator, and confirm the expected `Installer_<Script>_Js` class is present.
- For functional Installer changes, bump both the application patch in `version.php` and the final component of `Installer::$moduleVersion` exactly once. Documentation-only changes to this file require neither version bump.
