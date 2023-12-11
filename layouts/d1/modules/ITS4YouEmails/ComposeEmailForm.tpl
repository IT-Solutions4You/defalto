{*<!--
/* * *******************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
-->*}
{strip}
    <div class="SendEmailFormStep2 modal-dialog modal-xl" id="composeEmailContainer" style="width: 1100px; height: 80vh;">
        <div class="modal-content">
            <form class="form-horizontal" id="massEmailForm" method="post" action="index.php" enctype="multipart/form-data" name="massEmailForm">
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate('LBL_COMPOSE_EMAIL', $MODULE)}}
                <div class="modal-body">
                    <input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
                    <input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
                    <input type="hidden" name="viewname" value="{$VIEWNAME}"/>
                    <input type="hidden" name="module" value="{$MODULE}"/>
                    <input type="hidden" name="mode" value="massSave"/>
                    <input type="hidden" name="view" value="MassSaveAjax"/>
                    <input type="hidden" name="selected_sourceid" value="{$SELECTED_SOURCEID}">
                    {foreach item=source_name key=SID name="sourcenames" from=$SOURCE_NAMES}
                        <input type="hidden" name="{$SID}toemailinfo" value='{ZEND_JSON::encode($TOMAIL_INFO[$SID])}'/>
                        <input type="hidden" name="{$SID}ccemailinfo" value='{ZEND_JSON::encode($CCMAIL_INFO[$SID])}'/>
                        <input type="hidden" name="{$SID}bccemailinfo" value='{ZEND_JSON::encode($BCCMAIL_INFO[$SID])}'/>
                        <input type="hidden" name="{$SID}toMailNamesList" value='{ZEND_JSON::encode($TOMAIL_NAMES_LIST[$SID])}'/>
                        <input type="hidden" name="{$SID}ccMailNamesList" value='{ZEND_JSON::encode($CCMAIL_NAMES_LIST[$SID])}'/>
                        <input type="hidden" name="{$SID}bccMailNamesList" value='{ZEND_JSON::encode($BCCMAIL_NAMES_LIST[$SID])}'/>
                    {/foreach}
                    <input type="hidden" name="to" value='{ZEND_JSON::encode($TO)}'/>
                    <input type="hidden" name="cc" value='{ZEND_JSON::encode($CC)}'/>
                    <input type="hidden" name="bcc" value='{ZEND_JSON::encode($BCC)}'/>

                    <input type="hidden" id="flag" name="flag" value=""/>
                    <input type="hidden" id="maxUploadSize" value="{$MAX_UPLOAD_SIZE}"/>
                    <input type="hidden" id="documentIds" name="documentids" value='{Zend_Json::encode($DOCUMENT_IDS)}' />
                    <input type="hidden" name="emailMode" value="{$EMAIL_MODE}"/>
                    <input type="hidden" name="source_module" value="{$SOURCE_MODULE}"/>
                    {if !empty($PARENT_EMAIL_ID)}
                        <input type="hidden" name="parent_id" value="{$PARENT_EMAIL_ID}"/>
                        <input type="hidden" name="parent_record_id" value="{$PARENT_RECORD}"/>
                    {/if}
                    {if !empty($RECORDID)}
                        <input type="hidden" name="record" value="{$RECORDID}"/>
                    {/if}
                    <input type="hidden" name="search_key" value="{$SEARCH_KEY}"/>
                    <input type="hidden" name="operator" value="{$OPERATOR}"/>
                    <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}"/>
                    <input type="hidden" name="search_params" value='{ZEND_JSON::encode($SEARCH_PARAMS)}'/>
                    <input type="hidden" name="email_template_ids" value='{$EMAIL_TEMPLATE_IDS}'/>
                    <input type="hidden" name="email_template_language" value='{$EMAIL_TEMPLATE_LANGUAGE}'/>
                    <input type="hidden" name="pdf_template_ids" value='{$PDF_TEMPLATE_IDS}'/>
                    <input type="hidden" name="pdf_template_language" value='{$PDF_TEMPLATE_LANGUAGE}'/>
                    {if $IS_MERGE_TEMPLATES}
                        <input type="hidden" name="is_merge_templates" value='{$IS_MERGE_TEMPLATES}'/>
                    {/if}
                    <div class="topContent">
                        <div class="row toEmailField py-2">
                            <div class="col-lg-2 text-end text-secondary">
                                <span>{vtranslate('LBL_FROM_EMAIL',$MODULE)}</span>
                            </div>
                            <div class="col-lg">
                                <select name="from_email" class="select2 inputElement form-select">
                                    <optgroup label="{vtranslate('LBL_FROM_EMAIL',$MODULE)}">
                                        {html_options  options=$FROM_EMAILS selected=$SELECTED_DEFAULT_FROM}
                                    </optgroup>
                                    {if $SMTP_RECORDS}
                                        <optgroup label="{vtranslate('LBL_SMTP',$MODULE)}">
                                            {foreach from=$SMTP_RECORDS item=SMTP_RECORD key=SMTP_RECORD_ID}
                                                {if !$SMTP_RECORD->isEmpty('from_email_field')}
                                                    <option value="s::{$SMTP_RECORD->getId()}">{$SMTP_RECORD->get('from_name_field')} &lt;{$SMTP_RECORD->get('from_email_field')}&gt;</option>
                                                {/if}
                                            {/foreach}
                                        </optgroup>
                                    {/if}
                                </select>
                            </div>
                        </div>
                        {if $SINGLE_RECORD neq 'yes'}
                            <div class="row toEmailField py-2">
                                <div class="col-lg-2 text-end text-secondary">
                                    <span>{vtranslate('LBL_RECORDS_LIST',$SOURCEMODULE)}&nbsp</span>
                                </div>
                                <div class="col-lg">
                                    <select name="emailSourcesList" style="width:100%" class="select2 inputElement emailSourcesList">
                                        {html_options  options=$SOURCE_NAMES selected=$SELECTED_SOURCEID}
                                    </select>
                                </div>
                            </div>
                        {/if}
                        <div class="row toEmailField py-2">
                            <div class="col-lg-2 text-end text-secondary">
                                <span>{vtranslate('LBL_TO',$MODULE)}</span>
                                <span class="text-danger ms-2">*</span>
                            </div>
                            <div class="col-lg">
                                <select id="emailField" style="width:100%" name="toEmail" class="autoComplete sourceField form-select" data-rule-required="true" multiple="multiple" placeholder="{vtranslate('LBL_TYPE_AND_SEARCH',$MODULE)}"></select>
                                <!-- //ITS4You:{Zend_Json::encode($TO_EMAILS)}-->
                            </div>
                        </div>
                        <div class="row py-2 ccContainer ccEmailField {if empty($CC)}hide{/if}">
                            <div class="col-lg-2 text-end text-secondary">
                                <span>{vtranslate('LBL_CC',$MODULE)}</span>
                            </div>
                            <div class="col-lg">
                                <select id="emailccField" style="width:100%" name="ccEmail" class="autoComplete sourceField form-select" multiple="multiple" placeholder="{vtranslate('LBL_TYPE_AND_SEARCH',$MODULE)}"></select>
                            </div>
                        </div>
                        <div class="row py-2 bccContainer bccEmailField {if empty($BCC)}hide{/if}">
                            <div class="col-lg-2 text-end text-secondary">
                                <span>{vtranslate('LBL_BCC',$MODULE)}</span>
                            </div>
                            <div class="col-lg">
                                <select id="emailbccField" style="width:100%" name="bccEmail" class="autoComplete sourceField form-select" multiple="multiple" placeholder="{vtranslate('LBL_TYPE_AND_SEARCH',$MODULE)}"></select>
                            </div>
                        </div>
                        <div class="row {if (!empty($CC)) and (!empty($BCC))}hide{/if}">
                            <div class="col-lg-2">
                            </div>
                            <div class="col-lg">
                                <a href="#" class="btn btn-outline-secondary me-2 {if (!empty($CC))}hide{/if}" id="ccLink">{vtranslate('LBL_ADD_CC', $MODULE)}</a>
                                <a href="#" class="btn btn-outline-secondary {if (!empty($BCC))}hide{/if}" id="bccLink">{vtranslate('LBL_ADD_BCC', $MODULE)}</a>
                            </div>
                        </div>
                        <div class="row py-2 subjectField">
                            <div class="col-lg-2 text-end text-secondary">
                                <span>{vtranslate('LBL_SUBJECT',$MODULE)}</span>
                                <span class="text-danger ms-2">*</span>
                            </div>
                            <div class="col-lg">
                                <input type="text" name="subject" value="{$SUBJECT}" data-rule-required="true" id="subject" spellcheck="true" class="inputElement form-control"/>
                            </div>
                        </div>
                        <div class="row py-2 attachment">
                            <div class="col-lg-2 text-end text-secondary">
                                <span>{vtranslate('LBL_ATTACHMENT',$MODULE)}</span>
                            </div>
                            <div class="col-lg">
                                <div class="dropdown">
                                    <div class="dropdown-toggle btn btn-outline-secondary" data-bs-toggle="dropdown">
                                        <span>{vtranslate('LBL_MORE', $MODULE)}</span>
                                    </div>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <div class="dropdown-item">
                                                <input type="file" class="{if $FILE_ATTACHED}removeNoFileChosen{/if}" id="multiFile" name="file[]" title="{vtranslate('LBL_UPLOAD', $MODULE)}"/>
                                            </div>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" id="browseCrm" data-url="{$DOCUMENTS_URL}" title="{vtranslate('LBL_BROWSE_CRM',$MODULE)}">{vtranslate('LBL_BROWSE_CRM',$MODULE)}</a>
                                        </li>
                                        {if $RECORD_DOCUMENTS_URL}
                                        <li>
                                            <a class="dropdown-item" href="#" id="browseRecord" data-url="{$RECORD_DOCUMENTS_URL}" title="{vtranslate('LBL_BROWSE_RECORD',$MODULE)}">{vtranslate('LBL_BROWSE_RECORD',$MODULE)}</a>
                                        </li>
                                        {/if}
                                    </ul>
                                </div>
                                <div>
                                    <div id="attachments">
                                        {foreach item=ATTACHMENT from=$ATTACHMENTS}
                                            {if ('docid'|array_key_exists:$ATTACHMENT)}
                                                {assign var=DOCUMENT_ID value=$ATTACHMENT['docid']}
                                                {assign var=FILE_TYPE value="document"}
                                            {else}
                                                {assign var=FILE_TYPE value="file"}
                                            {/if}
                                            <div class="MultiFile-label customAttachment" data-file-id="{$ATTACHMENT['fileid']}" data-file-type="{$FILE_TYPE}" data-file-size="{$ATTACHMENT['size']}" {if $FILE_TYPE eq "document"} data-document-id="{$DOCUMENT_ID}"{/if}>
                                                {if $ATTACHMENT['nondeletable'] neq true}
                                                    <a name="removeAttachment" class="cursorPointer">x </a>
                                                {/if}
                                                <span>{$ATTACHMENT['attachment']}</span>
                                            </div>
                                        {/foreach}
                                    </div>
                                    {if $PDF_TEMPLATES}
                                        <input type="hidden" name="pdftemplateids" value="{$PDF_TEMPLATE_IDS}">
                                        <input type="hidden" name="pdflanguage" value="{$PDF_TEMPLATE_LANGUAGE}">
                                        {foreach key=PDF_TEMPLATE_ID item=PDF_TEMPLATE_NAME from=$PDF_TEMPLATES}
                                            <div class="row">
                                                <a href="#" class="generatePreviewPDF cursorPointer" data-templateid="{$PDF_TEMPLATE_ID}">
                                                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                                    <span style="margin-left: 1rem">{$PDF_TEMPLATE_NAME}</span>
                                                </a>
                                            </div>
                                        {/foreach}
                                    {/if}
                                </div>
                            </div>
                            <div class="col-lg-auto ms-auto">
                                <button type="button" class="btn btn-outline-secondary includeSignature me-2">{vtranslate('LBL_INCLUDE_SIGNATURE',$MODULE)}</button>
                                <button id="selectEmailTemplate" class="btn btn-success" data-url="{ITS4YouEmails_Record_Model::getSelectTemplateUrl($SOURCERECORD, $SOURCEMODULE)}">{vtranslate('LBL_SELECT_EMAIL_TEMPLATE',$MODULE)}</button>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid hide" id='emailTemplateWarning'>
                        <div class="alert alert-warning fade in">
                            <a href="#" class="close" data-dismiss="alert">&times;</a>
                            <p>{vtranslate('LBL_EMAILTEMPLATE_WARNING_CONTENT',$MODULE)}</p>
                        </div>
                    </div>
                    <div class="row templateContent">
                        <div class="col-lg-12">
                            <textarea style="width:390px;height:200px;" id="description" name="description">{$DESCRIPTION}</textarea>
                        </div>
                    </div>

                    {if $RELATED_LOAD eq true}
                        <input type="hidden" name="related_load" value={$RELATED_LOAD}/>
                    {/if}
                    <input type="hidden" name="attachments" value='{ZEND_JSON::encode($ATTACHMENTS)}'/>
                    <div id="emailTemplateWarningContent" style="display: none;">
                        {vtranslate('LBL_EMAILTEMPLATE_WARNING_CONTENT',$MODULE)}
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col text-end cancelLinkContainer">
                                <a href="#" class="btn btn-primary cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                            </div>
                            <div class="col">
                                <button id="sendEmail" name="sendemail" class="btn btn-primary active" title="{vtranslate("LBL_SEND_EMAIL",$MODULE)}" type="submit">
                                    <strong>{vtranslate("LBL_SEND_EMAIL",$MODULE)}</strong>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/strip}