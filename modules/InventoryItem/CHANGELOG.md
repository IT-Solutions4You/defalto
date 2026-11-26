# Changelog

## 1.11 - 2026-11-26

- The simplified product block has the Currency, Tax Region and Price Book selects of the document in the header of the block, with the same look as the standard block (button groups with a dropdown). Changing the currency or the price book asks for a confirmation and reloads the prices of the product and service rows from the price book (new rows take the price from the selected price book); the price book list is filtered by the currency. Changing the tax region reloads the taxes of the rows.
- The Total column of a row now includes the tax. The row total takes the item discount and the overall discount into account, so the sum of the rows equals the totals of the document. The discounts themselves are not editable in the simplified block, existing values are kept and applied.
- The unit of a row is a select (the units of the product or the service, translated) and is filled when a product or service is selected, like in the standard block.
- Every product and service row has a description on its own line: a textarea of one line which grows to 4 lines when it has focus and shrinks back when it loses it. Text rows have no description. The detail view and the summary widget show the description under the item.
- The purchase cost of the product is stored in the item when a product is selected (converted to the currency of the document) and the margin is calculated on save. The purchase cost and the price book of each row are saved together with the item although they are not visible. The totals of the document (price after discount, tax, purchase cost, margin, grand total) are recalculated from the items on every save.

## 1.10 - 2026-11-22

- New setting Settings > Inventory Items > Product block: switch between the standard product block and the simplified product block.
- In the simplified product block the items are added, edited and removed directly in the edit view of the record (item name, quantity, unit, price and total only). The detail view and the summary widget show the same five columns read-only, without the Add menu or editing.
- The new table `df_inventoryitem_settings` stores the setting; it is created on install and module update.
- Saving the product block setting shows the standard success notification in the top right corner (the page is no longer reloaded).
- The edit view of the simplified product block has the same Text / Products / Services buttons as the standard block, shown by the same rule (the user can edit the document and the module is an active item module). A button adds a row instead of opening a dialog.
- A Products or Services row has the ajax autocomplete of the standard block: choosing a record fills the item name, quantity (when empty), unit and price (by the currency and price book of the document), and keeps the link to the record. This also works when the document is created.
- A new document starts with no row: the user chooses whether the first row is a text, a product or a service.
- A text row has the full width of the table and only the text (no quantity, unit, price or total).
- A Products or Services row must be chosen from the list of records; the document cannot be saved with an unselected row.
- Taxes defined in Settings are carried over to the simplified block. When the company uses taxes, the rows get a Tax column (the taxes of the selected product, depending on the tax region of the document, or no tax) and the totals show the price without VAT, the VAT and the price with VAT. The tax of a row is saved the same way as in the standard block. A company without taxes keeps the five columns and a single total.
- The unit and tax selects of the rows use select2 (searchable), like the selects in the rest of the system.

## 1.9 - 2026-11-06

- Discounts in the items block and in the item dialog now follow the profile permissions of the Inventory Items fields: a discount can be hidden completely, shown read-only, or left editable for each profile.
- The Overall Discount button is hidden or disabled for profiles without permission, and changing the overall discount is refused on the server without it.
- Changing the quantity or price of a line keeps its existing discount and recalculates the totals, even when the user cannot edit discounts.

## 1.8 - 2026-10-30

- The Items widget now appears first in the right summary column, uses the user's language for its title, and keeps long item lists compact with visible headings and totals.
- When a record rule restricts converting a document (record action permissions, for example while a quote waits for approval), the conversion links in the detail view are hidden and the item review dialog and its save refuse the conversion with the reason.
- When editing a document is restricted, its items block no longer shows the Text / Products / Services buttons, and changing items is refused with the reason of the restriction instead of a general permission message.

## 1.7 - 2026-10-27

- The item review dialog shown before copying items to another document no longer repeats an introductory text above the table, and its table header has the same background as list view headers. When a Purchase Order is created, the billing and shipping address fields are shown without the "Which address should be copied?" selector.

## 1.6 - 2026-10-26

- The Items widget in the summary has an Add menu (Text, Products, Services) that opens the item dialog; after saving only the widget with its totals is reloaded.

## 1.5 - 2026-10-26

- Items of a read-only document (or a document you are not allowed to edit) can no longer be added, changed, deleted, reordered or recalculated. The item dialog does not open and the buttons for changing items, overall discount, adjustment, price book, currency and region are hidden or disabled in the items block.
