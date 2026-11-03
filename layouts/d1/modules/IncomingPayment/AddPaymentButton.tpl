{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Opens the quick create form; a payment changes the document header, the widget and the related lists, so the page is reloaded after saving. Without quick create the link opens the full form. *}
{strip}
    {if Users_Privileges_Model::isPermitted('IncomingPayment', 'CreateView')}
        <a class="btn ms-2 {$DETAIL_VIEW_BASIC_LINK->getStyleClass()}" id="{$MODULE_NAME}_detailView_advancedAction_AddIncomingPayment"
           href="{IncomingPayment_WidgetSummary_Helper::getAddUrl($MODULE_NAME, $RECORD->getId())}"
           onclick="const quickCreate = jQuery('#quickCreateModules [data-name=IncomingPayment]'); if (quickCreate.length) { event.preventDefault(); quickCreate.trigger('click', { data: { relationid: {$RECORD->getId()} }, noCache: false, callbackFunction: function () { window.onbeforeunload = null; window.location.reload(); } }); }">
            <i class="fa-solid fa-money-bill-trend-up"></i>
            <span class="ms-2">{vtranslate('LBL_ADD_INCOMING_PAYMENT', 'IncomingPayment')}</span>
        </a>
    {/if}
{/strip}
