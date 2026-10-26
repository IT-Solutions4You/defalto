# LayoutEditor

## Ownership

- LayoutEditor administers module fields, blocks and layout settings. `Settings_LayoutEditor_Index_View` exposes the modes `showFieldLayout` (default, `FieldsList.tpl`), `showHeaderFieldsLayout`, `showRelatedListLayout`, `showRelatedListSettings`, `showPopupSettings`, `showFieldEdit` and `showDuplicationHandling`.
- The editor uses `Settings_LayoutEditor_Module_Model`, `_Block_Model` and `_Field_Model`. Editor-specific presentation, such as `getFieldDataTypeLabel()`, belongs to the editor field model, not ordinary module field models. Persistence stays in these models.
- This shared Settings component has no independent module version or schema.

## Runtime model handling

- `Module_Model::getInstanceByName()` copies the module's metadata but resets the copied `fields` and `blocks` caches, so the editor loads its own models instead of reusing runtime module models.
- `Block_Model::getFields()` converts every field to a `Settings_LayoutEditor_Field_Model`, whether freshly loaded or pre-populated through `setFields()` (as `showFieldLayout()` does), and keeps existing editor instances.

## Custom fields

- `Block_Model::isAddCustomFieldEnabled()` blocks custom-field creation only in `LBL_TICKET_RESOLUTION` and `LBL_COMMENTS` (HelpDesk), `LBL_COMMENT_INFORMATION` (Faq) and `LBL_ITEM_DETAILS` of every inventory module from `InventoryItem_Utils_Helper::getInventoryItemModules()`. Item details are rendered by InventoryItem; other blocks of inventory modules must still allow document-level custom fields.
- `Module_Model::addField()` names the column `cf_<fieldid>` and stores it in the entity's `customFieldTable[0]` if defined, otherwise `vtiger_<module>cf`. Fields added to inventory modules always get quick-create value 3.

## Validation

- Run `php -l` on changed PHP. Check cold and pre-populated block fields, repeated access, and module conversion with populated runtime caches; editor field lists must expose editor-specific methods without modifying the original runtime module objects.
