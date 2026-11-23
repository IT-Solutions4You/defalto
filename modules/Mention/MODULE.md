# Mention

## Runtime ownership

- `Mention` is a non-entity extension. `Mention_Module_Model` selects accessible users, groups and active record owners. `Mention_Users_Action` requires an authenticated session, source-module access and record access when a record is supplied.
- `Mention_Comment_Handler` handles ModComments after-save events and delegates recipient selection and email creation to the module and record models. Delivery uses the existing ITS4YouEmails module and its SMTP configuration.
- The same candidate list determines suggestions and notification recipients. Group recipients are expanded through Settings Groups and deduplicated by user ID.
- Candidate display names are decoded once from the framework's HTML-encoded user/group model values before JSON serialization. The frontend inserts suggestions with `.text()` and escapes names when replacing comment HTML; never render decoded names as HTML. Mention usernames and group tokens retain their existing identifiers.
- Optional source-module selectors use `modules/Mention/models/<SourceModule>Users.php`, class `Mention_<SourceModule>Users_Model`, with `selectUsers($sourceModule, $sourceRecord, array $users): array`. Return candidates with `id`, `username`, `name`, `image`, `type`. Keep customer selectors out of shared packages.

## Database and lifecycle

- There are no Mention-owned data tables. Users, groups, comments and email records remain owned by their existing modules.
- `Mention_Install_Model` uses Core installation to register the non-entity module and version in `vtiger_tab`, the global header script and the ModComments event handler. Enable/update registers links and handlers; disable/uninstall unregisters both.
- Installation can rename a legacy ITS4YouMention registration while preserving its tab ID and permissions. It removes the legacy header script and event handler. Duplicate old/new module registrations stop migration for explicit reconciliation.
- Licensing and package entitlement remain owned by Installer. Do not restore the upstream ITS4YouInstaller runtime license checks.
- Module source and language files use the standard Defalto AGPL v3 header and the root `LICENSE-AGPLv3.txt`. Keep the MIT notice for the Composer-managed Mention.js dependency in `THIRD_PARTY_LICENSES.txt`. Legacy module names in `installTables()` are migration identifiers, not runtime license checks.

## UI and validation

- Module UI assets live under `layouts/d1/modules/Mention/resources`. Composer installs the unmodified Mention.js and bundled Typeahead dependency to `vendor/jakiestfu/mention`, pinned to an immutable upstream revision through the root package repository and lockfile. Install Composer dependencies when deploying; extension-only file copies cannot supply these assets.
- `Mention_Js` lazily retrieves suggestions once per page and loads the editor dependencies on demand. Email HTML is rendered by `Email.tpl`.
- Suggestions use the current Bootstrap dropdown utilities and theme variables, with a scoped adapter for the legacy typeahead row-active state. Upstream temporarily replaces the shared renderer during initialization; immediately restore it and set the safe Bootstrap renderer on the editor instance. Matching follows the mention at the caret. Lazy assets inherit the header script's resource-version query to invalidate cached CSS and JavaScript together.
- Run PHP lint on module and language PHP files; load module classes with their real parents to check inheritance. Run Node syntax checks on the JavaScript assets and the existing ScriptsValidator when available.
- Verify fresh installation, repeated installation, enable/disable registration, source-record access, list/detail suggestions and group recipient deduplication. Do not send diagnostic email without authorization.
