# Email composition and delivery

- `ITS4YouEmails_Record_Model` owns email content preparation and delivery; `ITS4YouEmails_Mailer_Model` extends PHPMailer. `retrieveContent()` resolves EMAILMaker recipient content before assigning processed HTML and a separate plain-text alternative to the mailer. Module schema/setup belongs to `ITS4YouEmails_Install_Model`; the root module declares its install/update version.
- `ITS4YouEmails_Record_Model::getSubjectForMode()` owns reply and forward subject prefixes for both stored Emails and MailManager messages. It recognizes localized reply prefixes (`Re`, `Aw`, `Odp`, `Sv`) and forward prefixes (`Fwd`, `Fw`, `Tr`) case-insensitively with optional whitespace, preserves an existing prefix, and otherwise adds the canonical `Re: ` or `Fwd: ` prefix.
- `Record::getSignature()` retrieves and decodes the stored user signature without extracting its body. Workflow's separate signature option appends it after workflow substitution. `IndexAjax::getUserSignature()` separately returns the current user's decoded signature for `resources/MassEdit.js` to insert into CKEditor. These paths bypass workflow HTML field normalization.
- `getProcessedBody()` decodes, merges variables, sanitizes event attributes, removes scripts, converts tracking URLs and CSS, then appends branding/tracking markup. It does not apply blanket `nl2br()`. Repeated decoding also occurs in `getBody()`, EMAILMaker and workflow preparation; do not add another decoding pass without checking encoded literal text.
- For records with source `WF`, `getProcessedBody()` calls `Core_SimpleHtmlDom_Helper::convertParagraphBreaks()` after variable/CSS conversion and before branding/tracking. This removes existing br runs adjacent to paragraphs after both signature insertion and recipient template expansion; newline conversion alone cannot remove those tags. Other email sources retain their original spacing. Full signature HTML and document wrappers remain intact, and signatures are not deduplicated.
- `convertCssToInline()` and `InStyle::convert()` currently require characters between `style` and `>` in their matching pattern. Bare `<style>...</style>` is not inlined, while `<style type="text/css">...</style>` is. InStyle extracts only the first matched style block and can apply fragment selectors globally; inspect actual output for margin/line-height rules and conflicting inline styles.
- `getProcessedAltBody()` replaces a limited set of paragraph/br spellings before stripping tags; its spacing differs from the HTML part. Diagnose the MIME part actually displayed rather than treating plain-text conversion as an HTML mutation.
- Validate automatic signature appending, composer insertion, both EMAILMaker passes, final HTML, plain alternative and CSS variants. Never send diagnostic mail without authorization; an isolated harness can exercise transformations with fixture data and a stubbed database.

# Attachment storage

- Related attachments and Documents are resolved through `Core_Storage_Model::getAttachmentFilePath()` before PHPMailer reads them. This supports both legacy relative paths and configured absolute storage paths without prepending the application root twice.
- Embedded images retain their physical resolved path when attached to the outgoing message. Browser-visible template images must use the public attachment endpoint supplied by Core.

# Composer document selection

- `layouts/d1/modules/ITS4YouEmails/resources/MassEdit.js` owns the compose dialog's attachment UI. Its record-document and CRM-document pickers are nested popups inside the compose modal.
- Open those pickers through `app.helper.showPopup()` so the shared modal helper assigns the popup and backdrop above the composer. When the picker closes, retain `modal-open` while the compose modal remains visible.
