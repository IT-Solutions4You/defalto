# Inventory Items

Inventory items represent product and service lines used in inventory documents. When a selected product has an image, the inventory editor displays it through the CRM attachment endpoint, including when physical attachment storage is outside the public web directory.

## Creating a document from another document

Documents with items (for example a credit note created from an invoice) can be created from the detail view of the source document. Depending on the configuration, the action is either hidden, opens the new document directly with all items copied, or first shows a window with the items of the source document.

In that window you can change the quantity or the price of each product row, or tick **Remove** to leave the row out. New products cannot be added. Discounts and tax of the source rows are kept and recalculated for the changed values; a fixed discount amount is shown for information and is not changed. Click **Continue** to open the new document, review it and save. If you close the new document without saving, nothing is created and the source document stays unchanged.

Administrators configure each conversion in **Settings > Inventory > Conversions**: whether the action is shown, whether the items are reviewed in the window, which item fields (quantity, price) can be edited, and which fields of the new document (for example subject or due date) appear at the top of the window. Values entered there are carried into the new document's form.

The **Field mapping** button of each conversion lets an administrator decide which field of the source document fills which field of the new document. Fields with the same name are still copied automatically. Choose **Not copied** for a field that should stay empty in the new document.
