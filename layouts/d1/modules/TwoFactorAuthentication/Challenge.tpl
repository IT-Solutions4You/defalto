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
{assign var=CHALLENGE_CSS value="layouts/$DEFAULT_LAYOUT/modules/TwoFactorAuthentication/resources/Challenge.css"}
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="robots" content="noindex, nofollow"/>
    <title>{$PAGETITLE|escape}</title>
    <link rel="stylesheet" type="text/css" href="{vresource_url($BOOTSTRAP_CSS)}">
    <link rel="stylesheet" type="text/css" href="{vresource_url($BOOTSTRAP_ICONS_CSS)}">
    <link rel="stylesheet" type="text/css" href="{vresource_url($SKIN_CSS)}">
    <link rel="stylesheet" type="text/css" href="{vresource_url($CHALLENGE_CSS)}">
</head>
<body class="bg-body-secondary">
<div class="container-fluid p-0 two-factor-authentication-page">
    <div class="row g-0 min-vh-100">
        <main class="col-lg-6 d-flex align-items-center justify-content-center bg-body p-4 p-md-5">
            <section class="two-factor-authentication-panel w-100" aria-labelledby="two-factor-authentication-title">
                <header class="mb-4 text-center">
                    <img class="two-factor-authentication-logo mb-4"
                         src="{Core_Utils_Helper::getLogo()}"
                         alt="{$PAGETITLE|escape}">
                    <span class="two-factor-authentication-icon d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary mb-3"
                          aria-hidden="true">
                        <i class="bi bi-shield-lock-fill"></i>
                    </span>
                    <h1 id="two-factor-authentication-title" class="h3 mb-0">
                        {vtranslate('LBL_2FA_TITLE', $QUALIFIED_MODULE)}
                    </h1>
                </header>

                {if $ERROR_MESSAGE}
                    <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-exclamation-circle-fill flex-shrink-0" aria-hidden="true"></i>
                        <span>{$ERROR_MESSAGE|escape}</span>
                    </div>
                {/if}
                {if $NOTICE_MESSAGE}
                    <div class="alert alert-success d-flex align-items-center gap-2" role="status">
                        <i class="bi bi-check-circle-fill flex-shrink-0" aria-hidden="true"></i>
                        <span>{$NOTICE_MESSAGE|escape}</span>
                    </div>
                {/if}

                {if $STAGE eq 'choose'}
                    <p class="mb-4 text-center text-body-secondary">
                        {vtranslate('LBL_CHOOSE_METHOD', $QUALIFIED_MODULE)}
                    </p>
                    <div class="d-grid gap-3">
                        {foreach item=M from=$ALLOWED_METHODS}
                            <form method="post" action="index.php" class="m-0">
                                <input type="hidden" name="module" value="TwoFactorAuthentication">
                                <input type="hidden" name="action" value="Verify">
                                <input type="hidden" name="mode" value="choose">
                                <input type="hidden" name="two_factor_method" value="{$M|escape}">
                                <button type="submit"
                                        class="btn btn-outline-primary d-flex align-items-center gap-3 w-100 p-3 text-start">
                                    <i class="bi {if $M eq 'email'}bi-envelope-fill{else}bi-phone-fill{/if} fs-4 flex-shrink-0"
                                       aria-hidden="true"></i>
                                    <span>
                                        <span class="d-block fw-semibold">
                                            {if $M eq 'email'}
                                                {vtranslate('LBL_CHOICE_EMAIL', $QUALIFIED_MODULE)}
                                            {else}
                                                {vtranslate('LBL_CHOICE_TOTP', $QUALIFIED_MODULE)}
                                            {/if}
                                        </span>
                                        <small class="d-block fw-normal text-body-secondary mt-1">
                                            {if $M eq 'email'}
                                                {vtranslate('LBL_CHOICE_EMAIL_SUB', $QUALIFIED_MODULE)}
                                            {else}
                                                {vtranslate('LBL_CHOICE_TOTP_SUB', $QUALIFIED_MODULE)}
                                            {/if}
                                        </small>
                                    </span>
                                    <i class="bi bi-chevron-right ms-auto flex-shrink-0" aria-hidden="true"></i>
                                </button>
                            </form>
                        {/foreach}
                    </div>
                    <form method="post" action="index.php" class="mt-4 text-center">
                        <input type="hidden" name="module" value="TwoFactorAuthentication">
                        <input type="hidden" name="action" value="Verify">
                        <input type="hidden" name="mode" value="cancel">
                        <button type="submit" class="btn btn-link p-0 text-decoration-none">
                            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
                            {vtranslate('LBL_BACK_TO_LOGIN', $QUALIFIED_MODULE)}
                        </button>
                    </form>
                {else}
                    {if $STAGE eq 'email'}
                        <p class="mb-4 text-center text-body-secondary">
                            {vtranslate('LBL_EMAIL_PROMPT', $QUALIFIED_MODULE)}
                        </p>
                    {elseif $STAGE eq 'enroll'}
                        <p class="mb-4 text-center text-body-secondary">
                            {vtranslate('LBL_ENROLL_PROMPT', $QUALIFIED_MODULE)}
                        </p>
                        <div class="two-factor-authentication-qr rounded border bg-body-tertiary p-3 mb-3 text-center">
                            {$ENROLL.qr}
                        </div>
                        <div class="rounded border bg-body-tertiary p-3 mb-4 text-center text-body-secondary">
                            <span class="d-block small mb-2">
                                {vtranslate('LBL_MANUAL_KEY', $QUALIFIED_MODULE)}
                            </span>
                            <code class="two-factor-authentication-key d-block">{$ENROLL.secret|escape}</code>
                        </div>
                    {else}
                        <p class="mb-4 text-center text-body-secondary">
                            {vtranslate('LBL_TOTP_PROMPT', $QUALIFIED_MODULE)}
                        </p>
                    {/if}
                    <form method="post" action="index.php" autocomplete="off">
                        <input type="hidden" name="module" value="TwoFactorAuthentication">
                        <input type="hidden" name="action" value="Verify">
                        <div class="mb-3">
                            <label class="visually-hidden" for="two-factor-authentication-code">
                                {vtranslate('LBL_CODE_PLACEHOLDER', $QUALIFIED_MODULE)}
                            </label>
                            <input type="text"
                                   id="two-factor-authentication-code"
                                   class="form-control form-control-lg two-factor-authentication-code"
                                   name="code"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   maxlength="20"
                                   autofocus
                                   required
                                   placeholder="{vtranslate('LBL_CODE_PLACEHOLDER', $QUALIFIED_MODULE)}">
                        </div>
                        <button type="submit"
                                class="btn btn-primary w-100 py-3 fw-semibold"
                                {if $LOCK_REMAINING > 0}disabled{/if}>
                            <i class="bi bi-shield-check me-2" aria-hidden="true"></i>
                            {if $STAGE eq 'enroll'}
                                {vtranslate('LBL_ENROLL_VERIFY', $QUALIFIED_MODULE)}
                            {else}
                                {vtranslate('LBL_VERIFY', $QUALIFIED_MODULE)}
                            {/if}
                        </button>
                    </form>
                    <div class="d-flex flex-wrap align-items-center justify-content-center gap-3 mt-4">
                        {if $STAGE eq 'email'}
                            <form method="post" action="index.php" class="m-0">
                                <input type="hidden" name="module" value="TwoFactorAuthentication">
                                <input type="hidden" name="action" value="Verify">
                                <input type="hidden" name="mode" value="resend">
                                <button type="submit" class="btn btn-link p-0 text-decoration-none">
                                    <i class="bi bi-arrow-clockwise me-1" aria-hidden="true"></i>
                                    {vtranslate('LBL_RESEND_CODE', $QUALIFIED_MODULE)}
                                </button>
                            </form>
                        {/if}
                        {if $MULTIPLE_METHODS}
                            <form method="post" action="index.php" class="m-0">
                                <input type="hidden" name="module" value="TwoFactorAuthentication">
                                <input type="hidden" name="action" value="Verify">
                                <input type="hidden" name="mode" value="switch">
                                <button type="submit" class="btn btn-link p-0 text-decoration-none">
                                    <i class="bi bi-arrow-left-right me-1" aria-hidden="true"></i>
                                    {vtranslate('LBL_USE_OTHER_METHOD', $QUALIFIED_MODULE)}
                                </button>
                            </form>
                        {/if}
                        <form method="post" action="index.php" class="m-0">
                            <input type="hidden" name="module" value="TwoFactorAuthentication">
                            <input type="hidden" name="action" value="Verify">
                            <input type="hidden" name="mode" value="cancel">
                            <button type="submit" class="btn btn-link p-0 text-decoration-none">
                                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
                                {vtranslate('LBL_BACK_TO_LOGIN', $QUALIFIED_MODULE)}
                            </button>
                        </form>
                    </div>
                    {if $STAGE ne 'enroll'}
                        <p class="small text-center text-body-secondary mt-3 mb-0">
                            {vtranslate('LBL_BACKUP_HINT', $QUALIFIED_MODULE)}
                        </p>
                    {/if}
                {/if}
            </section>
        </main>
        <aside class="col-lg-6 d-none d-lg-block two-factor-authentication-cover" aria-hidden="true"></aside>
    </div>
</div>
</body>
</html>
