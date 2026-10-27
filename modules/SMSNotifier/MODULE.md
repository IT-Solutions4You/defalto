# SMS Notifier

- `callbacks/ClickATellNew.php` is the public ClickATell delivery-status callback entry point. It changes to the application root, loads the standard configuration and Composer bootstrap, verifies that SMSNotifier is active, and delegates validation and status persistence to the ClickATell provider model.
- `providers/ClickATellNew.php` owns callback URL generation (`.../callbacks/ClickATellNew.php?vtsecret=<secret>`) and request validation: the `vtsecret` parameter must match the parameters of the active ClickATellNew provider (`vtiger_smsnotifier_servers`). `models/Provider.php` and the provider implementation own provider loading; the callback must not duplicate provider credentials or status rules.
- SMS messages are stored in `vtiger_smsnotifier`; provider configuration is stored in `vtiger_smsnotifier_servers`; delivery state is stored in `vtiger_smsnotifier_status`. `models/Install.php` owns these schemas and module links.
- The provider configuration UI is in `modules/Settings/SMSNotifier`. Workflows send SMS through the `VTSMSTask` task (`workflow/VTSMSTask.php`).
- `SMSNotifier::$moduleVersion` is the install/update version.
- Validate callback changes with `php -l modules/SMSNotifier/callbacks/ClickATellNew.php`, lint the module class after a version change, and submit a valid ClickATell delivery callback for a known message ID.
