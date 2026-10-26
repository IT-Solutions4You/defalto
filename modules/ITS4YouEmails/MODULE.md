# Ownership

- ITS4YouEmails owns Emails records, composing, delivery, attachments and open/click tracking. EMAILMaker owns template rendering and MailManager owns IMAP mailboxes (see `modules/EMAILMaker/MODULE.md`); MailManager reply, reply all and forward are composed and sent here.
- `ITS4YouEmails_Record_Model` prepares content and delivers; `ITS4YouEmails_Mailer_Model` extends PHPMailer. Schema/setup belongs to `ITS4YouEmails_Install_Model`; the root module class declares the install/update version.

# Email composition and delivery

- `retrieveContent()` resolves EMAILMaker recipient content (when EMAILMaker is active), then assigns the processed HTML body and a separate plain-text `AltBody` to the mailer.
- `getSubjectForMode()` owns reply and forward subject prefixes for stored Emails and MailManager messages. It recognizes reply prefixes `Re`, `Aw`, `Odp`, `Sv` and forward prefixes `Fwd`, `Fw`, `Tr` (case-insensitive, optional whitespace), preserves an existing prefix, and otherwise adds `Re: ` or `Fwd: `.
- `Record::getSignature()` returns the stored user signature decoded and whole (no body extraction); the workflow email task's signature option appends it after workflow substitution. `IndexAjax::getUserSignature()` separately returns the current user's decoded signature for `resources/MassEdit.js` to insert into CKEditor. Both bypass workflow HTML field normalization.
- `getProcessedBody()` decodes, merges variables, sanitizes event attributes, removes scripts, converts tracking URLs and CSS, then appends branding/tracking markup. It does not apply blanket `nl2br()`. Decoding also happens in `getBody()`, EMAILMaker and workflow preparation; do not add another decoding pass without checking encoded literal text.
- For records with source `WF`, `getProcessedBody()` calls `Core_SimpleHtmlDom_Helper::convertParagraphBreaks()` after variable/CSS conversion and before branding/tracking. It removes br runs adjacent to paragraphs (after signature insertion and recipient template expansion); newline conversion alone cannot. Other sources keep their spacing. Signature HTML and document wrappers stay intact, and signatures are not deduplicated.
- `convertCssToInline()` and `InStyle::convert()` match `<style[^>]+>`, so bare `<style>...</style>` is not inlined while `<style type="text/css">...</style>` is. InStyle uses only the first matched style block and can apply fragment selectors globally; inspect actual output for margin/line-height rules and conflicting inline styles.
- `getProcessedAltBody()` replaces only `<p>`, `<br>` and `<br />` with newlines before stripping tags, so its spacing differs from the HTML part. Diagnose the MIME part actually displayed.
- Validate automatic signature appending, composer insertion, both EMAILMaker passes, final HTML, plain alternative and CSS variants. Never send diagnostic mail without authorization; an isolated harness with fixture data and a stubbed database can exercise the transformations.

# SMTP and send flags

- `ITS4YouEmails_Mailer_Model::setSMTP()` uses XOAUTH2 for any `Core_Auth_Model` provider (Google, Microsoft) when a refresh token is stored; the provider comes from `Core_Auth_Model::getProviderNameByServer()`. Microsoft 365 outgoing server: `tls://smtp.office365.com:587`.
- Runtime send flags are set with `set()`, never saved, and absent by default: `skip_save_email_to_sent_folder` skips the IMAP append to the Basic MailManager Sent folder (`saveEmailToSentFolder()`); `skip_in_reply_to` (`isInReplyToSkipped()`) skips `retrieveInReplyTo()` because the caller (MailManager Pro replies) sets `In-Reply-To`/`References` itself; `skip_smtp_retrieval` (`isSMTPRetrievalSkipped()`) keeps the SMTP server the caller set with `getMailer()->setSMTP()` (MailManager Pro shared mailboxes) instead of `retrieveSMTP()`.

# MailManager integration

- `MassEdit.js` `replyEmail()`/`replyAllEmail()`/`forwardEmail()` accept an optional `parentFolder` (the IMAP folder). `ComposeEmail::retrieveEmailInfoFromMailManager()` sets it on `MailManager_Message_Model` and `Utils::getMailManagerAttachments()` filters by `mfolder`, because IMAP UIDs are unique only per folder. Requires the `mfolder` column of `vtiger_mailmanager_mailattachments` (created by `MailManager_Install_Model`).

# Tracking

- `ITS4YouEmails_Tracker_Handler` counts opens and clicks also for mail without a related record (`parentId` 0, for example an address unknown to the CRM, e.g. from Mail Manager Pro); `its4you_emails_access.record_id` is then 0. `isEmailOpenedRecently()` ("opened within the last hour") checks the same `record_id` that `saveAccess()` writes.

# Attachment storage

- Related attachments and Documents are resolved through `Core_Storage_Model::getAttachmentFilePath()` before PHPMailer reads them. This supports legacy relative paths and configured absolute storage paths without prepending the application root twice.
- `MassSaveAjax` treats submitted attachment metadata as untrusted. Existing files are resolved again by attachment ID through `ITS4YouEmails_Attachment_Model`; access requires DetailView permission on a related CRM record or ownership of the MailManager attachment. Submitted Document IDs require Documents DetailView permission. Never restore request-provided attachment paths, stored names or MIME types as filesystem input.
- Embedded images keep their physical resolved path when attached to the outgoing message. Browser-visible template images must use the public attachment endpoint supplied by Core.

# Composer document selection

- `layouts/d1/modules/ITS4YouEmails/resources/MassEdit.js` owns the compose dialog's attachment UI; its record-document and CRM-document pickers are nested popups inside the compose modal.
- Open them through `app.helper.showPopup()` so the shared modal helper places the popup and backdrop above the composer, and keep `modal-open` on `body` while the compose modal remains visible after a picker closes.
