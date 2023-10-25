{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
<form id="headerForm" method="POST">
    <div class="row align-items-center pt-3">
        {assign var=FIELDS_MODELS_LIST value=$MODULE_MODEL->getFields()}
        {foreach item=FIELD_MODEL from=$FIELDS_MODELS_LIST}
            {assign var=FIELD_DATA_TYPE value=$FIELD_MODEL->getFieldDataType()}
            {assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
            {if $FIELD_MODEL->isHeaderField() && $FIELD_MODEL->isActiveField() && $FIELD_MODEL->isViewable()}
                {if $ADD_SLASH}
                    <div class="col-auto fs-5 text-secondary d-none d-lg-inline-block">/</div>
                {/if}
                {assign var=ADD_SLASH value=true}
                {assign var=FIELD_MODEL value=$FIELD_MODEL->set('fieldvalue', $RECORD->get($FIELD_NAME))}
                <div class="col-lg-auto headerAjaxEdit td">
                    <div class="fieldLabel">
                        <div class="row text-secondary fieldName" style="min-height: 2rem">
                            <div class="col">{vtranslate($FIELD_MODEL->get('label'),$MODULE)}</div>
                        </div>
                        <div class="row" style="min-height: 2rem">
                            {assign var=DISPLAY_VALUE value="{$FIELD_MODEL->getDisplayValue($RECORD->get($FIELD_NAME))}"}
                            <div class="col value fw-semibold {$FIELD_NAME}" title="{vtranslate($FIELD_MODEL->get('label'),$MODULE)} : {strip_tags($DISPLAY_VALUE)}">
                                {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
                            </div>
                            {if $FIELD_MODEL->isEditable() eq 'true' && $LIST_PREVIEW neq 'true' && $IS_AJAX_ENABLED eq 'true'}
                                <div class="col-auto d-flex justify-content-end">
                                    <span class="hide edit w-100">
                                        {assign var=HEADER_FIELD_NAME value=$FIELD_MODEL->get('name')}
                                        {if $FIELD_DATA_TYPE eq 'multipicklist'}
                                            <input type="hidden" class="fieldBasicData" data-name="{$HEADER_FIELD_NAME}[]" data-type="{$FIELD_MODEL->getFieldDataType()}" data-displayvalue="{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}" data-value="{$FIELD_MODEL->get('fieldvalue')}" />
                                        {else}
                                            <input type="hidden" class="fieldBasicData" data-name="{$HEADER_FIELD_NAME}" data-type="{$FIELD_MODEL->getFieldDataType()}" data-displayvalue="{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}" data-value="{$FIELD_MODEL->get('fieldvalue')}" />
                                        {/if}
                                    </span>
                                    <span class="action">
                                        <a href="#" onclick="return false;" class="editAction fa fa-pencil"></a>
                                    </span>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            {/if}
        {/foreach}
    </div>
</form>
{/strip}