{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
<div class="px-4 pb-4">
    <div class="bg-body rounded pb-3">
        <div class="p-3">
            <h4 class="m-0">{vtranslate('ITS4YouCalendar', $QUALIFIED_MODULE)} {vtranslate('LBL_INTEGRATION', $QUALIFIED_MODULE)}</h4>
        </div>
        <table class="table table-borderless">
            <tr>
                <td class="bg-body-secondary text-secondary w-50">{vtranslate('LBL_MODULE', $QUALIFIED_MODULE)}</td>
                <td class="bg-body-secondary text-secondary">{vtranslate('LBL_MODULE_FIELD', $QUALIFIED_MODULE)}</td>
            </tr>
            <tr>
                <td class="bg-body-secondary">
                    <input type="text" class="searchInput inputElement form-control" placeholder="{vtranslate('LBL_SEARCH_MODULE', $QUALIFIED_MODULE)}">
                </td>
                <td class="bg-body-secondary"></td>
            </tr>
            {foreach from=$SUPPORTED_MODULES item=$SUPPORTED_MODULE}
                {assign var=MODULE_MODEL value=$SUPPORTED_MODULE->moduleModel}
                {assign var=MODULE_NAME value=$MODULE_MODEL->getName()}
                {assign var=MODULE_LABEL value=vtranslate($MODULE_NAME, $MODULE_NAME)}
                <tr class="searchModule border-bottom" data-module="{strtolower($MODULE_NAME)}" data-label="{strtolower($MODULE_LABEL)}">
                    <td>
                        <a href="{$MODULE_MODEL->getListViewUrl()}">{$MODULE_LABEL}</a>
                    </td>
                    <td>
                        <input type="checkbox" class="fieldModule form-check-input" {if $SUPPORTED_MODULE->isActiveField()}checked="checked"{/if} value="{$MODULE_NAME}">
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
</div>