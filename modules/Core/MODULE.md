# Core UI ownership

- The shared top bar renders global search through `layouts/d1/modules/Core/partials/TopbarSearch.tpl`, included by `partials/Topbar.tpl`.
- Module selection and filtering use `Vtiger_BasicSearch_Js` in `layouts/d1/modules/Vtiger/resources/BasicSearch.js`; search configuration belongs to GlobalSearch.
- Shared picker styling lives in `layouts/d1/skins/base/style.less` and its committed `style.css`. Keep the dropdown scrollable within the viewport, allowing for the extra search row below the `lg` breakpoint and using the shared header height variables.
