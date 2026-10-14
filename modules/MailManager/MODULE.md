# Attachment storage

- MailManager upload actions use the shared `decideFilePath()` attachment directory. Saved-message attachment reads and cleanup resolve stored relative or absolute paths through `Core_Storage_Model::getFilePath()`.
- `vtiger_mailmanager_mailattachments.path` follows the same physical storage policy as `vtiger_attachments.path`, while its table and message lifecycle remain owned by MailManager.
