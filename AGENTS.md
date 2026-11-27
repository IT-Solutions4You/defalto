# AGENTS.md

## Repository Rules

- Never add or execute content/data migrations during development unless the user explicitly requests that migration. A request to change field storage, schema, install configuration, or application behavior does not authorize copying, moving, transforming, backfilling, merging, or deleting existing record content.
- Trace the runtime owner before changing code. Prefer the existing module, Core, Vtiger, or Installer mechanism over a local one-off fix.
- Before changing a module or installable extension, read the nearest `MODULE.md` under its module path (for example, `modules/<Module>/MODULE.md` or `modules/Settings/<Module>/MODULE.md`) and analyze the module's runtime owner, entry points, persistent data, external contracts, UI assets, and install lifecycle.
- Treat the module's `MODULE.md` as durable extension memory. Create or update it in the same task whenever verified, reusable module knowledge is learned, and update every affected module file for cross-module work. Keep it concise and in English; record ownership boundaries, invariants, data flows, integration points, and validation commands, but never task history, guesses, generated inventories, environment-specific state, credentials, license keys, or other secrets. Remove or correct stale guidance when the implementation changes.
- Maintain a customer-facing `USER_GUIDE.md` in each affected module when programming a functional module change. Describe what the module does, its user-facing workflow, important features, statuses, related-module actions, and practical usage tips in plain English. Create the file for a new module when needed, and update the guide whenever the change alters visible behavior or the supported workflow. Keep `MODULE.md` limited to technical extension memory and runtime guidance.
- Maintain a customer-facing `CHANGELOG.md` in every affected installable module. After each functional module change, add concise customer-visible bullet points under a `## <moduleVersion> - YYYY-MM-DD` heading, keep the newest version first, and retain at least the latest three version sections when verified history is available. The first heading must match the module's current `$moduleVersion`. Do not add implementation details, task history, or unverified claims. Documentation-only, comments-only, tests-only, and generated-map-only changes do not require a changelog entry.
- Keep Installer license checks inside the Installer-owned manual update and scheduled license-check flows. Do not add license hooks to global WebUI startup/login processing or to Users save, deactivate, or delete events; preserve the existing system and Users lifecycle behavior.
- Keep module PHP code under `modules/<Module>/{actions,models,views,helpers,handlers,dashboards,uitypes}`.
- Keep module frontend assets under `layouts/d1/modules/<Module>/resources`.
- Put reusable system behavior into `modules/Core` or an existing shared Vtiger/Core helper.
- When adding a new custom framework component that could belong to either Vtiger or Core, create it under `modules/Core` with a `Core_...` class name. Do not add new custom framework components under `modules/Vtiger`.
- Use central language files under `languages/<locale>/<Module>.php` for labels. Do not change technical identifiers only to alter display text.
- Whenever adding, changing, or removing a user-facing language key, update it in every locale directory present under `languages/` in the same task, creating the module language file when it does not yet exist. Keep both `$languageStrings` and `$jsLanguageStrings` key sets aligned across every `languages/<locale>/<Module>.php` file, translate the values instead of copying the English text unless the value is intentionally language-neutral, and verify that no locale directory is missing the file or retaining missing or stale keys. Shared labels owned by a central module such as Core may remain in that module's language files and must not be duplicated locally.
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

## Commit Messages

- When the user asks for `commits`, create the requested commits and push the current branch after they succeed unless the user explicitly asks not to push.
- Separate shared Core/Vtiger changes from installable module changes whenever practical, and create a separate commit for each affected installable module.
- Name commits owned by Core or Vtiger as `Defalto <defaltoVersion>: <short description>`, for example `Defalto 1.10.92: Support module-owned list query rules`. Use the current application version from `version.php`.
- Name commits owned by an installable module as `<ModuleName> <moduleVersion>: <short description>`, for example `Appointments 1.4: Preserve all-day calendar dates`. Use the module name and its current declared `$moduleVersion`.
- Keep the description short and customer-readable. Do not add Conventional Commit prefixes such as `feat:`, `fix:`, or `chore:` before the required owner and version.
- Documentation-only, comments-only, tests-only, and generated-map-only commits still use the applicable owner and current version format, but must not cause a version bump solely for the commit message.

