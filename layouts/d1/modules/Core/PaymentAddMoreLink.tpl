{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Add Payment item of a payment module (PAYMENT_MODULE) in the More menu of a document; opens the full form. *}
{strip}
    {if Users_Privileges_Model::isPermitted($PAYMENT_MODULE, 'CreateView')}
        {assign var=PAYMENT_CONFIG value=Core_PaymentConfig_Helper::getInstance($PAYMENT_MODULE)}
        <li id="{$MODULE_NAME}_detailView_moreAction_Add{$PAYMENT_MODULE}">
            <a class="dropdown-item text-secondary" href="{Core_PaymentWidgetSummary_Helper::getAddUrl($PAYMENT_MODULE, $MODULE_NAME, $RECORD->getId())}&app={$SELECTED_MENU_CATEGORY}">
                <i class="{$PAYMENT_CONFIG->getIcon()}"></i>
                <span class="ms-2">{vtranslate($PAYMENT_CONFIG->getAddLinkLabel(), $PAYMENT_MODULE)}</span>
            </a>
        </li>
    {/if}
{/strip}
