{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="two-factor-authentication-settings px-4 pb-4">
        <div class="rounded bg-body">
            <div class="container-fluid p-3 border-bottom">
                <h4 class="m-0">
                    <i class="bi bi-shield-lock-fill me-2"></i>
                    {vtranslate('TwoFactorAuthentication', $QUALIFIED_MODULE)}
                </h4>
            </div>

            <ul class="nav nav-tabs gap-2 px-3 pt-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#two-factor-general" type="button">
                        {vtranslate('LBL_TAB_GENERAL', $QUALIFIED_MODULE)}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#two-factor-users" type="button">
                        {vtranslate('LBL_TAB_USERS', $QUALIFIED_MODULE)}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#two-factor-validation" type="button">
                        {vtranslate('LBL_TAB_VALIDATION', $QUALIFIED_MODULE)}
                        {if $VALIDATION.errors gt 0}
                            <span class="badge bg-danger ms-1">{$VALIDATION.errors}</span>
                        {elseif $VALIDATION.warnings gt 0}
                            <span class="badge bg-warning text-dark ms-1">{$VALIDATION.warnings}</span>
                        {else}
                            <i class="bi bi-check-circle-fill text-success ms-1"></i>
                        {/if}
                    </button>
                </li>
                {foreach item=TPL from=$EMAIL_TEMPLATES}
                    <li class="nav-item" role="presentation">
                        <button class="nav-link two-factor-authentication-email-tab" data-bs-toggle="tab"
                                data-bs-target="#two-factor-email-{$TPL.key}" type="button">
                            {if $TPL.key eq 'login'}
                                {vtranslate('LBL_TPL_TITLE_LOGIN', $QUALIFIED_MODULE)}
                            {else}
                                {vtranslate('LBL_TPL_TITLE_BACKUP', $QUALIFIED_MODULE)}
                            {/if}
                        </button>
                    </li>
                {/foreach}
            </ul>

            <div class="tab-content p-3">
                <div class="tab-pane fade show active" id="two-factor-general">
                    <h5>{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</h5>
                    <p class="text-body-secondary">{vtranslate('LBL_STATUS_INTRO', $QUALIFIED_MODULE)}</p>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input two-factor-authentication-toggle" id="twoFactorAuthenticationStatus"
                               type="checkbox" role="switch"
                               data-activate-confirm="{vtranslate('LBL_ACTIVATE_CONFIRM', $QUALIFIED_MODULE)}"
                               data-deactivate-confirm="{vtranslate('LBL_DEACTIVATE_CONFIRM', $QUALIFIED_MODULE)}"
                               {if $IS_2FA_ACTIVE}checked{/if}>
                        <label class="form-check-label" for="twoFactorAuthenticationStatus">
                            {if $IS_2FA_ACTIVE}
                                {vtranslate('LBL_ACTIVE', $QUALIFIED_MODULE)}
                            {else}
                                {vtranslate('LBL_INACTIVE', $QUALIFIED_MODULE)}
                            {/if}
                        </label>
                    </div>

                    <div class="alert {if $IS_2FA_ACTIVE}alert-info{else}alert-warning{/if}">
                        {if !$IS_2FA_ACTIVE}
                            {vtranslate('LBL_NOT_ACTIVE_NOTE', $QUALIFIED_MODULE)}
                        {elseif $ENFORCE_MODE eq 'all'}
                            {vtranslate('LBL_ENFORCE_ALL_NOTE', $QUALIFIED_MODULE)}
                        {else}
                            {vtranslate('LBL_ENFORCE_OFF_NOTE', $QUALIFIED_MODULE)}
                        {/if}
                    </div>

                    <h5 class="mt-4">{vtranslate('LBL_AVAILABLE_METHODS', $QUALIFIED_MODULE)}</h5>
                    <p class="text-body-secondary">{vtranslate('LBL_AVAILABLE_METHODS_INTRO', $QUALIFIED_MODULE)}</p>

                    <div class="two-factor-authentication-methods">
                        <div class="form-check mb-2">
                            <input class="form-check-input two-factor-authentication-method" id="twoFactorMethodEmail"
                                   type="checkbox" data-method="email"
                                   {if $ALLOW_EMAIL}checked{/if} {if !$MAIL_CONFIGURED}disabled{/if}>
                            <label class="form-check-label" for="twoFactorMethodEmail">
                                {vtranslate('LBL_METHOD_EMAIL', $QUALIFIED_MODULE)}
                            </label>
                            {if !$MAIL_CONFIGURED}
                                <div class="form-text">{vtranslate('LBL_EMAIL_NEEDS_SERVER', $QUALIFIED_MODULE)}</div>
                            {/if}
                        </div>
                        <div class="form-check">
                            <input class="form-check-input two-factor-authentication-method" id="twoFactorMethodTotp"
                                   type="checkbox" data-method="totp" {if $ALLOW_TOTP}checked{/if}
                                    {if !$TOTP_AVAILABLE}disabled{/if}>
                            <label class="form-check-label" for="twoFactorMethodTotp">
                                {vtranslate('LBL_METHOD_TOTP', $QUALIFIED_MODULE)}
                            </label>
                            {if !$TOTP_AVAILABLE}
                                <div class="form-text">{vtranslate('LBL_TOTP_RUNTIME_MISSING', $QUALIFIED_MODULE)}</div>
                            {/if}
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="two-factor-users">
                    <p class="text-body-secondary">{vtranslate('LBL_USERS_INTRO', $QUALIFIED_MODULE)}</p>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                            <tr>
                                <th>{vtranslate('LBL_USER', $QUALIFIED_MODULE)}</th>
                                <th class="text-center">{vtranslate('LBL_2FA', $QUALIFIED_MODULE)}</th>
                                <th>{vtranslate('LBL_METHOD', $QUALIFIED_MODULE)}</th>
                                <th class="text-center">{vtranslate('LBL_UNUSED_CODES', $QUALIFIED_MODULE)}</th>
                                <th>{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach item=USER from=$USERS_OVERVIEW}
                                <tr data-userid="{$USER.id}">
                                    <td>
                                        {$USER.user_name|escape}
                                        {if $USER.first_name neq '' or $USER.last_name neq ''}
                                            <span class="text-body-secondary">({$USER.first_name|escape} {$USER.last_name|escape})</span>
                                        {/if}
                                        {if $USER.is_admin eq 'on'}
                                            <span class="badge bg-info text-dark">{vtranslate('LBL_ADMIN', $QUALIFIED_MODULE)}</span>
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        <input class="form-check-input two-factor-authentication-exempt-toggle"
                                               type="checkbox" data-userid="{$USER.id}" {if $USER.exempt neq 1}checked{/if}>
                                    </td>
                                    <td class="two-factor-authentication-method-cell">
                                        <span class="two-factor-authentication-method-text">
                                            {if $USER.method eq 'email'}
                                                {vtranslate('LBL_OPT_EMAIL', $QUALIFIED_MODULE)}
                                            {elseif $USER.method eq 'totp'}
                                                {vtranslate('LBL_OPT_APP', $QUALIFIED_MODULE)}
                                            {else}
                                                {vtranslate('LBL_METHOD_NOT_SET', $QUALIFIED_MODULE)}
                                            {/if}
                                        </span>
                                        <button type="button" class="btn btn-sm btn-link two-factor-authentication-method-edit"
                                                title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <select class="form-select form-select-sm two-factor-authentication-method-select d-none"
                                                data-userid="{$USER.id}">
                                            {if $ALLOW_EMAIL}
                                                <option value="email" {if $USER.method eq 'email'}selected{/if}>
                                                    {vtranslate('LBL_OPT_EMAIL', $QUALIFIED_MODULE)}
                                                </option>
                                            {/if}
                                            {if $ALLOW_TOTP}
                                                <option value="totp" {if $USER.method eq 'totp'}selected{/if}>
                                                    {vtranslate('LBL_OPT_APP', $QUALIFIED_MODULE)}
                                                </option>
                                            {/if}
                                        </select>
                                    </td>
                                    <td class="text-center">{$USER.backup_count}</td>
                                    <td>
                                        {if $USER.exempt neq 1}
                                            <button type="button" class="btn btn-sm btn-outline-danger two-factor-authentication-reset"
                                                    data-record="{$USER.id}"
                                                    data-confirm="{vtranslate('LBL_RESET_CONFIRM', $QUALIFIED_MODULE)}">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                                {vtranslate('LBL_RESET', $QUALIFIED_MODULE)}
                                            </button>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="two-factor-validation">
                    <p class="text-body-secondary">{vtranslate('LBL_VALIDATION_INTRO', $QUALIFIED_MODULE)}</p>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                            <tr>
                                <th>{vtranslate('LBL_VALIDATION_CHECK', $QUALIFIED_MODULE)}</th>
                                <th>{vtranslate('LBL_VALIDATION_RESULT', $QUALIFIED_MODULE)}</th>
                                <th>{vtranslate('LBL_DETAILS', $QUALIFIED_MODULE)}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach item=CHECK from=$VALIDATION.checks}
                                <tr>
                                    <td>{vtranslate($CHECK.label, $QUALIFIED_MODULE)}</td>
                                    <td>
                                        <span class="badge {if $CHECK.status eq 'success'}bg-success{elseif $CHECK.status eq 'danger'}bg-danger{elseif $CHECK.status eq 'warning'}bg-warning text-dark{else}bg-secondary{/if}">
                                            {vtranslate($CHECK.message, $QUALIFIED_MODULE)}
                                        </span>
                                    </td>
                                    <td>
                                        <code>{$CHECK.details|escape}</code>
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>

                {foreach item=TPL from=$EMAIL_TEMPLATES}
                    <div class="tab-pane fade" id="two-factor-email-{$TPL.key}">
                        <p class="text-body-secondary">{vtranslate('LBL_EMAILS_INTRO', $QUALIFIED_MODULE)}</p>
                        <div class="two-factor-authentication-template" data-key="{$TPL.key}">
                            <p class="small text-body-secondary">
                                {vtranslate('LBL_TPL_VARS', $QUALIFIED_MODULE)}:
                                {foreach item=VARIABLE from=$TPL.vars}
                                    <code>{$VARIABLE}</code>
                                {/foreach}
                            </p>
                            <div class="mb-3">
                                <label class="form-label">{vtranslate('LBL_TPL_SUBJECT', $QUALIFIED_MODULE)}</label>
                                <input type="text" class="form-control two-factor-authentication-template-subject"
                                       value="{$TPL.subject|escape}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{vtranslate('LBL_TPL_BODY', $QUALIFIED_MODULE)}</label>
                                <textarea class="two-factor-authentication-template-body"
                                          id="two-factor-body-{$TPL.key}">{$TPL.body|escape}</textarea>
                            </div>
                            <button type="button" class="btn btn-primary two-factor-authentication-template-save">
                                {vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}
                            </button>
                            <button type="button" class="btn btn-outline-secondary two-factor-authentication-template-reset ms-2">
                                {vtranslate('LBL_RESET_DEFAULT', $QUALIFIED_MODULE)}
                            </button>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
{/strip}
