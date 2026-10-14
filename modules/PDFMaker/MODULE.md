# Attachment images

- PDF generation reads user, contact, product and record images through `Core_Storage_Model` physical paths. Both legacy relative attachment paths and configured absolute paths are supported.
- Browser image-selection modals use `Core_Storage_Model::getAttachmentPublicUrl()` and must never expose a physical attachment path as an image URL.
- PDFMaker attachment uploads continue through `decideFilePath()`, which is owned by the shared Core storage flow.
