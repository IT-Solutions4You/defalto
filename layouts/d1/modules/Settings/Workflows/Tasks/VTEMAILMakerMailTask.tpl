{*<!--
/* * *******************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
-->*}
{strip}
    <div id="VtEmailTaskContainer">
        <div class="contents tabbable ui-sortable">
            <div class="row py-2">
                <div class="col-lg-2"></div>
                <div class="col-lg">
                    <ul class="nav nav-pills layoutTabs massEditTabs">
                        <li class="nav-item">
                            <a data-bs-toggle="tab" href="#detailViewLayout" id="detailViewLayoutBtn" class="nav-link active">
                                <strong>{vtranslate('LBL_EMAIL_DETAILS','EMAILMaker')}</strong>
                            </a>
                        </li>
                        <li class="relatedListTab">
                            <a data-bs-toggle="tab" href="#relatedTabTemplate" class="nav-link workflowTab">
                                <strong>{vtranslate('LBL_EMAIL_CONTENT','EMAILMaker')}</strong>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="tab-content layoutContent padding20 themeTableColor overflowVisible">
                <div class="tab-pane active" id="detailViewLayout">
                    {assign var=SMTP_SERVERS value=$TASK_OBJECT->getSMTPServers()}
                    {if !empty($SMTP_SERVERS)}
                        <div class="row form-group py-2">
                            <div class="col-lg-2">{vtranslate('SMTP', $QUALIFIED_MODULE)}</div>
                            <div class="col-lg-4">
                                <select name="smtp" id="smtp" class="select2 inputElement form-select">
                                    <option>{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                    {foreach from=$TASK_OBJECT->getSMTPServers() key=SMTP_SERVER_ID item=SMTP_SERVER}
                                        <option value="{$SMTP_SERVER_ID}" {if $SMTP_SERVER_ID eq $TASK_OBJECT->smtp}selected="selected"{/if}>{$SMTP_SERVER->get('server')} &lt;{$SMTP_SERVER->get('server_username')}&gt;</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    {/if}
                    <div class="row form-group py-2">
                        <div class="col-lg-2">{vtranslate('LBL_FROM', $QUALIFIED_MODULE)}</div>
                        <div class="col-lg-4">
                            <input name="fromEmail" class=" fields inputElement form-control" type="text" value="{$TASK_OBJECT->fromEmail}"/>
                        </div>
                        <div class="col-lg-4">
                            <select id="fromEmailOption" class="inputElement select2 form-select" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
                                <option></option>
                                {$FROM_EMAIL_FIELD_OPTION}
                            </select>
                        </div>
                    </div>
                    <div class="row form-group py-2">
                        <div class="col-lg-2">{vtranslate('Reply To',$QUALIFIED_MODULE)}</div>
                        <div class="col-lg-4">
                            <input name="replyTo" class="fields inputElement form-control" type="text" value="{$TASK_OBJECT->replyTo}"/>
                        </div>
                        <div class="col-lg-4">
                            <select class="inputElement task-fields select2 overwriteSelection form-select" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
                                <option></option>
                                {$EMAIL_FIELD_OPTION}
                            </select>
                        </div>
                    </div>
                    <div class="row form-group py-2">
                        <div class="col-lg-2">
                            <span>{vtranslate('LBL_TO',$QUALIFIED_MODULE)}</span>
                            <span class="text-danger ms-2">*</span>
                        </div>
                        <div class="col-lg-4">
                            <input data-rule-required="true" name="recepient" class="fields inputElement form-control" type="text" value="{$TASK_OBJECT->recepient}"/>
                        </div>
                        <div class="col-lg-4">
                            <select class="inputElement task-fields select2 form-select" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
                                <option></option>
                                {$EMAIL_FIELD_OPTION}
                            </select>
                        </div>
                    </div>
                    <div class="row form-group py-2 {if empty($TASK_OBJECT->emailcc)}hide{/if}" id="ccContainer">
                        <div class="col-lg-2">{vtranslate('LBL_CC',$QUALIFIED_MODULE)}</div>
                        <div class="col-lg-4">
                            <input class="fields inputElement form-control" type="text" name="emailcc" value="{$TASK_OBJECT->emailcc}"/>
                        </div>
                        <div class="col-lg-4">
                            <select class="inputElement task-fields select2 form-select" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
                                <option></option>
                                {$EMAIL_FIELD_OPTION}
                            </select>
                        </div>
                    </div>
                    <div class="row form-group py-2 {if empty($TASK_OBJECT->emailbcc)}hide{/if}" id="bccContainer">
                        <div class="col-lg-2">{vtranslate('LBL_BCC',$QUALIFIED_MODULE)}</div>
                        <div class="col-lg-4">
                            <input class="fields inputElement form-control" type="text" name="emailbcc" value="{$TASK_OBJECT->emailbcc}"/>
                        </div>
                        <div class="col-lg-4">
                            <select class="inputElement task-fields select2 form-select" data-placeholder='{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}'>
                                <option></option>
                                {$EMAIL_FIELD_OPTION}
                            </select>
                        </div>
                    </div>
                    <div class="row form-group py-2 {if (!empty($TASK_OBJECT->emailcc)) and (!empty($TASK_OBJECT->emailbcc))} hide {/if}">
                        <div class="col-lg-2"></div>
                        <div class="col-lg-4">
                            <a class="btn btn-outline-secondary me-2 {if (!empty($TASK_OBJECT->emailcc))}hide{/if}" id="ccLink">{vtranslate('LBL_ADD_CC',$QUALIFIED_MODULE)}</a>
                            <a class="btn btn-outline-secondary {if (!empty($TASK_OBJECT->emailbcc))}hide{/if}" id="bccLink">{vtranslate('LBL_ADD_BCC',$QUALIFIED_MODULE)}</a>
                        </div>
                    </div>
                    {assign var=MODULE_FIELDS value=$TASK_OBJECT->getModuleFields($SOURCE_MODULE)}
                    {if $MODULE_FIELDS}
                        <div class="row form-group py-2" id="templateFieldsContainer">
                            <div class="col-lg-2">{vtranslate('LBL_EMAIL_CONTENT','EMAILMaker')}</div>
                            <div class="col-lg-4">
                                <select id="template_field" name="template_field" class="inputElement span7 select2 form-select">
                                    {html_options  options=$MODULE_FIELDS selected=$TASK_OBJECT->template_field}
                                </select>
                            </div>
                            <div class="col-lg-4"></div>
                        </div>
                    {/if}
                </div>
                <div class="tab-pane" id="relatedTabTemplate">
                    <div class="row form-group py-2">
                        <div class="col-lg-2">{vtranslate('LBL_EMAIL_TEMPLATE','EMAILMaker')}</div>
                        <div class="col-lg-4">
                            <select id="task_template" name="template" class="inputElement select2 form-select">
                                {html_options  options=$TASK_OBJECT->getTemplates($SOURCE_MODULE) selected=$TASK_OBJECT->template}
                            </select>
                            <input type="hidden" id="task_folder_value" value="{$TASK_OBJECT->template}">
                        </div>
                    </div>
                    <div class="row form-group py-2">
                        <div class="col-lg-2">{vtranslate('LBL_EMAIL_LANGUAGE','EMAILMaker')}</div>
                        <div class="col-lg-4">
                            {assign var=LANGUAGES_ARRAY value=$TASK_OBJECT->getLanguages()}
                            <select id="task_template_language" name="template_language" class="inputElement select2 form-select">
                                {html_options  options=$LANGUAGES_ARRAY selected=$TASK_OBJECT->template_language}
                            </select>
                            <input type="hidden" id="template_language_value" value="{$TASK_OBJECT->template_language}">
                        </div>
                    </div>
                    <div class="row form-group py-2">
                        <div class="col-lg-2">{vtranslate('LBL_SIGNATURE','EMAILMaker')}</div>
                        <div class="col-lg-8">
                            <input type="checkbox" name="signature" id="signature" class="form-check-input" {if $TASK_OBJECT->signature}checked="checked"{/if}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="modules/EMAILMaker/workflow/VTEMAILMakerMailTask.js" type="text/javascript" charset="utf-8"></script>
        <script type="text/javascript">
            Settings_Workflows_Edit_Js.prototype.registerVTEMAILMakerMailTaskEvents = function () {
                let textAreaElement = jQuery('#content');

                this.registerFillTaskFromEmailFieldEvent();
                this.registerCcAndBccEvents();
            };

            Settings_Workflows_Edit_Js.prototype.VTEMAILMakerMailTaskCustomValidation = function () {
                let result = true,
                    selectElement1 = jQuery('input[name="recepient"]'),
                    control1 = selectElement1.val();

                if (!control1) {
                    jQuery('#detailViewLayoutBtn').trigger('click');
                    result = app.vtranslate('JS_REQUIRED_FIELD');
                }

                return result;
            };
        </script>
    </div>
{/strip}