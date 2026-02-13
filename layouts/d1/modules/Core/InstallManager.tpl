{strip}
    <div class="px-4 pb-4">
        <div class="container-fluid">
            <div class="row py-2">
                <div class="col text-end fs-3">
                    <span>Module:</span>
                    <b class="mx-2">{$INSTALL_MODULE}</b>
                </div>
                <div class="col fs-3">
                    <span>Mode:</span>
                    <b class="mx-2">{$INSTALL_MODE}</b>
                </div>
            </div>
            <div class="row">
                <div class="col border rounded-start">
                    {foreach from=$MODULES item=MODULE}
                        {assign var=MODULE_NAME value=$MODULE->getName()}
                        <div class="row align-items-center border-bottom py-2">
                            <div class="col fw-bold">{$MODULE->get('label')} ({$MODULE_NAME})</div>
                            <div class="col-auto">
                                <a class="btn btn-primary me-2" href="index.php?module={$MODULE_NAME}&view=InstallManager&mode=install">Install</a>
                                <a class="btn btn-primary me-2" href="index.php?module={$MODULE_NAME}&view=InstallManager&mode=update">Update</a>
                                <a class="btn btn-success me-2" href="index.php?module={$MODULE_NAME}&view=InstallManager&mode=migrate">Migrate</a>
                                <a class="btn btn-danger me-2" href="index.php?module={$MODULE_NAME}&view=InstallManager&mode=delete">Delete</a>
                            </div>
                        </div>
                    {/foreach}
                </div>
                <div class="col border rounded-end">
                    {if $INSTALL_MODE && $INSTALL_MODULE}
                        <iframe class="h-100 w-100" sandbox="" src="index.php?module={$INSTALL_MODULE}&view=Install&mode={$INSTALL_MODE}" frameborder="0"></iframe>
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/strip}