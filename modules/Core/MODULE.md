# Core UI ownership

- Core language files expose `LBL_GROUP` in both PHP and JavaScript dictionaries. Shared advanced filters render existing group titles through `vtranslate()` and dynamically added titles through `app.vtranslate()`; keep the translations aligned.

## Module-specific filter objects

Define `modules/<Module>/models/Filter.php` with `<Module>_Filter_Model extends Core_Filter_Model`. For example, a module with a `subject` field can restrict that field to equality in every filter entry point:

```php
class Example_Filter_Model extends Core_Filter_Model
{
    protected function initialize(): void
    {
        $this->setFieldOperators('subject', ['e' => vtranslate('LBL_EQUALS')]);
    }
}
```

Use the actual module name instead of `Example`. Unconfigured fields retain the common defaults. For metadata-dependent rules, override `getOperators()` and delegate to its parent. For value/operator transformations, override `getQueryCondition()` and pass the transformed condition to its parent. Configure shared behavior in the subclass rather than mutating one temporary factory instance: UI rendering and query execution create independent objects, including across requests.

## Shared filter integration

- Standalone `T` / `bw` criteria leave `getQueryCondition()` with an array of separately normalized time endpoints. Both query parsers and `getConditionValue()` accept this structure, so unrelated comma-handling flags cannot collapse the range. Other operators retain string values; Calendar `time_start`/`time_end` keep their existing timezone path. Never send a comma-separated range to the single-time `getTimeValueWithSeconds()` parser. Reapplying normalization must preserve the array and its endpoints.
- `Core_Filter_Model::getInstance($moduleName)` resolves `<Module>_Filter_Model` from `modules/<Module>/models/Filter.php` through the standard loader, falling back to Core. Each call creates a separate object and invokes protected `initialize(): void` after setting its module. Subclasses must extend Core and preserve method signatures. Both UI paths use the source/list module's object, including for related fields.
- `Core_FilterOperator_Model::getForField()` delegates operator presentation to that object's `getOperators(Vtiger_Field_Model $field): array`; `getDefaultForField()` holds the common defaults. Defaults consume Vtiger field/type/date definitions. Do not add toolbar allowlists. Module overrides may restrict labels/operators or call `setFieldOperators($name, $translatedOperatorMap)` in `initialize()`. These settings are object-local, not persisted.
- `Core_QueryGenerator_Model::parseAdvFilterList()` calls `getQueryCondition(array $condition): array` for quick and saved advanced criteria before delegating escaping and SQL construction to the inherited parser. Keep legacy generators unchanged. Shared list/popup factories, list export and MiniList instantiate the Core model. The input/output contract has `columnname`, `comparator`, `value` in user display format and `column_condition`. Preserve connectors and related-field identifiers. Overrides may map a custom operator to an existing supported operator; adding a label alone does not implement new SQL semantics. Keep this method deterministic, idempotent and free of writes; do not perform timezone conversion here.
- Quick date inputs use `AdvanceFilter_Field_Js` and the same date/datetime renderer as the saved editor, including numeric day/hour offsets, relative period quantity/unit, and calendar ranges. `lastperiod` retains its `quantity|unit` value through list conversion and saved-view read/write; it is not a date string. The shared query-condition model normalizes both standalone time endpoints and adds whole-day datetime bounds to date-only ranges. Date-only fields and currency strings stay unchanged.

- The list-filter toolbar uses `fa-solid fa-filter` for the condition action. Keep the translated tooltip and visually hidden label unchanged.
- List-filter metadata is built from `Vtiger_FilterRecordStructure_Model`, the same structure used by the saved-list condition editor. Base fields and one level of permitted reference fields therefore share field visibility, labels, operators, relation identifiers and saved-view columns. Reuse the existing `(parent ; (Module) field)` identifier and cloned field's `reference_fieldname`; keep the base module/field in popup metadata. Do not recursively expand relations.

