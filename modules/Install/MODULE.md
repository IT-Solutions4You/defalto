# Installation and data migration

- `Install_Utils_Model::installTables()` dispatches schema creation to module install models. `migrateTablesData()` is the module data migration dispatcher invoked by `Migration_Index_View::migrateData()`.
- The dispatcher invokes only the module-specific `migrate()`. Modules owning workflow defaults explicitly call the inherited `migrateWorkflowConditions()` from that method; workflow regeneration is not a global dispatcher hook. Ordinary workflow installation skips existing definitions.
- Initial license activation remains delegated by `saveInstallerLicenses()` to Installer-owned activation; Install must not persist license state directly.
- Validate dispatcher changes with PHP lint and manual checks of module migration ordering and idempotence; workflow migration must preserve modern conditions, task definitions, status, scheduling, and creation metadata.
