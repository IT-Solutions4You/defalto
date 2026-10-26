# PBX Manager

- `callbacks/PBXManager.php` is the public PBX callback entry point. It changes to the application root, loads the standard configuration and Composer bootstrap, then delegates authenticated requests to `PBXManager_PBXManager_Controller`.
- PBX request validation is owned by the configured connector. The callback compares `vtigersignature` with the connector secret before processing call events.
- Connector selection and call-event persistence remain owned by `controllers/PBXManager.php`, the connector implementations, and the PBXManager record models. Do not duplicate those rules in the callback entry point.
- `models/Install.php` owns PBXManager fields and the `vtiger_pbxmanager`, `vtiger_pbxmanagercf`, `vtiger_pbxmanager_gateway`, and `vtiger_pbxmanager_phonelookup` schemas.
- PBXManager inherits the base CRM module version because it has no module-local version override.
- Validate callback changes with `php -l modules/PBXManager/callbacks/PBXManager.php` and a signed callback request against a configured PBX connector.
