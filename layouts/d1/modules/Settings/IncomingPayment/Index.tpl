{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="incoming-payment-settings px-4 pb-4">
        <div class="rounded bg-body">
            <div class="container-fluid p-3 border-bottom">
                <h4 class="m-0">
                    <i class="fa-solid fa-money-bill-trend-up me-2"></i>
                    {vtranslate('LBL_INCOMING_PAYMENT_INTEGRATION', $QUALIFIED_MODULE)}
                </h4>
            </div>
            <div class="p-3">
                <p class="text-body-secondary">{vtranslate('LBL_INTEGRATION_INTRO', $QUALIFIED_MODULE)}</p>
                {if empty($INTEGRATION_ROWS)}
                    <div class="alert alert-info mb-0">{vtranslate('LBL_NO_MODULES', $QUALIFIED_MODULE)}</div>
                {else}
                    <form class="incomingPaymentIntegrationForm">
                        <div class="row g-2 align-items-center mb-3">
                            <label class="col-auto col-form-label fw-semibold" for="underpaymentThreshold">{vtranslate('LBL_UNDERPAYMENT_THRESHOLD', $QUALIFIED_MODULE)}</label>
                            <div class="col-auto">
                                <input type="number" min="0" step="0.01" class="form-control" id="underpaymentThreshold" name="underpayment_threshold" value="{$UNDERPAYMENT_THRESHOLD|escape}">
                            </div>
                            <div class="col-12 form-text mt-0">{vtranslate('LBL_UNDERPAYMENT_THRESHOLD_HELP', $QUALIFIED_MODULE)}</div>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>{vtranslate('LBL_MODULE', $QUALIFIED_MODULE)}</th>
                                        <th class="text-center">{vtranslate('LBL_ACTIVE', $QUALIFIED_MODULE)}</th>
                                        <th>{vtranslate('LBL_STATUS_FIELD', $QUALIFIED_MODULE)}</th>
                                        <th>{vtranslate('LBL_STATUS_VALUE', $QUALIFIED_MODULE)}</th>
                                        <th>{vtranslate('LBL_STATUS_VALUE_BACK', $QUALIFIED_MODULE)}</th>
                                        <th>{vtranslate('LBL_RELATED_TO_FIELD', $QUALIFIED_MODULE)}</th>
                                        <th>{vtranslate('LBL_TOTAL_FIELD', $QUALIFIED_MODULE)}</th>
                                        <th>{vtranslate('LBL_PAID_FIELD', $QUALIFIED_MODULE)}</th>
                                        <th>{vtranslate('LBL_BALANCE_FIELD', $QUALIFIED_MODULE)}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach item=ROW from=$INTEGRATION_ROWS}
                                        {assign var=ROW_MODULE value=$ROW.module}
                                        {assign var=ROW_CONFIG value=$ROW.config}
                                        {assign var=ROW_STATUS_VALUES value=[]}
                                        {if isset($ROW.picklists[$ROW_CONFIG.status_field])}
                                            {assign var=ROW_STATUS_VALUES value=$ROW.picklists[$ROW_CONFIG.status_field]}
                                        {/if}
                                        <tr class="integrationRow" data-module="{$ROW_MODULE}">
                                            <td class="fw-semibold">{$ROW.label|escape}</td>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input" name="integration[{$ROW_MODULE}][active]" value="1"{if $ROW_CONFIG.active} checked{/if}>
                                            </td>
                                            <td>
                                                <select class="form-select statusField" name="integration[{$ROW_MODULE}][status_field]" data-picklists="{$ROW.picklistsJson|escape}">
                                                    <option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                                    {foreach key=FIELD_NAME item=FIELD_LABEL from=$ROW.statusFields}
                                                        <option value="{$FIELD_NAME}"{if $ROW_CONFIG.status_field eq $FIELD_NAME} selected{/if}>{$FIELD_LABEL|escape}</option>
                                                    {/foreach}
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select statusValue" name="integration[{$ROW_MODULE}][status_value]" data-selected="{$ROW_CONFIG.status_value|escape}">
                                                    <option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                                    {foreach key=VALUE item=VALUE_LABEL from=$ROW_STATUS_VALUES}
                                                        <option value="{$VALUE|escape}"{if $ROW_CONFIG.status_value eq $VALUE} selected{/if}>{$VALUE_LABEL|escape}</option>
                                                    {/foreach}
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select statusValue" name="integration[{$ROW_MODULE}][status_value_back]" data-selected="{$ROW_CONFIG.status_value_back|escape}">
                                                    <option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                                    {foreach key=VALUE item=VALUE_LABEL from=$ROW_STATUS_VALUES}
                                                        <option value="{$VALUE|escape}"{if $ROW_CONFIG.status_value_back eq $VALUE} selected{/if}>{$VALUE_LABEL|escape}</option>
                                                    {/foreach}
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select" name="integration[{$ROW_MODULE}][relatedto_field]">
                                                    <option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                                    {foreach key=FIELD_NAME item=FIELD_LABEL from=$ROW.referenceFields}
                                                        <option value="{$FIELD_NAME}"{if $ROW_CONFIG.relatedto_field eq $FIELD_NAME} selected{/if}>{$FIELD_LABEL|escape}</option>
                                                    {/foreach}
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select totalField" name="integration[{$ROW_MODULE}][total_field]">
                                                    <option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                                    {foreach key=FIELD_NAME item=FIELD_LABEL from=$ROW.amountFields}
                                                        <option value="{$FIELD_NAME}"{if $ROW_CONFIG.total_field eq $FIELD_NAME} selected{/if}>{$FIELD_LABEL|escape}</option>
                                                    {/foreach}
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select" name="integration[{$ROW_MODULE}][paid_field]">
                                                    <option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                                    {foreach key=FIELD_NAME item=FIELD_LABEL from=$ROW.amountFields}
                                                        <option value="{$FIELD_NAME}"{if $ROW_CONFIG.paid_field eq $FIELD_NAME} selected{/if}>{$FIELD_LABEL|escape}</option>
                                                    {/foreach}
                                                    {if $ROW_CONFIG.paid_field neq '' && !isset($ROW.amountFields[$ROW_CONFIG.paid_field])}
                                                        <option value="{$ROW_CONFIG.paid_field|escape}" selected>{$ROW_CONFIG.paid_field|escape}</option>
                                                    {/if}
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select" name="integration[{$ROW_MODULE}][balance_field]">
                                                    <option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                                    {foreach key=FIELD_NAME item=FIELD_LABEL from=$ROW.amountFields}
                                                        <option value="{$FIELD_NAME}"{if $ROW_CONFIG.balance_field eq $FIELD_NAME} selected{/if}>{$FIELD_LABEL|escape}</option>
                                                    {/foreach}
                                                    {if $ROW_CONFIG.balance_field neq '' && !isset($ROW.amountFields[$ROW_CONFIG.balance_field])}
                                                        <option value="{$ROW_CONFIG.balance_field|escape}" selected>{$ROW_CONFIG.balance_field|escape}</option>
                                                    {/if}
                                                </select>
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                        <p class="text-body-secondary small">{vtranslate('LBL_FIELDS_NOTE', $QUALIFIED_MODULE)}</p>
                        <div class="text-end">
                            <button type="button" class="btn btn-primary saveIntegration">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
                        </div>
                    </form>
                {/if}
            </div>
        </div>
    </div>
{/strip}
