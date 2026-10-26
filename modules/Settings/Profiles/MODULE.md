# Profiles

## Ownership and runtime

- `Settings_Profiles_Record_Model` assembles, displays, and persists profile permissions for modules, actions, and fields.
- Stored module access is authoritative in `vtiger_profile2tab`. A missing row is treated as not permitted; the UI must never present missing persisted access as enabled.
- Installation and migration backfills are owned by `Install_Utils_Model::initProfileModulePermissions()` and must preserve every existing permission value.

## Validation

- Lint touched PHP files.
- Verify a profile with a missing `vtiger_profile2tab` row renders the module as disabled.
- Verify the permission backfill is idempotent and regenerate user sharing privileges after migration.
