# Settings Vtiger

## Settings menu

- Navigation is owned by `Settings_Vtiger_Menu_Model` (`vtiger_settings_blocks`) and `Settings_Vtiger_MenuItem_Model` (`vtiger_settings_field`). The sidebar reads persisted items, not `$defaultMenuItemLinks` directly, so additions for existing installations need a migration through these model APIs; keep the default definition for new installations. `active = 0` means visible.
- `MenuItem_Model::installLink($label)` installs one missing link from `$defaultMenuItemLinks`: it returns an existing item without writes (including disabled or moved items), creates a missing declared item with its default block, sequence, description and pin state, and returns `false` for an unknown label. `createLinks()` keeps its bulk synchronization behavior.

## Summary widgets

- Summary Widgets is shared CRM functionality with no separate module version. Its default link is `LBL_SUMMARY_WIDGET_EDITOR` in `LBL_MODULE_MANAGER` (`index.php?parent=Settings&module=Vtiger&view=SummaryWidgets`), labelled from `languages/<locale>/Settings/Core.php`.
- The `SummaryWidgets` and `SummaryWidgetEdit` views and the `SummaryWidgetsAjax` action delegate persistence to `Settings_Vtiger_SummaryWidgets_Model`. Configuration is per source module in `vtiger_links` (`DETAILVIEWWIDGET`); even/odd sequences are the left/right columns. Registered widgets are disabled by a `block://SummaryWidgets` URL; custom list widgets are deleted when removed.
- Default widget definitions belong to `Core_Install_Model`; list widget data belongs to `Core_SummaryWidgetList_Model`. Editor assets are in `layouts/d1/modules/Settings/Vtiger/resources`.

## Company details and logo

- `Settings_Vtiger_CompanyDetails_Model` owns logo persistence, validation, fallback and removal (stored in `vtiger_organizationdetails.logoname`, files in `test/logo/`).
- Uploads are limited by the shared `upload_maxsize` through `Vtiger_Util_Helper::getMaxUploadSizeInBytes()` and to 5000 x 5000 pixels. The filename extension must be an allowed JPEG, PNG or GIF extension and match the detected image type. Keep the server and browser checks on the same byte limit.
- `getLogoPath()` returns the custom logo when it exists, otherwise `layouts/d1/resources/Images/login-logo.png`; use `hasCustomLogo()` for actions that apply only to an uploaded logo (for example the remove button in `CompanyDetails.tpl`).
- `CompanyDetailsSave` and `UpdateCompanyLogo` return AJAX responses; translated exceptions flow through the central WebUI handler and are shown by `CompanyDetails.js`. A saved logo is also copied to `test/logo/application.ico`; removing it clears `logoname`, deletes the file and restores the layout favicon (`layouts/d1/favicon/favicon.ico`) as `application.ico`.

## Validation

- Run `php -l` on changed PHP and the existing ScriptsValidator on changed resource JavaScript. For migrations, check repeat execution and preservation of unrelated settings items.

- The Settings Module Manager menu declares LBL_LINK_SETTINGS and installs it through MenuItem::installLink() in migration 20261127143000. Its distinct LayoutEditor LinkSettings view is independent of the field editor tabs. LinkSettingsPage.tpl owns the heading and module selector; LinkSettings.js owns selection and deletion events, inherits only shared Vtiger components and keeps view=LinkSettings in the URL. Module selection requests contentsOnly=1, replaces only the action list, and disables selection/deletion during loading. The default is the first translated module in alphabetical order; explicit unsupported module names retain the model permission error. LayoutEditor Index no longer exposes or renders a link-settings tab. Shared labels remain in Core locales. The standalone view sets its persisted menu field/block before shared preprocessing, preventing the LayoutEditor module-name fallback from selecting the field editor entry.
