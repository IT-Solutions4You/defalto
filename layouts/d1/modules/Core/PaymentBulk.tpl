{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Bulk payment window of documents selected in a list (Core_PaymentBulk_View); handled by Core_PaymentBulk_Js. *}
{strip}
    <div class="modal-dialog modal-lg paymentBulkContainer">
        <form class="modal-content paymentBulkForm" method="post" action="index.php">
            <input type="hidden" name="module" value="{$MODULE}">
            <input type="hidden" name="action" value="PaymentBulkSave">
            <input type="hidden" name="source_module" value="{$SOURCE_MODULE}">
            <input type="hidden" name="document_ids" value="{Core_Multireference_UIType::convertToValue(array_column($DOCUMENTS, 'id'))}">
            {include file=vtemplate_path('ModalHeader.tpl', $MODULE) TITLE=vtranslate('LBL_BULK_PAYMENT', $MODULE)}
            <div class="modal-body">
                {if $ERROR neq ''}
                    <div class="alert alert-warning mb-0">{$ERROR|escape}</div>
                {else}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{vtranslate('Amount', $MODULE)}</label>
                            {* Amount with its currency as in the payment form (uitype 35): Vtiger_Index_Js::registerAmountCurrencyEvents() handles the menu *}
                            <div class="input-group">
                                {foreach item=CURRENCY from=$CURRENCIES}
                                    {if $CURRENCY.currency_id eq $CURRENCY_ID}
                                        <button class="btn btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-2 amountCurrencyToggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{vtranslate('LBL_CURRENCY', $MODULE)}">{decode_html($CURRENCY.currencysymbol)|escape}</button>
                                    {/if}
                                {/foreach}
                                <ul class="dropdown-menu">
                                    {foreach item=CURRENCY from=$CURRENCIES}
                                        <li>
                                            <a class="dropdown-item amountCurrencyOption{if $CURRENCY.currency_id eq $CURRENCY_ID} active{/if}" href="#" data-value="{$CURRENCY.currency_id}" data-symbol="{decode_html($CURRENCY.currencysymbol)|escape}" data-rate="{$CURRENCY.conversionrate}">{decode_html($CURRENCY.currencysymbol)|escape} {decode_html($CURRENCY.currencylabel)|escape}</a>
                                        </li>
                                    {/foreach}
                                </ul>
                                <input type="hidden" class="amountCurrencySelect paymentBulkCurrency" name="currency_id" value="{$CURRENCY_ID}">
                                <input type="text" class="form-control text-end paymentBulkAmount" name="amount" value="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{vtranslate('Payment Date', $MODULE)}</label>
                            <div class="input-group">
                                <input type="text" class="form-control dateField" name="payment_date" value="{$PAYMENT_DATE|escape}" data-date-format="{$USER_MODEL->get('date_format')}">
                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-2">
                            <thead>
                                <tr>
                                    <th>{vtranslate('LBL_DOCUMENT', $MODULE)}</th>
                                    <th>{vtranslate('Due Date', $MODULE)}</th>
                                    <th class="text-end">{vtranslate('LBL_OPEN_AMOUNT', $MODULE)}</th>
                                    <th class="text-center">{vtranslate('LBL_PAY_OFF', $MODULE)}</th>
                                    <th class="text-end">{vtranslate('Amount', $MODULE)}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach item=DOCUMENT from=$DOCUMENTS}
                                    <tr class="paymentBulkRow" data-open="{$DOCUMENT.open}" data-rate="{$DOCUMENT.rate}">
                                        <td>{$DOCUMENT.number|escape} – {$DOCUMENT.name|escape}</td>
                                        <td>{$DOCUMENT.dueDate|escape}</td>
                                        <td class="text-end">{Core_PaymentCalculation_Helper::formatAmount($DOCUMENT.open, $DOCUMENT.currencyId)|escape}</td>
                                        <td class="text-center"><input type="checkbox" class="form-check-input paymentBulkPayOff"></td>
                                        <td class="w-25"><input type="text" class="form-control form-control-sm text-end paymentBulkPart" name="payment_allocation[{$DOCUMENT.id}]"></td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                    <div class="form-text text-end paymentBulkRemaining" data-label="{vtranslate('LBL_NOT_SPLIT', $MODULE)|escape}"></div>
                {/if}
            </div>
            {if $ERROR eq ''}
                {include file=vtemplate_path('ModalFooter.tpl', $MODULE) BUTTON_NAME=vtranslate('LBL_BULK_PAYMENT_SAVE', $MODULE)}
            {else}
                <div class="modal-footer">
                    <a href="#" class="btn btn-primary cancelLink" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                </div>
            {/if}
        </form>
    </div>
{/strip}
