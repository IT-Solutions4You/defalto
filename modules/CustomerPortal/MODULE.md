# Attachment storage

- Customer Portal image encoding and file downloads resolve `vtiger_attachments` rows through `Core_Storage_Model::getAttachmentFilePath()`.
- Both legacy relative attachment paths and configured absolute storage paths are supported. Never prepend `root_directory` directly to an attachment path without first checking whether it is absolute.
