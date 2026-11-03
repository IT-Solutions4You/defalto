{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {if Users_Privileges_Model::isPermitted('IncomingPayment', 'CreateView')}
        <li id="{$MODULE_NAME}_detailView_moreAction_AddIncomingPayment">
            <a class="dropdown-item text-secondary" href="{IncomingPayment_WidgetSummary_Helper::getAddUrl($MODULE_NAME, $RECORD->getId())}&app={$SELECTED_MENU_CATEGORY}">
                <i class="fa-solid fa-money-bill-trend-up"></i>
                <span class="ms-2">{vtranslate('LBL_ADD_INCOMING_PAYMENT', 'IncomingPayment')}</span>
            </a>
        </li>
    {/if}
{/strip}
