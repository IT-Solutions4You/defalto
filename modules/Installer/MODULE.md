# Installer Module

Installer manages licenses, Defalto system updates and extension packages. The initial setup wizard belongs to the `Install` module, not to Installer.

## Purpose and Ownership

- Treat this file as durable, verified design memory for `modules/Installer` and its D1 layout assets. Update it when behavior or invariants change; do not append task history or speculative notes.
- Installer owns membership and product-license state, activation rules, entitlement selection, API communication, licensed-user counting, update metadata, package installation, Installer notifications and requirement diagnostics. `Installer_ModulePackage_Model` also owns the validated local module-package flow used by Settings Module Manager.
- Reusable module installation lifecycle behavior stays in `Core_Install_Model`.
- The `Install` module collects license keys during setup. It activates them through `Installer_Api_Model::activateLicenseInfo()` and persists them through `Installer_License_Model::setInfo()` and `save()`; it does not call `activate()` and must not write `df_licenses` directly.
- Purchase redirects (Membership, Order, Buy, Store, SourceForge and others) are owned by `modules/Core/views/Redirect.php`. Installer only renders the branding from `Installer_SystemInstall_Model::getMembershipBranding()` (`getBranding()` delegates to it).
- SQL stays in Installer models. Views and actions validate requests, select models and render or return responses.

## Entry Points and Authorization

- Installer index, AJAX and requirements views/actions are administrator-only; preserve their permission checks when adding modes. `Installer_PremiumModal_View` is a reusable read-only footer modal for extension users, not an administrator endpoint; do not add privileged mutations to it.
- License activation and reactivation enter only through `Installer_IndexAjax_Action::licenseSave()`, which delegates to `Installer_License_Model::activate()`.
- Explicit deactivation enters only through `Installer_IndexAjax_Action::licenseDelete()`. Call the remote deactivation first and delete local state only when that operation permits it.
- `Installer_Index_View::installer()` may only run the debounced, non-forced `check(false)` (900 s) for each stored license while rendering.
- Forced checks belong only to Installer-owned manual flows (Check license, Check for updates), immediately before a requested protected system or extension installation, and the daily `InstallerLicenses` cron. Never move them into application startup, login, Users lifecycle events, unrelated handlers or unrelated cron tasks.
- Extension uninstall is a confirmed, CSRF-protected write. It is allowed only for modules whose source is `custom` and delegates removal to the Core install lifecycle (`module.preuninstall`, `deleteModule()`).

## License Contract and State

- `Installer_License_Model` owns the contract and persistence of `df_licenses`; `Installer_Api_Model` owns HTTP communication; `Installer_UserCount_Model` owns the count of active, non-deleted users.
- Multiple independently validated licenses coexist. An extension is active only when a valid license explicitly entitles it; Membership does not unlock every extension.
- A license becomes active only when the API contract is complete: `success === true`, `license === "valid"`, `users_valid === true`, expiration data exists, and the local user count does not exceed a positive limit (zero means unlimited). The live local user-limit check keeps applying after a transport failure.
- Persist a newly entered key only after the full activation contract succeeds. On later validation failures keep the stored key and persist the inactive/error state. On transport failures keep both the key and its last verified state and record the connection error so the administrator can see it and retry.
- Keep the API v2 metadata needed for display and entitlement checks: product identity, expiration, canonical user limit, user count and extension entitlements. Never expose license keys in logs or debug payloads.
- Keep the non-forced check debounce and per-request/process locking. When license state changes, clear the license collection and all Installer runtime caches (`Installer_Cache_Model` holds the request-local collection and ID hydration caches; invalidate the affected entry and collection after persistence) and the system/extension session metadata.
- `Installer_License_Model::getErrorHelp()` owns ordered, localized troubleshooting steps. Failed activation responses return them for the license modal; render response strings as text, retain the entered key and put support last. Opening the modal may show a stored error but must not trigger a license request. Unknown error codes use a translated inactive message and generic troubleshooting.
- The activation response keeps `help` as an array of plain descriptions for older clients; titled step objects go in `helpSteps`, which the current renderer prefers while still accepting legacy text arrays. Do not change the type of an existing response field.

## API and Metadata Boundaries

