{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Vtiger/views/MassActionAjax.php *}

{strip}
    <div id="sendEmailContainer" class="modal-dialog">
        <form class="form-horizontal" id="SendEmailFormStep1" method="post" action="index.php">
            <div class="modal-content">
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate('LBL_SELECT_EMAIL_IDS', $MODULE)}}
                <div class="modal-body">
                    <input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
                    <input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
                    <input type="hidden" name="viewname" value="{$VIEWNAME}">
                    <input type="hidden" name="module" value="ITS4YouEmails">
                    <input type="hidden" name="view" value="ComposeEmail">
                    <input type="hidden" name="search_key" value="{$SEARCH_KEY}">
                    <input type="hidden" name="operator" value="{$OPERATOR}">
                    <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}">
                    {if $SEARCH_PARAMS}
                        <input type="hidden" name="search_params" value='{ZEND_JSON::encode($SEARCH_PARAMS)}'>
                    {/if}
                    <input type="hidden" name="fieldModule" value="{$SOURCE_MODULE}">
                    <input type="hidden" name="to" value='{ZEND_JSON::encode($TO)}'>
                    <input type="hidden" name="source_module" value="{$SELECTED_EMAIL_SOURCE_MODULE}">
                    {if !empty($PARENT_MODULE)}
                        <input type="hidden" name="sourceModule" value="{$PARENT_MODULE}">
                        <input type="hidden" name="sourceRecord" value="{$PARENT_RECORD}">
                        <input type="hidden" name="parentModule" value="{$RELATED_MODULE}">
                    {/if}
                    <input type="hidden" name="ispdfactive" id="ispdfactive" value="{if $PDFTEMPLATEIDS neq ""}1{else}0{/if}">
                    <input type="hidden" name="pdf_template_ids" id="pdftemplateid" value="{$PDFTEMPLATEID}">

                    {if !empty($FOR_CAMPAIGN)}<input type="hidden" name="cid" value="{$FOR_CAMPAIGN}">{/if}


                    <input type="hidden" name="prefsNeedToUpdate" id="prefsNeedToUpdate" value="{$PREF_NEED_TO_UPDATE}">
                    <div id='multiEmailContainer'>
                        {assign var=IS_INPUT_SELECTED_DEFINED value='0'}
                        <div class="modal-body tabbable">
                            <div class="row">
                                <h5>{vtranslate('LBL_TO','EMAILMaker')}:</h5>
                            </div>
                            <div class="row">
                                <div class="emailToFields">
                                    <input type="hidden" class="emailFields" value="{$EMAIL_FIELDS_COUNT}">
                                    <select id="emailField" name="toEmail" type="text" class="form-control emailFieldSelects" multiple>
                                        {include file="SelectEmailFieldOptions.tpl"|vtemplate_path:$MODULE IS_INPUT_SELECTED_ALLOWED=true}
                                    </select>
                                </div>
                            </div>
                            <div class="ccContent hide">
                                <div class="row">
                                    <h5>{vtranslate('LBL_CC','EMAILMaker')}:</h5>
                                </div>
                                <div class="row">
                                    <div class="emailToFields">
                                        <select id="emailccField" name="toEmailCC" type="text" class="form-control emailFieldSelects" multiple>
                                            {include file="SelectEmailFieldOptions.tpl"|vtemplate_path:$MODULE}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="bccContent hide">
                                <div class="row">
                                    <h5>{vtranslate('LBL_BCC','EMAILMaker')}:</h5>
                                </div>
                                <div class="row">
                                    <div class="emailToFields">
                                        <select id="emailbccField" name="toEmailBCC" type="text" class="form-control emailFieldSelects" multiple>
                                            {include file="SelectEmailFieldOptions.tpl"|vtemplate_path:$MODULE}
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <span id="ccLinkContent"><a href="#" class="cursorPointer" id="ccLink">{vtranslate('LBL_ADD_CC','Email')}</a>&nbsp;&nbsp;</span>
                                    <span id="bccLinkContent"><a href="#" class="cursorPointer" id="bccLink">{vtranslate('LBL_ADD_BCC','Email')}</a></span>

                                </div>
                            </div>
                            <br/>
                            {if $CRM_TEMPLATES_EXIST eq '0'}
                                <div class="row">
                                    <h5>{vtranslate('LBL_SELECT_EMAIL_TEMPLATE','EMAILMaker')}:</h5>
                                </div>
                                <div class="row">
                                    <select id="use_common_email_template" name="email_template_ids" class="form-control">
                                        <option value="">{vtranslate('LBL_NONE','EMAILMaker')}</option>
                                        {foreach from=$CRM_TEMPLATES["1"] item="options" key="category_name"}
                                            <optgroup label="{$category_name}">
                                                {foreach from=$options item="option"}
                                                    <option value="{$option.value}" {if $option.title neq ""}title="{$option.title}"{/if} {if $option.value eq $DEFAULT_TEMPLATE}selected{/if}>{$option.label}</option>
                                                {/foreach}
                                            </optgroup>
                                        {/foreach}
                                        {foreach from=$CRM_TEMPLATES["0"] item="option"}
                                            <option value="{$option.value}" {if $option.title neq ""}title="{$option.title}"{/if} {if $option.value eq $DEFAULT_TEMPLATE}selected{/if}>{$option.label}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            {/if}

                            {if $TEMPLATE_LANGUAGES|@sizeof > 1}
                                <div class="row">
                                    <h5>{vtranslate('LBL_LANGUAGE','EMAILMaker')}:</h5>
                                </div>
                                <div class="row">
                                    <select name="email_template_language" id="email_template_language" class="form-control">
                                        {html_options  options=$TEMPLATE_LANGUAGES selected=$CURRENT_LANGUAGE}
                                    </select>
                                </div>
                            {else}
                                {foreach from="$TEMPLATE_LANGUAGES" item="lang" key="lang_key"}
                                    <input type="hidden" name="email_template_language" id="email_template_language" value="{$lang_key}"/>
                                {/foreach}
                            {/if}
                            {if $IS_PDFMAKER eq 'yes'}
                                <br>
                                <div {if $PDFTEMPLATEIDS eq ""}class='hide'{/if} id='EMAILMakerPDFTemplatesContainer'>
                                    <div class="row">
                                        <h5>{vtranslate('LBL_SELECT_PDF_TEMPLATES','EMAILMaker')}:</h5>
                                    </div>
                                    <div class="row">
                                        <select id="use_common_pdf_template" multiple class="form-control">
                                            {foreach from=$PDF_TEMPLATES item=PDF_TEMPLATE_DATA key=PDF_TEMPLATE_ID}
                                                <option value="{$PDF_TEMPLATE_ID}"
                                                        {if $PDFTEMPLATEID neq ''}
                                                            {if $PDF_TEMPLATE_ID|in_array:$PDFTEMPLATEIDS}
                                                                selected="selected"
                                                            {/if}
                                                        {else}
                                                            {if $PDF_TEMPLATE_DATA.is_default eq '1' || $PDF_TEMPLATE_DATA.is_default eq '3'}
                                                                selected="selected"
                                                            {/if}
                                                        {/if}
                                                >{$PDF_TEMPLATE_DATA.templatename}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="row">
                                        <h5>{vtranslate('LBL_MERGE_PDF_TEMPLATES','EMAILMaker')}:</h5>
                                    </div>
                                    <div class="row">
                                        <input type="checkbox" value="1" name="is_merge_templates">
                                    </div>
                                    <div class="row">
                                        <div class='pull-right paddingTop5'>
                                            <button type="button" id='removePDFMakerTemplate' class='btn btn-danger' onClick='return false'><i class="fa fa-minus"></i> {vtranslate('LBL_REMOVE_PDFMAKER_TEMPLATES','EMAILMaker')}</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <span class='pull-right{if $PDFTEMPLATEIDS neq ""} hide{/if}' id='EMAILMakerPDFTemplatesBtn'><button id='addPDFMakerTemplate' class='btn btn btn-primary' onClick='return false'><i class="fa fa-plus"></i> {vtranslate('LBL_ADD_PDFMAKER_TEMPLATES','EMAILMaker')}</button></span>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
                {include file="ModalFooter.tpl"|vtemplate_path:$MODULE BUTTON_NAME={vtranslate('LBL_SELECT', $MODULE)}}
            </div>
        </form>
    </div>
{/strip}


