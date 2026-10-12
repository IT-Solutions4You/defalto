# Products

## Runtime ownership

- Products is an inventory entity stored in vtiger_products, vtiger_productcf and the shared vtiger_crmentity table. Products.php declares the installable module version; Products_Install_Model extends Core_Install_Model and owns setup and related-list defaults, including PriceBooks, product bundles and inventory documents.
- Products_Detail_View extends the shared Detail view. Products_RecordQuickPreview_View extends Vtiger_RecordQuickPreview_View and overrides getQuickPreviewHeaderScripts() to load PriceBooks Detail/RelatedList before Products Detail/RelatedList. Declare modules.Core.resources.QuickPreview last in this list; do not append resources separately in process() or templates.
- Module UI assets belong under layouts/d1/modules/Products/resources. Preview rendering, scoped initialization and cleanup use the shared Vtiger/Core lifecycle. Shared Detail deletion owns confirmation and the request; the registered Core handler owns successful modal closure and source refresh. Preserve standard record permissions and deletion URLs.
- Preview dependency changes require no schema migration. Lint touched PHP files and load the real Detail/RecordQuickPreview/Products hierarchy to check method compatibility and dependency order. Exercise preview open, close, reopen and delete; check that PriceBooks dependencies precede Products and Core is included exactly once at the end.