- Use the API only through `Installer_Api_Model`: `/license/v1` for license actions, `/extension/v1` for the public catalog and licensed packages, `/system/v1` for system metadata. The model also centralizes URL normalization, the licensed-user count in requests, timeouts, transport-error classification, JSON validation and redaction.
- Without an active Membership license the system endpoint may return public update status. Keep only `version` and `label` and blank or remove download URLs, package folders, checksums and every other protected package field.
- The unauthenticated `/extension/v1` response is the canonical visible catalog. Entries without download URLs stay visible; only packages with a download URL and folder in that response get the locally assigned `installer_public_download` flag. Never trust an API-provided flag to bypass licensing.
- Protected package metadata is fetched only for valid licenses with the matching entitlement and only enriches modules already in the public catalog; it must not add a module to the list. Tag packages with their `installer_license_id`; metadata from an unrelated license must not overwrite an entitled package or public download.
- `Installer_SystemInstall_Model` and `Installer_ExtensionInstall_Model` own their session caches. Reuse them instead of adding API calls and clear them after forced checks and successful state-changing operations. The extension session catalog has format version 3, which invalidates older catalogs that allowed protected responses to add non-public entries.

## System Update Flow

- Require an active Membership license and force-check it immediately before a protected system update. Reject the update if the refreshed license is invalid or protected download metadata is missing.
- Before extraction add every extension entitled by the Membership license to the ZIP skip lists so a system update cannot overwrite extension-owned module and layout files.
- Apply the API-provided SHA-256 checksum when present, download and extract, run the Composer dependency install, apply database migrations, and commit staged files only after every step succeeds. If any step fails (including Composer or migration), roll back the staged files. Do not bypass `Installer_Download_Model` or commit files before the database work finishes.
- The System block shows Membership status in its header, version/update state in the content column and actions in the Actions column. The update button opens a modal that requires three confirmations (database backup, source backup, system requirements checked). When Membership is inactive, `getBranding()` renders below the action in every system row. Without protected download metadata the action falls back to the public SourceForge download via `Redirect`.
- Version information stays visible without a license. When the public API identifies the current release, render the normal up-to-date state instead of an empty update message. "Check for updates" (`updateInformation`) is offered only when the system is current and the session cache is older than 60 seconds; it updates all licenses, clears the extension and system caches and reconciles notifications.

## Extension Install Flow

- Validate module names before lookup or installation and bind each protected package to the license in `installer_license_id`. Force-check that license and refresh catalog metadata immediately before installation; public downloads need no license.
- `Installer_ExtensionInstall_Model` merges API catalog metadata with installed module state; do not duplicate this in views or templates. Read an installed version only from `modules/<Module>/<Module>.php`, never from `vtiger_tab.version`, for display, update comparison and post-install validation.
- List every catalog extension, including products whose package needs a license and is currently unavailable. Do not list local custom modules (including Module Builder modules) just because they are installed.
- Offer install/update for public packages or protected packages whose originating license is valid and entitles the module; other entries show the download-unavailable explanation. `isInstallAvailable()` controls the button: a downloadable package needs no installed version or a strictly newer available version, and equal or older versions hide the button without a license error.
- Install only under the roots `modules`, `layouts` and `languages`; every path must belong to the installed module. Cron handlers live in `modules/<Module>/cron`; a package must never contain a root `cron` directory. Require the standard writable paths and, for a new module, the metadata and privilege paths needed by the install lifecycle.
- Require `<Module>_Install_Model`, validate entity-module table metadata and use the Core `module.postinstall`/`module.postupdate` lifecycle. Preserve and restore an existing module's sharing permission and regenerate module metadata through the Core mechanism.
- Before commit verify that the module exists and is active, that its installed version equals the package version and that its default URL is usable; then clear extension metadata. If a later step fails, roll back copied files, restore sharing state where possible and database error settings, and rethrow the original failure with rollback problems logged separately.
- A package may provide `modules/<Module>/CHANGELOG.md` with newest-first `## <version> - YYYY-MM-DD` headings and plain `- <change>` items (`Installer_Changelog_Model`). The first heading must equal the package version; at most the newest three versions are shown. The date is a display string with no time-zone conversion. A missing changelog stays installable and shows the missing-changelog message; a malformed one (invalid UTF-8, wrong heading, duplicate or unordered versions, size limit) is an error.
- The catalog confirmation downloads and validates the ZIP without extracting it, shows the changelog and starts installation only after the administrator confirms. The installation request downloads and validates the package again and never trusts preview state. The progress UI shows spinner, status, extension name, target version and progress indicator; the technical log stays collapsed unless opened or an error occurs.
- Manually uploaded packages (`Installer_ModulePackage_Model`): derive module name and version from `modules/<Module>/<Module>.php` (exactly one such file) and require `modules/<Module>/models/Install.php`. The application structure may sit at the ZIP root or inside one wrapper directory. `manifest.xml` is optional; when present its name and version must match the module file and its minimum Defalto version is enforced. An update must be strictly newer than the installed version. Never trust module name, type or version sent back by the browser. The same optional changelog is parsed during inspection and shown on the confirmation screen. Errors propagate unchanged to the central WebUI handler; the model cleans up and rolls back uncommitted files without replacing the original error.

## Package Safety and Recovery

