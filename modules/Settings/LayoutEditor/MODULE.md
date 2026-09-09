# LayoutEditor

- LayoutEditor owns administration of module fields, blocks, and layout settings. `Settings_LayoutEditor_Index_View::showFieldLayout()` passes its module fields into blocks and renders `layouts/d1/modules/Settings/LayoutEditor/FieldsList.tpl`.
- The editor uses `Settings_LayoutEditor_Module_Model`, `Settings_LayoutEditor_Block_Model`, and `Settings_LayoutEditor_Field_Model`. Field presentation methods such as `getFieldDataTypeLabel()` belong to the editor field model, not ordinary module field models.
- `getInstanceByName()` copies module metadata but resets copied runtime field/block caches so editor-owned factories load the proper models. Block `getFields()` must normalize both freshly loaded and pre-populated fields, including fields assigned by `setFields()`, while preserving existing editor instances.
- Field/block persistence remains in the existing LayoutEditor models. This shared Settings component has no independent installable module version; runtime fixes require the application patch bump, not changes to downstream module versions or a schema migration.
- Validate changed PHP with `php -l`. Check cold and pre-populated block fields, repeated access, and module conversion with populated runtime caches; field lists must expose editor-specific methods without modifying the original runtime module objects.
