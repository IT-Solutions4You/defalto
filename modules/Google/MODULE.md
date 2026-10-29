# Google module

## Ownership and runtime

- `modules/Google` owns Google Contacts and Calendar OAuth, synchronization, map address lookup, settings, and installation of Google-owned tables.
- OAuth 2.0 is implemented by `Google_Oauth2_Connector`. The `Google_List_View` popup starts authorization and synchronization; Google redirects to `Google_Authenticate_View` through `Google_Config_Connector::getRedirectUrl()`.
- OAuth authorization requests use a cryptographically random, single-use `state` value stored in the authenticated CRM session. The callback must consume and match that value before exchanging a code or handling a denial.
- Map requests enter through `Google_MapAjax_Action`, require read validation, and resolve configured address fields through `Google_Map_Helper`. Request-derived module names must always be passed to database queries as bound parameters.

## Persistent data and contracts

- `vtiger_google_oauth2` stores per-user access and refresh tokens for `GoogleContacts` and `GoogleCalendar`.
- `vtiger_google_sync`, `vtiger_google_sync_settings`, and `vtiger_google_sync_fieldmapping` store synchronization state and configuration.
- `vtiger_google_event_calendar_mapping` maps CRM appointments to Google Calendar events, while `vtiger_google_map` maps supported CRM modules to address fields.
- The Google OAuth redirect URI configured at Google must exactly match the URL returned by `Google_Config_Connector::getRedirectUrl()`.

## UI and lifecycle

- Settings UI assets are under `layouts/d1/modules/Google`; popup synchronization uses `Contents.tpl`, `ContentDetails.tpl`, and `resources/List.js`.
- `Google_Install_Model` owns Google table creation and the settings link. The built-in Google module is excluded from Installer extension-package versioning and has no independent `$moduleVersion`.

## Validation

- Run `php -l` on touched Google PHP files.
- Run `node --check layouts/d1/modules/Google/resources/List.js` after changing the popup controller.
- Run the scripts validator at `index.php?module=ITS4You&mode=ScriptsValidator&only_errors=1` after changing Google resource JavaScript.
- Verify that the OAuth authorization URL contains `state`, callbacks with a missing or mismatched state fail before token exchange, and a matching state is accepted only once.
