# Attachment storage

- Comment attachment metadata is stored in `vtiger_attachments` and linked through `vtiger_seattachmentsrel`. `ModComments_FilePreview_View` and `ModComments_Record_Model::getAttachmentDetails()` (used to prepare email attachments) resolve physical files through `Core_Storage_Model`, which accepts legacy relative paths and configured absolute paths.
- Browser downloads go through `ModComments_DownloadFile_Action`, which requires `DetailView` permission on the comment record.

# Creation permissions

- Interactive comment creation is authorized by `ModComments_Module_Model::getPermittedRelatedRecordId()`. It requires `CreateView` permission for ModComments, an active canonical `related_to` record, and record-level `DetailView` permission for the module derived from that record.
- `SaveAjax`, create-mode `Save`, `MassSaveAjax`, and MailManager comment creation must use this boundary before persisting. Trusted background creation flows remain outside this current-user UI authorization boundary.
- Comment edits authorize the existing related record through `getViewableRelatedRecordId()` and preserve `related_to` and `parent_comments`; editing comment text must not move a comment to another record or thread.
