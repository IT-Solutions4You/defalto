# Ownership across the email modules

- EMAILMaker owns e-mail templates (storage, editing, display conditions, links to PDF Maker templates) and placeholder rendering (`EMAILMaker_EMAILContent_Model`). It also owns the template and PDF selection in the send window (`IndexAjax::showComposeEmailForm()`, `SelectEmailFields.tpl`).
- ITS4YouEmails owns Emails records, composing, MIME/SMTP delivery, attachments and open/click tracking; it calls EMAILMaker rendering through `ITS4YouEmails_Record_Model::retrieveContent()`. See `modules/ITS4YouEmails/MODULE.md`.
- MailManager owns IMAP mailboxes and saved-message data; reply/forward composing and sending are delegated to ITS4YouEmails.
- The workflow email task (`modules/com_vtiger_workflow/tasks/VTEmailTask.inc`) renders through EMAILMaker, then creates ITS4YouEmails records with source `WF`.

# Email content rendering

- `VTEmailTask::getContents()` runs `getContent(false)` before workflow variable substitution; `ITS4YouEmails_Record_Model::retrieveContent()` runs recipient-aware `getContent(true, ...)` again before assigning the mailer body. Keep both passes in view when diagnosing formatting.
- Number formatting is owned by `EMAILMaker_Record_Model::getDecimalSettings()`. Stored module-wide settings take precedence; while `vtiger_emakertemplates_settings` is empty, rendering and numeric template functions use the current user's currency decimal count and separators.
- `getFieldDisplayValue()` delegates modern `text` fields to the field's `getDisplayValue()` (`Vtiger_Text_UIType` skips newline conversion for `isHtmlField()` fields such as `Users.signature`). Legacy user variables from `getUserValue()` keep their original values. Neither path removes HTML document wrappers.
- `getFieldValueUtils()` keeps the legacy encoded-br guard and converts textarea newlines through `Core_SimpleHtmlDom_Helper::convertNewlinesToHtml()`, which avoids extra breaks before literal or encoded paragraph tags. The private `EMAILContent::getFieldValue()` wrapper has no local caller.
- `getContent()` decodes entities on the combined subject/body and replaces `&nbsp;` with ordinary spaces. `handleRowbreak()` always runs; it only splits table cells at row-break tokens and is not a newline-to-br conversion. Spacing depends on paragraphs, breaks and CSS in the template.
- Validate formatting through both passes, modern and legacy signature variables, inline styles, encoded literal text, and the final ITS4YouEmails mailer body. Isolated method execution is not evidence of the HTML a configured workflow delivers.

# Product block management

- `EMAILMaker_ProductBlocks_View` is only a compatibility route adapter (`index.php?module=EMAILMaker&view=ProductBlocks`) for the Core Inventory Items Block manager (`Core_ProductBlocks_View`, also linked from Settings).
- Product-block templates are shared with PDFMaker and PDFMaker Pro in `df_inventoryitems_block` (`Core_InventoryItemsBlock_Model`). The obsolete `vtiger_emakertemplates_productbloc_tpl` storage is removed by `EMAILMaker_Install_Model::migrate()` and is not created on new installations.
- Global rows with an empty module are available to every supported inventory document module. Core installs two global starter templates only when the shared table is empty.

# Database lifecycle

- `EMAILMaker_Install_Model::migrate()` removes obsolete EMAIL Maker campaign/drip, mass-email, attachment/content, sequence, license/version, picklist, product-block and related-block tables that have no runtime owner. Related blocks and product blocks are owned by Core in `df_related_block` and `df_inventoryitems_block`.
- Keep `vtiger_emakertemplates_sent` and `vtiger_emakertemplates_emails` while the existing send/status flow still reads them.

# Display conditions

- `EMAILMaker_FilterRecordStructure_Model` takes blocks from `Core_FilterEditor_Model::getConditionBlocks()` for the source and referenced modules: ordinary blocks keep their order and `LBL_ITEM_DETAILS` is listed last. This changes only field ordering.

