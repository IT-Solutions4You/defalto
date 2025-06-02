<div class="container-fluid main-container">
    <div class="row">
        {include file='ModuleNavigator.tpl'|vtemplate_path:$MODULE}
        <div class="col p-0 overflow-auto">
            <div class="bg-body mb-lg-4 mx-lg-4 rounded">
                {include file="ModuleLinks.tpl"|vtemplate_path:$MODULE}
                <div class="p-3">
                    {if $TEMPLATE}
                        {include file=$TEMPLATE|vtemplate_path:$MODULE}
                    {else}
                        {include file='InstallView.tpl'|vtemplate_path:$MODULE}
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>