- Time filter inputs use `vtUtils.registerEventForTimeFields()` with the user's hour format and shared time validation. `Core_Filter_Model::getQueryCondition()` normalizes these wall-clock values to HH:mm:ss without timezone conversion. Destroy replaced timepickers, hide them when closing the filter, and recognize picker clicks in the dropdown close guard.
- List-filter fields follow the display types accepted by `CustomView::getColumnsListbyBlock()` (1, 2, 3), in addition to field visibility and permissions. Exclude line-item-only fields (5), password fields (4), and starred/tag fields (6); retain inventory totals with display type 3. Apply this in `Core_ListFilter_Model::isFilterField()` before building either flat metadata or block groups, including metadata reused when saving toolbar filters.

- Reference filter inputs offer the existing `Vtiger_Popup_Js` record picker. Field metadata supplies allowed reference modules and translated module labels; polymorphic references use a Select2 module selector. Popup selection fills the record name while preserving the operator and edit position, since reference queries compare display names. Free text remains available for contains conditions.
- Reopen the reference filter editor from the popup's `hidden.bs.modal` event, deferred until the current click finishes, for both selection and cancellation. Reopening inside the selection callback lets Bootstrap's document click handler immediately close it again. Preserve the existing inputs and edit index; do not reset the editor on return.

- Date filter inputs use `vtUtils.registerEventForDateFields()` and the user's date format. Between uses the existing single-input range calendar (`data-calendar-type="range"`). Validate calendar dates and range order with the shared datepicker parser; send the original user-format dates without UTC conversion. Calendar clicks must not close the surrounding Bootstrap dropdown, and replaced inputs must destroy their datepickers.
- Detect calendar clicks using the native click event's `composedPath()` as well as target ancestry. Month navigation redraws the datepicker and detaches the clicked control before Bootstrap handles the click, so target ancestry alone incorrectly closes the filter.

- Currency-list filters use a single-value Select2 even when no active currencies are available. Option values are currency IDs with translated name labels; the existing EnhancedQueryGenerator currency_id branch compares numeric values against the currency ID and still accepts legacy name conditions. Allowed operators are equality, inequality, empty and not-empty. Currency amounts and exchange-rate calculations are separate and unchanged.

- Reference fields use the shared text operator set in the list-filter toolbar, including contains and does-not-contain. The existing query generator applies these operators to related record display names rather than numeric reference IDs.

- Owner filter metadata retains translated Users/Groups sections in `valueGroups`, rendered as Select2 optgroups; empty sections are omitted. Keep the flat `values` map for chip labels and restoring selections, and preserve name-based query values.

- Stop mousedown propagation from Apply/Cancel to Select2's body close handler. Inline results must keep their height until the button click completes; closing them on mousedown moves the button before mouseup and loses the action. The existing dropdown hide handler closes Select2 after the action starts.

- Selecting an owner field again resumes its single existing editable AND condition, restoring the operator and selected names so Apply replaces it instead of adding conflicting owner conditions. Multiple conditions for that field and OR conditions remain independently editable through their chips.

- Owner and group names from user metadata are HTML-decoded before becoming filter option values and labels; the query generator compares names, not HTML entities. The Apply button calls the shared `applyFilter()` action directly; validation opens invalid Select2 controls instead of attempting to focus their hidden native selects.

- All toolbar selects use `vtUtils.showSelect2ElementView()` with full width and their immediate field wrapper as dropdown parent. Scoped CSS puts the attached results container in normal flow so the scrollable editor grows instead of clipping an absolute overlay. Update Bootstrap positioning when results open/close or controls change. Initialize when opening the editor and after creating value selects, refresh programmatic selections with `change.select2`, and destroy dynamic value widgets before replacing their controls.

- The list-filter operator container starts hidden and its select disabled; `updateOperators()` synchronizes both states with the selected field, including editor resets and editing an applied condition.

- `Core_Kanban_View` supplies picklist colors through `Core_Kanban_Model` to `layouts/d1/modules/Vtiger/KanbanView.tpl`. Colors must be six-digit `#RRGGBB` strings; missing or invalid colors use the model's default, and RGB conversion independently applies that fallback. The picklist lookup can return an empty string. This shared Core behavior has no separate installable module version or schema migration. Validate color conversion with valid, empty, null and malformed inputs, and lint `modules/Core/models/Kanban.php`.

