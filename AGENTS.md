# AGENTS.md

## Repository Rules

- Trace the runtime owner before changing code. Prefer the existing module, Core, Vtiger, or Installer mechanism over a local one-off fix.
- Before changing a module or installable extension, read the nearest `MODULE.md` under its module path (for example, `modules/<Module>/MODULE.md` or `modules/Settings/<Module>/MODULE.md`) and analyze the module's runtime owner, entry points, persistent data, external contracts, UI assets, and install lifecycle.
- Treat the module's `MODULE.md` as durable extension memory. Create or update it in the same task whenever verified, reusable module knowledge is learned, and update every affected module file for cross-module work. Keep it concise and in English; record ownership boundaries, invariants, data flows, integration points, and validation commands, but never task history, guesses, generated inventories, environment-specific state, credentials, license keys, or other secrets. Remove or correct stale guidance when the implementation changes.
- Keep Installer license checks inside the Installer-owned manual update and scheduled license-check flows. Do not add license hooks to global WebUI startup/login processing or to Users save, deactivate, or delete events; preserve the existing system and Users lifecycle behavior.
- Keep module PHP code under `modules/<Module>/{actions,models,views,helpers,handlers,dashboards,uitypes}`.
- Keep module frontend assets under `layouts/d1/modules/<Module>/resources`.
- Put reusable system behavior into `modules/Core` or an existing shared Vtiger/Core helper.
- When adding a new custom framework component that could belong to either Vtiger or Core, create it under `modules/Core` with a `Core_...` class name. Do not add new custom framework components under `modules/Vtiger`.
- Use central language files under `languages/<locale>/<Module>.php` for labels. Do not change technical identifiers only to alter display text.
- Keep all instructions and example text in `AGENTS.md` and `MODULE.md` in English. Translate user-facing confirmations at response time to the language used by the user in the current request.
- Whenever you program a functional change, bump the application/resource patch version in `version.php` in the same change.
- Do not bump the version for documentation-only, comments-only, analysis-only, or generated-map-only changes.

## Installer Licensing Ownership

- Keep the complete membership license implementation owned by `modules/Installer`. Do not duplicate license state, API requests, user counting, activation rules, or entitlement checks in Core, Vtiger, Users, or another module.
- Preserve support for multiple independently validated Installer licenses. A valid membership license may coexist with product-specific extension licenses such as AI or Google synchronization; do not collapse the `df_licenses` store or Installer UI back to one license.
- Persist a newly entered license only after the Installer-owned API returns the complete valid contract (`success === true`, `license === "valid"`, and `users_valid === true`). Keep existing stored keys on later validation failures so their errors can be displayed and retried.
- Preserve multi-license activation during initial installation in `Install_Utils_Model::saveInstallerLicenses()`. Read the `licenses` array and the legacy singular `license` value, normalize and deduplicate keys, and delegate every activation to `Installer_License_Model::activate()`; do not write `df_licenses` directly from the Install module.
- Keep the license contract and local license state in `modules/Installer/models/License.php`, HTTP communication in `modules/Installer/models/Api.php`, and the current licensed-user count in `modules/Installer/models/UserCount.php`.
- License activation is allowed only through `modules/Installer/actions/IndexAjax.php::licenseSave()`, which delegates to `Installer_License_Model::activate()`.
- Explicit license deactivation is allowed only through `modules/Installer/actions/IndexAjax.php::licenseDelete()`, which delegates to `Installer_License_Model::deactivate()` before deleting local state when appropriate.
- Forced manual checks are allowed in the Installer-owned `modules/Installer/views/IndexAjax.php::licenseCheckProgress()` and `updateInformation()` flows through `Installer_License_Model::updateAll()`.
- A forced license check is allowed immediately before a protected system update or extension installation in `modules/Installer/views/IndexAjax.php::systemProgress()` and `extensionProgress()`. These checks must remain part of the requested Installer operation and must not become global lifecycle hooks.
- The Installer page may perform only the existing debounced non-forced check in `modules/Installer/views/Index.php::installer()` by calling `check(false)`. Do not change this page load to an unconditional or forced request.
- The scheduled license check is owned only by `modules/Installer/cron/UpdateLicenses.php`, registered as `InstallerLicenses` by `modules/Installer/models/Install.php`. Keep its daily interval unless a user explicitly requests a different schedule.
- `Installer_Api_Model` may retrieve public system version status through the existing `/system/v1` flow when no active Membership license exists. In that state retain only the version and label, and strip download URLs, download folders, checksums, and all other protected package metadata. Full system package metadata and extension metadata remain protected and may reuse the debounced license check; do not add additional API requests around the same retrieval flow.
- Never add membership license checks to `includes/main/WebUI.php`, global application startup or login, Users save/deactivate/delete events, event handlers, or unrelated cron tasks. Do not recreate `Installer_UserLicense_Handler` or another Users lifecycle handler.
- Preserve the membership purchase routes `Membership` and `MembershipOrder` in `modules/Core/views/Redirect.php`. In every Defalto system-update row, when no active Membership license exists, render the purchase prompt through `Installer_SystemInstall_Model::getBranding()` in the Actions column directly below the download action. Preserve the original System block layout and do not move the prompt into a separate callout row.

