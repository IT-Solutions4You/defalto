{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Add Payment button of a payment module (PAYMENT_MODULE) on a document, only for the module that pays the document; refunds are added from the More menu (PaymentAddMoreLink.tpl). Opens the quick create form; a payment changes the document header, the widget and the related lists, so the page is reloaded after saving. Without quick create the link opens the full form. *}
{strip}
    {assign var=PAYMENT_CONFIG value=Core_PaymentConfig_Helper::getInstance($PAYMENT_MODULE)}
    {if Users_Privileges_Model::isPermitted($PAYMENT_MODULE, 'CreateView') && !$PAYMENT_CONFIG->isRefundFor($MODULE_NAME)}
        <a class="btn ms-2 {$DETAIL_VIEW_BASIC_LINK->getStyleClass()}" id="{$MODULE_NAME}_detailView_advancedAction_Add{$PAYMENT_MODULE}"
           href="{Core_PaymentWidgetSummary_Helper::getAddUrl($PAYMENT_MODULE, $MODULE_NAME, $RECORD->getId())}"
           onclick="const quickCreate = jQuery('#quickCreateModules [data-name={$PAYMENT_MODULE}]'); if (quickCreate.length) { event.preventDefault(); quickCreate.trigger('click', { data: { reference_id: {$RECORD->getId()} }, noCache: false, callbackFunction: function () { window.onbeforeunload = null; window.location.reload(); } }); }">
            <i class="{$PAYMENT_CONFIG->getIcon()}"></i>
            <span class="ms-2">{vtranslate($PAYMENT_CONFIG->getAddLinkLabel($MODULE_NAME), $PAYMENT_MODULE)}</span>
        </a>
    {/if}
{/strip}
