# Email templates

EMAILMaker templates can include record fields and can use display conditions to control when configured content is shown.

When selecting a field for a display condition on an inventory document, the main record blocks are listed first and the Item Details fields are listed last. Select the field, comparison and value required by the template rule.

# Product block templates

Open Product Block Templates to manage reusable Inventory Item layouts. Choose the inventory document module, then create or edit a block and select the columns it should contain. The same templates are available in EMAILMaker, PDFMaker, and PDFMaker Pro. A new empty installation includes basic and detailed starter layouts.

# Attachment images

Images inserted from CRM records remain available when the physical attachment directory is outside the public web directory. Templates reference them through the CRM attachment endpoint instead of exposing the server filesystem path.

# PDF attachments from PDF Maker

When PDF Maker is installed and active, an e-mail template can bring its PDF documents along.

1. Open the e-mail template for editing. On the **Sending options** tab choose one or more **PDF Maker templates** of the template's module (only active templates you may use are offered). Drag them to set their order.
2. Save. The template detail lists them in the **Sending options** block.
3. When you send an e-mail from a record and choose this template (or it is your default), the **PDF templates** block opens with these PDFs already selected. You can remove them, add others, pick the PDF language or merge them into one file as before.

Notes:

- A PDF template that was deleted, switched off or that you are not allowed to use is skipped; the e-mail is still sent.
- Choosing another e-mail template replaces the PDFs the previous one brought; PDFs you added yourself stay.
- When you start the e-mail from PDF Maker with chosen PDFs, that choice is kept.
- When PDF Maker is not active, the field and the preselection are not shown.

# Sending options

The **Sending options** tab of a template holds what is sent together with the e-mail: the PDF Maker templates (when PDF Maker is active), **Load related documents** and **Related documents by folder** (the folders the related documents are taken from).
