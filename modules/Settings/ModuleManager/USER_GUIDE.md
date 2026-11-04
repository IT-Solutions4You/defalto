# Module Manager

Module Manager (Settings) lets an administrator enable or disable installed modules and install or update a custom module from a ZIP file. Each module row has a checkbox to enable or disable it and, for modules with their own settings, a **Settings** menu.

## Install from a ZIP file

Open Module Manager, choose **Import Module from Zip**, accept the disclaimer, and select the package. The confirmation screen shows the detected module, package version, the required Defalto version (if the package states one), and up to the latest three customer-facing changelog versions read from the ZIP. Module Manager chooses **Import Now** or **Update Now** from the current module state; the module identity is read from the package contents, not from the file name.

The ZIP may contain only:

- `modules/<Module>` and optional `modules/Settings/<Module>` files;
- module resources below `layouts/<Layout>/modules/<Module>` or its Settings path;
- module language files below `languages/<Locale>`.

The structure may be at the ZIP root or inside one wrapper directory. The package must include `modules/<Module>/<Module>.php` with a `moduleVersion`, and `modules/<Module>/models/Install.php`. An optional `modules/<Module>/manifest.xml` is used when present; its module name and version must match the module file. Cron handlers belong below `modules/<Module>/cron`; do not add a root `cron` directory.

Add customer-facing release notes at `modules/<Module>/CHANGELOG.md`: newest release first, using `## <version> - YYYY-MM-DD` followed by plain `- <change>` bullet points. The newest version must match the package version. Packages without this file remain installable, but the confirmation screen shows that release notes are unavailable.

Packages using legacy `templates`, `settings`, `images`, or root `cron` directories are rejected. An update package must have a newer version than the installed module.

Files are validated before installation. If installation fails, Module Manager restores changed files where possible and shows the original application error in a dialog. If you can correct the reported cause, close the dialog, fix it, and retry. If the package is incorrect or the problem persists, send the exact error to the package publisher. The dialog also lets you import another package.
