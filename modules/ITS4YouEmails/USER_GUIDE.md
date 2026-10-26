# Emails

The Emails module composes and sends CRM email, supports EMAILMaker templates, related records, file attachments and embedded images. Reply, Reply all and Forward in Mail Manager open the same composer.

When replying, the composer adds `Re: ` only when the subject does not already contain a recognized reply prefix. When forwarding, it adds `Fwd: ` unless a recognized forward prefix is already present.

# Attachments

Use the attachment menu in the composer to select a file from all CRM Documents or from Documents related to the current record. The document picker opens above the composer; select the required documents and confirm the selection to attach them without leaving the email.

When an existing attachment is reused in a reply or forwarded message, the server verifies that you can still access its related CRM record or that the attachment belongs to your Mail Manager mailbox. If access has been removed, the email is not sent with that attachment.

Physical attachment storage may be configured outside the public web directory. Email sending resolves both older relative paths and newer absolute paths automatically. Administrators must include the configured attachment directory in backups.
