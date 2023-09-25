{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
    <div class="modal-dialog modal-lg">
        <div id="convertLeadContainer" class="modelContainer modal-content">
            {if !$CONVERT_LEAD_FIELDS['Accounts'] && !$CONVERT_LEAD_FIELDS['Contacts']}
                <input type="hidden" id="convertLeadErrorTitle" value="{vtranslate('LBL_CONVERT_ERROR_TITLE',$MODULE)}"/>
                <input id="convertLeadError" class="convertLeadError" type="hidden" value="{vtranslate('LBL_CONVERT_LEAD_ERROR',$MODULE)}"/>
            {else}
                {assign var=HEADER_TITLE value={vtranslate('LBL_CONVERT_LEAD', $MODULE)}|cat:" "|cat:{$RECORD->getName()}}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
                <form class="form-horizontal" id="convertLeadForm" method="post" action="index.php">
                    <input type="hidden" name="module" value="{$MODULE}"/>
                    <input type="hidden" name="view" value="SaveConvertLead"/>
                    <input type="hidden" name="record" value="{$RECORD->getId()}"/>
                    <input type="hidden" name="modules" value=''/>
                    <input type="hidden" name="imageAttachmentId" value="{$IMAGE_ATTACHMENT_ID}">
                    {assign var=LEAD_COMPANY_NAME value=$RECORD->get('company')}
                    <div class="modal-body accordion container-fluid" id="leadAccordion">
                        {foreach item=MODULE_FIELD_MODEL key=MODULE_NAME from=$CONVERT_LEAD_FIELDS}
                            <div class="row">
                                <div class="col-lg-12 moduleContent">
                                    <div class="accordion-group convertLeadModules">
                                        <div class="header accordion-heading">
                                            <div data-bs-parent="#leadAccordion" data-bs-toggle="collapse" class="accordion-toggle moduleSelection" href="#{$MODULE_NAME}_FieldInfo">
                                                {if $ACCOUNT_FIELD_MODEL->isMandatory()}
                                                    <input type="hidden" id="oppAccMandatory" value={$ACCOUNT_FIELD_MODEL->isMandatory()}/>
                                                {/if}
                                                {if $CONTACT_FIELD_MODEL->isMandatory()}
                                                    <input type="hidden" id="oppConMandatory" value={$CONTACT_FIELD_MODEL->isMandatory()}/>
                                                {/if}
                                                {if $CONTACT_ACCOUNT_FIELD_MODEL->isMandatory()}
                                                    <input type="hidden" id="conAccMandatory" value={$CONTACT_ACCOUNT_FIELD_MODEL->isMandatory()}/>
                                                {/if}
                                                {assign var=SINGLE_MODULE_NAME value="SINGLE_$MODULE_NAME"}
                                                <h5>
                                                    <input id="{$MODULE_NAME}Module" class="convertLeadModuleSelection me-2" data-module="{vtranslate($MODULE_NAME,$MODULE_NAME)}" value="{$MODULE_NAME}" type="checkbox"
                                                        {if $MODULE_NAME eq 'Contacts' or ($LEAD_COMPANY_NAME neq '' and $MODULE_NAME eq 'Accounts') or ($CONTACT_ACCOUNT_FIELD_MODEL and $CONTACT_ACCOUNT_FIELD_MODEL->isMandatory() and $MODULE_NAME neq 'Potentials')}
                                                            {if $MODULE_NAME == 'Accounts' && $CONTACT_ACCOUNT_FIELD_MODEL && $CONTACT_ACCOUNT_FIELD_MODEL->isMandatory()} disabled="disabled" {/if} checked="checked"
                                                        {/if}/>
                                                    <span class="me-2">{vtranslate('LBL_CREATE', $MODULE)}</span>
                                                    <span>{vtranslate($SINGLE_MODULE_NAME, $MODULE_NAME)}</span>
                                                </h5>
                                            </div>
                                        </div>
                                        <hr>
                                        <div id="{$MODULE_NAME}_FieldInfo" class="accordion-body accordion-collapse collapse fieldInfo {$MODULE_NAME}_FieldInfo {if $CONVERT_LEAD_FIELDS['Accounts'] && $MODULE_NAME eq 'Accounts'}show{elseif !$CONVERT_LEAD_FIELDS['Accounts'] && $MODULE_NAME eq 'Contacts'}show{/if}">
                                            {foreach item=FIELD_MODEL from=$MODULE_FIELD_MODEL}
                                                <div class="row">
                                                    <div class="fieldLabel col-lg-4 text-end">
                                                        <label class='muted'>
                                                            <span class="me-2">{vtranslate($FIELD_MODEL->get('label'), $MODULE_NAME)}</span>
                                                            {if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger">*</span>{/if}
                                                        </label>
                                                    </div>
                                                    <div class="fieldValue col-lg-8">
                                                        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName())}
                                                    </div>
                                                </div>
                                                <br>
                                            {/foreach}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                        {/foreach}
                        <div class="defaultFields">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="my-3">
                                        <div class="row">
                                            {assign var=FIELD_MODEL value=$ASSIGN_TO}
                                            <div class="fieldLabel col-lg-4">
                                                <label class='muted pull-right'>
                                                    {vtranslate($FIELD_MODEL->get('label'), $MODULE_NAME)}&nbsp;
                                                    <span class="redColor">*</span>
                                                </label>
                                            </div>
                                            <div class="fieldValue col-lg-8">
                                                {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                            </div>
                                        </div>
                                        <br>
                                        <div class="row">
                                            <div class="fieldLabel col-lg-4">
                                                <label class='muted pull-right'>
                                                    {vtranslate('LBL_TRANSFER_RELATED_RECORD', $MODULE)}
                                                </label>
                                            </div>
                                            <div class="fieldValue col-lg-8">
                                                {foreach item=MODULE_FIELD_MODEL key=MODULE_NAME from=$CONVERT_LEAD_FIELDS}
                                                    {if $MODULE_NAME != 'Potentials'}
                                                        <label class="me-4">
                                                            <input type="radio" id="transfer{$MODULE_NAME}" class="transferModule me-2" name="transferModule" value="{$MODULE_NAME}" {if $CONVERT_LEAD_FIELDS['Contacts'] && $MODULE_NAME=="Contacts"} checked="" {elseif !$CONVERT_LEAD_FIELDS['Contacts'] && $MODULE_NAME=="Accounts"} checked="" {/if}/>
                                                            {if $MODULE_NAME eq 'Contacts'}
                                                                <span>{vtranslate('SINGLE_Contacts',$MODULE_NAME)}</span>
                                                            {else}
                                                                <span>{vtranslate('SINGLE_Accounts',$MODULE_NAME)}</span>
                                                            {/if}
                                                        </label>
                                                    {/if}
                                                {/foreach}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                        </div>
                    </div>
                    {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
                </form>
            {/if}
        </div>
    </div>
{/strip}
