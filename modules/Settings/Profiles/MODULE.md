# Profiles

## Ownership and runtime

- `Settings_Profiles_Record_Model` assembles, displays and persists profile permissions: global view/edit-all (`vtiger_profile2globalpermissions`), module access (`vtiger_profile2tab`), standard and utility actions (`vtiger_profile2standardpermissions`, `vtiger_profile2utility`) and field access (`vtiger_profile2field`).
- Stored module access in `vtiger_profile2tab` is authoritative. `getModulePermissions()` defaults a module without a row to not permitted, and the detail and edit templates read it through `hasModulePermission()`; the UI must never present missing persisted access as enabled. Field visibility and read-only flags default to permitted when no row exists.
- `save()` rewrites the profile's permissions and then calls `recalculate()`, which regenerates the privilege file of every user of the profile. Inactive modules are saved as permitted with full action and field access.
- `delete($transferToRecord)` removes the profile's permission rows; a role that would be left without a profile is moved to the transfer profile.
- Installation and migration backfills are owned by `Install_Utils_Model::initProfileModulePermissions()` (used by `InitSchema` and migration `20261016200000`, which then calls `Users_Privileges_Model::recalculateSharingRules()`). It inserts only missing `vtiger_profile2tab` and standard-permission rows as permitted and must preserve every existing value.

## Validation

- Lint touched PHP files.
- Verify a profile with a missing `vtiger_profile2tab` row renders the module as disabled.
- Verify the backfill is idempotent and that sharing privileges are regenerated after migration.