## Versioning

- When making a runtime application change in PHP, JavaScript, Smarty templates, CSS/LESS, install/schema logic, or database-affecting behavior, bump the patch identifier in `version.php` in the same change.
- If the change affects rendered or cache-busted browser assets, also bump the application/display patch version in `version.php` so `vresource_url()` invalidates cached resources.
- Do not bump `version.php` for documentation-only changes, comments-only changes, tests-only changes, generated-map-only changes, or analysis notes that do not alter runtime behavior.

### Module Versions

- Treat `public string $moduleVersion` in `modules/<Module>/<Module>.php` as the install and update version of that installable module; the install flow persists it to `vtiger_tab.version`.
- For functional changes owned by an installable module, increment the final numeric component of its `$moduleVersion` exactly once relative to the last committed version in `HEAD`. This includes module PHP, module-specific layout assets, language strings, install or schema behavior, and Settings code owned by that module.
- Before changing a module version, compare the working-tree value with `HEAD`, including any inherited version when the module has no explicit override. Increment only when the current version is already committed. If the working tree or index already contains an uncommitted version increment, keep that version for subsequent changes; staging does not count as committing. Do not increment again for another task, conversation turn, fix, or review before that version is committed.
- When reviewing accumulated uncommitted module changes, consolidate accidental repeated increments to one increment above the committed baseline. This rule applies to module versions; application/resource versioning in `version.php` follows its separate rules above.
- When one commit functionally changes multiple installable modules, bump every affected module independently and exactly once, regardless of how many files changed in each module.
- If a changed path has no root module version, trace its runtime and installer owner and bump the owning installable module. Do not introduce a new `$moduleVersion` only to satisfy this rule.
- A shared Core or Vtiger change does not require version bumps for every downstream module. Bump only installable modules whose owned runtime code or assets changed, in addition to the applicable `version.php` bump.
- Do not bump module versions for documentation-only, comments-only, tests-only, or generated-map-only changes.

## Control Flow Spacing

- In PHP and JavaScript, separate `if`, `elseif` (`else if` in JavaScript), `else`, `foreach`, and `while` blocks from adjacent statements with one blank line before and after each block. This means vertical spacing, not additional spaces inside parentheses.
- Separate conditional branches too: place the preceding closing brace on its own line, then one blank line, then `else {`, `elseif (...) {`, or `else if (...) {`. Do not keep `} else {` or `} elseif (...) {` on one line. Keep the trailing `while` attached to a `do` block.
- Do not add a blank line immediately after an enclosing opening brace or immediately before its closing brace solely for this rule. A block at the start or end of a method or another block needs spacing only on the side with an adjacent statement.
- Keep a comment documenting a block directly attached to it; place the separating blank line before that comment. Preserve existing blank lines without multiplying them.
- Apply this style to new and touched code. Do not reformat unrelated files or change runtime behavior as part of a spacing cleanup. Whitespace-only changes do not require application or module version bumps.

