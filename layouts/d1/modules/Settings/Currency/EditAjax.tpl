{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {assign var=CURRENCY_MODEL_EXISTS value=true}
    {assign var=CURRENCY_ID value=$RECORD_MODEL->getId()}
    {if empty($CURRENCY_ID)}
        {assign var=CURRENCY_MODEL_EXISTS value=false}
    {/if}
    <div class="currencyModalContainer modal-dialog modal-lg modelContainer">
        <div class="modal-content">
            {if $CURRENCY_MODEL_EXISTS}
                {assign var="HEADER_TITLE" value={vtranslate('LBL_EDIT_CURRENCY', $QUALIFIED_MODULE)}}
            {else}
                {assign var="HEADER_TITLE" value={vtranslate('LBL_ADD_NEW_CURRENCY', $QUALIFIED_MODULE)}}
            {/if}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form id="editCurrency" class="form-horizontal" method="POST">
                <input type="hidden" name="record" value="{$CURRENCY_ID}"/>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="form-group row my-3">
                            <label class="control-label fieldLabel col-sm-5">
                                <span>{vtranslate('LBL_CURRENCY_NAME', $QUALIFIED_MODULE)}</span>
                                <span class="text-danger ms-2">*</span>
                            </label>
                            <div class="controls fieldValue col-sm-6">
                                <select class="select2 inputElement" name="currency_name">
                                    {foreach key=CURRENCY_ID item=CURRENCY_MODEL from=$ALL_CURRENCIES name=currencyIterator}
                                        {if !$CURRENCY_MODEL_EXISTS && $smarty.foreach.currencyIterator.first}
                                            {assign var=RECORD_MODEL value=$CURRENCY_MODEL}
                                        {/if}
                                        <option value="{$CURRENCY_MODEL->get('currency_name')}" data-code="{$CURRENCY_MODEL->get('currency_code')}" data-symbol="{$CURRENCY_MODEL->get('currency_symbol')}" {if $RECORD_MODEL->get('currency_name') == $CURRENCY_MODEL->get('currency_name')} selected {/if}>
                                            {vtranslate($CURRENCY_MODEL->get('currency_name'), $QUALIFIED_MODULE)}&nbsp;({$CURRENCY_MODEL->get('currency_symbol')})
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group row my-3">
                            <label class="control-label fieldLabel col-sm-5">{vtranslate('LBL_CURRENCY_CODE', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></label>
                            <div class="controls fieldValue col-sm-6">
                                <input type="text" class="inputElement form-control bgColor cursorPointerNotAllowed" name="currency_code" readonly value="{$RECORD_MODEL->get('currency_code')}" data-rule-required="true"/>
                            </div>
                        </div>
                        <div class="form-group row my-3">
                            <label class="control-label fieldLabel col-sm-5">{vtranslate('LBL_CURRENCY_SYMBOL', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></label>
                            <div class="controls fieldValue col-sm-6">
                                <input type="text" class="inputElement form-control bgColor cursorPointerNotAllowed" name="currency_symbol" readonly value="{$RECORD_MODEL->get('currency_symbol')}" data-rule-required="true"/>
                            </div>
                        </div>
                        <div class="form-group row my-3">
                            <label class="control-label fieldLabel col-sm-5">{vtranslate('LBL_CONVERSION_RATE', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></label>
                            <div class="controls fieldValue col-sm-6">
                                <input type="text" class="inputElement form-control mb-3" name="conversion_rate" data-rule-required="true" data-rule-positive="true" data-rule-greater_than_zero="true" placeholder="{vtranslate('LBL_ENTER_CONVERSION_RATE', $QUALIFIED_MODULE)}" value="{$RECORD_MODEL->get('conversion_rate')}"/>
                                <span class="muted">({vtranslate('LBL_BASE_CURRENCY', $QUALIFIED_MODULE)} - {$BASE_CURRENCY_MODEL->get('currency_name')})</span>
                            </div>
                        </div>
                        <div class="form-group row my-3">
                            <label class="control-label fieldLabel col-sm-5">{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</label>
                            <div class="controls fieldValue col-sm-6">
                                <label class="checkbox form-check">
                                    <input type="hidden" name="currency_status" value="Inactive"/>
                                    <input type="checkbox" name="currency_status" value="Active" class="form-check-input currencyStatus alignBottom" {if !$CURRENCY_MODEL_EXISTS}checked{else}{$RECORD_MODEL->get('currency_status')}{if $RECORD_MODEL->get('currency_status') == 'Active'}checked{/if}{/if} />
                                    <span class="ms-2">{vtranslate('LBL_CURRENCY_STATUS_DESC', $QUALIFIED_MODULE)}</span>
                                </label>
                            </div>
                        </div>
                        <div class="control-group transferCurrency hide">
                            <label class="muted control-label">{vtranslate('LBL_TRANSFER_CURRENCY', $QUALIFIED_MODULE)}&nbsp;{vtranslate('LBL_TO', $QUALIFIED_MODULE)}</label>
                            <span class="redColor">*</span>
                            <div class="controls row-fluid">
                                <select class="select2 span6" name="transform_to_id">
                                    {foreach key=CURRENCY_ID item=CURRENCY_MODEL from=$OTHER_EXISTING_CURRENCIES}
                                        <option value="{$CURRENCY_ID}">{vtranslate($CURRENCY_MODEL->get('currency_name'), $QUALIFIED_MODULE)}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
            </form>
        </div>
    </div>
{/strip}
