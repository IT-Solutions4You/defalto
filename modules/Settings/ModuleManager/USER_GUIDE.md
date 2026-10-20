# Module Manager

Module Manager lets an administrator enable or disable installed modules and install or update a custom module from a ZIP file.

## Install from a ZIP file

Open Module Manager, choose **Install from ZIP**, accept the warning, and select the package. The confirmation screen shows the detected module and package version. Module Manager chooses installation or update from the current module state; package identity is not taken from the file name.

The ZIP must follow the Defalto directory structure. It may contain only:

- `modules/<Module>` and optional `modules/Settings/<Module>` files;
- module resources below `layouts/<Layout>/modules/<Module>` or its Settings path;
- module language files below `languages/<Locale>`.

Cron handlers belong below `modules/<Module>/cron`. Do not add a root `cron` directory. The package must include `modules/<Module>/<Module>.php`, a `moduleVersion`, and `modules/<Module>/models/Install.php`. An optional `modules/<Module>/manifest.xml` is used when present; its module name and version must match the module file. The structure may be at the ZIP root or inside one wrapper directory.

Packages using legacy `templates`, `settings`, `images`, or root `cron` directories are rejected. An update package must have a newer version than the installed module.

Module files are staged and validated before installation. If installation fails, Module Manager restores changed files where possible and displays the original application error in a dialog instead of replacing it with a generic failure message. If you can correct the reported cause, close the dialog, fix the problem, and retry the installation. If the package is incorrect or the problem persists, send the exact error to the package publisher. The dialog also offers an option to import another package.
