{strip}
    <div class="install-manager-page container-fluid px-3 px-lg-4 pb-4">
        <header class="card border-0 shadow-sm mb-3 overflow-hidden">
            <div class="card-body p-3 p-lg-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                    <span class="install-manager-heading-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary" aria-hidden="true">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </span>
                    <div class="flex-grow-1">
                        <h1 class="h3 mb-1">{vtranslate('LBL_INSTALL_MANAGER', 'Core')}</h1>
                        <p class="text-body-secondary mb-0">{vtranslate('LBL_INSTALL_MANAGER_DESCRIPTION', 'Core')}</p>
                    </div>
                    {if $INSTALL_MODE && $INSTALL_MODULE}
                        <div class="d-flex flex-wrap gap-2" aria-label="{vtranslate('LBL_SELECTED_ACTION', 'Core')}">
                            <span class="badge rounded-pill text-bg-light border px-3 py-2">
                                <i class="fa-solid fa-cube me-1 text-primary" aria-hidden="true"></i>
                                {$INSTALL_MODULE}
                            </span>
                            <span class="badge rounded-pill text-bg-primary px-3 py-2">{$INSTALL_MODE|escape}</span>
                        </div>
                    {/if}
                </div>
            </div>
        </header>

        <div class="row g-3 install-manager-workspace">
            <section class="col-xl-6" aria-labelledby="install-manager-modules-title">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-body d-flex align-items-center justify-content-between px-3 py-3">
                        <h2 id="install-manager-modules-title" class="h5 mb-0">{vtranslate('LBL_AVAILABLE_MODULES', 'Core')}</h2>
                        <span class="badge rounded-pill text-bg-secondary">{$MODULES|count}</span>
                    </div>
                    <div class="list-group list-group-flush install-manager-module-list">
                        {foreach from=$MODULES item=MODULE}
                            {assign var=MODULE_NAME value=$MODULE->getName()}
                            <article class="list-group-item px-3 py-3">
                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex align-items-center min-w-0">
                                        <span class="install-manager-module-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-body-tertiary text-primary flex-shrink-0">
                                            {$MODULE->getModuleIcon()}
                                        </span>
                                        <div class="ms-3 min-w-0">
                                            <h3 class="h6 mb-0 text-truncate">{$MODULE->get('label')}</h3>
                                            <span class="small text-body-secondary">{$MODULE_NAME}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a class="btn btn-sm {if $INSTALL_MODULE eq $MODULE_NAME && $INSTALL_MODE eq 'install'}btn-primary{else}btn-outline-primary{/if}" href="index.php?module={$MODULE_NAME}&view=InstallManager&mode=install">
                                            <i class="fa-solid fa-upload me-1" aria-hidden="true"></i>
                                            {vtranslate('LBL_INSTALL_ACTION', 'Core')}
                                        </a>
                                        <a class="btn btn-sm {if $INSTALL_MODULE eq $MODULE_NAME && $INSTALL_MODE eq 'update'}btn-secondary{else}btn-outline-secondary{/if}" href="index.php?module={$MODULE_NAME}&view=InstallManager&mode=update">
                                            <i class="fa-solid fa-rotate me-1" aria-hidden="true"></i>
                                            {vtranslate('LBL_UPDATE_ACTION', 'Core')}
                                        </a>
                                        <a class="btn btn-sm {if $INSTALL_MODULE eq $MODULE_NAME && $INSTALL_MODE eq 'migrate'}btn-success{else}btn-outline-success{/if}" href="index.php?module={$MODULE_NAME}&view=InstallManager&mode=migrate">
                                            <i class="fa-solid fa-database me-1" aria-hidden="true"></i>
                                            {vtranslate('LBL_MIGRATE_ACTION', 'Core')}
                                        </a>
                                        <a class="btn btn-sm {if $INSTALL_MODULE eq $MODULE_NAME && $INSTALL_MODE eq 'delete'}btn-danger{else}btn-outline-danger{/if}" href="index.php?module={$MODULE_NAME}&view=InstallManager&mode=delete">
                                            <i class="fa-solid fa-trash me-1" aria-hidden="true"></i>
                                            {vtranslate('LBL_DELETE_ACTION', 'Core')}
                                        </a>
                                    </div>
                                </div>
                            </article>
                        {foreachelse}
                            <div class="text-center text-body-secondary p-5">
                                <i class="fa-solid fa-box-open fs-1 mb-3 d-block" aria-hidden="true"></i>
                                {vtranslate('LBL_NO_INSTALL_MODULES', 'Core')}
                            </div>
                        {/foreach}
                    </div>
                </div>
            </section>

            <section class="col-xl-6" aria-labelledby="install-manager-preview-title">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 px-3 py-3">
                        <div class="min-w-0">
                            <h2 id="install-manager-preview-title" class="h5 mb-0">{vtranslate('LBL_INSTALL_OUTPUT', 'Core')}</h2>
                            {if $INSTALL_MODE && $INSTALL_MODULE}
                                <div class="small text-body-secondary text-truncate mt-1">
                                    {$INSTALL_MODULE} &middot; {$INSTALL_MODE|escape}
                                </div>
                            {/if}
                        </div>
                        {if $INSTALL_MODE && $INSTALL_MODULE}
                            <div class="d-flex flex-wrap gap-2" role="group" aria-label="{vtranslate('LBL_INSTALL_ACTIONS', 'Core')}">
                                <a class="btn btn-sm {if $INSTALL_MODE eq 'install'}btn-primary{else}btn-outline-primary{/if}" href="index.php?module={$INSTALL_MODULE}&view=InstallManager&mode=install">
                                    <i class="fa-solid fa-upload me-1" aria-hidden="true"></i>
                                    {vtranslate('LBL_INSTALL_ACTION', 'Core')}
                                </a>
                                <a class="btn btn-sm {if $INSTALL_MODE eq 'update'}btn-secondary{else}btn-outline-secondary{/if}" href="index.php?module={$INSTALL_MODULE}&view=InstallManager&mode=update">
                                    <i class="fa-solid fa-rotate me-1" aria-hidden="true"></i>
                                    {vtranslate('LBL_UPDATE_ACTION', 'Core')}
                                </a>
                                <a class="btn btn-sm {if $INSTALL_MODE eq 'migrate'}btn-success{else}btn-outline-success{/if}" href="index.php?module={$INSTALL_MODULE}&view=InstallManager&mode=migrate">
                                    <i class="fa-solid fa-database me-1" aria-hidden="true"></i>
                                    {vtranslate('LBL_MIGRATE_ACTION', 'Core')}
                                </a>
                                <a class="btn btn-sm {if $INSTALL_MODE eq 'delete'}btn-danger{else}btn-outline-danger{/if}" href="index.php?module={$INSTALL_MODULE}&view=InstallManager&mode=delete">
                                    <i class="fa-solid fa-trash me-1" aria-hidden="true"></i>
                                    {vtranslate('LBL_DELETE_ACTION', 'Core')}
                                </a>
                                <a class="btn btn-sm btn-outline-secondary" href="index.php?module={$INSTALL_MODULE}&view=InstallManager">
                                    <i class="fa-solid fa-xmark me-1" aria-hidden="true"></i>
                                    {vtranslate('LBL_CLOSE', 'Core')}
                                </a>
                            </div>
                        {/if}
                    </div>
                    <div class="card-body install-manager-preview">
                        {if $INSTALL_MODE && $INSTALL_MODULE}
                            <div class="install-manager-output">{$INSTALL_OUTPUT}</div>
                        {else}
                            <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center text-body-secondary p-5">
                                <span class="install-manager-empty-icon d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary mb-3" aria-hidden="true">
                                    <i class="fa-solid fa-arrow-pointer"></i>
                                </span>
                                <h3 class="h5 text-body mb-2">{vtranslate('LBL_SELECT_INSTALL_ACTION', 'Core')}</h3>
                                <p class="mb-0">{vtranslate('LBL_SELECT_INSTALL_ACTION_DESCRIPTION', 'Core')}</p>
                            </div>
                        {/if}
                    </div>
                </div>
            </section>
        </div>
    </div>
{/strip}
