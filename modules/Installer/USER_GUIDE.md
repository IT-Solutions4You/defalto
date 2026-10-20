# Installer

Installer lets administrators browse extensions, install or update available packages, manage license keys, and update Defalto.

## Browse and install extensions

The Modules list shows extensions published in the public download catalog. Licensed package information can unlock a catalog product, but it cannot add internal or local modules such as Tour or InventoryItem to the list. Local custom modules created with Module Builder are also not shown merely because they are installed. Catalog products such as Module Builder remain visible when their package requires a license. Each row shows the available version, while the installed version is read from the extension's base module file.

- Public free extensions have an Install or Update button without requiring a license key.
- The Install or Update button appears only for a module without an installed version or when a newer version is available. It stays hidden when the same or a newer version is already installed.
- Paid extensions require an active license that includes the particular module. A Membership license does not automatically unlock every extension.
- When a catalog package cannot be downloaded, its row remains visible with a download-unavailable message.

Use Search for updates to refresh the catalog after new extensions or versions are published. Before installing, complete the backup and requirements confirmations in the installation dialog. Installer refreshes package availability before starting the installation.

## Licenses and maintenance

Add each license key in the Licenses section. Multiple product licenses can coexist. Use Check license to refresh their status; the information icon shows errors and the modules included in each license. Correct any reported issue and reactivate the license when required.

If activation fails, the license dialog keeps your entered key and shows the error with numbered steps tailored to the cause. The most relevant check is expanded first; select another step to read its details. Follow the steps for occupied activations, an invalid or expired key, user limits, connection problems, or incomplete license data, then retry activation. Contact support only if the recommended checks do not resolve the problem. Include the product, system URL, Installer version, error, and checks already performed; do not include the full license key.

Installer interface messages, license statuses, update checks, and installation confirmations are localized for every language distributed with the system.

The System section shows Defalto version information without Membership. Protected one-click system updates require active Membership.

Installed extensions may provide Settings links for requirements, license details, and uninstalling. Uninstalling a custom extension removes its module data and files after confirmation, so make a backup first.

Extension packages use the Defalto application structure. Installable files are limited to the `modules`, `layouts`, and `languages` directories and must belong to the packaged module. An optional package manifest belongs at `modules/<Module>/manifest.xml`; when present, its module, version, and compatibility information are validated. Cron handlers belong inside `modules/<Module>/cron`; a package cannot install files into the application's root `cron` directory.
