# Inventory Items

Inventory items are the product and service lines of inventory documents (Quotes, Sales Orders, Invoices and similar). When a selected product has an image, the item editor displays it.

The unit of an item (for example Hours, Carton or Pieces) comes from the product or service and is shown in your language in the item block, in the item window and in the review window. Units are translated with the translations of the product and the service.

## Read-only documents

When a document is read-only (or you are not allowed to edit it), its items are locked as well. The items block shows the lines and totals, but you cannot add, edit, duplicate, delete or reorder lines, nor change the overall discount, adjustment, price book, currency or region.

## Unit price permissions

If your profile may only view the unit price, the price of a selected product is filled in automatically and cannot be changed. A product without a catalogue price (price on request) still lets you enter a price.

## Adding items from the summary

The **Items** widget is the first widget in the right summary column of a quote, sales order, invoice or purchase order. A long item list scrolls inside the widget while its column headings and total remain visible; the grand total and margin stay below the list. The widget has an **Add** menu next to **Details**. Choose **Text** or an item type (Products, Services; only active modules are offered) to open the same item dialog as in the items block. After saving, the widget shows the new line and the updated totals and you stay in the summary. The overall discount of the document is applied to the new line. The menu is not shown when you are not allowed to edit the document.

## Creating a document from another document

Documents with items (for example a credit note created from an invoice) can be created from the detail view of the source document. Depending on the configuration, the action either opens the new document directly with all items copied, or first shows a window with the items of the source document.

In that window (titled **Review items**) you can change the quantity or the price of each product row, or tick **Remove** to leave the row out. New products cannot be added. Discounts and tax of the source rows are kept and recalculated for the changed values; a fixed discount amount is shown for information and is not changed. The window also shows the fields chosen by the administrator and all mandatory fields of the new document; mandatory fields must be filled in before saving. Click **Save** to create the new document immediately; you are taken to its detail view. If you close the window, nothing is created and the source document stays unchanged.

Administrators configure each conversion in **Settings > Inventory > Conversions**: whether the items are reviewed in the window, which item fields (quantity, price) can be edited, and which fields of the new document (for example subject or due date) appear at the top of the window. Values entered there are saved into the new document. The review in the window is switched on by default for every conversion.

The **Field mapping** button of each conversion lets an administrator decide which field of the source document fills which field of the new document. Fields with the same name are still copied automatically. The mapping reads from left to right: the field of the source document, then the field of the new document. After you choose the source field, only fields of a compatible type are offered as the target (for example a date can only go to a date field). Choose **Not copied** as the source for a field that should stay empty in the new document.

## Restricted conversion

If creating another document from a quote, sales order or invoice is restricted (for example while the quote waits for approval), the conversion links are hidden and the item review dialog shows the reason instead of creating the document.

When editing the document itself is restricted, its items cannot be added or changed either: the buttons for adding text, products and services are hidden and any attempt shows the reason of the restriction.

## Discount permissions

Discounts follow the field permissions of the **Inventory Items** module (Settings > Profiles), so a company can choose how discounts work for each profile:

- **No discounts at all:** deactivate the Discount field (or make it invisible in every profile). The discount is hidden in the item dialog and in the items block.
- **Hidden for some profiles:** make the Discount field invisible for that profile (for example sales representatives). Other profiles, such as a sales manager, still see and enter discounts. Prices and totals still include discounts already given.
- **Visible but not editable:** set the Discount field to read-only. The user sees the discount but cannot change it.
- **Anyone can set discounts:** keep the field editable for all profiles (default).

The overall discount button of the items block follows the same rule for the Overall Discount field, together with the Overall Discount field of the document itself. A user who cannot see it does not get the button, and a user who can only view it cannot change it.

Changing the quantity or price of a line never removes its existing discount: the discount stays and the line and totals are recalculated, even if the user cannot edit discounts. New lines take the overall discount already set on the document.
