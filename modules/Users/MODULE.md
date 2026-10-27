# Users ownership

- `Users` owns user records in `vtiger_users`; `Users_Install_Model` owns field and schema setup, and `Users::$moduleVersion` is the install/update version.
- `Users_Privileges_Model::recalculateSharingRules()` rebuilds the privilege and sharing files of every active user. The global `RecalculateSharingRules()` in `include/utils/UserInfoUtil.php` is only a compatibility facade.
- `Users_Privileges_Model::getPermission()` owns current-user module, action, record ownership and sharing evaluation; `isPermitted()` is its boolean form and the global `isPermitted()` is a compatibility facade. An action permission value of `0` must continue into the record-level ownership and sharing checks; only a missing action entry may take the module-level early-allow path.

## Persistence encoding

- The shared raw/encoded persistence contract lives in `modules/Core/MODULE.md`. `Users::retrieve_entity_info()` keeps the encoded values runtime callers expect and registers raw database values via `TrackableObject::setRetrievedValue()`; `Users::insertIntoEntityTable()` restores a raw value through `getValueForSave()` only when its field is unchanged.
- Both calls are guarded by `method_exists()`, so Users stays loadable when only the module files are updated and the matching `data/CRMEntity.php` is not deployed yet. In that state it falls back to the legacy assignment and save flow, without raw-value preservation.

## Self-service saves and signature

- `Users_Field_Model::isEditable()` is the server-side authorization boundary for non-administrator saves. `actions/Save.php` and `actions/SaveAjax.php` skip fields that are not editable, protecting the login name, administrator flag, role, profile, status and hidden security fields from crafted requests while preferences remain editable. `is_admin` is forced off for non-administrators.
- The signature field is installed as uitype 21, but `layouts/d1/modules/Users/resources/Edit.js::registerSignatureEvent()` attaches CKEditor (full-page mode, from `Vtiger/resources/CkEditor.js`). `Save.php` and `SaveAjax.php` purify signature HTML before persistence; do not replace that with content-format detection.
- `Users_Field_Model::$htmlFields` contains only `signature`. The inherited `isHtmlField()` makes `Vtiger_Text_UIType::getDisplayValue()` return the stored HTML unchanged, so workflow substitution (`VTSimpleTemplate`) keeps the full value including wrappers, styles and breaks. `ITS4YouEmails_Record_Model::getSignature()` separately returns the decoded signature for automatic appending to emails.

## Validation

- Lint changed PHP. Manually check the encoding fallback, editable-field rejection for a non-admin, signature rendering, and workflow HTML preservation (final spacing in a delivered workflow email on a configured instance).
