# Installer

Installer lets administrators browse extensions, install or update available packages, manage license keys, and update Defalto.

## Browse and install extensions

The Modules list shows extensions published in the public download catalog. Use the search box together with All, Updates, or Not installed to narrow the list; the filter counts always describe the complete loaded catalog. Each row separates its installed and available versions and shows whether an update is available, the module is up to date, it is not installed, or its package is unavailable.

A license can unlock a product that is already in the catalog, but it cannot add other modules to the list. Local custom modules, including those created with Module Builder, are not shown merely because they are installed. Catalog products such as Module Builder remain visible when their package requires a license. The installed version is read from the extension's base module file.

- Public free extensions have an Install or Update button without requiring a license key.
- The Install or Update button appears only for a module without an installed version or when a newer version is available. It stays hidden when the same or a newer version is already installed.
- Paid extensions require an active license that includes the particular module. A Membership license does not automatically unlock every extension.
- When a catalog package cannot be downloaded, its row remains visible with a download-unavailable message.

Select Check for updates in the System section to refresh licenses, the system version and the catalog after new extensions or versions are published. The button appears when Defalto is up to date and the last check is older than one minute. Selecting Install or Update first downloads and validates the package without changing application files. The confirmation dialog shows customer-facing changes for up to the latest three packaged versions. Review them and select Install or Update in the dialog to continue. During installation, the extension name, target version, spinner, status, and progress indicator stay visible while the detailed technical log remains available in a collapsed section. Installer refreshes package availability before starting the installation.

## Licenses and maintenance

Add each license key in the Licenses section. Multiple product licenses can coexist. License keys supplied during initial installation are handled the same way: each distinct key must pass complete server validation before a new license is stored. Membership rows show active CRM accounts against the purchased user limit, including available seats or the exact excess; product-specific licenses show that user usage does not apply. License keys stay masked until an administrator uses Manage to show or copy one. The same menu contains reactivation and confirmed removal.

Use Check license to refresh status and expiration information. Select the license status (Active, Expired, Verification unavailable or Inactive) to open details showing the last successful check, errors, and included modules. Secure license communication uses the required PHP cURL extension. If the license server is temporarily unavailable, a previously verified license keeps its last valid state while the connection error remains visible in its details. A new or already inactive license does not become active without successful verification. Correct any reported issue and reactivate the license when required, such as after an account, site, or subscription change.

If activation fails, the license dialog keeps your entered key and shows the error with numbered steps tailored to the cause. The most relevant check is expanded first; select another step to read its details. A connection check may take up to 75 seconds while the license server waits for a slow store response. Follow the steps for occupied activations, an invalid or expired key, user limits, connection problems, or incomplete license data, then retry activation. Contact support only if the recommended checks do not resolve the problem. Include the product, system URL, Installer version, error, and checks already performed; do not include the full license key.

The System section shows the installed Defalto version, update state, Membership state, and the time of the last check. Version information remains visible without Membership. One-click system updates require active Membership; without it, the Download button opens the public download page. Before a system update starts, you must confirm that you have backed up the database and the source folder and have checked the system requirements. The update cannot be reverted.

Installed extensions have Settings links for requirements, license details, and uninstalling. Uninstalling a custom extension removes its module data and files after confirmation, so make a backup first.

Extension packages use the Defalto application structure. Installable files are limited to the `modules`, `layouts`, and `languages` directories and must belong to the packaged module. An optional package manifest belongs at `modules/<Module>/manifest.xml`; when present, its module, version, and compatibility information are validated. Cron handlers belong inside `modules/<Module>/cron`; a package cannot install files into the application's root `cron` directory.

An extension package can include `modules/<Module>/CHANGELOG.md`. Its newest version must match the packaged module version. If a legacy package has no changelog, Installer clearly indicates that no changelog is available and still allows installation.
