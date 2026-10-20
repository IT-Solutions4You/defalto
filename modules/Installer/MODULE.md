# Installer Module

## Purpose and Ownership

- Treat this file as durable, verified design memory for `modules/Installer` and its D1 layout assets. Update it when Installer behavior or invariants change; do not append task history or speculative notes.
- Keep membership and product-license state, activation rules, entitlement selection, API communication, licensed-user counting, update metadata, package installation, Installer notifications, and Installer requirement diagnostics owned by this module. `Installer_ModulePackage_Model` also owns the validated local module-package flow used by Settings Module Manager.
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
- In license rows, the status badge is a native Bootstrap dropdown button for license details in every state. Its dropdown shows the last successful check, either the actual error or the successful activation message, and all entitled modules; retain a minimum width of `30vw` with a viewport-safe maximum. Keep Manage left-aligned in the Actions cell.
- `Installer_License_Model::getErrorHelp()` owns ordered, localized troubleshooting steps. Failed activation responses return these steps for the existing license modal; render response strings as text, retain the entered key, and place support last. Opening the modal may display a stored error but must not trigger a license request. Unknown error codes use a translated inactive message and generic troubleshooting.
- Preserve the activation response `help` field as an array of plain descriptions for older loaded clients. Return titled step objects separately in `helpSteps`; the current renderer prefers that field and also accepts legacy text arrays. Do not change the type of an existing response field when extending troubleshooting presentation.

## API and Metadata Boundaries

- Use the Installer API endpoints through `Installer_Api_Model`: `/license/v1` for license actions, `/extension/v1` for the public extension catalog and licensed packages, and `/system/v1` for system metadata.
- Normalize the installation URL and send the current licensed-user count through the existing request builders. Keep timeouts, transport-error classification, JSON validation, and sensitive-value redaction centralized in the API model.
- Without an active Membership license, the system endpoint may return public update status. Preserve only `version` and `label`, and blank or remove download URLs, package folders, checksums, and every other protected package field.
- Fetch the public `/extension/v1` catalog without credentials, retaining entries without download URLs for display. Only packages with download URL and folder in this unauthenticated response receive the locally assigned `installer_public_download` flag. Never trust an API-provided flag to bypass licensing.
- Treat the unauthenticated extension response as the canonical visible catalog. Retrieve protected extension package metadata only for valid licenses with the matching entitlement, and use it only to enrich modules already present in that public catalog; a protected response must not introduce another module into the Installer list. Tag packages with their originating `installer_license_id`; metadata from an unrelated license must not overwrite an entitled package or public download.
- `Installer_SystemInstall_Model` and `Installer_ExtensionInstall_Model` own their session caches. Reuse these caches instead of adding duplicate API calls, and clear them after forced checks or successful state-changing operations.

## System Update Flow

- Require an active Membership license and force-check it immediately before a protected system update. Reject the operation if the refreshed license is invalid or protected download metadata is absent.
- Before extracting a system package, add every extension entitled by the Membership license to the ZIP skip lists so the system update cannot overwrite extension-owned module and layout files.
- Apply an API-provided SHA-256 checksum when present, run the package download/extraction and Composer update, apply database migrations, and commit staged files only after every step succeeds.
- Roll back staged files when download, extraction, Composer, or migration work fails. Do not bypass `Installer_Download_Model` or commit files before database work finishes.
- Preserve the System block layout: show Membership status in its header, render version/update state in the content column, and render update/download actions in the Actions column. When Membership is inactive, show `Installer_SystemInstall_Model::getBranding()` directly below the action for every system-update row. Keep the public SourceForge download fallback when protected download metadata is unavailable.
- Continue showing whether Defalto is current or has a newer version even when no license is active. When the public API status identifies the current release, render the normal up-to-date state rather than an empty update message.

## Extension Install Flow