# Attachment images

- Browser-visible user, contact, product, record and company images use `Core_Storage_Model::getAttachmentPublicUrl()` instead of exposing `vtiger_attachments.path`, so templates work with attachment storage outside the web directory.
- PDFMaker renders from resolved physical paths; do not replace those with browser URLs without validating the PDF renderer.

# Template detail icons

- `EMAILMaker_EMAILMaker_Model::GetDetailViewData()` and the permission-denied box take `Enable.png`, `Disable.png` and `denied.gif` through `vimage_path()` (`layouts/d1/skins/images/`), like PDFMaker.
- `layouts/d1/modules/EMAILMaker/unsubscribeinfo/info_en_us.tpl` is not referenced by any code and points at `layouts/vlayout/...` images that do not exist.

# PDF Maker templates of an e-mail template

- Links live in `df_template_rel` (`Core_TemplateRelation_Model`: module `EMAILMaker`, related_module `PDFMaker`, `sequence` = order). `EMAILMaker_PDFTemplate_Model` reads and writes them only through that model and owns the PDF-specific rules. The table is created by Core (`Core_CreateTables_Config` -> `Core_TemplateRelation_Model::createTables()`). `vtiger_crmentityrel` is not used.
- Available only while PDF Maker is active (`EMAILMaker_Module_Model::isPDFMakerInstalled()`) and `df_template_rel` exists; otherwise the edit field, detail row and preselection are hidden and stored links stay untouched (`SaveEMAILTemplate` saves only when the form sent `pdf_templates_sent`).
- Offered templates come from `PDFMaker_PDFMaker_Model::getAvailableTemplates()` (current user's permissions and active status; display conditions when a record is given). Saving keeps only offered templates of the e-mail template's module; deleted, inactive or forbidden ones are skipped silently everywhere.
- Edit: the `LBL_SENDING_OPTIONS` tab (`tabs/SendingOptions.tpl`, pane `#editTabSendingOptions`, shown for every non-theme template) holds, in order, the PDF Maker templates (`#pdf_templates_select`, sortable through `vtUtils.makeSelect2ElementSortable()` into the hidden `;`-separated `pdf_templates`), `load_related_documents` and `folders_related_documents[]` (handled by `SaveEMAILTemplate` and `EMAILContent`; the detail view does not show them). `EMAILMaker_Edit_Js::loadPDFTemplates()` reloads the options from `action=PDFTemplates` and drops the chosen ones when the template's module changes. Detail: a "Sending options" block with the PDF Maker templates row (`PDF_TEMPLATE_DATA`).
- Send window: `EMAIL_PDF_TEMPLATES` (e-mail template id => offered PDF ids) is passed as `data-pdf-templates` on `#use_common_email_template`; `EMAILMaker_Actions_Js::registerPDFTemplatePreselection()` opens the PDF block and adds them for the chosen (also the default) template, replaces them when another template is chosen and leaves a PDF choice passed in from PDF Maker (`data-pdf-explicit`) alone. Language, merging and sending are unchanged (`pdftemplateid`).
- Deleting an e-mail template (`DeleteAjax`, `Delete`, `MassDelete`, `Module::deleteRecord()`/`deleteAllRecords()`) deletes its rows. PDF Maker does not remove links of a deleted PDF template; they are skipped when read (`Core_TemplateRelation_Model::deleteByRelated()` exists but is not called).
- The labels of this feature (`LBL_SENDING_OPTIONS`, `LBL_PDF_TEMPLATES`, `LBL_PDF_TEMPLATES_HINT`, `LBL_SELECT_PDF_TEMPLATES`) exist in every locale; a locale without the full EMAILMaker language file has a file with these labels only, and other keys fall back to `$default_language` through `Vtiger_Language_Handler::getTranslatedString()`.
