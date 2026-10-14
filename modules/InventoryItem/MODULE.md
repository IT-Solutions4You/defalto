# Inventory item images

- `InventoryItem_GetItemDetails_Action` obtains product image URLs through `Core_Storage_Model::getAttachmentPublicUrl()`. Never expose `vtiger_attachments.path` as a browser URL because attachment storage may be outside the web directory.
- Product image metadata remains owned by `Products_Record_Model`; InventoryItem only prepares item data for inventory editors.
