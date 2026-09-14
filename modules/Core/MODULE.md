# Core UI ownership

- The shared top bar renders global search through `layouts/d1/modules/Core/partials/TopbarSearch.tpl`, included by `partials/Topbar.tpl`.
- Module selection and filtering use `Vtiger_BasicSearch_Js` in `layouts/d1/modules/Vtiger/resources/BasicSearch.js`; search configuration belongs to GlobalSearch.
- Shared picker styling lives in `layouts/d1/skins/base/style.less` and its committed `style.css`. Keep the dropdown scrollable within the viewport, allowing for the extra search row below the `lg` breakpoint and using the shared header height variables.

# Workflow installation

- `Core_SimpleHtmlDom_Helper::convertNewlinesToHtml()` preserves newline/indentation runs immediately before opening or closing p tags (also entity-encoded tags), applying nl2br elsewhere. It never deletes existing br tags or document wrappers and does not parse, sanitize, or decode HTML. Workflow fields/comments, the Text UI type, and legacy EMAILMaker textarea conversion share this behavior.

- `convertParagraphBreaks()` handles existing plain br elements at paragraph boundaries separately from newline conversion. It removes runs before opening/closing p and after closing p, including before closing body/html wrappers followed by a paragraph. It preserves interior paragraph breaks, other text breaks, HTML wrappers/attributes, comments and pre/textarea/script/style content. It is idempotent and used only for final workflow email HTML; explicit blank lines immediately adjacent to paragraphs are intentionally removed there.

- `Core_Install_Model::updateWorkflowTask()` saves module-provided condition arrays through `Settings_Workflows_Record_Model`; `VTWorkflowManager::save()` normalizes supported conditions into the modern editor format. `updateWorkflowTasks()` skips definitions already present by module and workflow name. Explicit data migration calls `migrateWorkflowConditions()` with the module's `registerWorkflowTasks`: uniquely matched legacy definitions are regenerated from module defaults, while modern customizations remain unchanged.