## Function Naming

- When creating new reusable PHP or JavaScript methods, choose a clear action prefix from the existing intent families before inventing another verb.
- Use `get` and `set` for simple value access or assignment without persistence.
- Use `is` and `has` for boolean state checks.
- Use `exists` only when the method specifically checks storage-backed existence and the surrounding code already follows that wording.
- Use `get` or `retrieve` for lookup/query methods that may return records, models, or collections without changing state.
- Use `retrieve` for hydrating state from an external source; use `load` only when the surrounding class already uses that wording or the method loads local object/UI state.
- Use `save`, `create`, `update`, and `delete` for persistence operations that write application data.
- Use `add` and `remove` for changing collections, relations, UI lists, or module setup; prefer `create` when a persisted record or table is created.
- Use `clear` for resetting a value, cache, or collection.
- Use `read`, `write`, and `append` for file, stream, request/response, or low-level IO style operations.
- Use `register` for event, listener, link, script, or handler setup.
- Use `trigger` and `handle` for event-style flows where one method emits or delegates and the other reacts.
- Use `process` for request/action/view entry points.
- Use `requires`, `check`, and `validate` for permission, request, requirement, and data checks.
- Use `show` and `hide` for UI visibility methods.
- Use `init` and `initialize` for setup/bootstrap methods; keep existing class or framework conventions when choosing between them.
- Use `pre` and `post` prefixes for lifecycle hooks around an existing operation, such as `preProcess` and `postProcess`.
- Use `install` and `migrate` for module installation and data/schema migration flows.
- Use `convert` and `transform` for value or structure conversion without persistence side effects.
- Use `import` and `export` for package, file, or data transfer boundaries.
- Use `send` for email, notification, request, or message dispatch.
- For database-oriented reads, prefer the repo's established `retrieve`, `get`, and `fetch` wording before introducing a rarer verb.
- Use `retrieve` when the method hydrates model/helper state or assembles data from storage; use `fetch` for direct row/result loading from the database, for example `fetchInvoiceRows()`.
- Use `get` when the value is expected to exist or is a normal model accessor, for example `getInvoiceNumber()`.
- Use `load` only for legacy/local patterns that already use that wording or when the method loads and prepares object/UI state, for example `loadInvoiceDetails()`.
- Do not introduce `find`, `findById`, or `findAll` as generic repository naming in this codebase; prefer `retrieve`, `get`, or `fetch` according to the method behavior.
- Use `select` only in technical DB helper layers for direct SELECT wrappers, for example `selectInvoices()`; prefer `retrieve`, `get`, or `fetch` in module/domain models.
- Use `count` only for aggregate record counts, for example `countInvoices()`.
- When iterating database result sets, use `while ($row = $db->fetchByAssoc($result))` or the local `$adb->fetchByAssoc(...)` variant. Do not add new `fetch_array()` loops.
- Use `query_result()` or `query_result_rowdata()` for a single known row/value, not for new multi-row loops. If several rows are expected, loop with `while` and fetch rows directly.
- For database writes, use `insert` for inserting a new row, `create` for application-level record creation, `update` for updating an existing record, `save` for insert-or-update behavior, and `delete` for permanent deletion.
- Use `remove` for deleting a relation or detaching an item rather than deleting the underlying record, for example `removeProductRelation()`.
- Use `exists` only for storage-backed existence checks, preferably as an object-first method name such as `invoiceExists()`, and only when it reads naturally next to the surrounding code.
- Prefer the prefix that matches the side effect. For example, do not name a database write `set...`, do not name a boolean check `get...`, and do not name a collection query `retrieve...` unless it hydrates local object state.

