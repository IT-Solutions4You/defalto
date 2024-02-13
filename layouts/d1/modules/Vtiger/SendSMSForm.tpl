{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Vtiger/views/MassActionAjax.php *}
<div id="sendSmsContainer" class='modal-xs modal-dialog'>
    <form class="form-horizontal" id="massSave" method="post" action="index.php">
        <input type="hidden" name="module" value="{$MODULE}"/>
        <input type="hidden" name="source_module" value="{$SOURCE_MODULE}"/>
        <input type="hidden" name="action" value="MassSaveAjax"/>
        <input type="hidden" name="viewname" value="{$VIEWNAME}"/>
        <input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
        <input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
        <input type="hidden" name="search_key" value="{$SEARCH_KEY}"/>
        <input type="hidden" name="operator" value="{$OPERATOR}"/>
        <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}"/>
        <input type="hidden" name="search_params" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($SEARCH_PARAMS))}'/>
        <div class="modal-content">
            {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=vtranslate('LBL_SEND_SMS', $MODULE)}
            <div class="modal-body">
                <div class="py-2">
                    <strong>{vtranslate('LBL_STEP_1',$MODULE)}:</strong>
                    <span class="ms-2">{vtranslate('LBL_SELECT_THE_PHONE_NUMBER_FIELDS_TO_SEND',$MODULE)}</span>
                </div>
                <div class="py-2">
                    <select name="fields[]" data-placeholder="{vtranslate('LBL_SELECT_THE_PHONE_NUMBER_FIELDS_TO_SEND',$MODULE)}" data-rule-required="true" multiple class="select2 form-control">
                        {foreach item=PHONE_FIELD from=$PHONE_FIELDS}
                            {assign var=PHONE_FIELD_NAME value=$PHONE_FIELD->get('name')}
                            <option value="{$PHONE_FIELD_NAME}">
                                {if !empty($SINGLE_RECORD)}
                                    {assign var=FIELD_VALUE value=$SINGLE_RECORD->get($PHONE_FIELD_NAME)}
                                {/if}
                                {vtranslate($PHONE_FIELD->get('label'), $SOURCE_MODULE)}{if !empty($FIELD_VALUE)} ({$FIELD_VALUE}){/if}
                            </option>
                        {/foreach}
                    </select>
                </div>
                <div class="py-2">
                    <span id="phoneFormatWarning">
                        <span rel="popover" data-bs-placement="right" id="phoneFormatWarningPop" data-bs-original-title="{vtranslate('LBL_WARNING',$MODULE)}" data-bs-trigger="hover" data-bs-content="{vtranslate('LBL_PHONEFORMAT_WARNING_CONTENT',$MODULE)}">
                            <i class="bi bi-info-circle-fill"></i>
                        </span>
                        <span class="ms-2">{vtranslate('LBL_PHONE_FORMAT_WARNING', $MODULE)}</span>
                    </span>
                </div>
                <hr>
                <div class="py-2">
                    <strong>{vtranslate('LBL_STEP_2',$MODULE)}:</strong>
                    <span class="ms-2">{vtranslate('LBL_TYPE_THE_MESSAGE',$MODULE)} ({vtranslate('LBL_SMS_MAX_CHARACTERS_ALLOWED',$MODULE)})</span>
                </div>
                <div class="py-2">
                    <textarea class="form-control smsTextArea" data-rule-required="true" name="message" id="message" maxlength="160" placeholder="{vtranslate('LBL_WRITE_YOUR_MESSAGE_HERE', $MODULE)}"></textarea>
                </div>
            </div>
            {include file='ModalFooter.tpl'|vtemplate_path:$MODULE BUTTON_NAME=vtranslate('LBL_SEND', $MODULE)}
        </div>
    </form>
</div>
