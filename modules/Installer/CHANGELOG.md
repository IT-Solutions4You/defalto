# Installer changelog

## 1.63 - 2026-11-27

- Excludes obsolete-link removal instructions from missing link requirements.

- Displays the available Installer version and up-to-date status together on one line.
- Explains disabled system and extension installation buttons with a tooltip when Installer needs updating or its latest version cannot be verified.
- Matches the Installer update row to the system update design with a status icon, prominent installed version, available version below it and consistent update buttons.
- Shows a compact red status badge beside the Installer name, with the full installation prerequisite available on hover and to screen readers.
- Requires the latest Installer before updating Defalto or installing, updating or reinstalling extensions, including uploaded ZIP packages.
- Shows Installer updates above the system update and keeps license management available while other installations are blocked.
- Prevents system update packages from overwriting Installer and prevents installing an older Installer version.
- Blocks system and extension installation when the latest Installer version cannot be verified, while allowing a validated Installer repair package.
- Displays GitHub release-note bullet points on separate lines even when the list directly follows the introduction.
- Allows reinstalling equal or older extension versions from the catalog and Module Manager, showing both versions and a notice before confirmation.
- Warns about possible data incompatibility before installing an older extension package.
- Shows the selected Defalto version's GitHub release notes, including categories and lists, before confirming a system update.
- Explains when release notes are missing or temporarily unavailable while still allowing the update to continue.

## 1.62 - 2026-11-03

- Keeps dependency requirements and locked versions together during system updates, preventing failures caused by a retained older lock file.

- Displays installation logs as a readable Markdown-style list with emphasized errors.

- Shows the specific installation error in a prominent alert, with the expandable technical log below it, including when the request fails.

## 1.61 - 2026-10-27

- Adds complete Installer and Settings translations for every language included with Defalto.
- Extends the license-server response window so slow store connections can complete and return a diagnostic result.
- Validates and activates every distinct license supplied during initial installation, storing only keys that pass complete validation.
- Uses PHP cURL for secure license-server communication while retaining the existing client as a compatibility fallback.
- Provides administrators with safe connection diagnostics when license activation is retried in debug mode.
- Keeps a previously verified license active during a temporary license-server outage and applies refreshed status when the server is available again.
- Adds safer validation, staging, rollback, and compatibility checks for manually uploaded module ZIP packages.
- Improves license management, extension filtering, license-key actions, and localized error guidance.
- Shows validated extension release notes before an administrator starts installation or update.
- Displays up to the latest three extension versions and keeps the detailed installation log collapsed below a compact progress indicator.
- Keeps the extension name, target version, spinner, and localized installation status visible while the technical log is collapsed.
- Shows the same packaged release notes when installing or updating a module from a ZIP file in Module Manager.

## 1.60 - 2026-10-19

- Improved installed-version detection and partial-update compatibility.
- Kept the public extension catalog authoritative while applying licensed download details only to matching products.