### Module Versions

- Treat `public string $moduleVersion` in `modules/<Module>/<Module>.php` as the install and update version of that installable module; the install flow persists it to `vtiger_tab.version`.
- Extension versions may use two or three numeric components, such as `1.1` or `1.1.1`. Preserve the chosen format; never cast versions to floating-point numbers or truncate their components. Compare versions with `version_compare()`. Keep the exact version string aligned in the module declaration, public catalog, optional manifest and newest changelog heading. When incrementing a three-component version, increment only its final component (for example, `1.1.1` to `1.1.2`); do not convert existing modules to a new format unless requested.
- For functional changes owned by an installable module, determine the latest publicly available module version from the canonical public distribution metadata, such as the Installer public extension catalog, and increment its final numeric component exactly once for the next unreleased version. This includes module PHP, module-specific layout assets, language strings, install or schema behavior, and Settings code owned by that module.
- Before changing a module version, verify the public version instead of deriving the next version only from `HEAD`. If the working tree, index, or current development branch already uses the single next version after the public release, keep it for subsequent unreleased changes and commits. Do not increment it again until that version is publicly available.
- When reviewing accumulated unreleased module changes, consolidate accidental repeated increments to one increment above the public release. This rule applies to module versions; application/resource versioning in `version.php` follows its separate rules above.
- When one commit functionally changes multiple installable modules, bump every affected module independently and exactly once, regardless of how many files changed in each module.
- If a changed path has no root module version, trace its runtime and installer owner and bump the owning installable module. Do not introduce a new `$moduleVersion` only to satisfy this rule.
- A shared Core or Vtiger change does not require version bumps for every downstream module. Bump only installable modules whose owned runtime code or assets changed, in addition to the applicable `version.php` bump.
- Do not bump module versions for documentation-only, comments-only, tests-only, or generated-map-only changes.

## Control Flow Spacing

- In PHP views, prefer computing branch-dependent values in local variables first, then assigning related template values together. Use fluent method chains when every method is verified to return the same object (for example, supported `Vtiger_Viewer::assign()` calls). Put each chained call on its own continuation line for multi-line groups. Preserve evaluation order and side effects; do not force chaining across unrelated operations or methods that can return null.

- In PHP and JavaScript, separate a complete `if` / `elseif` / `else` chain, `foreach` block, or `while` block from adjacent statements with one blank line before and after the whole construct. This means vertical spacing, not additional spaces inside parentheses.
- Do not insert blank lines between conditional branches. Keep the closing brace and the next branch on the same line: `} else {`, `} elseif (...) {` in PHP, or `} else if (...) {` in JavaScript. Keep the trailing `while` attached to a `do` block.
- Do not add a blank line immediately after an enclosing opening brace or immediately before its closing brace solely for this rule. A block at the start or end of a method or another block needs spacing only on the side with an adjacent statement.
- Keep a comment documenting a block directly attached to it; place the separating blank line before that comment. Preserve existing blank lines without multiplying them.
- Apply this style to new and touched code. Do not reformat unrelated files or change runtime behavior as part of a spacing cleanup. Whitespace-only changes do not require application or module version bumps.

## Function Naming

### Readable Conditions and Model Ownership

