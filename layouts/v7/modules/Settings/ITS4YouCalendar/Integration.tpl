<div class="padding20">
    <h4>{vtranslate('ITS4YouCalendar', $QUALIFIED_MODULE)} {vtranslate('LBL_INTEGRATION', $QUALIFIED_MODULE)}</h4>
    <hr>
    <table class="table border1px">
        <tr>
            <td class="w-50">{vtranslate('LBL_MODULE', $QUALIFIED_MODULE)}</td>
            <td>{vtranslate('LBL_MODULE_FIELD', $QUALIFIED_MODULE)}</td>
        </tr>
        <tr>
            <td>
                <input type="text" class="searchInput inputElement" placeholder="{vtranslate('LBL_SEARCH_MODULE', $QUALIFIED_MODULE)}">
            </td>
            <td></td>
        </tr>
        {foreach from=$SUPPORTED_MODULES item=$SUPPORTED_MODULE}
            {assign var=MODULE_MODEL value=$SUPPORTED_MODULE->moduleModel}
            {assign var=MODULE_NAME value=$MODULE_MODEL->getName()}
            {assign var=MODULE_LABEL value=vtranslate($MODULE_NAME, $MODULE_NAME)}
            <tr class="searchModule" data-module="{strtolower($MODULE_NAME)}" data-label="{strtolower($MODULE_LABEL)}">
                <td><a href="{$MODULE_MODEL->getListViewUrl()}">{$MODULE_LABEL}</a></td>
                <td><input type="checkbox" class="fieldModule" {if $SUPPORTED_MODULE->isActiveField()}checked="checked"{/if} value="{$MODULE_NAME}"></td>
            </tr>
        {/foreach}
    </table>
</div>