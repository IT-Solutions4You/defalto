# Installer changelog

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
