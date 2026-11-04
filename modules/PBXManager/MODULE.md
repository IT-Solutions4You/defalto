# PBX Manager

- `callbacks/PBXManager.php` is the public PBX callback entry point. It changes to the application root, loads the standard configuration and Composer bootstrap, and compares the request's `vtigersignature` with the secret key from the connector (`getVtigerSecretKey()`). A mismatch gets the XML authentication-failure response; a match is delegated to `PBXManager_PBXManager_Controller`.
- There is a single connector, `PBXManager_PBXManager_Connector` (`connectors/PBXManager.php`, an Asterisk connector), returned by `PBXManager_Server_Model::getConnector()`. There is no connector selection.
- `controllers/PBXManager.php` dispatches on the `callstatus` request value (`StartApp`, `DialAnswer`, `Record`, `EndCall`, `Hangup`) to the matching process method, which persists call records through the PBXManager record models. Do not duplicate those rules in the callback entry point.
- Server settings (`webappurl`, `outboundcontext`, `outboundtrunk`, `vtigersecretkey`) are stored in `vtiger_pbxmanager_gateway` and edited in `Settings/PBXManager`. Users' extensions come from the `phone_crm_extension` user field.
- `PBXManagerHandler` keeps `vtiger_pbxmanager_phonelookup` in sync with phone fields of saved, deleted and restored records.
- `models/Install.php` owns PBXManager fields, schemas, the global incoming-call header script link, and phone lookup event handlers. Keep link and handler registration in its `registerCustomLinks` and `registerEventHandler` declarations so install, update, disable, enable, and uninstall use the shared Core lifecycle.
- Caller matching in `PBXManager_Record_Model` must bind normalized phone numbers as query parameters for both CRM phone lookup and user extension lookup.
- PBX gateway settings and the generated callback secret are admin-only. Settings views inherit this rule from `Settings_Vtiger_Index_View`; any PBX settings controller that does not inherit it must enforce the same admin check explicitly.
- PBXManager inherits the base CRM module version because it has no module-local version override.
- Validate callback changes with `php -l modules/PBXManager/callbacks/PBXManager.php` and a request signed with the configured secret key.