- When a condition is difficult to understand at its call site, extract it into a clearly named boolean method on the model object that logically owns the checked data or domain rule, and call that method instead of keeping the compound condition inline. Apply this to new and touched code, including conditions used only once.
- Look for mixed logical operators, repeated negations, field metadata checks, or several related checks whose domain meaning is not immediately clear. Judge readability by meaning rather than a fixed number of clauses; keep simple, self-explanatory checks inline.
- Trace the runtime owner and reuse an existing appropriate module or shared model. Do not place domain predicates in a view, action, unrelated model, or generic utility merely because that is the current call site. Reuse an existing equivalent method before adding another one.
- Name the method after the decision it expresses, following the boolean naming rules below, and declare a `bool` return type in PHP. For example, a workflow task model can expose `isOwnerNameMapping(...)` or `isParentOwnerField(...)`, allowing the view to use `if (!$taskModel->isOwnerNameMapping(...)) { continue; }`.
- Preserve the original evaluation order, short-circuit behavior, null handling, defaults, and lookup semantics when extracting a condition. Keep predicates free of writes and other state-changing operations, and pass only the data or model context they need.

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

- Use the standard `Exception` class for application errors; do not introduce `AppException`. Let the central WebUI exception handler handle these errors.

- Let `Vtiger_Loader` autoload framework component classes whose names and paths follow the `Module_Component_Type` convention, such as `Core_DateFilter_Helper` in `modules/Core/helpers/DateFilter.php`. Do not add manual `require`, `require_once`, `include`, or `include_once` statements for these components; verify the class-to-path mapping before removing a legacy include. Standalone scripts may explicitly bootstrap files when the application autoloader is not initialized.
- Before completing a new, copied, or renamed module, compare every overridden method with the actual parent class or interface declaration. Match visibility, staticness, parameter types and defaults, reference/variadic markers, and return types; do not rely on the legacy source module's signature.
- Audit controller lifecycle overrides especially carefully, including `validateRequest()`, `checkPermission()`, `preProcess()`, `postProcess()`, `process()`, `getHeaderScripts()`, and `getHeaderCss()`. An isolated `php -l` does not detect inheritance incompatibilities when the parent class is not loaded, so also load the child with its real parent or an equivalent compatibility harness.
- Do not introduce `try`, `catch`, or `finally` in new application code. Never hide, suppress, log-and-continue, or translate an exception into a generic response or template state. Let the central WebUI exception handler expose the original application error. Keep cleanup explicit after successful operations or in the lifecycle of the owning model without intercepting the original error.
- Keep all runtime database reads and writes in model classes. Helpers orchestrate domain behavior and call model APIs; actions and views validate or present data and must not contain SQL or private database accessors.
- Prefer `Core_QueryGenerator_Model` for new application-level list and record query generation. Extend its structured query API when a required condition, join, grouping, or ordering feature is missing; do not locate SQL clauses or inject conditions by parsing and rewriting completed SQL strings.
- Implement query-generator behavior changes in `modules/Core/models/QueryGenerator.php` (`Core_QueryGenerator_Model`), not in legacy files under `include/QueryGenerator/`. When legacy behavior needs modification, port or override the required methods in the Core model and verify that the affected runtime flow uses that model. Preserve existing contracts and do not copy the entire legacy implementation when a focused override is sufficient.
- Create a dedicated model for every module-owned table and name the model after the table's domain suffix, for example `df_two_factor_backup_code` is represented by `TwoFactorAuthentication_BackupCode_Model`. Keep that table's CRUD and atomic updates in its model; for shared system tables, reuse the existing owning Core, Vtiger, Settings, or module model instead of creating a duplicate local data model.
- Make table-backed runtime models extend `Core_DatabaseData_Model`, declare their protected `$table` and `$tableId`, initialize the shared connection through `retrieveDB()`, and prefer `selectData()`, `insertData()`, `updateData()`, and `deleteData()` for ordinary CRUD. Use `getDB()` with custom SQL only when the shared CRUD API cannot express a required aggregate, join, upsert, or atomic conditional update without changing its semantics.
- Give each table-backed model a clearly named table accessor such as `getModTrackerBasicTable()` or `getBackupCodeTable()`. Implement it by returning `$this->getTable($this->table, $this->tableId)` and use an appropriate return type. This shared method returns a configured clone and initializes its database connection; it does not configure the original model in place.
- Use that named accessor for ordinary CRUD inside the owning model, for example `$table = $this->getModTrackerBasicTable();` followed by `$table->selectData(...)`, `$table->insertData(...)`, `$table->updateData(...)`, or `$table->deleteData(...)`. Reuse the returned table object within an operation instead of repeating its configuration.
- Do not add constructors that manually call `set('table', ...)` or `set('table_id', ...)`, and do not call CRUD on an unconfigured model instance. Keep `$table` and `$tableId` as the single source of table metadata. If custom SQL uses the original model's `getDB()`, initialize that original instance with `retrieveDB()` first; configuring a clone does not initialize the original.
- Callers outside the owning model should prefer its domain methods, such as `saveHistory()` or `saveFieldChange()`, over accessing its table directly. Apply this table-access pattern to new models and models being refactored without broad unrelated rewrites.
- Before adding domain methods to a `Core_DatabaseData_Model` subclass, audit inherited method names and signatures such as `save()`, `delete()`, `getId()`, and `getName()`. Use a specific domain name such as `saveTemplate()` when different parameters or behavior would make an override incompatible.
- Keep install, migration, and schema lifecycle queries in the owning install/database model. A cross-table read belongs to the model representing the primary domain result and should not leak SQL back into a helper.
- Keep `installTables()` limited to table/schema creation and maintenance, including columns, keys and constraints. Do not seed, insert, update or delete application/configuration data there, and do not keep data-initialization flags or checks in this method. Put default-data registration in a separate named method called by the appropriate later installation lifecycle step, or by an explicitly authorized migration. Preserve administrator choices on updates and repeated registration.
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

