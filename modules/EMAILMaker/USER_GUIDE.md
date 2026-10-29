# Email templates

EMAILMaker templates can include record fields and can use display conditions to control when configured content is shown.

Amounts and other numeric fields use the decimal count and separators configured in the template's **Settings** tab. Before these shared settings are saved for the first time, EMAILMaker uses the current user's currency formatting preferences.

In the **Display conditions** section of a template (**Edit Conditions**), the main record blocks are listed first and the **Item Details** fields of an inventory document are listed last. Select the field, comparison and value required by the template rule.

# Product block templates

Open **Product block templates** (Settings, Inventory section) to manage reusable Inventory Item layouts. Choose the inventory document module, then create or edit a block and select the columns it should contain. The same templates are available in EMAILMaker, PDFMaker, and PDFMaker Pro. A new empty installation includes basic and detailed starter layouts.

# Attachment images

Images inserted from CRM records remain available when the physical attachment directory is outside the public web directory. Templates reference them through the CRM attachment endpoint instead of exposing the server filesystem path.

# Sending options and PDF attachments

The **Sending options** tab of a template holds what is sent together with the e-mail: the **PDF Maker templates** (only when PDF Maker is installed and active), **Related documents load** and **Related documents by folders** (the folders the related documents are taken from).

To bring PDF documents along with an e-mail template:

1. Open the e-mail template for editing. On the **Sending options** tab choose one or more **PDF Maker templates** of the template's module (only active templates you may use are offered). Drag them to set their order. Changing the template's module clears the choice.
2. Save. The template detail lists them in the **Sending options** block.
3. When you send an e-mail from a record and choose this template (or it is your default), the PDF selection opens with these PDFs already selected. You can remove them, add others, pick the PDF language or merge them into one file as before.

Notes:

- A PDF template that was deleted, switched off or that you are not allowed to use is skipped; the e-mail is still sent.
- Choosing another e-mail template replaces the PDFs the previous one brought; PDFs you added yourself stay.
- When you start the e-mail from PDF Maker with chosen PDFs, that choice is kept.
- When PDF Maker is not active, the PDF field and the preselection are not shown.

# Restricted email sending

If sending emails is restricted for a record (for example while it waits for approval), the **Send Email** button in its detail is disabled with a lock; hover over it to see the reason.
