# Installer

Installer lets administrators browse extensions, install or update available packages, manage license keys, and update Defalto.

## Browse and install extensions

The Modules list shows extensions published in the public download catalog. Use the search box together with All, Updates, or Not installed to narrow the list; the filter counts always describe the complete loaded catalog. Each row separates its installed and available versions and shows whether an update is available, the module is up to date, it is not installed, or its package is unavailable.

Licensed package information can unlock a catalog product, but it cannot add internal or local modules such as Tour or InventoryItem to the list. Local custom modules created with Module Builder are also not shown merely because they are installed. Catalog products such as Module Builder remain visible when their package requires a license. The installed version is read from the extension's base module file.

- Public free extensions have an Install or Update button without requiring a license key.
- The Install or Update button appears only for a module without an installed version or when a newer version is available. It stays hidden when the same or a newer version is already installed.
- Paid extensions require an active license that includes the particular module. A Membership license does not automatically unlock every extension.
- When a catalog package cannot be downloaded, its row remains visible with a download-unavailable message.

Use Search for updates to refresh the catalog after new extensions or versions are published. Selecting Install or Update first downloads and validates the package without changing application files. The confirmation dialog shows customer-facing changes for up to the latest three packaged versions. Review them and select Install or Update in the dialog to continue. During installation, the extension name, target version, spinner, status, and progress indicator stay visible while the detailed technical log remains available in a collapsed section. Installer refreshes package availability before starting the installation.

## Licenses and maintenance

Add each license key in the Licenses section. Multiple product licenses can coexist. Membership rows show active CRM accounts against the purchased user limit, including available seats or the exact excess; product-specific licenses show that user usage does not apply. License keys stay masked until an administrator uses Manage to show or copy one. The same menu contains reactivation and confirmed removal.

Use Check license to refresh status and expiration information. Select the license status, such as Active, to open details showing the last successful check, errors, and included modules. Manage is left-aligned in the Actions column. If the license server is temporarily unavailable, a previously verified license keeps its last valid state while the connection error remains visible in its details. A new or already inactive license does not become active without successful verification. Correct any reported issue and reactivate the license when required, such as after an account, site, or subscription change.

If activation fails, the license dialog keeps your entered key and shows the error with numbered steps tailored to the cause. The most relevant check is expanded first; select another step to read its details. Follow the steps for occupied activations, an invalid or expired key, user limits, connection problems, or incomplete license data, then retry activation. Contact support only if the recommended checks do not resolve the problem. Include the product, system URL, Installer version, error, and checks already performed; do not include the full license key.

Installer interface messages, license statuses, update checks, and installation confirmations are localized for every language distributed with the system.

The compact System section shows the installed Defalto version, update state, Membership state, and last successful check time. Version information remains visible without Membership. Protected one-click system updates require active Membership.

Installed extensions may provide Settings links for requirements, license details, and uninstalling. Uninstalling a custom extension removes its module data and files after confirmation, so make a backup first.

Extension packages use the Defalto application structure. Installable files are limited to the `modules`, `layouts`, and `languages` directories and must belong to the packaged module. An optional package manifest belongs at `modules/<Module>/manifest.xml`; when present, its module, version, and compatibility information are validated. Cron handlers belong inside `modules/<Module>/cron`; a package cannot install files into the application's root `cron` directory.

An extension package can include `modules/<Module>/CHANGELOG.md`. Its newest version must match the packaged module version. If a legacy package has no changelog, Installer clearly indicates that no changelog is available and still allows installation.