## Deployment Guidance

- Before changing the deployment workflow or deploying, read the checkout-local `DEPLOYMENT.md` if present. Follow its target, file selection, and verification instructions. After validating a functional change, deploy its runtime files before reporting completion when a local deployment is configured. For documentation-only work, skip runtime deployment.

## Short Change Summary

- Treat the command `summary` as a request for a concise summary of the completed work.
- Respond in the user's current language and group the summary by affected module, with each module name as a separate heading. Put shared Core/Vtiger changes in their own group.
- Under each module, use two subsections: added or repaired functionality, and restricted or removed functionality. Keep manual test scenarios exclusively in `testing`, not in `summary`.
- Under each section, use short bullet points, with one change or restriction per bullet. Do not compress a whole section into one long bullet.
- As in `testing`, start every change bullet with the place in the application (screen, section, or navigation path), followed by a colon. Use the format `**Place → Screen:** What changed and what users gain, lose, or experience differently.` Describe the completed behavior and its practical effect, not an action for the user to test. Use visible UI labels and include any relevant role or conditions after the location. A brief statement that there are no restrictions or removals does not need a location.
- Describe added or repaired functionality and its practical benefit. State restrictions or removals explicitly; if none, say so briefly.
- Keep bullets concise and in customer language, without nested lists or long explanations. Keep changes under the module that owns them.
- Avoid implementation details, filenames, test output, commit hashes, and unnecessary introductory text unless the user asks for them.
- When responding to `summary`, append the relevant completed commits after the module groups. For each commit, show its full commit title followed by its full GitHub commit URL in a separate fenced `text` code block so the URL is directly copyable. Use verified commit URLs and include only commits belonging to the summarized work. Omit this section when the summarized work has no completed commits.

## Manual Testing Checklist Command

