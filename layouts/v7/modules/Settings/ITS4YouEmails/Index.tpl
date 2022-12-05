<div class="padding20 emailsIntegration">
    <h3>{vtranslate($MODULE, $QUALIFIED_MODULE)}</h3>
    <hr>
    <form method="post">
        <table class="table border1px">
            <tr>
                <th colspan="2">
                    <input type="text" class="inputElement searchModule" placeholder="{vtranslate('LBL_SEARCH_MODULE', $QUALIFIED_MODULE)}">
                </th>
            </tr>
            <tr>
                {assign var=SUPPORTED_NUM value=0}
                {foreach from=$SUPPORTED_MODULES item=SUPPORTED_MODULE}
                {assign var=SUPPORTED_NUM value=$SUPPORTED_NUM + 1}
                {assign var=SUPPORTED_MODULE_NAME value=$SUPPORTED_MODULE->getName()}
                <td class="updateModuleTd">
                    <label>
                        <input class="updateModule" type="checkbox" {if ITS4YouEmails_Integration_Model::isActive($SUPPORTED_MODULE_NAME)}checked="checked"{/if} data-module="{$SUPPORTED_MODULE_NAME}">
                        <span>{vtranslate($SUPPORTED_MODULE_NAME, $SUPPORTED_MODULE_NAME)}</span>
                    </label>
                </td>

                {if $SUPPORTED_NUM eq 2}
                {assign var=SUPPORTED_NUM value=0}
            </tr>
            <tr>
                {/if}
                {/foreach}
            </tr>
        </table>
    </form>
</div>