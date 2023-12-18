<div class="emailsIntegration px-4 pb-4">
    <div class="rounded bg-body p-3">
        <form method="post">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-6 py-2 border-bottom">
                        <h3>{vtranslate($MODULE, $QUALIFIED_MODULE)}</h3>
                    </div>
                    <div class="col-lg-6 py-2 border-bottom">
                        <div class="input-group">
                            <input type="text" id="searchModule" class="inputElement searchModule form-control" placeholder="{vtranslate('LBL_SEARCH_MODULE', $QUALIFIED_MODULE)}">
                            <label for="searchModule" class="input-group-text">
                                <i class="fa fa-search"></i>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    {assign var=SUPPORTED_NUM value=0}
                    {foreach from=$SUPPORTED_MODULES item=SUPPORTED_MODULE}
                    {assign var=SUPPORTED_NUM value=$SUPPORTED_NUM + 1}
                    {assign var=SUPPORTED_MODULE_NAME value=$SUPPORTED_MODULE->getName()}
                    <div class="updateModuleTd col-lg-6 py-2 border-bottom">
                        <label>
                            <input class="updateModule" type="checkbox" {if ITS4YouEmails_Integration_Model::isActive($SUPPORTED_MODULE_NAME)}checked="checked"{/if} data-module="{$SUPPORTED_MODULE_NAME}">
                            <span>{vtranslate($SUPPORTED_MODULE_NAME, $SUPPORTED_MODULE_NAME)}</span>
                        </label>
                    </div>
                    {/foreach}
                </div>
            </div>
        </form>
    </div>
</div>