- Treat the command `testing` as a request to print a concise manual testing checklist for the completed changes, similar to `summary`. It does not authorize running automated or manual tests, changing files, deploying, committing, or pushing. The pre-commit test-planning and execution rules below remain separate.
- Respond in the user's current language and group scenarios by affected module, with each module name as a separate heading. Put shared Core/Vtiger scenarios in their own group.
- When moving test points out of an existing `summary`, include only its original test bullets in `testing`. Preserve their scenario content and module grouping; do not add or expand scenarios unless the user requests it.
- Derive the checklist from the actual completed changes and their directly affected workflows. Include repaired/new behavior and relevant regression scenarios; do not list unrelated module functionality or every possible test.
- Use short bullets, one scenario per bullet, without nested lists. Start every bullet with the place in the application (screen, section, or navigation path), followed by a colon, the concrete action to perform, and the expected visible result. Use the format `**Place → Screen:** Perform the action. Expected result.` Put any necessary role, record, or setup prerequisites after the location, before the action.
- Include normal use and relevant permission, invalid-input, existing-data, repeat-operation, or cross-module cases only when the changes affect those behaviors. Describe each case in customer language using visible UI labels.
- Present these bullets as checks for the user to perform, not as tests already passed. Omit implementation details, filenames, commands, automated-test output, commit hashes, and unnecessary introductory text unless explicitly requested.

## Clean Code Command

- Run this workflow only when the user explicitly invokes `clean code` or `run clean code`, or explicitly requests a clean-code review/refactor. Do not run it automatically after development, during `summary`, `testing`, or `commits`, or as an unsolicited repository cleanup. Existing repository rules still apply to ordinary development.
- Treat `clean code` as an analysis-only request. Report findings and proposed improvements without editing files, running tests, deploying, staging, committing, or pushing.
- Treat `run clean code` as an explicit request to analyze and perform scoped behavior-preserving refactoring and its relevant validation. Reuse preceding findings only while they remain valid for the current code and scope. This command does not authorize commits, push, deployment, data migrations, or functional changes. Deploy a clean-code refactor only when explicitly requested, as an exception to automatic deployment guidance.
- Use the scope named by the user. Otherwise, review the current task's changes and their direct runtime dependencies, distinguishing unrelated staged, unstaged, and new files. If no task scope can be established, ask for a target instead of scanning or refactoring the whole repository.
- Before assessing a module, read its nearest `MODULE.md`, trace the runtime owner and relevant callers, and inspect the actual parent contracts. Prefer the existing module/Core mechanisms over new local infrastructure.

### Review Principles

- **SRP:** Look for unrelated responsibilities or independent reasons to change in a function, class, or module. Keep business rules, persistence, request handling, and presentation with their established owners. A cohesive orchestration method may call several steps; do not split code merely because it is long or performs multiple related operations.
- **MVC ownership:** Trace the affected request-to-render flow and verify responsibility boundaries against the actual framework: models own domain rules and all runtime database access; actions/controllers validate requests and permissions and orchestrate model operations; PHP view controllers prepare presentation data and assign it to templates. Keep domain decisions in the owning model rather than relocating them from a template into a controller. Flag database queries, external requests, persistence, or business calculations in controllers or templates when they belong to a model.
- **Prepared template data:** Pass ready-to-render values, collections, URLs, labels, and clearly named visibility/state flags from the PHP view controller. Move variable initialization, data assembly, filtering, aggregation, repeated method calls, and compound decision logic out of Smarty templates whenever practical. Templates should primarily render supplied data; keep only necessary presentation loops, simple display conditions, translation, formatting, and context-appropriate escaping. Prefer a prepared flag over a template condition that interprets domain state or permissions, while enforcing actual authorization in the server-side request/model flow. Preserve existing escaping, locale/time-zone/currency formatting, and framework template contracts; do not pre-render HTML or introduce elaborate presentation abstractions merely to eliminate every template condition.
- **KISS and YAGNI:** Prefer the simplest implementation that meets verified current requirements. Identify unnecessary indirection, speculative configuration, unused abstractions, and premature patterns. Do not introduce interfaces, services, dependency-injection containers, or extension points without a concrete need. Verify dynamic/framework consumers before proposing removal of apparently unused code.
- **Guard clauses:** Prefer early returns or loop continuation when they make exceptional paths clear and reduce nesting. Preserve validation and permission order, short-circuit behavior, null/default handling, side effects, transaction boundaries, and required cleanup or lifecycle completion. Never replace a required error with a silent return.
- **Expressive naming:** Use domain-specific names that reveal purpose and side effects, following the repository's Function Naming rules. Prefer comments explaining reasons, constraints, or non-obvious contracts over comments restating the code. Preserve public identifiers, framework-discovered names, and external contracts unless their change is explicitly authorized.
- **CQS:** Make reads and writes distinguishable at the call site. Flag unexpected persistence, external requests, or mutations hidden in query-like methods. Respect documented model hydration and cache behavior. Do not split an atomic write merely to prevent it from returning its ID, result, or status, and do not break framework method contracts to enforce strict CQS.
- **Composition over inheritance:** Prefer small collaborating components for new optional behavior when they reduce coupling. Reuse the established framework base classes and lifecycle hooks; do not replace required inheritance or add dependency-injection infrastructure solely to satisfy this principle.
- **DRY and the Rule of Three:** For a new shared abstraction, normally wait for a third occurrence of the same domain rule, then verify that the occurrences share meaning, ownership, and reasons to change. Similar syntax alone is insufficient, and a third occurrence does not mandate extraction. Reuse an existing appropriate abstraction immediately. This heuristic does not delay required ownership separation or extraction of a clearly named domain predicate, even when used once.

