# AGENTS.md

## Repository Rules

- Trace the runtime owner before changing code. Prefer the existing module, Core, Vtiger, or Installer mechanism over a local one-off fix.
- Keep module PHP code under `modules/<Module>/{actions,models,views,helpers,handlers,dashboards,uitypes}`.
- Keep module frontend assets under `layouts/d1/modules/<Module>/resources`.
- Put reusable system behavior into `modules/Core` or an existing shared Vtiger/Core helper.
- Use central language files under `languages/<locale>/<Module>.php` for labels. Do not change technical identifiers only to alter display text.

## JavaScript / Scripts Checks

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

- Use `Core_Install_Model` for field creation, field deletion, related lists, filters, popup fields, and layout field defaults.
- Use `blocksHeaderFields`, `blocksSummaryFields`, `blocksListFields`, and `blocksQuickCreateFields` for module layout defaults instead of hardcoding vtiger field flags in unrelated places.
- Use `Core_DatabaseTable_Model` for schema column lifecycle changes such as create, rename, or drop column.
- Put settings/list-view settings links in the module model `getSettingLinks()` or the existing Installer/Vtiger settings-link flow.

## Validation

- Run `php -l` on touched PHP files.
- Run the scripts validator when module resource JavaScript changes.
- Check that `version.php` was bumped when rendered application styles changed.
