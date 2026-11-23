# Installer

System release notes display each bullet point on its own line, including GitHub descriptions without a blank line between the introduction and the list.

Installer lets administrators browse extensions, install or update available packages, manage license keys, and update Defalto.
Installer follows the CRM language selected by the user, including its Settings entry and uninstall confirmation.

## Browse and install extensions

When Installer is current, its available version and up-to-date status appear together on one line below its name.

Hover over a disabled system or extension installation button, or focus its tooltip with the keyboard, to see why installation is blocked. The message explains that Installer must be updated first or that its latest version could not be verified.

The Installer update section follows the System section design: a status icon and installed version on the left, with the available version below it and actions on the right. When installation is blocked, a compact red badge beside the Installer name shows that an update is available or verification is unavailable. Hover over the badge to read the full prerequisite message; screen readers receive the same explanation. A green check means current, an up arrow indicates an update, and a warning triangle means verification is unavailable. Actions move below the version information on smaller screens.

Extensions can use versions such as `1.1` or `1.1.1`. Installer displays the full version and compares each part: `1.1.1` is newer than `1.1`, and `1.1.10` is newer than `1.1.9`. Catalog downloads and uploaded ZIP packages support both formats. Package publishers must use the same version in the module declaration, catalog, optional manifest and latest changelog heading.

The Installer section above System shows the installed and latest available Installer versions. Update Installer first when prompted. Until Installer is current, system updates and extension installation, update and reinstall actions are blocked, including ZIP packages uploaded through Module Manager. License management remains available. Use Check for updates to retry if the latest version cannot be verified. A local version newer than the public release is also accepted.

Installer updates remain available while other installations are blocked. Installer cannot be downgraded below its installed or latest known public version. If the catalog is unavailable, a validated Installer ZIP at least as recent as the installed version can be uploaded through Module Manager to repair it. Close the successful installation dialog to reload the page before starting another installation. System update packages preserve Installer files and cannot replace the separately updated Installer.

The Modules list shows extensions published in the public download catalog. Use the search box together with All, Updates, or Not installed to narrow the list; the filter counts always describe the complete loaded catalog. Each row separates its installed and available versions and shows whether an update is available, the module is up to date, it is not installed, or its package is unavailable.

A license can unlock a product that is already in the catalog, but it cannot add other modules to the list. Local custom modules, including those created with Module Builder, are not shown merely because they are installed. Catalog products such as Module Builder remain visible when their package requires a license. The installed version is read from the extension's base module file.

- Public free extensions have an Install or Update button without requiring a license key.
- Every downloadable module package has an action: Install for a new module, Update for a newer version, or Reinstall for an equal or older version. The Updates filter still lists only newer versions.
- Paid extensions require an active license that includes the particular module. A Membership license does not automatically unlock every extension.
- When a catalog package cannot be downloaded, its row remains visible with a download-unavailable message.

Select Check for updates in the System section to refresh licenses, the system version and the catalog after new extensions or versions are published. The button appears when Defalto is up to date and the last check is older than one minute. Selecting Install, Update, or Reinstall first downloads and validates the package without changing application files. The confirmation dialog shows customer-facing changes for up to the latest three packaged versions. Review them and confirm the chosen action in the dialog to continue. During installation, the extension name, target version, spinner, status, and progress indicator stay visible while the detailed technical log remains available in a collapsed section. Installer refreshes package availability before starting the installation.

## Licenses and maintenance

Before confirming an extension installation, review the installed and available versions shown above the changelog. A newer version has an informational notice. An equal version warns that the files will be reinstalled and the module update will run again. An older version warns that existing data may be incompatible; back up the database and module files before continuing. These notices allow you to continue. Reinstalling an older package does not restore a previous database backup. Package validation, minimum Defalto requirements, and license requirements still apply. The same notices appear for uploaded ZIP packages in Module Manager.

Add each license key in the Licenses section. Multiple product licenses can coexist. License keys supplied during initial installation are handled the same way: each distinct key must pass complete server validation before a new license is stored. Membership rows show active CRM accounts against the purchased user limit, including available seats or the exact excess; product-specific licenses show that user usage does not apply. License keys stay masked until an administrator uses Manage to show or copy one. The same menu contains reactivation and confirmed removal.

Use Check license to refresh status and expiration information. Select the license status (Active, Expired, Verification unavailable or Inactive) to open details showing the last successful check, errors, and included modules. Secure license communication uses the required PHP cURL extension. If the license server is temporarily unavailable, a previously verified license keeps its last valid state while the connection error remains visible in its details. A new or already inactive license does not become active without successful verification. Correct any reported issue and reactivate the license when required, such as after an account, site, or subscription change.

If activation fails, the license dialog keeps your entered key and shows the error with numbered steps tailored to the cause. The most relevant check is expanded first; select another step to read its details. A connection check may take up to 75 seconds while the license server waits for a slow store response. Follow the steps for occupied activations, an invalid or expired key, user limits, connection problems, or incomplete license data, then retry activation. Contact support only if the recommended checks do not resolve the problem. Include the product, system URL, Installer version, error, and checks already performed; do not include the full license key.

The System section shows the installed Defalto version, update state, Membership state, and the time of the last check. Version information remains visible without Membership. One-click system updates require active Membership; without it, the Download button opens the public download page. Selecting Update opens a confirmation dialog with the selected version's release notes from the official Defalto GitHub repository. Category headings and lists follow the published description, in its original language. Only the matching release is shown; another version's notes are never substituted. If the release notes are missing or GitHub is temporarily unavailable, the dialog explains this and you can still continue. Successful lookups are cached for one hour and missing or unavailable results for five minutes; Check for updates clears this cache. Before a system update starts, you must confirm that you have backed up the database and the source folder and have checked the system requirements. The update cannot be reverted.

Installed extensions have Settings links for requirements, license details, and uninstalling. Uninstalling a custom extension removes its module data and files after confirmation, so make a backup first.

Extension packages use the Defalto application structure. Installable files are limited to the `modules`, `layouts`, and `languages` directories and must belong to the packaged module. An optional package manifest belongs at `modules/<Module>/manifest.xml`; when present, its module, version, and compatibility information are validated. Cron handlers belong inside `modules/<Module>/cron`; a package cannot install files into the application's root `cron` directory.

An extension package can include `modules/<Module>/CHANGELOG.md`. Its newest version must match the packaged module version. If a legacy package has no changelog, Installer clearly indicates that no changelog is available and still allows installation.

If an extension installation fails, the progress dialog shows the specific package error, such as the path of a file that is not writable. The error appears first in a red alert. Expand the technical log below it to inspect the installation steps, including when the server returns an HTTP error. Correct the reported issue before retrying.

Installation logs use a Markdown-style list with one step per item. Errors are emphasized in red; server output is displayed as text.

System updates install the dependency versions supplied with the release, replacing the previous dependency lock file together with the dependency requirements. If an update fails, both files are restored through the update rollback. If the log reports a mismatch between `composer.json` and `composer.lock`, use a corrected complete release package before retrying.
