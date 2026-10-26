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
