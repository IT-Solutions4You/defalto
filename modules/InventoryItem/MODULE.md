# Inventory item images

- `InventoryItem_GetItemDetails_Action` obtains product image URLs through `Core_Storage_Model::getAttachmentPublicUrl()`. Never expose `vtiger_attachments.path` as a browser URL because attachment storage may be outside the web directory.
- Product image metadata remains owned by `Products_Record_Model`; InventoryItem only prepares item data for inventory editors.

# Read-only unit price and hidden purchase cost

- The edit record structure leaves out fields the profile may only view. `InventoryItem_PopupItemEdit_View` adds `price` back when it is viewable (`PRICE_READONLY`); an existing item with a catalogue price above 0 (`decideItemPriceAndPriceBook()` with the parent record's currency and price book) is `PRICE_LOCKED`. `PopupItemEdit.tpl` marks the input (`data-price-readonly`, `readonly`) and hides the price book picker; `InventoryItemDetail.js` `mapResultsToFields()` locks the price for a catalogue price and leaves it editable for a price on request (0).
- `CRMEntity::insertIntoEntityTable()` writes only fields with `readonly = 0` for a non-admin. `InventoryItem_SaveFromPopup_Action::setReadOnlyPrices()` sets a non-editable `price` (catalogue price for a new item or a changed product, otherwise the stored one; a catalogue price of 0 keeps the entered price) and a non-editable `purchase_cost` (`getItemPurchaseCost()`, converted to the parent's currency like `GetItemDetails`), then sets `isWorkFlowFieldUpdate` so the entity saves them. A price sent from the browser for a locked item is ignored.
- The items block of the parent detail (`modifiers/DetailView.php`) only displays values, so its columns follow the view permission, not the edit structure.
- Not covered here: `GetItemDetails` and the `SaveFromPopup` response still send the purchase cost and margin to users without permission to those fields.

# Parent record permissions

- Items are separate `InventoryItem` records whose own permissions know nothing about the parent document (read-only flag, sharing rules, profile). Every client entry point that changes items checks `EditView` of the parent through `InventoryItem_ParentEntity_Model::requireEditableParent()` in `checkPermission()`: `SaveFromPopup` (`source_record`), `SaveItemsBlockDetail` (`for_record`), `DeleteProductLine` and `SortLineItems` (parent of each item, resolved by `InventoryItem_Module_Model::fetchParentIds()`) and the `PopupItemEdit` view (`source_record`). A new entry point that writes items must do the same.
- An existing item sent with a request must belong to the parent record the request is authorized for (`requireItemsOfParent()`); an id that is not an item is refused.
- `SaveItemsBlockDetail` runs only the methods listed in `$allowedModes`; never dispatch the `mode` request value to an arbitrary method.
- The detail items block gets `ITEMS_EDITABLE` from the detail modifier and renders `data-items-readonly`; `InventoryItemDetail.js` then hides or disables the editing controls. This is convenience only, the server check is authoritative.

# Parent record permissions

- Items are separate `InventoryItem` records whose own permissions know nothing about the parent document (read-only flag, sharing rules, profile). Every client entry point that changes items checks `EditView` of the parent through `InventoryItem_ParentEntity_Model::requireEditableParent()` in `checkPermission()`: `SaveFromPopup` (`source_record`), `SaveItemsBlockDetail` (`for_record`), `DeleteProductLine` and `SortLineItems` (parent of each item, resolved by `InventoryItem_Module_Model::fetchParentIds()`) and the `PopupItemEdit` view (`source_record`). A new entry point that writes items must do the same.
- An existing item sent with a request must belong to the parent record the request is authorized for (`requireItemsOfParent()`); an id that is not an item is refused.
- `SaveItemsBlockDetail` runs only the methods listed in `$allowedModes`; never dispatch the `mode` request value to an arbitrary method.
- The detail items block gets `ITEMS_EDITABLE` from the detail modifier and renders `data-items-readonly`; `InventoryItemDetail.js` then hides or disables the editing controls. This is convenience only, the server check is authoritative.

# Items widget in the summary

- The summary widget (`InventoryItem_Widget_View::showItems`, `ItemsWidget.tpl`) is registered with `headerActions=1` in `InventoryItem_Install_Model::$registerSummaryWidgets`; `updateSummaryWidgets()` rewrites the stored link URL on install and module update. `Vtiger_DetailView_Model` sets `headerActionsModule` from it and the shared `SummaryViewWidget.tpl` includes `SummaryWidgetHeaderActions.tpl` of that module.
- `layouts/d1/modules/InventoryItem/SummaryWidgetHeaderActions.tpl` renders the "Add" menu (Text, then `InventoryItem_ItemModules_Model::getItemModules()`) only when `InventoryItem_ParentEntity_Model::isParentEditable()` allows it and not in the list quick preview (`LIST_PREVIEW`). It carries `getItemPopupValues()` (currency, price book, region, overall discount of the parent) as data attributes, because the popup code otherwise reads them from the items block.
- `InventoryItemDetail.js`: `showItemPopup()` opens `PopupItemEdit` for the block (add, edit, duplicate) and for the widget; `getParentRecordValue()` / `getOverallDiscount()` read the block inputs, otherwise the widget data attributes. Without the items block `reloadInventoryItemsBlock()` reloads only the items widget (`Vtiger_Detail_Js::loadWidget()`), so the user stays in the summary.
