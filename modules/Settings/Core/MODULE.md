# Core settings

## Settings labels

- Settings/Core language files describe shared administrator settings such as outgoing mail configuration.
- User-facing server, support, documentation, and product references must use Defalto ownership. Do not restore upstream vtiger service domains or support addresses.
- External product destinations belong to `Core_Redirect_Model`; settings templates should link through the corresponding `Core/Redirect` mode instead of embedding a destination URL.

## Validation

- Run PHP lint for every changed `languages/<locale>/Settings/Core.php` file.

## Payment integration settings

- `Settings_Core_PaymentIntegration_View`, `Settings_Core_PaymentIntegrationSave_Action` and `Settings_Core_PaymentModule_Model` are the abstract bases of the integration settings page of a payment module (`index.php?module=<Module>&parent=Settings&view=Index`); each payment module has empty subclasses `Settings_<Module>_Index_View`, `Settings_<Module>_IntegrationSave_Action` and `Settings_<Module>_Module_Model`. The module is taken from the class name (`Core_PaymentConfig_Helper::getForClass()`), see the Payments base section of `modules/Core/MODULE.md`.
- Template `layouts/d1/modules/Settings/Core/PaymentIntegration.tpl`, labels from the payment module's Settings language file (`QUALIFIED_MODULE`). Script `layouts/d1/modules/Settings/Core/resources/PaymentIntegration.js` defines `Settings_Vtiger_PaymentIntegration_Js` (same naming as `Taxes.js`); the view loads it before the module's `resources/Index.js`, which only declares `Settings_<Module>_Index_Js` as its subclass, the page controller Defalto looks up by module name. The script posts to the current module (`app.getModuleName()`).