- Validate module names before lookup or installation and bind each protected package to the license identified by `installer_license_id`. Force-check that license immediately before installation. Refresh catalog metadata before installation, including public packages; public downloads require no license.
- Merge API extension metadata with installed module state in `Installer_ExtensionInstall_Model`; do not duplicate this reconciliation in a view or template. Read an installed extension's version only from its base file at `modules/<Module>/<Module>.php`; never use the persisted `vtiger_tab.version` value for Installer display, update comparison, or post-install validation.
- Show every API catalog extension, including products whose package requires a license and is not currently available. Do not list local custom modules, including modules created through Module Builder, merely because they are installed. Resolve catalog names against installed module records instead of treating clean module placeholders as installed modules.
- Offer install/update actions for public packages or protected packages whose originating license is currently valid and explicitly entitles that module. Show the download-unavailable explanation for other catalog entries. Session catalog format version 3 invalidates catalogs that allowed protected responses to introduce non-public entries.
- `Installer_ExtensionInstall_Model::isInstallAvailable()` controls the catalog button: a downloadable package must have no installed version or a strictly newer available version. Equal or older catalog versions hide the button without presenting a license error for an otherwise downloadable package.
- Install extension packages only under the allowed roots `modules`, `layouts`, and `languages`. Every package path must belong to the installed module. Cron handlers are module files under `modules/<Module>/cron`; extension packages must never contain a root `cron` directory. Require the standard writable paths and, for a new module, the module metadata and privilege paths needed by the install lifecycle.
- Require `<Module>_Install_Model`, validate entity-module table metadata, and use the Core `postinstall` or `postupdate` lifecycle. Preserve and restore an existing module's sharing permission and regenerate module metadata through the existing Core mechanism.
- Before commit, verify that the module exists and is active, its installed version matches the package metadata, and its default URL is usable. Clear extension metadata after successful installation.
- If any later step fails, roll back copied files, restore sharing state where possible, restore database error settings, and rethrow the original failure with any rollback problem logged separately.
- For manually uploaded module packages, derive the module name and version from `modules/<Module>/<Module>.php`, require `modules/<Module>/models/Install.php`, and accept the application directory structure either at the ZIP root or below one wrapper directory. `modules/<Module>/manifest.xml` is optional; when supplied, require its name and version to match the module file and enforce its minimum Defalto version. Do not trust the module name, type, or version sent back by the browser.
- Let manual package errors propagate unchanged to the central WebUI handler. `Installer_ModulePackage_Model` owns lifecycle cleanup and rolls back uncommitted files without catching or replacing the original error.

## Package Safety and Recovery

- Use `Installer_Download_Model` for every system or extension package. Preserve its isolated `cache/installer` workspace, package lock, HTTP(S)-only URL validation, TLS peer verification, redirect limit, timeouts, ZIP signature check, 200 MiB download limit, and optional SHA-256 verification.
- Use `Installer_ZipArchive_Model` for extraction. Preserve the 10,000-entry and 200 MiB uncompressed limits, allowed-root and module-owned-path enforcement, duplicate-target detection, unsafe path rejection, destination containment checks, protected file/folder skip lists, staging, backups, commit, and rollback.
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
- Use Bootstrap components and utilities for overview sections, tables, status badges, buttons, filters, spacing, and responsive layout. The shared skin supplies primary and success button colors; keep module CSS limited to license details sizing, troubleshooting disclosure behavior, and clipboard positioning.
- License and extension status labels use Bootstrap badges with `fs-6 fw-bold px-3 py-2` and icons separated by `me-2`. License badges open details through native buttons; extension badges are non-interactive. Keep inactive licenses and up-to-date extensions neutral and visually quieter than available actions.
- Overview tables use `table-borderless` with `border-top` on body rows (`tr`), not individual cells. The enclosing section owns the bottom outline, avoiding doubled borders even when filtering changes the last visible row.
- Keep the license table wrapper overflow visible so license details and Manage dropdowns can extend beyond the table; `table-responsive` clips these menus.
- The Installer overview is rendered from server-provided license, system, and public-catalog extension models. License usage means active, non-deleted CRM accounts; show it only for Membership licenses and keep missing limits distinct from unlimited limits.
- Keep extension names and installed/available versions in separate columns. Derive the row state through `Installer_ExtensionInstall_Model`; text search combines with the All, Updates, and Not installed client-side filters, whose counts describe the complete loaded catalog.
- Module filter selection uses `active text-primary`; unselected filters use `text-body-secondary`. Keep server-rendered defaults and client-side selection classes aligned, including Clear search.
- Keep license keys masked by default. The administrator-only Manage menu may reveal, copy, reactivate, or remove a key; removal retains its existing confirmation flow. Select the license status to view the last check, errors, and entitlements.
- Keep the license field unboxed and vertically stacked above a bordered warning alert. Troubleshooting uses numbered native details with localized titles and descriptions; expand only the first step initially. Rank steps by relevance to the returned error, with retry and support last. Preserve matching server-rendered and AJAX markup.
- Load `resources/Index.js` through `Installer_Index_View::getHeaderScripts()` using `modules.Installer.resources.Index`. Load `resources/Index.css` through the owning view's CSS path; do not add inline style blocks to templates.
- Keep user-facing labels in `languages/<locale>/Installer.php`. Templates must translate labels rather than hardcode localized copy.
- Keep the Installer PHP and JavaScript language-key sets aligned across every locale directory under `languages`, creating `Installer.php` when a locale does not yet provide it. Shared Core labels may remain in the central Core language files.
- A full page reload after license activation/deletion or completed extension installation is intentional only where license, menu, module, or resource state cannot be synchronized safely in place. Prefer targeted UI updates for smaller AJAX state changes.

## Validation

- For Installer PHP changes, run `php -l` on every touched PHP file.
- Run available focused Installer checks; use manual PHP scenarios for catalog normalization, public downloads, multiple-license merging, and entitlement rejection when no standalone tests are present.
- After changing `layouts/d1/modules/Installer/resources/*.js`, run a JavaScript syntax check and the application scripts validator, and confirm the expected `Installer_<Script>_Js` class is present.
- For functional Installer changes, bump both the application patch in `version.php` and the final component of `Installer::$moduleVersion` exactly once. Documentation-only changes to this file require neither version bump.
