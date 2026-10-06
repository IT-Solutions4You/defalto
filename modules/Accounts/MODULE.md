# Workflow defaults and migration

- `models/Install.php::registerWorkflowTasks` owns the module-provided workflow definitions, conditions, and actions; shared persistence and condition validation belong to Settings Workflows.
- `models/Install.php::migrate()` explicitly calls the inherited `migrateWorkflowConditions()` before its other data migrations. It regenerates uniquely matched legacy conditions from module defaults and preserves modern customizations, actions, status, scheduling, and creation metadata.
- Validate with PHP lint and manual checks of migration idempotence and module/name matching. Do not infer system ownership from workflow name alone.