### Findings and Authorized Refactoring

- Prioritize concrete maintainability problems by practical benefit, effort, and regression risk. Report each actionable finding with its location, observed problem, relevant principle, smallest useful improvement, and affected contracts or verification needs. Separate verified findings from uncertainties and state when no useful change is warranted; do not manufacture findings or impose arbitrary method-length limits.
- Respond in the user's current language and group findings by owning module, with shared Core/Vtiger findings in their own group. Keep the result concise and distinguish recommendations, applied changes, and unverified behavior.
- When refactoring is explicitly requested, make small behavior-preserving changes within the agreed scope. Preserve outputs, permissions, exception behavior, data semantics, API contracts, and install/update lifecycle. Do not mix bug fixes, new features, schema/data changes, broad formatting, or unrelated cleanup into the refactor; report those separately.
- Follow the existing documentation and versioning rules for the actual changes. Runtime PHP/JavaScript/template/style refactoring still requires the applicable application/resource patch bump; functional module changes require their module version, guide, and changelog updates. Do not bump versions for analysis or documentation alone.
- For authorized edits, run focused existing checks required by the touched paths and use relevant behavioral checks to verify preserved behavior. Do not add unit tests unless explicitly requested. Report which checks passed and which behavior remains unverified without presenting syntax checks as runtime proof.

## Commit Command

- Treat the command `commits` as an explicit request to create commits for the completed work.
- Run the `commits` workflow from the terminal. Use shell commands for validation, staging, commits, and push. Prefer existing CLI runners and command-line HTTP checks for relevant behavior. Keep checks that require a browser in the manual `testing` checklist and report them as unverified. Browser access or CRM sign-in is not a prerequisite for `commits`.
- Review the working tree, preserve unrelated user changes, create and execute the change-based test plan below, and split commits according to the repository ownership and commit-message rules.
- After committing, report the created commit hashes and short messages, and list any changes intentionally left uncommitted.

## Testing Before Commit

