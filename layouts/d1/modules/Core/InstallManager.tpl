{strip}
    <div class="px-4 pb-4">
        <div class="container-fluid h-100">
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
            <div class="row py-2">
                <div class="col border rounded-start">
                    {foreach from=$MODULES item=MODULE}
                        {assign var=MODULE_NAME value=$MODULE->getName()}
                        <div class="row align-items-center border-bottom py-2">
                            <div class="col">
                                {$MODULE->getModuleIcon()}
                                <b class="ms-2">{$MODULE->get('label')}</b>
                                <span class="ms-2">({$MODULE_NAME})</span>
                            </div>
                            <div class="col-auto">
                                <a class="btn btn-primary me-2" href="index.php?module={$MODULE_NAME}&view=InstallManager&mode=install">
                                    <i class="fa-solid fa-upload"></i>
                                    <span class="ms-2">Install</span>
                                </a>
                                <a class="btn btn-primary me-2" href="index.php?module={$MODULE_NAME}&view=InstallManager&mode=update">
                                    <i class="fa-solid fa-rotate"></i>
                                    <span class="ms-2">Update</span>
                                </a>
                                <a class="btn btn-success me-2" href="index.php?module={$MODULE_NAME}&view=InstallManager&mode=migrate">
                                    <i class="fa-solid fa-database"></i>
                                    <span class="ms-2">Migrate</span>
                                </a>
                                <a class="btn btn-danger me-2" href="index.php?module={$MODULE_NAME}&view=InstallManager&mode=delete">
                                    <i class="fa-solid fa-trash"></i>
                                    <span class="ms-2">Delete</span>
                                </a>
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