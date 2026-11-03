{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="incomingPaymentsWidget">
        {if !empty($SUMMARY.payments)}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{vtranslate('Payment No', $MODULE_NAME)}</th>
                            <th>{vtranslate('Payment Date', $MODULE_NAME)}</th>
                            <th>{vtranslate('Payment Status', $MODULE_NAME)}</th>
                            <th class="text-end">{vtranslate('Amount', $MODULE_NAME)}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach item=PAYMENT from=$SUMMARY.payments}
                            <tr>
                                <td>
                                    <a class="btn-link" href="{$PAYMENT.url}" title="{$PAYMENT.name|escape}">{$PAYMENT.number|escape}</a>
                                </td>
                                <td>{$PAYMENT.date|escape}</td>
                                <td>
                                    <span class="badge {if $PAYMENT.isReceived}text-bg-success{else}text-bg-secondary{/if}">
                                        {vtranslate($PAYMENT.status, $MODULE_NAME)}
                                    </span>
                                </td>
                                <td class="text-end">{$PAYMENT.amount|escape}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {else}
            <p class="text-body-secondary mb-2">{vtranslate('LBL_NO_PAYMENTS', $MODULE_NAME)}</p>
        {/if}
        {if $SUMMARY.isIntegrated}
            <div class="d-flex flex-wrap gap-3 justify-content-end pt-3">
                {if $SUMMARY.total neq ''}
                    <span>{vtranslate('LBL_TOTAL_AMOUNT', $MODULE_NAME)}: <strong>{$SUMMARY.total|escape}</strong></span>
                {/if}
                {if $SUMMARY.paid neq ''}
                    <span>{vtranslate('LBL_PAID_AMOUNT', $MODULE_NAME)}: <strong>{$SUMMARY.paid|escape}</strong></span>
                {/if}
                {if $SUMMARY.balance neq ''}
                    <span>{vtranslate('LBL_BALANCE', $MODULE_NAME)}: <strong>{$SUMMARY.balance|escape}</strong></span>
                {/if}
            </div>
        {else}
            <p class="text-body-secondary mb-0">{vtranslate('LBL_INTEGRATION_INACTIVE', $MODULE_NAME)}</p>
        {/if}
    </div>
{/strip}
