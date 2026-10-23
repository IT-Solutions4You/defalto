# Email content ownership

- `EMAILMaker_EMAILContent_Model` owns email template rendering. `VTEmailTask::getContents()` runs `getContent(false)` before workflow variable substitution; `ITS4YouEmails_Record_Model::retrieveContent()` runs recipient-aware `getContent(true, ...)` again before assigning the mailer body. Keep both passes in view when diagnosing formatting.
- `getFieldDisplayValue()` delegates modern text fields to their field display method. Vtiger_Text_UIType checks isHtmlField(), preserving configured HTML fields such as Users.signature without newline conversion; EMAILMaker retains its existing decoding pass. Legacy user variables from getUserValue() still pass through their original values. Neither path removes HTML document wrappers.
- `getFieldValueUtils()` retains the legacy encoded-br guard, but delegates textarea newline conversion to Core_SimpleHtmlDom_Helper::convertNewlinesToHtml() to avoid extra breaks before literal or encoded paragraph tags. The private EMAILContent::getFieldValue() wrapper has no local invocation; active modern fields use the shared Text UI type instead.
- `getContent()` decodes entities on the combined subject/body and replaces nonbreaking-space entities with ordinary spaces. It calls `handleRowbreak()` even without rowbreak tokens; that parser pass is not a general newline-to-br conversion. Explicit paragraphs, breaks and CSS in the template remain relevant to spacing.
- Validate formatting through both EMAILMaker passes, modern and legacy signature variables, inline styles, encoded literal text, and the final ITS4YouEmails mailer body. Isolated method execution is not evidence of the exact HTML delivered by a configured workflow.

# Display conditions

- `EMAILMaker_FilterRecordStructure_Model` uses `Core_FilterEditor_Model::getConditionBlocks()` for source and referenced modules. Ordinary blocks keep their configured order, while the block named `LBL_ITEM_DETAILS` is presented last. This changes only display-condition field ordering.

# Attachment images

- Browser-visible user, contact, product, record and company attachment images use `Core_Storage_Model::getAttachmentPublicUrl()` rather than exposing `vtiger_attachments.path`. This keeps templates compatible with attachment storage outside the web directory.
- PDFMaker keeps using resolved physical paths for local PDF rendering; do not replace those paths with browser URLs without validating the PDF renderer.

# Template detail icons

- `EMAILMaker_EMAILMaker_Model::GetDetailViewData()` (default for DetailView / ListView) and the permission-denied box (`denied.gif`) take their icons through `vimage_path()` (`layouts/d1/skins/images/Enable.png`, `Disable.png`, `denied.gif`), the same as PDFMaker. The former `layouts/vlayout/...` paths do not exist in Defalto.
- `layouts/d1/modules/EMAILMaker/unsubscribeinfo/info_en_us.tpl` still points to `layouts/vlayout/modules/EMAILMaker/images/unsubscribe_email_*.jpg`; those images exist nowhere and the template is not loaded by any code.
