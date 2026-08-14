{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{assign var=BOOTSTRAP_CSS value="vendor/twbs/bootstrap/dist/css/bootstrap.min.css"}
{assign var=BOOTSTRAP_ICONS_CSS value="vendor/twbs/bootstrap-icons/font/bootstrap-icons.min.css"}
{assign var=SKIN_CSS value="layouts/$DEFAULT_LAYOUT/skins/base/style.css"}
{assign var=INSTALL_CSS value="layouts/$DEFAULT_LAYOUT/modules/Core/resources/Install.css"}
<!DOCTYPE html>
<html lang="{$LANGUAGE|escape}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{$PAGETITLE|escape} - {$MODULE|escape}</title>
    <link rel="stylesheet" type="text/css" href="{vresource_url($BOOTSTRAP_CSS)}">
    <link rel="stylesheet" type="text/css" href="{vresource_url($BOOTSTRAP_ICONS_CSS)}">
    <link rel="stylesheet" type="text/css" href="{vresource_url($SKIN_CSS)}">
    <link rel="stylesheet" type="text/css" href="{vresource_url($INSTALL_CSS)}">
</head>
<body class="bg-body-secondary install-view-page">
<main class="container-fluid p-3 p-lg-4">
    <section class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="card-body p-3 p-lg-4">
            <div class="d-flex flex-column flex-md-row align-items-md-start gap-3">
                <span class="install-view-heading-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary flex-shrink-0" aria-hidden="true">
                    <i class="bi bi-box-seam-fill"></i>
                </span>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2 mb-2">
                        <h1 class="h3 mb-0">{vtranslate('LBL_INSTALL_WIZARD', 'Core')}</h1>
                        <span class="badge rounded-pill text-bg-light border text-truncate">{$MODULE}</span>
                    </div>
                    <p class="text-body-secondary mb-0">{vtranslate('LBL_INSTALL_WIZARD_DESCRIPTION', 'Core')}</p>
                </div>
                <a class="btn btn-outline-secondary flex-shrink-0"
                   href="index.php?module={$MODULE}&view=InstallManager"
                   target="_top">
                    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
                    {vtranslate('LBL_BACK_TO_INSTALL_MANAGER', 'Core')}
                </a>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4" role="group" aria-label="{vtranslate('LBL_INSTALL_ACTIONS', 'Core')}">
                <a class="btn {if $INSTALL_MODE eq 'install'}btn-primary{else}btn-outline-primary{/if}" href="index.php?module={$MODULE}&view=Install&mode=install">
                    <i class="bi bi-cloud-arrow-up-fill me-1" aria-hidden="true"></i>
                    {vtranslate('LBL_INSTALL_ACTION', 'Core')}
                </a>
                <a class="btn {if $INSTALL_MODE eq 'update'}btn-secondary{else}btn-outline-secondary{/if}" href="index.php?module={$MODULE}&view=Install&mode=update">
                    <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i>
                    {vtranslate('LBL_UPDATE_ACTION', 'Core')}
                </a>
                <a class="btn {if $INSTALL_MODE eq 'migrate'}btn-success{else}btn-outline-success{/if}" href="index.php?module={$MODULE}&view=Install&mode=migrate">
                    <i class="bi bi-database-fill-gear me-1" aria-hidden="true"></i>
                    {vtranslate('LBL_MIGRATE_ACTION', 'Core')}
                </a>
                <a class="btn {if $INSTALL_MODE eq 'delete'}btn-danger{else}btn-outline-danger{/if}" href="index.php?module={$MODULE}&view=Install&mode=delete">
                    <i class="bi bi-trash3-fill me-1" aria-hidden="true"></i>
                    {vtranslate('LBL_DELETE_ACTION', 'Core')}
                </a>
            </div>
        </div>
    </section>

    <section class="card border-0 shadow-sm overflow-hidden{if !$INSTALL_MODE} d-none{/if}" aria-labelledby="install-output-title">
        <div class="card-header bg-body d-flex align-items-center justify-content-between gap-2 px-3 py-3">
            <h2 id="install-output-title" class="h5 mb-0">{vtranslate('LBL_INSTALL_OUTPUT', 'Core')}</h2>
            <span class="badge rounded-pill text-bg-primary">{$INSTALL_MODE|escape}</span>
        </div>
        <div class="card-body install-view-output">