## JavaScript / Scripts Checks

- Combine adjacent JavaScript variable declarations in the same lexical scope into one comma-separated declaration. Use one `const` when every binding is constant; if any binding in the adjacent group needs reassignment, declare the whole group with one `let`. Format longer initialized groups on continuation lines under the first declaration.
- Do not merge declarations across executable statements, conditions, early returns, callbacks, loops, or different scopes merely to reduce the declaration count; preserving initialization order and scope takes precedence over grouping.
- Avoid form control names that shadow native `HTMLFormElement` properties or methods, especially `method`, `action`, `submit`, `reset`, `elements`, `length`, `name`, `target`, `encoding`, and `enctype`. Audit third-party form and CSRF integrations when adding or renaming named controls.
- Prefer updating the affected UI state after AJAX operations over reloading or navigating the entire page. On success, update, add, or remove the relevant elements and show a confirmation notification. Use a full-page reload only when the state cannot be synchronized safely in place, and document the reason in the code.
- New module scripts should live in `layouts/d1/modules/<Module>/resources/*.js`.
- Load page-specific scripts from the owning view/controller through `getHeaderScripts(Vtiger_Request $request)` and `$this->checkAndConvertJsScripts($jsFileNames)`.
- Use loader names such as `modules.<Module>.resources.Edit`, `modules.<Module>.resources.Detail`, or `modules.<Module>.resources.<Script>` when the file is inside the layout module resources folder.
- Use `~layouts/...` or another explicit resolved path only for library files, legacy cross-layout files, or files outside the standard module resources namespace.
- Always merge with parent scripts in the same order as the local flow expects. If a module extends another module's UI behavior, load the parent/base script before the module script.
- For overlay edit/detail/quick-preview screens, add scripts through the matching overlay method such as `getOverlayHeaderScripts()` or `getQuickPreviewHeaderScripts()`, not only through the normal full-page `getHeaderScripts()`.
- Do not add scripts directly in Smarty templates when the controller already has a script-loading path. Templates should consume the `SCRIPTS` assigned by the view.
- Use `HEADERSCRIPT` links only for truly global module hooks that must be registered at install/update time. Prefer `public array $registerCustomLinks` in the module install model over direct scattered `addLink()` calls.
- Avoid `HEADERSCRIPT` for one screen or one action; use that screen's view/controller script list instead.
- Keep shared base libraries in `layouts/d1/modules/Vtiger/JSResources.tpl` only when they are globally required by the application.
- A resource file should expose the expected class name `<Module>_<Script>_Js`. For example, `layouts/d1/modules/Accounts/resources/Edit.js` should provide `Accounts_Edit_Js`.
- `layouts/d1/modules/Core/resources/*.js` is the repo exception: those scripts validate as `Vtiger_*_Js`, not `Core_*_Js`.
- Prefer extending existing Vtiger/Core JS classes (`Vtiger_Edit_Js`, `Vtiger_Detail_Js`, `Vtiger_Index_Js`, etc.) instead of duplicating shared UI behavior.
- Keep reusable JS helpers as same-level controller/class methods when follow-up reuse is plausible; avoid hiding reusable behavior inside nested local functions.
- For form validation, prefer the existing validation stack: global rules in `layouts/d1/modules/Vtiger/resources/validation.js`, field metadata/data attributes in templates, and module-local JS only for module-specific rules.
- After changing `layouts/d1/modules/**/resources/*.js`, run or manually check the existing scripts validator at `index.php?module=ITS4You&mode=ScriptsValidator&only_errors=1`.
- For touched JavaScript files, run a syntax check when practical and verify that the expected class is present.

## CSS / Style Checks

