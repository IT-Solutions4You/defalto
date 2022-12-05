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
    <div class="SendEmailFormStep2 modal-dialog" id="composeEmailContainer" style="width: 1100px; height: 80vh;">
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
                        {if $SMTP_RECORDS}
                            <div class="row SMTPField">
                                <div class="col-lg-12">
                                    <div class="col-lg-2">
                                        <span class="pull-right">{vtranslate('LBL_SMTP',$MODULE)}&nbsp</span>
                                    </div>
                                    <div class="col-lg-6">
                                        <select name="smtp" class="select2 inputElement">
                                            {foreach from=$SMTP_RECORDS item=SMTP_RECORD key=SMTP_RECORD_ID}
                                                <option value="{$SMTP_RECORD_ID}">{$SMTP_RECORD->get('server')} &lt;{$SMTP_RECORD->get('server_username')}&gt;</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        {/if}
                        <div class="row toEmailField">
                            <div class="col-lg-12">
                                <div class="col-lg-2">
                                    <span class="pull-right">{vtranslate('LBL_FROM_EMAIL',$MODULE)}&nbsp</span>
                                </div>
                                <div class="col-lg-6">
                                    <select name="from_email" class="select2 inputElement">
                                        <optgroup label="{vtranslate('LBL_FROM_EMAIL',$MODULE)}">
                                            {html_options  options=$FROM_EMAILS selected=$SELECTED_DEFAULT_FROM}
                                        </optgroup>
                                        {if $SMTP_RECORDS}
                                            <optgroup label="{vtranslate('LBL_SMTP',$MODULE)}">
                                                {foreach from=$SMTP_RECORDS item=SMTP_RECORD key=SMTP_RECORD_ID}
                                                    {if !$SMTP_RECORD->isEmpty('from_email_field')}
                                                        <option value="{$SMTP_RECORD->get('from_email_field')}">{$SMTP_RECORD->get('server')} &lt;{$SMTP_RECORD->get('from_email_field')}&gt;</option>
                                                    {/if}
                                                {/foreach}
                                            </optgroup>
                                        {/if}
                                    </select>
                                </div>
                            </div>
                        </div>
                        {if $SINGLE_RECORD neq 'yes'}
                            <div class="row toEmailField">
                                <div class="col-lg-12">
                                    <div class="col-lg-2">
                                        <span class="pull-right">{vtranslate('LBL_RECORDS_LIST',$SOURCEMODULE)}&nbsp</span>
                                    </div>
                                    <div class="col-lg-6">
                                        <select name="emailSourcesList" class="select2 inputElement emailSourcesList">
                                            {html_options  options=$SOURCE_NAMES selected=$SELECTED_SOURCEID}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        {/if}
                        <div class="row toEmailField">
                            <div class="col-lg-12">
                                <div class="col-lg-2">
                                    <span class="pull-right">{vtranslate('LBL_TO',$MODULE)}&nbsp;<span class="redColor">*</span></span>
                                </div>
                                <div class="col-lg-6">
                                    <input id="emailField" style="width:100%" name="toEmail" type="text" class="autoComplete sourceField select2" data-rule-required="true" data-rule-multiEmails="true" value="" placeholder="{vtranslate('LBL_TYPE_AND_SEARCH',$MODULE)}">
                                    <!-- //ITS4You:{Zend_Json::encode($TO_EMAILS)}-->
                                </div>
                                <div class="col-lg-4 input-group">
                                    <select style="width: 140px;" class="select2 emailModulesList pull-right">
                                        {foreach item=MODULE_NAME from=$RELATED_MODULES}
                                            <option value="{$MODULE_NAME}" {if $MODULE_NAME eq $FIELD_MODULE} selected {/if}>{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
                                        {/foreach}
                                    </select>
                                    <a href="#" class="clearReferenceSelection cursorPointer" name="clearToEmailField"> X </a>
                                    <span class="input-group-addon">
                                        <span class="selectEmail cursorPointer">
                                            <i class="fa fa-search" title="{vtranslate('LBL_SELECT', $MODULE)}"></i>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row {if empty($CC)} hide {/if} ccContainer ccEmailField">
                            <div class="col-lg-12">
                                <div class="col-lg-2">
                                    <span class="pull-right">{vtranslate('LBL_CC',$MODULE)}</span>
                                </div>
                                <div class="col-lg-6">
                                    <input id="emailccField" style="width:100%" name="ccEmail" type="text" class="autoComplete sourceField select2" data-rule-multiEmails="true" value="" placeholder="{vtranslate('LBL_TYPE_AND_SEARCH',$MODULE)}">
                                </div>
                                <div class="col-lg-4"></div>
                            </div>
                        </div>

                        <div class="row {if empty($BCC)} hide {/if} bccContainer bccEmailField">
                            <div class="col-lg-12">
                                <div class="col-lg-2">
                                    <span class="pull-right">{vtranslate('LBL_BCC',$MODULE)}</span>
                                </div>
                                <div class="col-lg-6">
                                    <input id="emailbccField" style="width:100%" name="bccEmail" type="text" class="autoComplete sourceField select2" data-rule-multiEmails="true" value="" placeholder="{vtranslate('LBL_TYPE_AND_SEARCH',$MODULE)}">
                                </div>
                                <div class="col-lg-4"></div>
                            </div>
                        </div>

                        <div class="row {if (!empty($CC)) and (!empty($BCC))} hide {/if} ">
                            <div class="col-lg-12">
                                <div class="col-lg-2">
                                </div>
                                <div class="col-lg-6">
                                    <a href="#" class="cursorPointer {if (!empty($CC))}hide{/if}" id="ccLink">{vtranslate('LBL_ADD_CC', $MODULE)}</a>&nbsp;&nbsp;
                                    <a href="#" class="cursorPointer {if (!empty($BCC))}hide{/if}" id="bccLink">{vtranslate('LBL_ADD_BCC', $MODULE)}</a>
                                </div>
                                <div class="col-lg-4"></div>
                            </div>
                        </div>

                        <div class="row subjectField">
                            <div class="col-lg-12">
                                <div class="col-lg-2">
                                    <span class="pull-right">{vtranslate('LBL_SUBJECT',$MODULE)}&nbsp;<span class="redColor">*</span></span>
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" name="subject" value="{$SUBJECT}" data-rule-required="true" id="subject" spellcheck="true" class="inputElement"/>
                                </div>
                                <div class="col-lg-4"></div>
                            </div>
                        </div>

                        <div class="row attachment">
                            <div class="col-lg-12">
                                <div class="col-lg-2">
                                    <span class="pull-right">{vtranslate('LBL_ATTACHMENT',$MODULE)}</span>
                                </div>
                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-4 browse">
                                            <input type="file" {if $FILE_ATTACHED}class="removeNoFileChosen"{/if} id="multiFile" name="file[]"/>&nbsp;
                                        </div>
                                        <div class="col-lg-4 brownseInCrm">
                                            <button type="button" class="btn btn-small btn-default" id="browseCrm" data-url="{$DOCUMENTS_URL}" title="{vtranslate('LBL_BROWSE_CRM',$MODULE)}">{vtranslate('LBL_BROWSE_CRM',$MODULE)}</button>
                                        </div>
                                        <div class="col-lg-4 insertTemplate">
                                            <button id="selectEmailTemplate" class="btn btn-success pull-right" data-url="{ITS4YouEmails_Record_Model::getSelectTemplateUrl($SOURCERECORD, $SOURCEMODULE)}">{vtranslate('LBL_SELECT_EMAIL_TEMPLATE',$MODULE)}</button>
                                        </div>
                                    </div>
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
                                            <div class="row"><a href="#" class="generatePreviewPDF cursorPointer" data-templateid="{$PDF_TEMPLATE_ID}"><i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;{$PDF_TEMPLATE_NAME}
                                                </a></div>
                                        {/foreach}
                                    {/if}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-default includeSignature">{vtranslate('LBL_INCLUDE_SIGNATURE',$MODULE)}</button>
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
                    <div class="pull-right cancelLinkContainer">
                        <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    </div>
                    <button id="sendEmail" name="sendemail" class="btn btn-success" title="{vtranslate("LBL_SEND_EMAIL",$MODULE)}" type="submit"><strong>{vtranslate("LBL_SEND_EMAIL",$MODULE)}</strong></button>
                </div>
            </form>
        </div>
    </div>
{/strip}