- `Core_ListFilter_Model::getFieldGroups()` groups already-permitted filter metadata by module blocks, preserving block order and omitting empty groups. The field selector renders translated, escaped `optgroup` labels; the flat metadata map remains the JavaScript lookup contract.

- The filter icon count includes saved-view and temporary conditions. The dropdown identifies the saved view and explains that clearing additional filters preserves its conditions; its edit shortcut is shown only when the view is editable. Saved conditions remain outside the toolbar search state.

- `Core_ListFilter_Model` supplies permitted base-module field metadata for the shared list filter toolbar. `ListFilter.tpl` and `resources/ListFilter.js`/`.css` live under the Core layout; the JavaScript class is `Vtiger_ListFilter_Js` as required by the Core resource convention. Vtiger's list controller loads these assets and delegates list requests to its existing search pipeline.
- Applied conditions are the original two positional `search_params` groups (AND, OR), separate from the dropdown's draft inputs. Never reconstruct applied state from visible chips or collapse the OR group into AND. Monetary input remains a string in the user's format; date inputs keep the user's calendar format, leaving timezone conversion to the existing server query path. Datetime quick filters use whole-day ranges.
- The toolbar uses Bootstrap dropdown/form/button utilities and theme colors. Custom CSS bounds the scrollable dropdown and wraps long condition labels. New conditions may use permitted module fields absent from the displayed columns. Existing unsupported conditions remain visible and removable.
- The toolbar sits beside the list-column picker in the first header cell. Only the compact filter icon and active count appear in the header; all applied conditions, clear/save actions and condition inputs are inside its dropdown. After applying or removing a condition, reopen the refreshed dropdown with the new condition list. Use fixed Popper positioning to escape the scrolling table's clipping. Filter inputs and the apply button explicitly belong to the separate `listFilterForm` outside `listedit`; never nest filter and inline-edit forms, including dynamically created value inputs.
- Validate add/edit/cancel/remove/clear, empty-value operators, multi-selection, date ranges, AJAX failures, paging/sorting/export and switching saved views. Run JavaScript syntax checks and ITS4You ScriptsValidator, and lint `modules/Core/models/ListFilter.php`.

- The shared top bar renders global search through `layouts/d1/modules/Core/partials/TopbarSearch.tpl`, included by `partials/Topbar.tpl`.
- Module selection and filtering use `Vtiger_BasicSearch_Js` in `layouts/d1/modules/Vtiger/resources/BasicSearch.js`; search configuration belongs to GlobalSearch.
- Shared picker styling lives in `layouts/d1/skins/base/style.less` and its committed `style.css`. Keep the dropdown scrollable within the viewport, allowing for the extra search row below the `lg` breakpoint and using the shared header height variables.

# Workflow installation

- `Core_SimpleHtmlDom_Helper::convertNewlinesToHtml()` preserves newline/indentation runs immediately before opening or closing p tags (also entity-encoded tags), applying nl2br elsewhere. It never deletes existing br tags or document wrappers and does not parse, sanitize, or decode HTML. Workflow fields/comments, the Text UI type, and legacy EMAILMaker textarea conversion share this behavior.

- `convertParagraphBreaks()` handles existing plain br elements at paragraph boundaries separately from newline conversion. It removes runs before opening/closing p and after closing p, including before closing body/html wrappers followed by a paragraph. It preserves interior paragraph breaks, other text breaks, HTML wrappers/attributes, comments and pre/textarea/script/style content. It is idempotent and used only for final workflow email HTML; explicit blank lines immediately adjacent to paragraphs are intentionally removed there.

- `Core_Install_Model::updateWorkflowTask()` saves module-provided condition arrays through `Settings_Workflows_Record_Model`; `VTWorkflowManager::save()` normalizes supported conditions into the modern editor format. `updateWorkflowTasks()` skips definitions already present by module and workflow name. Explicit data migration calls `migrateWorkflowConditions()` with the module's `registerWorkflowTasks`: uniquely matched legacy definitions are regenerated from module defaults, while modern customizations remain unchanged.
