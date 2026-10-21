{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{* The mailbox's own SMTP server with its test (SmtpTest.js) — the wizard's
   step 3 (SMTP_WIZARD: "same password as IMAP"), the admin's mailbox edit
   and My Settings (an empty password keeps / tests the stored one).
   Place it inside .its4you-smtp-box *}
{strip}
{assign var=SMTP_LANG value='Settings:MailManager'}
<div class="row py-2 its4you-form-row">
    <div class="col-lg-3 fieldLabel">{vtranslate('LBL_SMTP_SERVER', $SMTP_LANG)}<span class="text-danger ms-2">*</span></div>
    <div class="col-lg-4 fieldValue">
        <input type="text" class="inputElement form-control" name="smtp_server" value="{$SMTP_SERVER|escape}" placeholder="smtp.company.com">
    </div>
</div>
<div class="row py-2 its4you-form-row">
    <div class="col-lg-3 fieldLabel">{vtranslate('LBL_SMTP_PORT', $SMTP_LANG)}</div>
    <div class="col-lg-4 fieldValue">
        <input type="text" class="inputElement form-control" name="smtp_port" value="{$SMTP_PORT|escape}" placeholder="587" style="max-width:120px;">
        &nbsp;&nbsp;
        <select class="inputElement form-select" name="smtp_encryption" style="max-width:160px;">
            <option value="tls" {if $SMTP_ENCRYPTION neq 'ssl' && $SMTP_ENCRYPTION neq 'none'}selected{/if}>STARTTLS</option>
            <option value="ssl" {if $SMTP_ENCRYPTION eq 'ssl'}selected{/if}>SSL/TLS</option>
            <option value="none" {if $SMTP_ENCRYPTION eq 'none'}selected{/if}>{vtranslate('LBL_SMTP_NO_ENCRYPTION', $SMTP_LANG)}</option>
        </select>
    </div>
</div>
<div class="row py-2 its4you-form-row">
    <div class="col-lg-3 fieldLabel">{vtranslate('LBL_SMTP_USERNAME', $SMTP_LANG)}</div>
    <div class="col-lg-4 fieldValue">
        <input type="text" class="inputElement form-control" name="smtp_username" value="{$SMTP_USERNAME|escape}">
    </div>
</div>
{if $SMTP_WIZARD}
    {* most hosts use one account for both servers *}
    <div class="row py-2 its4you-form-row">
        <div class="col-lg-3 fieldLabel"></div>
        <div class="col-lg-4 fieldValue">
            <label><input class="form-check-input" type="checkbox" name="same_password" checked>&nbsp;{vtranslate('LBL_SMTP_SAME_PASSWORD', $SMTP_LANG)}</label>
        </div>
    </div>
{/if}
<div class="row py-2 its4you-form-row its4you-smtp-password-row {if $SMTP_WIZARD}hide{/if}">
    <div class="col-lg-3 fieldLabel">{vtranslate('LBL_SMTP_PASSWORD', $SMTP_LANG)}</div>
    <div class="col-lg-4 fieldValue">
        <input type="password" class="inputElement form-control" name="smtp_password" value="" autocomplete="new-password"
               placeholder="{if $SMTP_HAS_PASSWORD}{vtranslate('LBL_PASSWORD_UNCHANGED', $SMTP_LANG)}{/if}">
    </div>
</div>
<div class="row py-2 its4you-form-row">
    <div class="col-lg-3 fieldLabel"></div>
    <div class="col-lg-4 fieldValue">
        <button type="button" class="btn btn-outline-secondary its4you-test-smtp">
            <i class="fa-solid fa-plug"></i>&nbsp;{vtranslate('LBL_TEST_SMTP', $SMTP_LANG)}
        </button>
        <div class="alert alert-success its4you-smtp-status {if !$SMTP_TESTED}hide{/if}" style="margin-top:10px; max-width:460px;">
            &#10003;&nbsp;{vtranslate('LBL_SMTP_OK', $SMTP_LANG)}
        </div>
    </div>
</div>
{/strip}
