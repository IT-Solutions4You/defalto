{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Vtiger/views/ComposeEmail.php *}
{strip}
    <div class="SendEmailFormStep2 modal-dialog modal-lg" id="composeEmailContainer">
        <div class="modal-content">
            <form class="form-horizontal" id="massEmailForm" method="post" action="index.php" enctype="multipart/form-data" name="massEmailForm">
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate('LBL_COMPOSE_EMAIL', $MODULE)}}
                <div class="modal-body">
                    <input type="hidden" name="selected_ids" value='{ZEND_JSON::encode($SELECTED_IDS)}'/>
                    <input type="hidden" name="excluded_ids" value='{ZEND_JSON::encode($EXCLUDED_IDS)}'/>
                    <input type="hidden" name="viewname" value="{$VIEWNAME}"/>
                    <input type="hidden" name="module" value="{$MODULE}"/>
                    <input type="hidden" name="mode" value="massSave"/>
                    <input type="hidden" name="toemailinfo" value='{ZEND_JSON::encode($TOMAIL_INFO)}'/>
                    <input type="hidden" name="view" value="MassSaveAjax"/>
                    <input type="hidden" name="to" value='{ZEND_JSON::encode($TO)}'/>
                    <input type="hidden" name="toMailNamesList" value='{$TOMAIL_NAMES_LIST}'/>
                    <input type="hidden" id="flag" name="flag" value=""/>
                    <input type="hidden" id="maxUploadSize" value="{$MAX_UPLOAD_SIZE}"/>
                    <input type="hidden" id="documentIds" name="documentids" value=""/>
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
                    <input type="hidden" name="search_params" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($SEARCH_PARAMS))}'/>

                    <div class="toEmailField">
                        <div class="row">
                            <div class="col-lg-2">
                                <span class="">{vtranslate('LBL_TO',$MODULE)}&nbsp;<span class="redColor">*</span></span>
                            </div>
                            <div class="col-lg-10">
                                <div class="input-group">
                                    {if !empty($TO)}
                                        {assign var=TO_EMAILS value=","|implode:$TO|htmlentities}
                                    {/if}
                                    <select id="emailField" multiple data-tags="true" name="toEmail" type="text" class="select2 autoComplete sourceField" data-rule-required="true" data-rule-multiEmails="true" value="{$TO_EMAILS}" placeholder="{vtranslate('LBL_TYPE_AND_SEARCH',$MODULE)}">
                                        <option value="">Search values</option>
                                    </select>
                                    <select class="select2 emailModulesList">
                                        {foreach item=MODULE_NAME from=$RELATED_MODULES}
                                            <option value="{$MODULE_NAME}" {if $MODULE_NAME eq $FIELD_MODULE} selected {/if}>{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
                                        {/foreach}
                                    </select>
                                    <a href="#" class="input-group-text clearReferenceSelection cursorPointer" name="clearToEmailField">
                                        <i class="fa fa-times"></i>
                                    </a>
                                    <span class="input-group-text selectEmail cursorPointer">
                                        <i class="fa fa-search" title="{vtranslate('LBL_SELECT', $MODULE)}"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row {if empty($CC)} hide {/if} ccContainer">
                        <div class="col-lg-12">
                            <div class="col-lg-2">
                                <span class="">{vtranslate('LBL_CC',$MODULE)}</span>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" name="cc" data-rule-multiEmails="true" value="{if !empty($CC)}{$CC}{/if}"/>
                            </div>
                            <div class="col-lg-4"></div>
                        </div>
                    </div>

                    <div class="row {if empty($BCC)} hide {/if} bccContainer">
                        <div class="col-lg-12">
                            <div class="col-lg-2">
                                <span class="">{vtranslate('LBL_BCC',$MODULE)}</span>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" name="bcc" data-rule-multiEmails="true" value="{if !empty($BCC)}{$BCC}{/if}"/>
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
                                <span class="">{vtranslate('LBL_SUBJECT',$MODULE)}&nbsp;<span class="redColor">*</span></span>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" name="subject" value="{$SUBJECT}" data-rule-required="true" id="subject" spellcheck="true" class="inputElement form-control"/>
                            </div>
                            <div class="col-lg-4"></div>
                        </div>
                    </div>

                    <div class="row attachment">
                        <div class="col-lg-12">
                            <div class="col-lg-2">
                                <span class="">{vtranslate('LBL_ATTACHMENT',$MODULE)}</span>
                            </div>
                            <div class="col-lg-9">
                                <div class="row">
                                    <div class="col-lg-4">

                                        <input type="file" {if $FILE_ATTACHED}class="removeNoFileChosen"{/if} id="multiFile" name="file[]"/>&nbsp;
                                    </div>
                                    <div class="col-lg-4 brownseInCrm">
                                        <button type="button" class="btn btn-small btn-default" id="browseCrm" data-url="{$DOCUMENTS_URL}" title="{vtranslate('LBL_BROWSE_CRM',$MODULE)}">{vtranslate('LBL_BROWSE_CRM',$MODULE)}</button>
                                    </div>
                                    <div class="col-lg-4 insertTemplate">
                                        <button id="selectEmailTemplate" class="btn btn-success" data-url="module=EmailTemplates&view=Popup">{vtranslate('LBL_SELECT_EMAIL_TEMPLATE',$MODULE)}</button>
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
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="col-lg-2">
                                <span class="">{vtranslate('LBL_INCLUDE_SIGNATURE',$MODULE)}</span>
                            </div>
                            <div class="item col-lg-9">
                                <input class="" type="checkbox" name="signature" value="Yes" checked="checked" id="signature">
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
                        <a href="#" class="cancelLink" type="reset" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    </div>
                    <button id="sendEmail" name="sendemail" class="btn btn-success" title="{vtranslate("LBL_SEND_EMAIL",$MODULE)}" type="submit"><strong>{vtranslate("LBL_SEND_EMAIL",$MODULE)}</strong></button>
                    <button id="saveDraft" name="savedraft" class="btn btn-default" title="{vtranslate('LBL_SAVE_AS_DRAFT',$MODULE)}" type="submit"><strong>{vtranslate('LBL_SAVE_AS_DRAFT',$MODULE)}</strong></button>
                </div>
            </form>
        </div>
    </div>
{/strip}
