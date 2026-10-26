# Attachment storage

- Comment attachment metadata is stored in `vtiger_attachments` and linked through `vtiger_seattachmentsrel`. `ModComments_FilePreview_View` and `ModComments_Record_Model::getAttachmentDetails()` (used to prepare email attachments) resolve physical files through `Core_Storage_Model`, which accepts legacy relative paths and configured absolute paths.
- Browser downloads go through `ModComments_DownloadFile_Action`, which requires `DetailView` permission on the comment record.
