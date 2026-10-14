# Attachment storage

- Comment attachment metadata is stored in `vtiger_attachments`. Preview and email attachment preparation resolve physical files through `Core_Storage_Model`, supporting legacy relative paths and configured absolute paths.
- Browser downloads remain permission-checked through the existing ModComments download action.