- Before committing functional changes, derive a test plan from the actual intended commit diff and the affected runtime paths. Select checks by changed behavior and risk, not only by file extension or module name. Do not run every repository suite by default.
- Distinguish staged changes, intended unstaged/new files, and unrelated user changes. Test the code that will actually be committed; a passing result for another snapshot is not evidence for the final commit. For dependent commits, also verify the combined integration path.
- Use the reusable prompt below to produce the plan. Then execute its required checks, reuse existing relevant results while the tested code and dependencies remain unchanged, and rerun checks invalidated by subsequent edits.
- Follow the repository's Validation rules for adding tests. When the user requests a new test, use the applicable existing Defalto test runner and cover a meaningful failure or boundary. Do not add tests that merely mirror the implementation or cover documentation-only edits.
- For touched PHP and language files, run `php -l` when available and review compatibility with the PHP range declared in `composer.json`; a successful lint on a recent PHP version does not prove compatibility with the minimum supported version. Syntax-check touched JavaScript with system Node or the bundled Codex Node runtime. Run `git diff --check` for the intended changes.
- Distinguish static checks, isolated/mocked tests, real HTTP/browser checks, and live integrations. Syntax checks do not prove behavior; simulated CRM or mailer tests do not prove database installation, browser rendering, or SMTP delivery. Where the changed boundary depends on HTTP, browser parsing, or persistence, plan an appropriate runtime check.
- Use temporary fixtures or an authorized test environment. Keep customer data intact and do not send real campaigns, delete customer files, or change production configuration merely to obtain a PASS. Existing task authorization still governs test actions; the plan itself grants no additional authority.
- Before committing, resolve failures introduced by the patch. Report pre-existing failures and unavailable checks separately, with their impact and the smallest missing prerequisite. If a missing check is essential to establish correctness or security, finish the available work and report the commit as blocked; do not silently waive the check or claim verification. Do not introduce a new approval step for routine authorized checks.
- Documentation-only changes need a focused review of wording, consistency, references and examples, plus the applicable diff check. Do not run unrelated runtime suites, deploy, or increment a version solely for those changes.

Reusable test-planning prompt:

```text
Create a pre-commit test plan for the intended changes in this checkout.
Analyze only; do not edit source, run tests, stage, commit, push or deploy while producing the plan.

1. Establish the exact intended diff and base revision. Include relevant new files and dependencies; distinguish unrelated staged or dirty files. Read affected MODULE.md guides and inspect existing tests before selecting coverage.
2. Explain the behavior that changes, the original failure or requested outcome, the behavior that must remain, and the direct callers/consumers that could regress. Follow data through later decoding, personalization, rendering and delivery where applicable.
3. Build a compact table: module/runtime path | change and risk | setup/role/data | action or input | expected result | command, runner or UI path | required or conditional. Every scenario must have a concrete observable outcome. Explain important exclusions instead of adding speculative tests.
4. Select applicable scenarios:
   - PHP/business logic: original failure, ordinary success, boundary/invalid input, optional modes and relevant supported PHP behavior.
   - Requests/permissions: permitted and denied roles, own and foreign records, missing/deleted/wrong-module IDs, request method, CSRF and direct endpoint access when affected.
   - UI/JavaScript/Smarty: actual page-controller startup, resource loading, user interaction, save/reopen/preview, AJAX refresh/repeated registration, escaping and affected layouts.
   - Persistence/install/update: new and existing data, first installation versus repair/update/enable, repeated runs, retained settings/records, partial failures and restoration of temporary state.
   - Files/HTML/integrations: authorization and ownership, supported and invalid formats/encodings/paths, temporary versus saved state, concurrent work if affected, linked copies, final output and relevant failure handling. Separate simulated checks from actual upload/download/render/send behavior.
   - Languages/resources/packaging: changed-key parity and real translations across supported locales and normal/Settings domains; actual asset paths, bundled dependencies and clean compatible installation when packaging changes.
   - Shared Core/Vtiger mechanisms: representative direct consumers in affected modules, inherited contracts and both branches of changed conditions. Expand coverage only when the dependency trace justifies it.
   - Documentation-only changes: consistency, references and examples; no unrelated runtime checks.
5. Identify existing Defalto checks and their CLI or UI entry points. If the user requests a new regression test, choose a meaningful coverage gap. List relevant syntax/diff checks and prerequisites, including PHP extensions, authentication and fixture cleanup.
6. Order checks from focused syntax and regression checks to affected runtime/integration flows. State acceptance criteria, previously valid evidence that can be reused, untested risks and blockers. Do not present planned or mocked checks as completed end-to-end verification.
```
