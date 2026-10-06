# Users ownership

- `Users` owns user records in `vtiger_users`; `Users_Install_Model` owns field/schema setup, and `Users::$moduleVersion` is the install/update version.
- User signature editing uses `layouts/d1/modules/Users/resources/Edit.js::registerSignatureEvent()` to attach CKEditor to `signature`, although its install metadata uses uitype 21. `Users/actions/Save.php` and `SaveAjax.php` sanitize signature HTML before persistence; do not replace that sanitization with content-format detection.
- `Users_Field_Model::$htmlFields` contains only `signature`. The inherited `isHtmlField()` exposes its HTML semantics to workflow substitution without changing other user fields or editor behavior. `ITS4YouEmails_Record_Model::getSignature()` separately retrieves the decoded signature for automatic appending.
- The signature editor inherits CKEditor full-page mode. Workflow preserves the complete value of fields marked as HTML, including html/head/body wrappers, styles and explicit breaks; it does not normalize those wrappers.
- Validate field-model inheritance, signature classification, plain-text fallback, and workflow HTML preservation with PHP lint and an isolated manual harness; verify final spacing in a delivered workflow email when a configured runtime is available.
