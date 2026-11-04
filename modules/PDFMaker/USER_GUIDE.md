# PDF Maker

PDF Maker generates PDF documents from CRM templates and can include images from users, contacts, products, and related records. These images remain available when attachment storage is configured outside the public web directory. Image-selection dialogs display files through the CRM attachment endpoint.

PDF downloads and previews follow the CRM record permissions. A user can generate a PDF only for a record they are allowed to view.

If PDF export of a record is restricted (for example while the record waits for approval), the **Export to PDF** button is disabled with a lock; hover over it to see the reason. A download or preview of that record shows the reason instead of a PDF.

PDF Maker follows the language selected in Defalto. All languages included with the application provide the complete PDF Maker interface, including its Settings pages, template editor, installation messages, confirmations, and validation notices.

Only users whose profile allows PDF Maker editing can save changes to templates. Template previews display the document layout in an isolated frame, so embedded scripts are not run in the CRM interface.

## Product block templates

Open **Product block templates** (Settings, Inventory section) to manage reusable Inventory Item layouts. Choose the inventory document module, then create or edit a block and select the columns it should contain. PDFMaker, PDFMaker Pro, and EMAILMaker use the same templates. A new empty installation includes basic and detailed starter layouts.
