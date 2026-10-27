# Ownership

- MailManager owns IMAP mailbox access, the mailbox UI and saved-message data (`vtiger_mailmanager_mailrecord`, `vtiger_mailmanager_mailattachments`, `vtiger_mailmanager_mailrel`). `MailManager_Install_Model` creates these tables, including the `mfolder` column.
- Reply, Reply all and Forward are not composed here. `MailOpen.tpl` calls `ITS4YouEmails_MassEdit_Js.replyEmail()`, `replyAllEmail()` and `forwardEmail()` with the IMAP folder, and ITS4YouEmails composes and sends. See `modules/ITS4YouEmails/MODULE.md`.
- `MailManager_Relate_Action::associate()` relates a message to a CRM record by creating an ITS4YouEmails record and linking it with `relateEntities()`; `vtiger_mailmanager_mailrel` stores the message-to-record association.
- Email template rendering belongs to EMAILMaker (see `modules/EMAILMaker/MODULE.md`).

# Attachment storage

- MailManager upload actions use the shared `decideFilePath()` attachment directory. Saved-message attachment reads and cleanup resolve stored relative or absolute paths through `Core_Storage_Model::getFilePath()`.
- `vtiger_mailmanager_mailattachments.path` follows the same physical storage policy as `vtiger_attachments.path`, while its table and message lifecycle remain owned by MailManager.
