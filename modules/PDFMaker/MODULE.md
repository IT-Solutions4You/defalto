# Attachment images

- PDF generation reads user, contact, product and record images through `Core_Storage_Model` physical paths. Both legacy relative attachment paths and configured absolute paths are supported.
- Browser image-selection modals use `Core_Storage_Model::getAttachmentPublicUrl()` and must never expose a physical attachment path as an image URL.
- PDFMaker attachment uploads continue through `decideFilePath()`, which is owned by the shared Core storage flow.

# Product block management

- Product-block list, edit, save, and delete behavior is owned by the Core Inventory Items Block components. PDFMaker and PDFMaker Pro use the same compatibility routes as EMAILMaker.
- Product-block templates are shared with EMAILMaker in `df_inventoryitems_block`. The legacy `vtiger_pdfmaker_productbloc_tpl` table is not used by the runtime editor.
- Global rows with an empty module are available to every supported inventory document module. Core installs two functional global starter templates only when the shared table is empty.
