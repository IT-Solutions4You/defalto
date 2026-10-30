# Core settings

## Settings labels

- Settings/Core language files describe shared administrator settings such as outgoing mail configuration.
- User-facing server, support, documentation, and product references must use Defalto ownership. Do not restore upstream vtiger service domains or support addresses.
- External product destinations belong to `Core_Redirect_Model`; settings templates should link through the corresponding `Core/Redirect` mode instead of embedding a destination URL.

## Validation

- Run PHP lint for every changed `languages/<locale>/Settings/Core.php` file.
