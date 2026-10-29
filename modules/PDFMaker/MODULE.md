# Attachment images

- PDF generation reads user, contact, product and record images through `Core_Storage_Model` physical paths (`PDFContentUtils`). Both legacy relative attachment paths and configured absolute paths are supported. Do not replace them with browser URLs without validating the PDF renderer.
- Browser image-selection modals use `Core_Storage_Model::getAttachmentPublicUrl()` and must never expose a physical attachment path as an image URL.
- PDFs saved as Documents (`createPDFAndSaveFile`) and PDFMaker uploads go to the directory chosen by `decideFilePath()`, which is owned by the shared Core storage flow.

# Product block management

- Product-block list, edit, save and delete are owned by the Core Inventory Items Block components (`module=Core&view=ProductBlocks`, linked from Settings, Inventory). PDFMaker has no ProductBlocks view of its own. PDFMaker and PDFMaker Pro use the same compatibility routes as EMAILMaker.
- Templates are shared with EMAILMaker in `df_inventoryitems_block` (`Core_InventoryItemsBlock_Model`). The legacy `vtiger_pdfmaker_productbloc_tpl` table is still created by the installer but not used at runtime.
- Global rows with an empty module are available to every supported inventory document module. Core installs two global starter templates only when the shared table is empty.

# E-mail template links

- EMAILMaker links e-mail templates to PDFMaker templates in `df_template_rel`. `PDFMaker_PDFMaker_Model::getAvailableTemplates()` supplies the offered templates (user permissions, active status, display conditions). Details are in `modules/EMAILMaker/MODULE.md`.

# Languages

- PDFMaker owns its user-facing labels in `languages/<locale>/PDFMaker.php`; the same file also supplies labels rendered from PDFMaker Settings screens.
- Every locale shipped under `languages/` must provide the complete PDFMaker PHP and JavaScript key sets. Use `languages/sk_sk/PDFMaker.php` as the structural key reference and keep technical identifiers, URLs, file-system paths, HTML, placeholders, and product names unchanged while translating their surrounding text.

# PDF generation permissions

- `CreatePDFFromTemplate` and `IndexAjax::getPreviewContent` resolve the source module from the requested CRM record and require `DetailView` permission for that record before calling `PDFMaker_checkGenerate_Model::generate()`. Never trust the client-provided `source_module` or `relmodule` as the authorization owner.
- Keep direct downloads and inline or attachment preview content behind the same record-level permission boundary.

# Record action permissions (PdfExport)

- `PDFMaker_Module_Model::getPdfExportDenyReason()` is the only place that asks the core layer (`Core_RecordActionPermission_Model::ACTION_PDF_EXPORT`); it returns null when the class does not exist, so PDF Maker behaves as before on a core without the layer. Never call `Core_RecordActionPermission_Model` or render `Core/RecordActionDenied*.tpl` from PDF Maker without going through it.
- `PDFMaker_checkGenerate_Model::generate()` throws the reason before the PDF is built, which covers the download (`CreatePDFFromTemplate`) and the preview (`IndexAjax::getPreviewContent`). `PDFMaker_CreatePDFFromTemplate_Action` (`mode=checkRecords`) lets `PDFMakerFreeActions.js` (`checkExportableRecords()`) show the reason before downloading or previewing. The detail button template `GetPDFButtons.tpl` (DETAILVIEWBASIC `template:` link) and `PDFMaker_GetPDFActions_View` render `Core/RecordActionDeniedButton.tpl` (disabled button with the reason) or `Core/RecordActionDenied.tpl` for a denied record.
- PDFs attached to e-mails are filtered by ITS4YouEmails in the core (`removePdfDeniedRecords()`).