- Use `Installer_Download_Model` for every system or extension package: isolated `cache/installer` workspace, package lock, HTTP(S)-only URL validation, TLS peer verification, redirect limit, timeouts, ZIP signature check, 200 MiB download limit and optional SHA-256 verification.
- Use `Installer_ZipArchive_Model` for extraction: 10,000-entry and 200 MiB uncompressed limits, allowed-root and module-owned-path enforcement, duplicate-target detection, unsafe path rejection, destination containment, protected file/folder skip lists, staging, backups, commit and rollback.
- Do not broaden allowed roots, writable paths or protected-file exceptions without tracing both system and extension update behavior and adding focused regression coverage.
- Keep logs free of raw license keys, credentials, unsafe markup and unescaped attacker-controlled values.

## Installation, Notifications and Requirements

- `Installer_Install_Model` owns the `df_licenses` and `df_notifications` schema, the Installer and Requirements settings links and the daily (86400 s) `InstallerLicenses` cron in `modules/Installer/cron/UpdateLicenses.php`. The cron updates all licenses and then reconciles notifications; keep the interval unless a requirement changes it.
- `Installer_Notification_Model` owns license, system and extension notifications. It reconciles only managed names (`Update Defalto` and names starting with `License ` or `Extension `), removes stale duplicates and emits at most the latest relevant system-version notification.
- `Installer_Requirements_Model` owns server, PHP, database, file-permission and version compatibility checks; unknown future Defalto versions use the current safe requirement profile.
- `Installer_ModuleRequirements_Model` diagnoses registered module assets, cron entries, custom links, event handlers and related lists. Keep it diagnostic; module setup changes belong to the module's install model and Core install APIs.
- Installed extensions get Settings links to their requirements, license and uninstall pages from `Installer_ExtensionInstall_Model`.

## Frontend and Languages

- Templates live in `layouts/d1/modules/Installer`, assets in `layouts/d1/modules/Installer/resources`. `resources/Index.js` is loaded through `Installer_Index_View::getHeaderScripts()` as `modules.Installer.resources.Index`; `resources/Index.css` through the owning view's CSS path. No inline style blocks in templates.
- Use Bootstrap components and utilities; the shared skin supplies primary and success button colors. Module CSS is limited to license details sizing, troubleshooting disclosure and clipboard positioning.
- Status labels are Bootstrap badges (`fs-6 fw-bold px-3 py-2`, icons separated by `me-2`). License badges are native dropdown buttons in every state; extension badges are non-interactive. Inactive licenses and up-to-date extensions stay neutral and quieter than available actions.
- The license-details dropdown shows the last successful check, the actual error or the activation success message, and all entitled modules, with a minimum width of `30vw` and a viewport-safe maximum. Manage stays left-aligned in the Actions cell.
- Overview tables use `table-borderless` with `border-top` on body rows; the section owns the bottom outline. Keep the license table wrapper overflow visible so detail and Manage dropdowns are not clipped (`table-responsive` clips them).
- The overview is rendered from server-provided license, system and public-catalog extension models. License usage (active, non-deleted CRM accounts) is shown only for Membership licenses, and a missing limit stays distinct from unlimited.
- Extension names and installed/available versions sit in separate columns; row state comes from `Installer_ExtensionInstall_Model`. Text search combines with the All, Updates and Not installed client-side filters, whose counts describe the whole loaded catalog. Selected filters use `active text-primary`, others `text-body-secondary`; server-rendered defaults and client-side classes (including Clear search) must agree.
- License keys are masked by default. The Manage menu reveals, copies, reactivates or removes a key (removal keeps its confirmation).
- The license field is unboxed and stacked above a bordered warning alert. Troubleshooting uses numbered native details with localized titles and descriptions, only the first expanded, ranked by relevance to the returned error with retry and support last. Server-rendered and AJAX markup must match.
- Labels live in `languages/<locale>/Installer.php`; templates translate them. Keep PHP and JavaScript key sets aligned across every locale directory under `languages`, creating `Installer.php` where missing. Shared Core labels may stay in the Core language files.
- A full page reload after license activation/deletion or completed extension installation is intentional only where license, menu, module or resource state cannot be synchronized in place; prefer targeted updates otherwise.

## Validation

- Run `php -l` on every touched PHP file and any available focused Installer checks; without standalone tests, use manual PHP scenarios for catalog normalization, public downloads, multiple-license merging and entitlement rejection.
- After changing `layouts/d1/modules/Installer/resources/*.js`, run a JavaScript syntax check and the application scripts validator, and confirm the expected `Installer_<Script>_Js` class is present.
- For functional Installer changes bump both the application patch in `version.php` and the final component of `Installer::$moduleVersion` once. Documentation-only changes need neither.
