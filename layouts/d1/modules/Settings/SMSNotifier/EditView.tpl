{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="modal-dialog modal-lg">
        <form class="form-horizontal" id="smsConfig" method="POST">
            <div class="modal-content">
                {if $RECORD_ID}
                    {assign var=TITLE value="{vtranslate('LBL_EDIT_CONFIGURATION', $QUALIFIED_MODULE_NAME)}"}
                {else}
                    {assign var=TITLE value="{vtranslate('LBL_ADD_CONFIGURATION', $QUALIFIED_MODULE_NAME)}"}
                {/if}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE}
                <div class="modal-body configContent">
                    {if $RECORD_ID}
                        <input type="hidden" value="{$RECORD_ID}" name="record" id="recordId"/>
                    {/if}
                    {foreach item=FIELD_MODEL from=$EDITABLE_FIELDS}
                        {assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
                        <div class="form-group row py-2">
                            <div class="col-lg-4">
                                <label for="{$FIELD_NAME}">{vtranslate($FIELD_NAME, $QUALIFIED_MODULE_NAME)}</label>
                            </div>
                            <div class="col-lg-6">
                                {assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()}
                                {assign var=FIELD_VALUE value=$RECORD_MODEL->get($FIELD_NAME)}
                                {if $FIELD_TYPE == 'picklist'}
                                    <select {if $FIELD_VALUE && $FIELD_NAME eq 'providertype'} disabled="disabled" {/if} class="select2 providerType form-control" id="{$FIELD_NAME}" name="{$FIELD_NAME}" placeholder="{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE_NAME)}">
                                        <option></option>
                                        {foreach item=PROVIDER_MODEL from=$PROVIDERS}
                                            {assign var=PROVIDER_NAME value=$PROVIDER_MODEL->getName()}
                                            <option value="{$PROVIDER_NAME}" {if $FIELD_VALUE eq $PROVIDER_NAME} selected {/if}>
                                                {vtranslate($PROVIDER_NAME, $QUALIFIED_MODULE_NAME)}
                                            </option>
                                        {/foreach}
                                    </select>
                                    {if $FIELD_VALUE && $FIELD_NAME eq 'providertype'}
                                        <input type="hidden" name="{$FIELD_NAME}" value="{$FIELD_VALUE}"/>
                                    {/if}
                                {elseif $FIELD_TYPE == 'radio'}
                                    <label>
                                        <input type="radio" id="{$FIELD_NAME}" name="{$FIELD_NAME}" value='1' {if $FIELD_VALUE} checked="checked" {/if} />
                                        <span class="ms-2">{vtranslate('LBL_YES', $QUALIFIED_MODULE_NAME)}</span>
                                    </label>
                                    <label class="ms-4">
                                        <input type="radio" id="{$FIELD_NAME}" name="{$FIELD_NAME}" value='0' {if !$FIELD_VALUE} checked="checked" {/if}/>
                                        <span class="ms-2">{vtranslate('LBL_NO', $QUALIFIED_MODULE_NAME)}</span>
                                    </label>
                                {elseif $FIELD_TYPE == 'password'}
                                    <input type="password" name="{$FIELD_NAME}" class="form-control" id="{$FIELD_NAME}" value="{$FIELD_VALUE}"/>
                                {else}
                                    <input type="text" name="{$FIELD_NAME}" class="form-control" id="{$FIELD_NAME}" {if $FIELD_NAME == 'username'} {/if} value="{$FIELD_VALUE}"/>
                                {/if}
                            </div>
                        </div>
                    {/foreach}
                    <div id="provider">
                        {if $RECORD_MODEL->get('providertype') neq ''}
                            {foreach key=PROVIDER_NAME item=PROVIDER_MODEL from=$PROVIDERS_FIELD_MODELS}
                                {if $PROVIDER_NAME eq $RECORD_MODEL->get('providertype')}
                                    <div id="{$PROVIDER_NAME}_container" class="providerFields">
                                        {assign var=TEMPLATE_NAME value=Settings_SMSNotifier_ProviderField_Model::getEditFieldTemplateName($PROVIDER_NAME)}
                                        {include file=$TEMPLATE_NAME|@vtemplate_path:$QUALIFIED_MODULE_NAME RECORD_MODEL=$RECORD_MODEL}
                                    </div>
                                {/if}
                            {/foreach}
                        {/if}
                    </div>
                    <div class="mt-3">
                        <span id='phoneFormatWarning'> 
                            <span data-bs-trigger="hover" data-bs-toggle="popover" data-bs-placement="right" id="phoneFormatWarningPop" data-bs-original-title="{vtranslate('LBL_WARNING',$MODULE)}" data-bs-content="{vtranslate('LBL_PHONEFORMAT_WARNING_CONTENT',$MODULE)}">
                                <i class="bi bi-info-circle-fill"></i>
                            </span>
                            <span class="ms-2">
                                {vtranslate('LBL_PHONE_FORMAT_WARNING', $MODULE)}
                            </span>
                        </span>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </div>
        </form>
    </div>
{/strip}
