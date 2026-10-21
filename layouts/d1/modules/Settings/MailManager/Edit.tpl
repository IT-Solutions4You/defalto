{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{strip}
<div class="px-4 pb-4 its4you-module-settings its4you-mm-wizard"
     data-wizard-url="{$WIZARD_URL}" data-list-url="{$LIST_URL}" data-user-mode="{if $USER_MODE}1{else}0{/if}">
    <div class="bg-body rounded pb-3">
    <div class="editViewHeader container-fluid py-3 mb-3 border-bottom">
        <span class="fs-4 fw-bold">
            <i class="fa-solid fa-envelope me-2"></i>
            <a href="{$LIST_URL}">{if $USER_MODE}{vtranslate('MailManager', 'MailManager')}{else}{vtranslate('MailManager', $QUALIFIED_MODULE)}{/if}</a>
            &nbsp;&gt;&nbsp;{if $IS_EDIT}{vtranslate('LBL_EDIT_MAILBOX', $QUALIFIED_MODULE)}&nbsp;<span class="its4you-muted">({$EMAIL|escape})</span>{else}{vtranslate('LBL_ADD_MAILBOX', $QUALIFIED_MODULE)}{/if}
        </span>
    </div>

    <div class="its4you-wizard-steps clearfix {if $IS_EDIT}hide{/if}">
        <div class="its4you-step {if $STEP eq 1}active{elseif $STEP gt 1}done{/if}">
            <span class="its4you-step-no">{if $STEP gt 1}&#10003;{else}1{/if}</span>
            {vtranslate('LBL_STEP_EMAIL_PROVIDER', $QUALIFIED_MODULE)}
        </div>
        <div class="its4you-step {if $STEP eq 2}active{elseif $STEP gt 2}done{/if}">
            <span class="its4you-step-no">{if $STEP gt 2}&#10003;{else}2{/if}</span>
            {vtranslate('LBL_STEP_INCOMING', $QUALIFIED_MODULE)}
        </div>
        <div class="its4you-step {if $STEP eq 3}active{elseif $STEP gt 3}done{/if}">
            <span class="its4you-step-no">{if $STEP gt 3}&#10003;{else}3{/if}</span>
            {vtranslate('LBL_STEP_OUTGOING', $QUALIFIED_MODULE)}
        </div>
        <div class="its4you-step {if $STEP eq 4}active{/if}">
            <span class="its4you-step-no">4</span>
            {vtranslate('LBL_STEP_MAILBOX_SETTINGS', $QUALIFIED_MODULE)}
        </div>
    </div>

    {if $STEP eq 1}
        <form class="its4you-wizard-step1" onsubmit="return false;">
            <div class="row py-2 its4you-form-row">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_MAILBOX_NAME', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
                <div class="col-lg-4 fieldValue"><input type="text" class="inputElement form-control" name="mailbox_name" value="{$MAILBOX_NAME|escape}"></div>
            </div>
            <div class="row py-2 its4you-form-row">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_EMAIL_ADDRESS', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
                <div class="col-lg-4 fieldValue">
                    <input type="text" class="inputElement form-control" name="email" value="{$EMAIL|escape}" placeholder="name@company.com">
                    <div class="its4you-hint">{vtranslate('LBL_PROVIDER_DETECT_HINT', $QUALIFIED_MODULE)}</div>
                </div>
            </div>
            <div class="row py-2 its4you-form-row">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_PROVIDER', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
                <div class="col-lg-9 fieldValue">
                    <div class="its4you-provider-cards">
                        <div class="its4you-provider-card {if $PROVIDER eq 'office365'}selected{/if}" data-provider="office365">
                            <i class="fa-brands fa-microsoft"></i>
                            <b>{vtranslate('LBL_PROVIDER_MS', $QUALIFIED_MODULE)}</b>
                            <span>{vtranslate('LBL_PROVIDER_MS_DESC', $QUALIFIED_MODULE)}</span>
                        </div>
                        <div class="its4you-provider-card {if $PROVIDER eq 'google'}selected{/if}" data-provider="google">
                            <i class="fa-brands fa-google"></i>
                            <b>{vtranslate('LBL_PROVIDER_GOOGLE', $QUALIFIED_MODULE)}</b>
                            <span>{vtranslate('LBL_PROVIDER_GOOGLE_DESC', $QUALIFIED_MODULE)}</span>
                        </div>
                        <div class="its4you-provider-card {if $PROVIDER eq 'imap'}selected{/if}" data-provider="imap">
                            <i class="fa-solid fa-envelope"></i>
                            <b>{vtranslate('LBL_PROVIDER_IMAP', $QUALIFIED_MODULE)}</b>
                            <span>{vtranslate('LBL_PROVIDER_IMAP_DESC', $QUALIFIED_MODULE)}</span>
                        </div>
                    </div>
                    <input type="hidden" name="provider" value="{$PROVIDER}">
                    <div class="its4you-hint its4you-detect-result hide"></div>
                </div>
            </div>

            <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink" href="{$LIST_URL}">{vtranslate('LBL_BACK_TO_LIST', $QUALIFIED_MODULE)}</a></div>
            <div class="col-6"><button type="button" class="btn btn-primary active saveButton its4you-wizard-next">{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}</button></div>
        </div>
    </div>
</div>
        </form>
    {elseif $STEP eq 2 && $PROVIDER neq 'imap'}
        {* ---- Step 2, OAuth variant (Microsoft 365 / Google): a personal mailbox of the user,
           signed in through Core auth.php (MailManager_ProOAuthAccount_Model) ---- *}
        <div class="its4you-wizard-step2 its4you-oauth-step"
             data-provider="{$PROVIDER}" data-email="{$EMAIL|escape}" data-name="{$MAILBOX_NAME|escape}"
             data-server="{if $PROVIDER eq 'office365'}outlook.office365.com{else}imap.gmail.com{/if}">
            {if !$USER_MODE}
                <div class="alert alert-info">{vtranslate('LBL_PRO_OAUTH_PERSONAL_ONLY', $QUALIFIED_MODULE)}</div>
            {else}
                <div class="alert alert-info">{vtranslate('LBL_PRO_OAUTH_PERSONAL_INFO', $QUALIFIED_MODULE)}</div>
                <div class="its4you-oauth-box">
                    <div class="its4you-oauth-head">
                        {if $PROVIDER eq 'office365'}{vtranslate('LBL_OAUTH_APP_MS', $QUALIFIED_MODULE)}{else}{vtranslate('LBL_OAUTH_APP_GOOGLE', $QUALIFIED_MODULE)}{/if}
                    </div>
                    <div class="its4you-oauth-body">
                        <ol class="its4you-guide">
                            {if $PROVIDER eq 'office365'}
                                <li>{vtranslate('LBL_MS_GUIDE_1', $QUALIFIED_MODULE)}</li>
                                <li>{vtranslate('LBL_MS_GUIDE_2', $QUALIFIED_MODULE)}</li>
                                <li>{vtranslate('LBL_MS_GUIDE_3', $QUALIFIED_MODULE)}</li>
                                <li>{vtranslate('LBL_PRO_MS_GUIDE_4', $QUALIFIED_MODULE)}</li>
                                <li>{vtranslate('LBL_MS_GUIDE_5', $QUALIFIED_MODULE)}</li>
                            {else}
                                <li>{vtranslate('LBL_GOOGLE_GUIDE_1', $QUALIFIED_MODULE)}</li>
                                <li>{vtranslate('LBL_GOOGLE_GUIDE_2', $QUALIFIED_MODULE)}</li>
                                <li>{vtranslate('LBL_GOOGLE_GUIDE_3', $QUALIFIED_MODULE)}</li>
                                <li>{vtranslate('LBL_GOOGLE_GUIDE_4', $QUALIFIED_MODULE)}</li>
                                <li>{vtranslate('LBL_GOOGLE_GUIDE_5', $QUALIFIED_MODULE)}</li>
                            {/if}
                        </ol>
                        <div class="row py-2 its4you-form-row">
                            <div class="col-lg-3 fieldLabel">{vtranslate('LBL_REDIRECT_URI', $QUALIFIED_MODULE)}</div>
                            <div class="col-lg-4 fieldValue its4you-copy-row">
                                <input type="text" class="inputElement its4you-redirect-uri form-control" readonly value="{$REDIRECT_URI|escape}">
                                <button type="button" class="btn btn-outline-secondary its4you-copy-uri">{vtranslate('LBL_COPY', $QUALIFIED_MODULE)}</button>
                            </div>
                        </div>
                        {if $PROVIDER eq 'office365'}
                            <div class="row py-2 its4you-form-row">
                                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_TENANT_ID', $QUALIFIED_MODULE)}</div>
                                <div class="col-lg-4 fieldValue">
                                    <input type="text" class="inputElement form-control" readonly value="{$MICROSOFT_TENANT|escape}">
                                    <div class="its4you-hint">{vtranslate('LBL_PRO_MS_TENANT_HINT', $QUALIFIED_MODULE)}</div>
                                </div>
                            </div>
                        {/if}
                        <div class="row py-2 its4you-form-row">
                            <div class="col-lg-3 fieldLabel">{vtranslate('LBL_CLIENT_ID', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
                            <div class="col-lg-4 fieldValue"><input type="text" class="inputElement form-control" name="client_id" value="" autocomplete="off"></div>
                        </div>
                        <div class="row py-2 its4you-form-row">
                            <div class="col-lg-3 fieldLabel">{vtranslate('LBL_CLIENT_SECRET', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
                            <div class="col-lg-4 fieldValue"><input type="password" class="inputElement form-control" name="client_secret" value="" autocomplete="new-password"></div>
                        </div>
                    </div>
                </div>

                <br>
                <div class="row py-2 its4you-form-row">
                    <div class="col-lg-3 fieldLabel">{vtranslate('LBL_AUTHORIZE_MAILBOX', $QUALIFIED_MODULE)}</div>
                    <div class="col-lg-4 fieldValue">
                        <button type="button" class="btn btn-outline-secondary its4you-oauth-signin">
                            {if $PROVIDER eq 'office365'}<i class="fa-brands fa-microsoft"></i>&nbsp;&nbsp;{vtranslate('LBL_SIGN_IN_MS', $QUALIFIED_MODULE)}
                            {else}<i class="fa-brands fa-google"></i>&nbsp;&nbsp;{vtranslate('LBL_SIGN_IN_GOOGLE', $QUALIFIED_MODULE)}{/if}
                        </button>
                        <button type="button" class="btn btn-outline-secondary its4you-oauth-check">{vtranslate('LBL_PRO_OAUTH_CHECK', $QUALIFIED_MODULE)}</button>
                        <div class="its4you-hint">{vtranslate('LBL_PRO_OAUTH_SIGNIN_HINT', $QUALIFIED_MODULE)}</div>
                        <div class="alert alert-success its4you-conn-status hide">&#10003;&nbsp;{vtranslate('LBL_CONNECTED_AS', $QUALIFIED_MODULE)}&nbsp;<b>{$EMAIL|escape}</b></div>
                        <div class="alert alert-warning its4you-conn-error hide">{vtranslate('LBL_PRO_OAUTH_NOT_SIGNED_IN', $QUALIFIED_MODULE)}</div>
                    </div>
                </div>
            {/if}

            <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink" href="{$WIZARD_URL}&step=1&email={$EMAIL|escape:'url'}&provider={$PROVIDER}&name={$MAILBOX_NAME|escape:'url'}">{vtranslate('LBL_BACK', $QUALIFIED_MODULE)}</a></div>
            <div class="col-6">{if $USER_MODE}
                            <button type="button" class="btn btn-primary active saveButton its4you-oauth-connect" disabled>{vtranslate('LBL_PRO_OAUTH_CONNECT', $QUALIFIED_MODULE)}</button>
                        {/if}</div>
        </div>
    </div>
</div>
        </div>
    {elseif $STEP eq 2}
        {* ---- Step 2, IMAP variant ---- *}
        <div class="its4you-wizard-step2 its4you-imap-step"
             data-provider="imap" data-email="{$EMAIL|escape}" data-name="{$MAILBOX_NAME|escape}"
             data-connected="{if $IMAP_TESTED}1{else}0{/if}">

            <div class="row py-2 its4you-form-row">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_IMAP_SERVER', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
                <div class="col-lg-4 fieldValue">
                    <input type="text" class="inputElement form-control" name="server" value="{$IMAP_SERVER|escape}">
                    <div class="its4you-hint">{vtranslate('LBL_IMAP_SERVER_HINT', $QUALIFIED_MODULE)}</div>
                </div>
            </div>
            <div class="row py-2 its4you-form-row">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_PORT', $QUALIFIED_MODULE)} / {vtranslate('LBL_ENCRYPTION', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
                <div class="col-lg-4 fieldValue its4you-port-row">
                    <input type="text" class="inputElement its4you-imap-port form-control" name="port" value="{$IMAP_PORT}">
                    <select class="inputElement its4you-imap-encryption form-select" name="encryption">
                        <option value="ssl" {if $IMAP_ENCRYPTION eq 'ssl'}selected{/if}>SSL/TLS</option>
                        <option value="starttls" {if $IMAP_ENCRYPTION eq 'starttls'}selected{/if}>STARTTLS</option>
                        <option value="none" {if $IMAP_ENCRYPTION eq 'none'}selected{/if}>{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                    </select>
                    {* a certificate trusted in the dialog below (sha256) *}
                    <input type="hidden" name="cert_fingerprint" value="{$IMAP_CERT_FINGERPRINT|escape}">
                </div>
            </div>
            <div class="row py-2 its4you-form-row">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_USERNAME', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
                <div class="col-lg-4 fieldValue"><input type="text" class="inputElement form-control" name="username" value="{$IMAP_USERNAME|escape}"></div>
            </div>
            <div class="row py-2 its4you-form-row">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_PASSWORD', $QUALIFIED_MODULE)}&nbsp;{if !$IMAP_TESTED}<span class="text-danger ms-2">*</span>{/if}</div>
                <div class="col-lg-4 fieldValue">
                    <input type="password" class="inputElement form-control" name="password" value="" autocomplete="new-password" {if $IMAP_TESTED}placeholder="••••••••••"{/if}>
                </div>
            </div>
            <div class="row py-2 its4you-form-row">
                <div class="col-lg-3 fieldLabel"></div>
                <div class="col-lg-4 fieldValue">
                    <button type="button" class="btn btn-outline-secondary its4you-test-imap">{vtranslate('LBL_TEST_CONNECTION', $QUALIFIED_MODULE)}</button>
                    <div class="alert alert-success its4you-conn-status {if !$IMAP_TESTED}hide{/if}" style="margin-top:10px;">
                        &#10003;&nbsp;{vtranslate('LBL_CONNECTION_OK', $QUALIFIED_MODULE)}&nbsp;&mdash;&nbsp;<span class="its4you-folders-count">{$FOLDERS}</span>&nbsp;{vtranslate('LBL_FOLDERS_FOUND', $QUALIFIED_MODULE)}
                    </div>
                    <div class="alert alert-danger its4you-conn-error hide" style="margin-top:10px;"></div>
                    {include file="CertificateBox.tpl"|vtemplate_path:$QUALIFIED_MODULE}
                </div>
            </div>

            <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink" href="{$WIZARD_URL}&step=1&email={$EMAIL|escape:'url'}&provider={$PROVIDER}&name={$MAILBOX_NAME|escape:'url'}">{vtranslate('LBL_BACK', $QUALIFIED_MODULE)}</a></div>
            <div class="col-6"><button type="button" class="btn btn-primary active saveButton its4you-wizard-next-step2" {if !$IMAP_TESTED}disabled{/if}>{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}</button></div>
        </div>
    </div>
</div>
        </div>
    {elseif $STEP eq 3}
        {* ---- Step 3 — outgoing mail: the mailbox's own SMTP (the mail leaves
           from its own address), prefilled from the incoming connection ---- *}
        {if !$IMAP_CONNECTED}
            <div class="alert alert-warning">{vtranslate('LBL_NOT_CONNECTED_WARNING', $QUALIFIED_MODULE)}</div>
            <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink" href="{$WIZARD_URL}&step=2&email={$EMAIL|escape:'url'}&provider={$PROVIDER}&name={$MAILBOX_NAME|escape:'url'}">{vtranslate('LBL_BACK', $QUALIFIED_MODULE)}</a></div>
            <div class="col-6"></div>
        </div>
    </div>
</div>
        {else}
        <div class="its4you-wizard-smtp its4you-smtp-box" data-email="{$EMAIL|escape}" data-name="{$MAILBOX_NAME|escape}"
             data-provider="{$PROVIDER}" data-record="0">
            <div class="its4you-hint" style="max-width:640px; margin-bottom:14px;">{vtranslate('LBL_SMTP_STEP_HINT', $QUALIFIED_MODULE)}</div>
            {include file="SmtpFields.tpl"|vtemplate_path:$QUALIFIED_MODULE SMTP_WIZARD=true}
            <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink" href="{$WIZARD_URL}&step=2&email={$EMAIL|escape:'url'}&provider={$PROVIDER}&name={$MAILBOX_NAME|escape:'url'}">{vtranslate('LBL_BACK', $QUALIFIED_MODULE)}</a></div>
            <div class="col-6"><button type="button" class="btn btn-primary active saveButton its4you-wizard-next-step3" {if !$SMTP_TESTED}disabled{/if}>{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}</button></div>
        </div>
    </div>
</div>
        </div>
        {/if}
    {elseif $STEP eq 4}
        {* ---- Step 4 — mailbox settings (also the edit form) ---- *}
        {if !$SETTINGS_READY}
            <div class="alert alert-warning">{vtranslate('LBL_NOT_CONNECTED_WARNING', $QUALIFIED_MODULE)}</div>
            <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink" href="{$WIZARD_URL}&step={if $PROVIDER eq 'imap'}3{else}2{/if}&email={$EMAIL|escape:'url'}&provider={$PROVIDER}&name={$MAILBOX_NAME|escape:'url'}">{vtranslate('LBL_BACK', $QUALIFIED_MODULE)}</a></div>
            <div class="col-6"></div>
        </div>
    </div>
</div>
        {else}
        <div class="its4you-wizard-step3" data-provider="{$PROVIDER}" data-email="{$EMAIL|escape}" data-name="{$MAILBOX_NAME|escape}" data-record="{$RECORD_ID}">
            {* Edit mode shows the email in the breadcrumb header instead of a field *}
            {if !$IS_EDIT}
                <div class="alert alert-success" style="max-width:640px;">
                    &#10003;&nbsp;{vtranslate('LBL_CONNECTED_AS', $QUALIFIED_MODULE)}&nbsp;<b>{$ACCOUNT_EMAIL|escape}</b>
                </div>
            {/if}

            {if $IS_EDIT}
                {* On create the name is collected in step 1 and carried via data-name *}
                <div class="row py-2 its4you-form-row">
                    <div class="col-lg-3 fieldLabel">{vtranslate('LBL_MAILBOX_NAME', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
                    <div class="col-lg-4 fieldValue"><input type="text" class="inputElement form-control" name="name" value="{$MAILBOX_NAME|escape}"></div>
                </div>
            {/if}

            {* a user's own mailbox is always personal *}
            {if !$USER_MODE}
            <div class="row py-2 its4you-form-row">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_MAILBOX_TYPE', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
                <div class="col-lg-4 fieldValue">
                    <label class="form-check form-check-inline its4you-radio"><input class="form-check-input" type="radio" name="mailbox_type" value="personal" {if $MAILBOX_TYPE neq 'shared'}checked{/if}>&nbsp;{vtranslate('LBL_TYPE_PERSONAL', $QUALIFIED_MODULE)}</label>
                    <label class="form-check form-check-inline its4you-radio"><input class="form-check-input" type="radio" name="mailbox_type" value="shared" {if $MAILBOX_TYPE eq 'shared'}checked{/if}>&nbsp;{vtranslate('LBL_TYPE_SHARED', $QUALIFIED_MODULE)}</label>
                    <div class="its4you-hint">{vtranslate('LBL_TYPE_HINT', $QUALIFIED_MODULE)}</div>
                </div>
            </div>

            <div class="row py-2 its4you-form-row its4you-shared-with-row {if $MAILBOX_TYPE neq 'shared'}hide{/if}">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_SHARED_WITH', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
                <div class="col-lg-4 fieldValue">
                    <select class="select2 its4you-shared-with inputElement form-select" multiple data-placeholder="{vtranslate('LBL_SHARED_WITH', $QUALIFIED_MODULE)}">
                        {foreach key=USER_ID item=USER_NAME from=$USERS_LIST}
                            <option value="user:{$USER_ID}" {if is_array($MEMBERS_SELECTED) && in_array("user:`$USER_ID`", $MEMBERS_SELECTED)}selected{/if}>{$USER_NAME}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            {/if}

            {if !$IS_EDIT}
            <div class="row py-2 its4you-form-row">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_SYNC_FROM', $QUALIFIED_MODULE)}</div>
                <div class="col-lg-4 fieldValue">
                    <select class="inputElement form-select" name="sync_from_days" style="max-width:220px;">
                        <option value="30">{vtranslate('LBL_SYNC_30', $QUALIFIED_MODULE)}</option>
                        <option value="90" selected>{vtranslate('LBL_SYNC_90', $QUALIFIED_MODULE)}</option>
                        <option value="365">{vtranslate('LBL_SYNC_365', $QUALIFIED_MODULE)}</option>
                        <option value="0">{vtranslate('LBL_SYNC_ALL', $QUALIFIED_MODULE)}</option>
                    </select>
                    <div class="its4you-hint">{vtranslate('LBL_SYNC_FROM_HINT', $QUALIFIED_MODULE)}</div>
                </div>
            </div>
            {/if}

            <div class="row py-2 its4you-form-row">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_SET_DEFAULT', $QUALIFIED_MODULE)}</div>
                <div class="col-lg-4 fieldValue"><input class="form-check-input" type="checkbox" name="is_default" {if $IS_DEFAULT}checked{/if}></div>
            </div>

            {* An existing IMAP mailbox — the server certificate is checked here
               (no password needed); a new one goes through the connection test *}
            {if $RECORD_ID && $PROVIDER eq 'imap'}
                <div class="row py-2 its4you-form-row its4you-cert-row">
                    <div class="col-lg-3 fieldLabel">{vtranslate('LBL_IMAP_CERTIFICATE', $QUALIFIED_MODULE)}</div>
                    <div class="col-lg-4 fieldValue">
                        <input type="hidden" name="imap_cert_fingerprint" value="{$IMAP_CERT_FINGERPRINT|escape}">
                        <div class="its4you-cert-state" style="margin-bottom:6px;">
                            <span class="its4you-cert-state-ca {if $IMAP_CERT_FINGERPRINT}hide{/if}">{vtranslate('LBL_CERT_BY_AUTHORITY', $QUALIFIED_MODULE)}</span>
                            <span class="its4you-cert-state-trusted {if !$IMAP_CERT_FINGERPRINT}hide{/if}">
                                {vtranslate('LBL_CERT_TRUSTED_EXCEPTION', $QUALIFIED_MODULE)}
                                &nbsp;<a href="javascript:void(0);" class="its4you-cert-revoke">{vtranslate('LBL_CERT_REVOKE', $QUALIFIED_MODULE)}</a>
                            </span>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm its4you-cert-check">{vtranslate('LBL_CERT_CHECK', $QUALIFIED_MODULE)}</button>
                        <div class="alert alert-success its4you-cert-ok hide" style="margin-top:10px;"
                             data-verified="{vtranslate('LBL_CERT_VERIFIED', $QUALIFIED_MODULE)|escape}"
                             data-match="{vtranslate('LBL_CERT_MATCHES_TRUSTED', $QUALIFIED_MODULE)|escape}"
                             data-pending="{vtranslate('LBL_CERT_TRUSTED_ON_SAVE', $QUALIFIED_MODULE)|escape}"></div>
                        {include file="CertificateBox.tpl"|vtemplate_path:$QUALIFIED_MODULE}
                    </div>
                </div>
            {/if}

            {* Outgoing mail of an existing IMAP mailbox — a new one set it in step 3.
               A mailbox still on the CRM outgoing server is asked to switch *}
            {if $IS_EDIT && $PROVIDER eq 'imap'}
                <div class="its4you-smtp-box" data-record="{$RECORD_ID}">
                    {if $SMTP_MODE neq 'custom'}
                        <div class="alert alert-warning" style="max-width:640px;">{vtranslate('LBL_SMTP_CRM_WARNING', $QUALIFIED_MODULE)}</div>
                    {/if}
                    {include file="SmtpFields.tpl"|vtemplate_path:$QUALIFIED_MODULE SMTP_WIZARD=false}
                </div>
            {/if}

            <div class="row py-2 its4you-form-row">
                <div class="col-lg-3 fieldLabel">{vtranslate('LBL_FOLDERS_TO_SCAN', $QUALIFIED_MODULE)}</div>
                <div class="col-lg-4 fieldValue">
                    <div class="its4you-folders-scroll" data-delimiter="{$FOLDER_DELIMITER|escape}">
                        <table class="table table-bordered its4you-folders-table">
                            {foreach item=FOLDER from=$FOLDERS_LIST}
                                <tr class="its4you-folder-row {if $FOLDER.depth gt 0}hide{/if}"
                                    data-path="{$FOLDER.name|escape}" data-depth="{$FOLDER.depth}">
                                    <td style="padding-left: {10+$FOLDER.depth*22}px;">
                                        {if $FOLDER.has_children}
                                            <i class="fa-solid fa-caret-right its4you-folder-toggle" data-path="{$FOLDER.name|escape}" data-depth="{$FOLDER.depth}"></i>
                                        {else}
                                            <span class="its4you-toggle-spacer"></span>
                                        {/if}
                                        <label>
                                            <input type="checkbox" class="its4you-folder-check form-check-input"
                                                   value="{$FOLDER.remote_id|escape}"
                                                   data-path="{$FOLDER.name|escape}"
                                                   {if $FOLDER.checked}checked{/if}>
                                            &nbsp;<i class="fa {if $FOLDER.has_children}fa-folder{else}fa-folder-o{/if} its4you-folder-icon"></i>&nbsp;{$FOLDER.display|escape}
                                        </label>
                                    </td>
                                    <td class="its4you-folder-count">{if isset($FOLDER.count)}{$FOLDER.count}{/if}</td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                    <div class="its4you-hint">{vtranslate('LBL_FOLDERS_HINT', $QUALIFIED_MODULE)}</div>
                </div>
            </div>

            <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink" href="{$LIST_URL}">{vtranslate('LBL_BACK_TO_LIST', $QUALIFIED_MODULE)}</a></div>
            <div class="col-6"><button type="button" class="btn btn-primary active saveButton its4you-save-mailbox">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
                        {if $IS_EDIT}
                            
                        {else}
                            <a class="btn btn-primary cancelLink" href="{$WIZARD_URL}&step={if $PROVIDER eq 'imap'}3{else}2{/if}&email={$EMAIL|escape:'url'}&provider={$PROVIDER}&name={$MAILBOX_NAME|escape:'url'}">{vtranslate('LBL_BACK', $QUALIFIED_MODULE)}</a>
                        {/if}</div>
        </div>
    </div>
</div>
        </div>
        {/if}
    {/if}
</div>
</div>
{/strip}
