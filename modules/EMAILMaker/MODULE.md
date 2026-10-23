# Email content ownership

- `EMAILMaker_EMAILContent_Model` owns email template rendering. `VTEmailTask::getContents()` runs `getContent(false)` before workflow variable substitution; `ITS4YouEmails_Record_Model::retrieveContent()` runs recipient-aware `getContent(true, ...)` again before assigning the mailer body. Keep both passes in view when diagnosing formatting.
- `getFieldDisplayValue()` delegates modern text fields to their field display method. Vtiger_Text_UIType checks isHtmlField(), preserving configured HTML fields such as Users.signature without newline conversion; EMAILMaker retains its existing decoding pass. Legacy user variables from getUserValue() still pass through their original values. Neither path removes HTML document wrappers.
- `getFieldValueUtils()` retains the legacy encoded-br guard, but delegates textarea newline conversion to Core_SimpleHtmlDom_Helper::convertNewlinesToHtml() to avoid extra breaks before literal or encoded paragraph tags. The private EMAILContent::getFieldValue() wrapper has no local invocation; active modern fields use the shared Text UI type instead.
- `getContent()` decodes entities on the combined subject/body and replaces nonbreaking-space entities with ordinary spaces. It calls `handleRowbreak()` even without rowbreak tokens; that parser pass is not a general newline-to-br conversion. Explicit paragraphs, breaks and CSS in the template remain relevant to spacing.
- Validate formatting through both EMAILMaker passes, modern and legacy signature variables, inline styles, encoded literal text, and the final ITS4YouEmails mailer body. Isolated method execution is not evidence of the exact HTML delivered by a configured workflow.

# Display conditions

- `EMAILMaker_FilterRecordStructure_Model` uses `Core_FilterEditor_Model::getConditionBlocks()` for source and referenced modules. Ordinary blocks keep their configured order, while the block named `LBL_ITEM_DETAILS` is presented last. This changes only display-condition field ordering.

# Attachment images

- Browser-visible user, contact, product, record and company attachment images use `Core_Storage_Model::getAttachmentPublicUrl()` rather than exposing `vtiger_attachments.path`. This keeps templates compatible with attachment storage outside the web directory.
- PDFMaker keeps using resolved physical paths for local PDF rendering; do not replace those paths with browser URLs without validating the PDF renderer.

# Template detail icons

- `EMAILMaker_EMAILMaker_Model::GetDetailViewData()` (default for DetailView / ListView) and the permission-denied box (`denied.gif`) take their icons through `vimage_path()` (`layouts/d1/skins/images/Enable.png`, `Disable.png`, `denied.gif`), the same as PDFMaker. The former `layouts/vlayout/...` paths do not exist in Defalto.
- `layouts/d1/modules/EMAILMaker/unsubscribeinfo/info_en_us.tpl` still points to `layouts/vlayout/modules/EMAILMaker/images/unsubscribe_email_*.jpg`; those images exist nowhere and the template is not loaded by any code.

# PDF Maker templates of an e-mail template

- The links live in the shared template relations `df_template_rel` (`Core_TemplateRelation_Model`, module `EMAILMaker`, related_module `PDFMaker`, `sequence` = their order); `EMAILMaker_PDFTemplate_Model` reads and writes them only through that model and owns the PDF-specific rules. The table is created by the core (`Core_CreateTables_Config` → `Core_TemplateRelation_Model::createTables()`, fresh install and system update). vtiger_crmentityrel is not used: the core looks it up by id without the module.
- Available only while PDF Maker is active (`EMAILMaker_Module_Model::isPDFMakerInstalled()`) and `df_template_rel` exists; otherwise the edit field, the detail row and the preselection are not shown and the stored links stay untouched (`SaveEMAILTemplate` saves only when the form sent `pdf_templates_sent`).
- Offered templates come from `PDFMaker_PDFMaker_Model::getAvailableTemplates()` — the current user's permissions and active status (and the record's display conditions where a record is given). Saving keeps only offered templates of the e-mail template's module; deleted, inactive or forbidden ones are skipped silently everywhere.
- Edit, tab Sending options (`LBL_SENDING_OPTIONS`, `tabs/SendingOptions.tpl`, pane `#editTabSendingOptions`; a general block for what is sent with the mail — a BCC copy is to follow; Edit.tpl shows the tab only for a non-theme template while an option is available): `#pdf_templates_select` (select2 with `data-width="100%"` so it renders in the hidden tab, sortable through `vtUtils.makeSelect2ElementSortable()` into the hidden `pdf_templates`, `;`-separated); `EMAILMaker_Edit_Js::loadPDFTemplates()` reloads the options from `action=PDFTemplates` when the module (tab Properties) changes. Detail: block "Sending options" with the PDF Maker templates row (`PDF_TEMPLATE_DATA`).
- Send window (`IndexAjax::showComposeEmailForm()`, `SelectEmailFields.tpl`): `EMAIL_PDF_TEMPLATES` (e-mail template id => offered PDF ids) on `#use_common_email_template` as `data-pdf-templates`; `EMAILMaker_Actions_Js::registerPDFTemplatePreselection()` opens the PDF block and adds them for the chosen (also the default) e-mail template, replaces them on another choice and leaves a PDF choice passed in from PDF Maker (`data-pdf-explicit`) alone. Language, merging and sending are unchanged (`pdftemplateid`).
- Deleting an e-mail template (`DeleteAjax`, `Delete`, `MassDelete`, `Module::deleteRecord()`/`deleteAllRecords()`) deletes its rows (module `EMAILMaker`, record_id). A deleted PDF template is skipped when read (PDF Maker itself is not changed; `Core_TemplateRelation_Model::deleteByRelated()` is ready for it).
- Translations: the labels of this feature (`LBL_SENDING_OPTIONS`, `LBL_PDF_TEMPLATES`, `LBL_PDF_TEMPLATES_HINT`, `LBL_SELECT_PDF_TEMPLATES`) exist in every locale; a locale without the full EMAIL Maker language file has a file with these labels only — every other key falls back to `$default_language` (en_us) through `Vtiger_Language_Handler::getTranslatedString()`.
