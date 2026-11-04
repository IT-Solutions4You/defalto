# Workflow defaults and migration

- `models/Install.php::registerWorkflowTasks` owns the module-provided workflow definitions, conditions, and actions; shared persistence and condition validation belong to Settings Workflows.
- `models/Install.php::migrate()` calls the inherited `migrateWorkflowConditions()` before its other data migrations. Matching and restore rules are documented in `modules/Core/MODULE.md` and `modules/Settings/Workflows/MODULE.md`; do not duplicate them here.
- Validate with PHP lint and manual checks of migration idempotence and module/name matching. Do not infer system ownership from workflow name alone.
