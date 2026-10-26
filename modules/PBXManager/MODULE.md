# PBX Manager

- `callbacks/PBXManager.php` is the public PBX callback entry point. It changes to the application root, loads the standard configuration and Composer bootstrap, and compares the request's `vtigersignature` with the secret key from the connector (`getVtigerSecretKey()`). A mismatch gets the XML authentication-failure response; a match is delegated to `PBXManager_PBXManager_Controller`.
- There is a single connector, `PBXManager_PBXManager_Connector` (`connectors/PBXManager.php`, an Asterisk connector), returned by `PBXManager_Server_Model::getConnector()`. There is no connector selection.
- `controllers/PBXManager.php` dispatches on the `callstatus` request value (`StartApp`, `DialAnswer`, `Record`, `EndCall`, `Hangup`) to the matching process method, which persists call records through the PBXManager record models. Do not duplicate those rules in the callback entry point.
- Server settings (`webappurl`, `outboundcontext`, `outboundtrunk`, `vtigersecretkey`) are stored in `vtiger_pbxmanager_gateway` and edited in `Settings/PBXManager`. Users' extensions come from the `phone_crm_extension` user field.
- `PBXManagerHandler` keeps `vtiger_pbxmanager_phonelookup` in sync with phone fields of saved, deleted and restored records.
- `models/Install.php` owns PBXManager fields and the `vtiger_pbxmanager`, `vtiger_pbxmanagercf`, `vtiger_pbxmanager_gateway` and `vtiger_pbxmanager_phonelookup` schemas.
- PBXManager inherits the base CRM module version because it has no module-local version override.
- Validate callback changes with `php -l modules/PBXManager/callbacks/PBXManager.php` and a request signed with the configured secret key.
