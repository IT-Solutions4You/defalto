{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="two-factor-authentication-manage px-4 pb-4">
        <div class="rounded bg-body p-3">
            <h4 class="border-bottom pb-3 mb-3">
                <i class="bi bi-shield-lock-fill me-2"></i>
                {vtranslate('LBL_MANAGE_TITLE', $LANGUAGE_MODULE)}
            </h4>

            {if $ERROR_MESSAGE}
                <div class="alert alert-danger">{$ERROR_MESSAGE}</div>{/if}
            {if $NOTICE_MESSAGE}
                <div class="alert alert-success">{$NOTICE_MESSAGE}</div>{/if}

            {if $STAGE eq 'enroll'}
                <div class="two-factor-authentication-box">
                    <p class="text-body-secondary">{vtranslate('LBL_ENROLL_PROMPT', $LANGUAGE_MODULE)}</p>
                    <div class="two-factor-authentication-qr">{$ENROLL.qr}</div>
                    <p class="small text-body-secondary">
                        {vtranslate('LBL_MANUAL_KEY', $LANGUAGE_MODULE)}
                        <br>
                        <code>{$ENROLL.secret|escape}</code>
                    </p>
                    <form method="post" action="index.php" autocomplete="off">
                        <input type="hidden" name="module" value="TwoFactorAuthentication">
                        <input type="hidden" name="action" value="Manage">
                        <input type="hidden" name="mode" value="confirm">
                        <div class="mb-3">
                            <input type="text" class="form-control two-factor-authentication-code-input"
                                   name="code" inputmode="numeric" autocomplete="one-time-code"
                                   maxlength="10" autofocus
                                   placeholder="{vtranslate('LBL_CODE_PLACEHOLDER', $LANGUAGE_MODULE)}">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            {vtranslate('LBL_ENROLL_VERIFY', $LANGUAGE_MODULE)}
                        </button>
                        <button type="submit" name="mode" value="cancel" class="btn btn-link">
                            {vtranslate('LBL_CANCEL', $LANGUAGE_MODULE)}
                        </button>
                    </form>
                </div>
            {else}
                <ul class="nav nav-tabs gap-2" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {if $ACTIVE_TAB neq 'backup'}active{/if}" data-bs-toggle="tab"
                                data-bs-target="#two-factor-method" type="button">
                            {vtranslate('LBL_AUTH_METHOD', $LANGUAGE_MODULE)}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {if $ACTIVE_TAB eq 'backup'}active{/if}" data-bs-toggle="tab"
                                data-bs-target="#two-factor-backup-codes" type="button">
                            {vtranslate('LBL_BACKUP_CODES', $LANGUAGE_MODULE)}
                        </button>
                    </li>
                </ul>
                <div class="tab-content pt-3">
                    <div class="tab-pane fade {if $ACTIVE_TAB neq 'backup'}show active{/if}" id="two-factor-method">
                        <form method="post" action="index.php">
                            <input type="hidden" name="module" value="TwoFactorAuthentication">
                            <input type="hidden" name="action" value="Manage">
                            <input type="hidden" name="mode" value="savemethod">
                            {if $ALLOW_EMAIL}
                                <div class="form-check mb-2">
                                    <input class="form-check-input" id="twoFactorUserEmail" type="radio"
                                           name="two_factor_method" value="email" {if $CURRENT_METHOD eq 'email'}checked{/if}>
                                    <label class="form-check-label" for="twoFactorUserEmail">
                                        {vtranslate('LBL_OPT_EMAIL', $LANGUAGE_MODULE)}
                                    </label>
                                </div>
                            {/if}
                            {if $ALLOW_TOTP}
                                <div class="form-check mb-3">
                                    <input class="form-check-input" id="twoFactorUserTotp" type="radio"
                                           name="two_factor_method" value="totp" {if $CURRENT_METHOD eq 'totp'}checked{/if}>
                                    <label class="form-check-label" for="twoFactorUserTotp">
                                        {vtranslate('LBL_OPT_APP', $LANGUAGE_MODULE)}
                                    </label>
                                </div>
                            {/if}
                            <button type="submit" class="btn btn-primary">{vtranslate('LBL_SAVE', $LANGUAGE_MODULE)}</button>
                        </form>

                        {if $CURRENT_METHOD eq 'totp'}
                            <div class="two-factor-authentication-app-box mt-4 pt-3 border-top">
                                <p class="text-body-secondary">
                                    {if $ENROLLED}
                                        {vtranslate('LBL_APP_STATUS_ON', $LANGUAGE_MODULE)}
                                    {else}
                                        {vtranslate('LBL_APP_STATUS_OFF', $LANGUAGE_MODULE)}
                                    {/if}
                                </p>
                                <form method="post" action="index.php">
                                    <input type="hidden" name="module" value="TwoFactorAuthentication">
                                    <input type="hidden" name="action" value="Manage">
                                    <input type="hidden" name="mode" value="start">
                                    <button type="submit" class="btn {if $ENROLLED}btn-outline-secondary{else}btn-primary{/if}">
                                        {if $ENROLLED}
                                            {vtranslate('LBL_RECONFIGURE_APP', $LANGUAGE_MODULE)}
                                        {else}
                                            {vtranslate('LBL_SETUP_APP', $LANGUAGE_MODULE)}
                                        {/if}
                                    </button>
                                </form>
                            </div>
                        {/if}
                    </div>

                    <div class="tab-pane fade {if $ACTIVE_TAB eq 'backup'}show active{/if}" id="two-factor-backup-codes">
                        <p class="text-body-secondary">{vtranslate('LBL_BACKUP_CODES_INTRO_USER', $LANGUAGE_MODULE)}</p>
                        {if $BACKUP_CODES}
                            <div class="alert alert-warning two-factor-authentication-box">
                                {vtranslate('LBL_BACKUP_CODES_WARNING', $LANGUAGE_MODULE)}
                            </div>
                            <ul class="two-factor-authentication-codes">
                                {foreach item=CODE from=$BACKUP_CODES}
                                    <li>{$CODE|escape}</li>{/foreach}
                            </ul>
                            <form method="post" action="index.php">
                                <input type="hidden" name="module" value="TwoFactorAuthentication">
                                <input type="hidden" name="action" value="Manage">
                                <input type="hidden" name="mode" value="ack">
                                <input type="hidden" name="tab" value="backup">
                                <button type="submit" class="btn btn-primary">{vtranslate('LBL_DONE', $LANGUAGE_MODULE)}</button>
                            </form>
                        {else}
                            <p>
                                <strong>{vtranslate('LBL_UNUSED_CODES', $LANGUAGE_MODULE)}:</strong> {$BACKUP_COUNT}</p>
                            <form method="post" action="index.php">
                                <input type="hidden" name="module" value="TwoFactorAuthentication">
                                <input type="hidden" name="action" value="Manage">
                                <input type="hidden" name="mode" value="regen">
                                <button type="submit" class="btn btn-primary">
                                    {vtranslate('LBL_REGEN_BACKUP', $LANGUAGE_MODULE)}
                                </button>
                            </form>
                        {/if}
                    </div>
                </div>
            {/if}
        </div>
    </div>
{/strip}
