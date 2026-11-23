{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Add button of the common payments widget: the form of the only integrated payment module, or a choice of them. *}
{strip}
    {assign var=PAYMENT_ADD_LINKS value=Core_PaymentWidgetSummary_Helper::getAddLinks($MODULE_NAME, $RECORD->getId())}
    {if count($PAYMENT_ADD_LINKS) eq 1}
        {assign var=PAYMENT_ADD_LINK value=$PAYMENT_ADD_LINKS[0]}
        <div class="float-end">
            <a class="btn btn-sm text-secondary fw-bold" href="{$PAYMENT_ADD_LINK.url}&app={$SELECTED_MENU_CATEGORY}">
                <i class="fa fa-plus"></i>
                <span class="ms-2">{vtranslate($PAYMENT_ADD_LINK.label, 'Payments')}</span>
            </a>
        </div>
    {elseif count($PAYMENT_ADD_LINKS) gt 1}
        <div class="float-end dropdown paymentsWidgetAdd">
            <button type="button" class="btn btn-sm text-secondary fw-bold dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-plus"></i>
                <span class="ms-2">{vtranslate('LBL_ADD_PAYMENT', 'Payments')}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                {foreach item=PAYMENT_ADD_LINK from=$PAYMENT_ADD_LINKS}
                    <li>
                        <a class="dropdown-item" href="{$PAYMENT_ADD_LINK.url}&app={$SELECTED_MENU_CATEGORY}">
                            <i class="{$PAYMENT_ADD_LINK.icon}"></i>
                            <span class="ms-2">{vtranslate($PAYMENT_ADD_LINK.label, 'Payments')}</span>
                        </a>
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}
{/strip}
