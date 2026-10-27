# Workflow defaults and migration

- `models/Install.php::registerWorkflowTasks` owns the module-provided workflow definitions, conditions, and actions; shared persistence and condition validation belong to Settings Workflows. The active `Calculate or Update forecast amount` workflow calculates the record-currency `forecast_amount` as `amount * probability / 100` after every save, so no exchange-rate conversion or duplicate calculation belongs in `Potentials::save()`.
- `models/Install.php::migrate()` calls the inherited `migrateWorkflowConditions()` before its other data migrations (matching rules: `modules/Core/MODULE.md`, `modules/Settings/Workflows/MODULE.md`). It then renames the legacy `potentialtype` column reference to `opportunity_type` in custom views and moves `vtiger_contpotentialrel` rows into Core relations (dropping the table).
- Installation and migration must not backfill or recalculate `forecast_amount` on existing records. Runtime recalculation belongs exclusively to the workflow and occurs when an opportunity is saved.
- `registerSummaryWidgets` adds the Related Contacts and Related Products summary widgets; label translation fallback is owned by `Vtiger_Link_Model::getTranslatedLabel()` (see `modules/Vtiger/MODULE.md`). The related module still controls widget content and permissions.
- Validate with PHP lint and manual checks of migration idempotence and module/name matching. Do not infer system ownership from workflow name alone.

# Related actions

- Detail view links (`models/DetailView.php`) create Quote, Sales Order and Invoice through the `Record` model create URLs, and `Create Project` opens `ConvertPotential`; the Project action is hidden once `vtiger_potential.converted` is 1. Default Opportunity-to-Project field mapping is registered by `updateMapping()` in `vtiger_convertpotentialmapping`.
- The `Kanban View` list link is registered in `registerCustomLinks`.