- Global application styles are loaded through the current layout skin, mainly `layouts/d1/skins/base/style.css` with LESS source in `layouts/d1/skins/base/style.less`.
- Shared custom layout overrides belong in `layouts/d1/resources/custom.css` or `layouts/d1/resources/custom.less` when the behavior is not module-specific.
- Module-specific styles should stay under the owning module resource path, such as `layouts/d1/modules/<Module>/resources/*.css`, and should be loaded through the owning view/controller CSS path.
- Prefer Bootstrap utilities and existing shared skin rules before adding custom CSS for common spacing, alignment, display, and button layout.
- Do not add inline `<style>` blocks to Smarty templates when the rule can live in the skin, shared custom stylesheet, or module resource CSS.
- CSS resources are cache-busted by `vresource_url()` using `$defalto_current_version` from `version.php`.
- When changing any rendered application style file, bump the resource/application patch version in `version.php`. For example, if the version is `1.0.0`, update it to `1.0.1` in the same change.
- Do not bump the version for comments-only or generated-map-only changes that do not alter rendered CSS.
- After changing LESS source, make sure the corresponding generated CSS is updated when this repo expects committed CSS output.

## PHP / Install Structure Checks

- Let `Vtiger_Loader` autoload framework component classes whose names and paths follow the `Module_Component_Type` convention, such as `Core_DateFilter_Helper` in `modules/Core/helpers/DateFilter.php`. Do not add manual `require`, `require_once`, `include`, or `include_once` statements for these components; verify the class-to-path mapping before removing a legacy include. Standalone scripts may explicitly bootstrap files when the application autoloader is not initialized.
- Before completing a new, copied, or renamed module, compare every overridden method with the actual parent class or interface declaration. Match visibility, staticness, parameter types and defaults, reference/variadic markers, and return types; do not rely on the legacy source module's signature.
- Audit controller lifecycle overrides especially carefully, including `validateRequest()`, `checkPermission()`, `preProcess()`, `postProcess()`, `process()`, `getHeaderScripts()`, and `getHeaderCss()`. An isolated `php -l` does not detect inheritance incompatibilities when the parent class is not loaded, so also load the child with its real parent or an equivalent compatibility harness.
- Do not add a local `try/catch` in an action or view only to convert an `Exception` into `Vtiger_Response::setError()`. Let the central WebUI exception handler produce the standard action error response. Catch locally only when the code can recover, try a defined fallback, perform required cleanup, or add context before rethrowing; never swallow an exception that the framework should handle.
- Keep all runtime database reads and writes in model classes. Helpers orchestrate domain behavior and call model APIs; actions and views validate or present data and must not contain SQL or private database accessors.
- Prefer `Core_QueryGenerator_Model` for new application-level list and record query generation. Extend its structured query API when a required condition, join, grouping, or ordering feature is missing; do not locate SQL clauses or inject conditions by parsing and rewriting completed SQL strings.
- Create a dedicated model for every module-owned table and name the model after the table's domain suffix, for example `df_two_factor_backup_code` is represented by `TwoFactorAuthentication_BackupCode_Model`. Keep that table's CRUD and atomic updates in its model; for shared system tables, reuse the existing owning Core, Vtiger, Settings, or module model instead of creating a duplicate local data model.
- Make table-backed runtime models extend `Core_DatabaseData_Model`, declare their protected `$table` and `$tableId`, initialize the shared connection through `retrieveDB()`, and prefer `selectData()`, `insertData()`, `updateData()`, and `deleteData()` for ordinary CRUD. Use `getDB()` with custom SQL only when the shared CRUD API cannot express a required aggregate, join, upsert, or atomic conditional update without changing its semantics.
- Give each table-backed model a clearly named table accessor such as `getModTrackerBasicTable()` or `getBackupCodeTable()`. Implement it by returning `$this->getTable($this->table, $this->tableId)` and use an appropriate return type. This shared method returns a configured clone and initializes its database connection; it does not configure the original model in place.
- Use that named accessor for ordinary CRUD inside the owning model, for example `$table = $this->getModTrackerBasicTable();` followed by `$table->selectData(...)`, `$table->insertData(...)`, `$table->updateData(...)`, or `$table->deleteData(...)`. Reuse the returned table object within an operation instead of repeating its configuration.
- Do not add constructors that manually call `set('table', ...)` or `set('table_id', ...)`, and do not call CRUD on an unconfigured model instance. Keep `$table` and `$tableId` as the single source of table metadata. If custom SQL uses the original model's `getDB()`, initialize that original instance with `retrieveDB()` first; configuring a clone does not initialize the original.
- Callers outside the owning model should prefer its domain methods, such as `saveHistory()` or `saveFieldChange()`, over accessing its table directly. Apply this table-access pattern to new models and models being refactored without broad unrelated rewrites.
- Before adding domain methods to a `Core_DatabaseData_Model` subclass, audit inherited method names and signatures such as `save()`, `delete()`, `getId()`, and `getName()`. Use a specific domain name such as `saveTemplate()` when different parameters or behavior would make an override incompatible.
- Keep install, migration, and schema lifecycle queries in the owning install/database model. A cross-table read belongs to the model representing the primary domain result and should not leak SQL back into a helper.
- Use `Core_Install_Model` for field creation, field deletion, related lists, filters, popup fields, and layout field defaults.
- Use `blocksHeaderFields`, `blocksSummaryFields`, `blocksListFields`, and `blocksQuickCreateFields` for module layout defaults instead of hardcoding vtiger field flags in unrelated places.
- Use `Core_DatabaseTable_Model` for schema column lifecycle changes such as create, rename, or drop column.
- Put settings/list-view settings links in the module model `getSettingLinks()` or the existing Installer/Vtiger settings-link flow.

