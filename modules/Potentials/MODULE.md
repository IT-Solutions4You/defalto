# Workflow defaults and migration

- `models/Install.php::registerWorkflowTasks` owns the module-provided workflow definitions, conditions, and actions; shared persistence and condition validation belong to Settings Workflows. The active `Calculate or Update forecast amount` workflow calculates the record-currency `forecast_amount` as `amount * probability / 100` after every save, so no exchange-rate conversion or duplicate calculation belongs in `Potentials::save()`.
- `models/Install.php::migrate()` explicitly calls the inherited `migrateWorkflowConditions()` before its other data migrations. It regenerates uniquely matched legacy conditions from module defaults and preserves modern customizations, actions, status, scheduling, and creation metadata.
- Installation and migration must not backfill or recalculate `forecast_amount` on existing records. Runtime recalculation belongs exclusively to the workflow and occurs when an opportunity is saved.
- Summary widget labels registered by this module fall back to the source record module translation when the related module does not define the label. The related module still controls widget content and permissions.
- Validate with PHP lint and manual checks of migration idempotence and module/name matching. Do not infer system ownership from workflow name alone.