## Date, Time, and Time Zone Checks

- Whenever programming behavior involving dates or times, trace the complete value flow between the user, application/server, database, exports, scheduled jobs, and rendered output before completing the change.
- Verify that date-only values remain calendar dates without unintended time-zone conversion and that date-time values are converted exactly once between the user's time zone and the database time zone.
- Check relative periods and range boundaries around local midnight, different positive and negative UTC offsets, the configured first day of the week, and daylight-saving transitions where a day can contain 23 or 25 hours.
- Test time-sensitive behavior with representative zones including UTC, Europe/Bratislava, a negative-offset zone, and a high positive-offset zone. Include interactive report execution and any relevant background, scheduled, or export execution context.
- After successful verification, report the result in one short sentence in the user's current language, equivalent to: `Time zone behavior was checked and the functionality will work correctly.` Do not make this claim when the checks fail or remain incomplete; state the concrete issue instead.

## Currency and Monetary Calculation Checks

- Whenever programming behavior involving currency or monetary values, trace the complete value flow between the record currency, user currency, application/base currency, reporting currency, database storage, calculations, rendered output, exports, and scheduled jobs before completing the change.
- Verify the conversion direction and exchange-rate source explicitly. Check that values are not converted twice, left unconverted, or aggregated across mixed currencies before normalization to the intended reporting currency.
- Preserve monetary precision during intermediate calculations and apply the repository's established decimal precision and rounding rules only at the correct boundary. Do not use binary floating-point arithmetic when the existing decimal or currency helpers can preserve exact monetary semantics.
- Check zero, null, negative, very large, and fractional values; missing or zero exchange rates; inactive currencies; currencies with different decimal scales; and totals affected by per-line versus final-total rounding.
- Test the same calculation with matching currencies and with multiple different record, user, base, and reporting currencies. Verify list views, detail views, reports, grouping, summaries, charts, PDF/XLS exports, and background execution when they are relevant to the changed flow.
- After successful verification, report the result in one short sentence in the user's current language, equivalent to: `Calculations with different currencies were checked and should work correctly.` Do not make this claim when the checks fail or remain incomplete; state the concrete issue instead.

## Validation

- Do not create unit tests unless the user explicitly requests them. Use existing checks and manual verification for routine changes.
- Run `php -l` on touched PHP files.
- Run the scripts validator when module resource JavaScript changes.
- Check that `version.php` was bumped when rendered application styles changed.
