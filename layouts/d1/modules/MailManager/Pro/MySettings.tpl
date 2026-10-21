{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{strip}
<div class="col-lg-12 col-md-12 col-sm-12 its4you-module-settings its4you-my-settings">
    <div class="editViewHeader container-fluid py-3 mb-3 border-bottom">
        <span class="fs-4 fw-bold">
            {if $ADMIN_MODE}
                {* the admin's mailbox detail in Settings *}
                <i class="fa-solid fa-envelope me-2"></i>
                <a href="{$BACK_URL}">{vtranslate('MailManager', 'Settings:MailManager')}</a>
                &nbsp;&gt;&nbsp;{$SELECTED.name|escape}&nbsp;<span class="its4you-muted">({$SELECTED.email|escape})</span>
            {else}
            {* inside the inbox the sidebar is the way back — the page keeps the breadcrumb *}
            {if !$EMBEDDED}
                <a class="its4you-settings-close" href="index.php?module={$MODULE}&view=Index&app=TOOLS">{vtranslate($MODULE, $MODULE)}</a>
                &nbsp;&gt;&nbsp;
            {/if}
            {vtranslate('LBL_MY_SETTINGS', $MODULE)}
            {/if}
        </span>
    </div>

    {if !$MAILBOXES}
        <div class="alert alert-info">{vtranslate('LBL_NO_OWN_MAILBOXES', $MODULE)}</div>
        <a class="btn btn-primary" href="index.php?module={$MODULE}&view=ProAddMailbox&app=TOOLS">
            <i class="fa-solid fa-plus"></i>&nbsp;{vtranslate('LBL_ADD_MY_MAILBOX', $MODULE)}
        </a>
    {else}
        {* the mailbox the tabs below belong to — switching reloads the page *}
        <div class="row py-2 its4you-form-row its4you-my-mailbox-row">
            <div class="col-lg-3 fieldLabel">{vtranslate('LBL_MAILBOX', $MODULE)}</div>
            <div class="col-lg-4 fieldValue">
                <select class="inputElement its4you-my-mailbox form-select">
                    {foreach item=MY_MAILBOX from=$MAILBOXES}
                        <option value="{$MY_MAILBOX.mailboxid}" {if $MY_MAILBOX.mailboxid eq $SELECTED.mailboxid}selected{/if}>
                            {$MY_MAILBOX.name|escape} — {$MY_MAILBOX.email|escape}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>

        {if !$CAN_MANAGE}
            <div class="alert alert-warning">{vtranslate('LBL_SHARED_ADMIN_ONLY', $MODULE)}</div>
        {/if}

        {* Same tabs as the admin AI settings (native TaxIndex pattern) — the
           `.contents.tabbable` wrapper is mandatory, the skin scopes the
           active-tab underline to it *}
        <div class="contents tabbable clearfix">
        <ul class="nav nav-tabs layoutTabs massEditTabs">
            {if $CAN_MANAGE}
                <li class="nav-item"><a class="nav-link active" href="#mmMyTabGeneral" data-bs-toggle="tab">{vtranslate('LBL_TAB_GENERAL', 'Settings:MailManager')}</a></li>
                <li class="nav-item"><a class="nav-link" href="#mmMyTabFolders" data-bs-toggle="tab">{vtranslate('LBL_FOLDERS_TO_SCAN', 'Settings:MailManager')}</a></li>
                <li class="nav-item"><a class="nav-link" href="#mmMyTabSmtp" data-bs-toggle="tab">{vtranslate('LBL_SMTP_MODE', 'Settings:MailManager')}</a></li>
                {if $IS_PERSONAL}
                    <li class="nav-item"><a class="nav-link" href="#mmMyTabSharing" data-bs-toggle="tab">{vtranslate('LBL_SHARING', 'Settings:MailManager')}</a></li>
                {/if}
            {/if}
            <li class="nav-item"><a class="nav-link{if !$CAN_MANAGE} active{/if}" href="#mmMyTabStyle" data-bs-toggle="tab">{vtranslate('LBL_STYLE_PROFILE', 'Settings:MailManager')}</a></li>
            {if $SHOW_SIGNATURE}
                <li class="nav-item"><a class="nav-link" href="#mmMyTabSignature" data-bs-toggle="tab">{vtranslate('LBL_SIGNATURE', 'Settings:MailManager')}</a></li>
            {/if}
            {if $SHOW_NOTIFICATIONS}
                <li class="nav-item"><a class="nav-link" href="#mmMyTabNotifications" data-bs-toggle="tab">{vtranslate('LBL_NOTIFICATIONS', 'Settings:MailManager')}</a></li>
            {/if}
            {if $SHOW_LIST_SETTINGS}
                <li class="nav-item"><a class="nav-link" href="#mmMyTabList" data-bs-toggle="tab">{vtranslate('LBL_MAIL_LIST', 'Settings:MailManager')}</a></li>
            {/if}
            {if $SHOW_KNOWLEDGE}
                <li class="nav-item"><a class="nav-link" href="#mmMyTabKnowledge" data-bs-toggle="tab">{vtranslate('LBL_KNOWLEDGE', 'Settings:MailManager')}</a></li>
            {/if}
            {if $SHOW_LEARNING}
                <li class="nav-item"><a class="nav-link" href="#mmMyTabLabels" data-bs-toggle="tab">{vtranslate('LBL_EMAIL_LABELS', 'Settings:MailManager')}</a></li>
                <li class="nav-item"><a class="nav-link" href="#mmMyTabLearning" data-bs-toggle="tab">{vtranslate('LBL_LEARNING', 'Settings:MailManager')}</a></li>
            {/if}
            <li class="nav-item"><a class="nav-link" href="#mmMyTabHistory" data-bs-toggle="tab">{vtranslate('LBL_ACTIVITY_HISTORY', 'Settings:MailManager')}</a></li>
            {if $SHOW_SENDING}
                <li class="nav-item"><a class="nav-link" href="#mmMyTabSending" data-bs-toggle="tab">{vtranslate('LBL_SENDING', 'Settings:MailManager')}</a></li>
            {/if}
        </ul>
        <div class="tab-content layoutContent overflowVisible pt-3">

        {if $CAN_MANAGE}
            <div class="tab-pane fade show active" id="mmMyTabGeneral">
                <div class="row py-2 its4you-form-row">
                    <div class="col-lg-3 fieldLabel">{vtranslate('LBL_MAILBOX_NAME', 'Settings:MailManager')}<span class="text-danger ms-2">*</span></div>
                    <div class="col-lg-4 fieldValue">
                        <input type="text" class="inputElement its4you-my-name form-control" name="name" value="{$SELECTED.name|escape}">
                    </div>
                </div>
                {if $ADMIN_MODE}
                    {* the admin's part — whose mailbox it is, its type and the members of a shared one *}
                    <div class="row py-2 its4you-form-row">
                        <div class="col-lg-3 fieldLabel">{vtranslate('LBL_OWNER', 'Settings:MailManager')}</div>
                        <div class="col-lg-4 fieldValue its4you-form-text"><i class="fa-solid fa-user its4you-muted"></i>&nbsp;{$OWNER_NAME|escape}</div>
                    </div>
                    <div class="row py-2 its4you-form-row">
                        <div class="col-lg-3 fieldLabel">{vtranslate('LBL_MAILBOX_TYPE', 'Settings:MailManager')}<span class="text-danger ms-2">*</span></div>
                        <div class="col-lg-4 fieldValue">
                            <label class="form-check form-check-inline its4you-radio"><input class="form-check-input" type="radio" name="mailbox_type" value="personal" {if $IS_PERSONAL}checked{/if}>&nbsp;{vtranslate('LBL_TYPE_PERSONAL', 'Settings:MailManager')}</label>
                            <label class="form-check form-check-inline its4you-radio"><input class="form-check-input" type="radio" name="mailbox_type" value="shared" {if !$IS_PERSONAL}checked{/if}>&nbsp;{vtranslate('LBL_TYPE_SHARED', 'Settings:MailManager')}</label>
                            <div class="its4you-hint">{vtranslate('LBL_TYPE_HINT', 'Settings:MailManager')}</div>
                        </div>
                    </div>
                    <div class="row py-2 its4you-form-row its4you-shared-with-row {if $IS_PERSONAL}hide{/if}">
                        <div class="col-lg-3 fieldLabel">{vtranslate('LBL_SHARED_WITH', 'Settings:MailManager')}<span class="text-danger ms-2">*</span></div>
                        <div class="col-lg-4 fieldValue">
                            <select class="select2 its4you-shared-with inputElement form-select" multiple data-placeholder="{vtranslate('LBL_SHARED_WITH', 'Settings:MailManager')|escape}">
                                {foreach key=USER_ID item=USER_NAME from=$USERS_LIST}
                                    <option value="user:{$USER_ID}" {if !$IS_PERSONAL && in_array("user:`$USER_ID`", $MEMBERS_SELECTED)}selected{/if}>{$USER_NAME|escape}</option>
                                {/foreach}
                            </select>
                            <div class="its4you-hint">{vtranslate('LBL_OWNER_HAS_ACCESS', 'Settings:MailManager')}</div>
                        </div>
                    </div>
                {/if}

                <div class="row py-2 its4you-form-row">
                    <div class="col-lg-3 fieldLabel">{vtranslate('LBL_SET_DEFAULT', 'Settings:MailManager')}</div>
                    <div class="col-lg-4 fieldValue">
                        <input class="form-check-input" type="checkbox" name="is_default" {if $SELECTED.is_default eq 1}checked{/if}>
                    </div>
                </div>
                {* the server certificate of an IMAP mailbox is checked here (no password needed) *}
                {if $ADMIN_MODE && $SELECTED.provider eq 'imap'}
                    <div class="row py-2 its4you-form-row its4you-cert-row" data-record="{$SELECTED.mailboxid}">
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
                            {include file="CertificateBox.tpl"|vtemplate_path:'Settings:MailManager'}
                        </div>
                    </div>
                {/if}

                <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink its4you-settings-close" href="{$BACK_URL}">{vtranslate('LBL_CANCEL', $MODULE)}</a></div>
            <div class="col-6"><button type="button" class="btn btn-primary active saveButton its4you-my-save">{vtranslate('LBL_SAVE', $MODULE)}</button></div>
        </div>
    </div>
</div>
            </div>

            <div class="tab-pane fade" id="mmMyTabFolders">
                <div class="row py-2 its4you-form-row">
                    <div class="col-lg-3 fieldLabel">{vtranslate('LBL_FOLDERS_TO_SCAN', 'Settings:MailManager')}</div>
                    <div class="col-lg-4 fieldValue">
                        {* collapsible like the Mail Manager sidebar — subfolders start
                           closed, the caret opens one level (MySettings.js) *}
                        <div class="its4you-my-folders" data-delimiter="{if $FOLDERS}{$FOLDERS[0].delimiter|escape}{/if}">
                            {foreach item=MY_FOLDER from=$FOLDERS}
                                <div class="its4you-my-folder-row {if $MY_FOLDER.depth gt 0}hide{/if}"
                                     data-path="{$MY_FOLDER.name|escape}" data-depth="{$MY_FOLDER.depth}"
                                     style="padding-left: {$MY_FOLDER.depth * 20}px;">
                                    {if $MY_FOLDER.has_children}
                                        <i class="fa-solid fa-caret-right its4you-my-folder-toggle"></i>
                                    {else}
                                        <span class="its4you-my-toggle-spacer"></span>
                                    {/if}
                                    <label>
                                        <input type="checkbox" class="its4you-my-folder form-check-input" value="{$MY_FOLDER.remote_id|escape}"
                                               {if $MY_FOLDER.sync_enabled}checked{/if}>
                                        &nbsp;<i class="fa {if $MY_FOLDER.has_children}fa-folder{else}fa-folder-o{/if} its4you-my-folder-icon"></i>&nbsp;{$MY_FOLDER.display|escape}
                                    </label>
                                </div>
                            {/foreach}
                        </div>
                        <div class="its4you-hint">{vtranslate('LBL_FOLDERS_HINT', 'Settings:MailManager')}</div>
                    </div>
                </div>

                <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink its4you-settings-close" href="{$BACK_URL}">{vtranslate('LBL_CANCEL', $MODULE)}</a></div>
            <div class="col-6"><button type="button" class="btn btn-primary active saveButton its4you-my-save">{vtranslate('LBL_SAVE', $MODULE)}</button></div>
        </div>
    </div>
</div>
            </div>

            {* Outgoing server — the mailbox's own SMTP (the admin's fields);
               a mailbox still on the CRM outgoing server is asked to switch *}
            <div class="tab-pane fade" id="mmMyTabSmtp">
                <div class="its4you-smtp-box" data-record="{$SELECTED.mailboxid}">
                    {if $SMTP_MODE neq 'custom'}
                        <div class="alert alert-warning" style="max-width:640px;">{vtranslate('LBL_SMTP_CRM_WARNING', 'Settings:MailManager')}</div>
                    {/if}
                    {include file="SmtpFields.tpl"|vtemplate_path:'Settings:MailManager' SMTP_WIZARD=false}
                </div>

                <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink its4you-settings-close" href="{$BACK_URL}">{vtranslate('LBL_CANCEL', $MODULE)}</a></div>
            <div class="col-6"><button type="button" class="btn btn-primary active saveButton its4you-my-save">{vtranslate('LBL_SAVE', $MODULE)}</button></div>
        </div>
    </div>
</div>
            </div>

            {* Deputies — users who work in this personal mailbox (e.g. over a
               holiday) under their own name; they never see these settings *}
            {if $IS_PERSONAL}
            <div class="tab-pane fade" id="mmMyTabSharing">
                <div class="its4you-sharing" data-mailbox="{$SELECTED.mailboxid}"
                     data-confirm="{vtranslate('LBL_SHARING_REVOKE_CONFIRM', 'Settings:MailManager')|escape}"
                     data-pick-user="{vtranslate('LBL_SHARING_PICK_USER', 'Settings:MailManager')|escape}">
                    <div class="its4you-hint marginBottom10px">{vtranslate('LBL_SHARING_HINT', 'Settings:MailManager')}</div>
                    <div class="its4you-sharing-add marginBottom10px">
                        <select class="inputElement its4you-sharing-user form-select"
                                data-placeholder="{vtranslate('LBL_SHARING_PICK_USER', 'Settings:MailManager')|escape}">
                            <option value=""></option>
                            {foreach key=DEPUTY_ID item=DEPUTY_NAME from=$DEPUTY_OPTIONS}
                                <option value="{$DEPUTY_ID}">{$DEPUTY_NAME|escape}</option>
                            {/foreach}
                        </select>
                        <div class="input-group inputElement its4you-sharing-until">
                            <input type="text" class="dateField form-control inputElement" data-date-format="{$DATE_FORMAT}"
                                   placeholder="{vtranslate('LBL_SHARING_UNTIL_PLACEHOLDER', 'Settings:MailManager')|escape}">
                            <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                        </div>
                        <button type="button" class="btn btn-primary its4you-sharing-grant">
                            <i class="fa-solid fa-plus"></i>&nbsp;{vtranslate('LBL_SHARING_GRANT', 'Settings:MailManager')}
                        </button>
                    </div>
                    <table class="table table-bordered its4you-labels-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">{vtranslate('LBL_ACTIONS', 'Settings:MailManager')}</th>
                                <th>{vtranslate('LBL_SHARING_DEPUTY', 'Settings:MailManager')}</th>
                                <th style="width: 220px;">{vtranslate('LBL_SHARING_UNTIL', 'Settings:MailManager')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach item=DEPUTY from=$DEPUTIES}
                                <tr data-userid="{$DEPUTY.userid}" data-name="{$DEPUTY.user_name|escape}">
                                    <td>
                                        <a href="javascript:void(0);" class="its4you-sharing-revoke" title="{vtranslate('LBL_DELETE', 'Settings:MailManager')}">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                    <td><i class="fa-solid fa-user its4you-muted"></i>&nbsp;{$DEPUTY.user_name|escape}</td>
                                    <td>
                                        {if $DEPUTY.valid_display}
                                            {$DEPUTY.valid_display}
                                            {if $DEPUTY.expired}&nbsp;<span class="badge text-bg-secondary">{vtranslate('LBL_SHARING_EXPIRED', 'Settings:MailManager')}</span>{/if}
                                        {else}
                                            <span class="its4you-muted">{vtranslate('LBL_SHARING_UNTIL_REVOKED', 'Settings:MailManager')}</span>
                                        {/if}
                                    </td>
                                </tr>
                            {foreachelse}
                                <tr>
                                    <td colspan="3" class="text-center">{vtranslate('LBL_SHARING_EMPTY', 'Settings:MailManager')}</td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
            {/if}
        {/if}

            {* Reply style of the OWN mailbox — same learning as the admin screen *}
            <div class="tab-pane fade {if !$CAN_MANAGE}active{/if}" id="mmMyTabStyle">
                <div class="its4you-hint marginBottom10px its4you-my-style-hint">{vtranslate('LBL_STYLE_PROFILE_DESC', 'Settings:MailManager')}</div>
                <div class="marginBottom10px">
                    <button type="button" class="btn btn-outline-secondary its4you-my-style-generate">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>&nbsp;{vtranslate('LBL_STYLE_GENERATE', 'Settings:MailManager')}
                    </button>
                </div>
                <textarea class="inputElement its4you-my-style-text form-control" rows="14" style="width: 100%; min-height: 280px; resize: vertical;"
                          placeholder="{vtranslate('LBL_STYLE_EMPTY_HINT', 'Settings:MailManager')}">{$STYLE_PROFILE|escape}</textarea>

                <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink its4you-settings-close" href="{$BACK_URL}">{vtranslate('LBL_CANCEL', $MODULE)}</a></div>
            <div class="col-6"><button type="button" class="btn btn-primary active saveButton its4you-my-style-save">{vtranslate('LBL_SAVE', $MODULE)}</button></div>
        </div>
    </div>
</div>
            </div>

            {* The user's own signature — the core user field (My Preferences),
               the composer puts it under every new mail, reply and forward *}
            {if $SHOW_SIGNATURE}
                <div class="tab-pane fade" id="mmMyTabSignature"
                     data-saved="{vtranslate('LBL_SIGNATURE_SAVED', 'Settings:MailManager')|escape}">
                    <div class="its4you-hint marginBottom10px">{vtranslate('LBL_SIGNATURE_HINT', 'Settings:MailManager')}</div>
                    <textarea id="its4youMySignature" class="its4you-my-signature-text form-control">{$SIGNATURE_HTML|escape}</textarea>

                    <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink its4you-settings-close" href="{$BACK_URL}">{vtranslate('LBL_CANCEL', $MODULE)}</a></div>
            <div class="col-6"><button type="button" class="btn btn-primary active saveButton its4you-my-signature-save">{vtranslate('LBL_SAVE', $MODULE)}</button></div>
        </div>
    </div>
</div>
                </div>
            {/if}

            {* The user's own browser notifications of new mail — the browser asks
               for its permission when the switch is saved on *}
            {if $SHOW_NOTIFICATIONS}
                <div class="tab-pane fade" id="mmMyTabNotifications"
                     data-saved="{vtranslate('LBL_NOTIFY_SAVED', 'Settings:MailManager')|escape}">
                    <div class="row py-2">
                        <div class="col-lg-3 fieldLabel">{vtranslate('LBL_NOTIFY_NEW_MAIL', 'Settings:MailManager')}</div>
                        <div class="col-lg-6 fieldValue">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input its4you-my-notify" id="its4youMyNotify" {if $NOTIFY_ENABLED}checked{/if}>
                                <label class="form-check-label" for="its4youMyNotify">{vtranslate('LBL_NOTIFY_HINT', 'Settings:MailManager')}</label>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning its4you-notify-blocked hide">{vtranslate('LBL_NOTIFY_BLOCKED', 'Settings:MailManager')}</div>
                    <div class="alert alert-warning its4you-notify-unsupported hide">{vtranslate('LBL_NOTIFY_UNSUPPORTED', 'Settings:MailManager')}</div>

                    <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink its4you-settings-close" href="{$BACK_URL}">{vtranslate('LBL_CANCEL', $MODULE)}</a></div>
            <div class="col-6"><button type="button" class="btn btn-primary active saveButton its4you-my-notify-save">{vtranslate('LBL_SAVE', $MODULE)}</button></div>
        </div>
    </div>
</div>
                </div>
            {/if}

            {* The list tab the user's inbox opens with *}
            {if $SHOW_LIST_SETTINGS}
                <div class="tab-pane fade" id="mmMyTabList"
                     data-saved="{vtranslate('LBL_LIST_TAB_SAVED', 'Settings:MailManager')|escape}">
                    <div class="row py-2">
                        <div class="col-lg-3 fieldLabel">{vtranslate('LBL_DEFAULT_LIST_TAB', 'Settings:MailManager')}</div>
                        <div class="col-lg-4 fieldValue">
                            <select class="form-select inputElement its4you-my-list-tab">
                                {foreach item=LIST_TAB from=$LIST_TABS}
                                    <option value="{$LIST_TAB}" {if $LIST_TAB eq $DEFAULT_LIST_TAB}selected{/if}>{vtranslate('LBL_TAB_'|cat:strtoupper($LIST_TAB), $MODULE)}</option>
                                {/foreach}
                            </select>
                            <div class="form-text">{vtranslate('LBL_DEFAULT_LIST_TAB_HINT', 'Settings:MailManager')}</div>
                        </div>
                    </div>

                    <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink its4you-settings-close" href="{$BACK_URL}">{vtranslate('LBL_CANCEL', $MODULE)}</a></div>
            <div class="col-6"><button type="button" class="btn btn-primary active saveButton its4you-my-list-save">{vtranslate('LBL_SAVE', $MODULE)}</button></div>
        </div>
    </div>
</div>
                </div>
            {/if}

            {* The user's own knowledge — his replies answer from it first *}
            {if $SHOW_KNOWLEDGE}
                <div class="tab-pane fade" id="mmMyTabKnowledge">
                    {include file="Pro/partials/KnowledgeFiles.tpl"|vtemplate_path:$MODULE KNOWLEDGE_SCOPE='personal'}
                </div>
            {/if}

            {* Own labels — the same editor as the admin global ones; they
               apply to all of the user's personal mailboxes, not just the
               one selected above *}
            {if $SHOW_LEARNING}
                <div class="tab-pane fade" id="mmMyTabLabels">
                    {* the owner's own labels (the admin edits them for the owner);
                       a shared mailbox uses the global ones only — Settings *}
                    {if $SHOW_OWN_LABELS}
                        <div class="its4you-my-labels its4you-own-labels">
                            {* the admin reads whose labels they are — the owner's hint speaks to the owner *}
                            <div class="its4you-hint marginBottom10px">
                                {if $ADMIN_MODE}
                                    {vtranslate('LBL_OWNER_LABELS_HINT', 'Settings:MailManager')|replace:'%s':($OWNER_NAME|escape)}
                                {else}
                                    {vtranslate('LBL_MY_LABELS_HINT', 'Settings:MailManager')}
                                {/if}
                            </div>
                            {include file="Pro/partials/LabelsTable.tpl"|vtemplate_path:$MODULE LABELS=$MY_LABELS EDITABLE=true}
                        </div>

                        {* The common labels — the admin's, nobody deletes or renames
                           them here; the owner sets up what each one DOES in the
                           personal mailboxes (action, automation, what the AI sorts) *}
                        <div class="its4you-default-labels"
                             data-saved="{vtranslate('LBL_LABEL_SETTING_SAVED', 'Settings:MailManager')|escape}"
                             data-reset="{vtranslate('LBL_LABEL_SETTING_RESET', 'Settings:MailManager')|escape}">
                            <h5 class="its4you-label-rules-title">{vtranslate('LBL_DEFAULT_LABELS', 'Settings:MailManager')}</h5>
                            <div class="its4you-hint marginBottom10px">{vtranslate('LBL_DEFAULT_LABELS_HINT', 'Settings:MailManager')}</div>
                            <table class="table table-bordered its4you-labels-table">
                                <thead>
                                    <tr>
                                        <th style="width: 70px;">{vtranslate('LBL_ACTIONS', 'Settings:MailManager')}</th>
                                        <th style="width: 150px;">{vtranslate('LBL_LABEL', 'Settings:MailManager')}</th>
                                        <th>{vtranslate('LBL_AI_DESCRIPTION', 'Settings:MailManager')}</th>
                                        <th style="width: 190px;">{vtranslate('LBL_SUGGESTED_ACTION', 'Settings:MailManager')}</th>
                                        <th style="width: 110px;" class="text-center">{vtranslate('LBL_AUTO_ACTION', 'Settings:MailManager')}</th>
                                        <th style="width: 100px;" class="text-center">{vtranslate('LBL_AI_SORTS', 'Settings:MailManager')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach item=DEFAULT_LABEL from=$DEFAULT_LABELS}
                                        <tr data-labelid="{$DEFAULT_LABEL.labelid}">
                                            <td>
                                                <a href="javascript:void(0);" class="its4you-save-default-label" title="{vtranslate('LBL_SAVE', $MODULE)}">
                                                    <i class="fa-regular fa-floppy-disk"></i>
                                                </a>
                                                {if $DEFAULT_LABEL.customized}
                                                    &nbsp;&nbsp;
                                                    <a href="javascript:void(0);" class="its4you-reset-default-label" title="{vtranslate('LBL_LABEL_RESET', 'Settings:MailManager')}">
                                                        <i class="fa-solid fa-rotate-left"></i>
                                                    </a>
                                                {/if}
                                            </td>
                                            <td>
                                                <i class="fa-solid fa-lock its4you-muted"></i>&nbsp;
                                                <span class="its4you-label-chip" style="background: {$DEFAULT_LABEL.color|escape}; color: {Settings_MailManager_Label_Model::getTextColor($DEFAULT_LABEL.color)};">{$DEFAULT_LABEL.name|escape}</span>
                                                {if $DEFAULT_LABEL.customized}
                                                    <div class="its4you-muted its4you-default-customized">{vtranslate('LBL_LABEL_CUSTOMIZED', 'Settings:MailManager')}</div>
                                                {/if}
                                            </td>
                                            <td>
                                                <textarea class="inputElement its4you-default-description form-control" rows="2">{if $DEFAULT_LABEL.customized}{$DEFAULT_LABEL.own_description|escape}{else}{$DEFAULT_LABEL.ai_description|escape}{/if}</textarea>
                                            </td>
                                            <td>
                                                <select class="inputElement its4you-default-module form-select">
                                                    <option value="">&mdash;</option>
                                                    {foreach item=SUGGESTED_OPTION from=$SUGGESTED_OPTIONS}
                                                        <option value="{$SUGGESTED_OPTION}" {if $DEFAULT_LABEL.suggested_module eq $SUGGESTED_OPTION}selected{/if}>
                                                            {vtranslate("LBL_ACTION_"|cat:($SUGGESTED_OPTION|replace:':':'_'), 'Settings:MailManager')}
                                                        </option>
                                                    {/foreach}
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="its4you-default-auto form-check-input" {if $DEFAULT_LABEL.auto_action eq 1}checked{/if}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="its4you-default-ai form-check-input" {if $DEFAULT_LABEL.ai_enabled eq 1}checked{/if}>
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    {else}
                        <div class="alert alert-info">{vtranslate('LBL_SHARED_GLOBAL_LABELS_ONLY', 'Settings:MailManager')}</div>
                    {/if}
                </div>

                {* What the mailbox picked above learned from the user's
                   corrections — label rules and spam senders (switching the
                   spam learning on stays the admin's) *}
                <div class="tab-pane fade its4you-my-learning" id="mmMyTabLearning">
                    {include file="Pro/partials/LabelRulesTable.tpl"|vtemplate_path:$MODULE HIDE_MAILBOX=true}

                    <div class="its4you-my-spam"
                         data-confirm="{vtranslate('LBL_DELETE_SPAMRULE_CONFIRM', 'Settings:MailManager')|escape}"
                         data-deleted="{vtranslate('LBL_SPAMRULE_DELETED', 'Settings:MailManager')|escape}">
                        <h5 class="its4you-label-rules-title">{vtranslate('LBL_SPAM_SENDERS', 'Settings:MailManager')}</h5>
                        {if !$SPAM_ENABLED}
                            <div class="alert alert-info">{vtranslate('LBL_SPAM_LEARNING_OFF', 'Settings:MailManager')}</div>
                        {/if}
                        <div class="its4you-hint marginBottom10px">
                            {vtranslate('LBL_SPAM_TOTAL_PREFIX', 'Settings:MailManager')}&nbsp;<b>{$SPAM_TOTAL}</b>&nbsp;{vtranslate('LBL_SPAM_TOTAL_SUFFIX', 'Settings:MailManager')}
                        </div>
                        <table class="table table-bordered its4you-labels-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">{vtranslate('LBL_ACTIONS', 'Settings:MailManager')}</th>
                                    <th>{vtranslate('LBL_SENDER', 'Settings:MailManager')}</th>
                                    <th style="width: 110px;" class="text-center">{vtranslate('LBL_MANUAL_HITS', 'Settings:MailManager')}</th>
                                    <th style="width: 110px;" class="text-center">{vtranslate('LBL_AUTO_MOVED', 'Settings:MailManager')}</th>
                                    <th style="width: 150px;">{vtranslate('LBL_LAST_ACTIVITY', 'Settings:MailManager')}</th>
                                    <th style="width: 100px;">{vtranslate('LBL_STATUS', 'Settings:MailManager')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach item=SPAM_RULE from=$SPAM_RULES}
                                    <tr data-ruleid="{$SPAM_RULE.ruleid}" data-sender="{$SPAM_RULE.from_email|escape}">
                                        <td>
                                            <a href="javascript:void(0);" class="its4you-delete-my-spamrule" title="{vtranslate('LBL_DELETE', 'Settings:MailManager')}">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                        <td>{$SPAM_RULE.from_email|escape}</td>
                                        <td class="text-center">{$SPAM_RULE.hits}</td>
                                        <td class="text-center">{$SPAM_RULE.moved_count}</td>
                                        <td>{$SPAM_RULE.last_hit_display}</td>
                                        <td>
                                            {if $SPAM_RULE.trusted}
                                                <span class="badge text-bg-info">{vtranslate('LBL_RULE_TRUSTED', 'Settings:MailManager')}</span>
                                            {elseif $SPAM_RULE.hits >= $SPAM_ACTIVATION_HITS}
                                                <span class="badge text-bg-success">{vtranslate('LBL_RULE_ACTIVE', 'Settings:MailManager')}</span>
                                            {else}
                                                <span class="badge text-bg-secondary">{vtranslate('LBL_RULE_LEARNING', 'Settings:MailManager')}</span>
                                            {/if}
                                        </td>
                                    </tr>
                                {foreachelse}
                                    <tr>
                                        <td colspan="6" class="text-center">{vtranslate('LBL_NO_SPAM_RULES', 'Settings:MailManager')}</td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            {/if}

            {* what happened to the mail of the mailbox picked above — moves,
               deletions, labels and CRM links, the user's and the automatic ones *}
            <div class="tab-pane fade" id="mmMyTabHistory">
                {include file="Pro/partials/History.tpl"|vtemplate_path:$MODULE}
            </div>

            {* the user's own "Undo" time after Send *}
            {if $SHOW_SENDING}
                <div class="tab-pane fade" id="mmMyTabSending"
                     data-saved="{vtranslate('LBL_SEND_DELAY_SAVED', 'Settings:MailManager')|escape}">
                    <div class="row py-2">
                        <div class="col-lg-3 fieldLabel">{vtranslate('LBL_SEND_DELAY', 'Settings:MailManager')}</div>
                        <div class="col-lg-4 fieldValue">
                            <select class="form-select inputElement its4you-my-send-delay">
                                {foreach item=DELAY from=$SEND_DELAYS}
                                    <option value="{$DELAY}" {if $DELAY eq $SEND_DELAY}selected{/if}>{if $DELAY}{vtranslate('LBL_SECONDS', 'Settings:MailManager')|replace:'%s':$DELAY}{else}{vtranslate('LBL_SEND_DELAY_OFF', 'Settings:MailManager')}{/if}</option>
                                {/foreach}
                            </select>
                            <div class="form-text">{vtranslate('LBL_SEND_DELAY_HINT', 'Settings:MailManager')}</div>
                        </div>
                    </div>

                    <div class="modal-overlay-footer modal-footer border-top">
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-6 text-end"><a class="btn btn-primary cancelLink its4you-settings-close" href="{$BACK_URL}">{vtranslate('LBL_CANCEL', $MODULE)}</a></div>
            <div class="col-6"><button type="button" class="btn btn-primary active saveButton its4you-my-send-delay-save">{vtranslate('LBL_SAVE', $MODULE)}</button></div>
        </div>
    </div>
</div>
                </div>
            {/if}

        </div>
        </div>
    {/if}
</div>

{* Label modal — a sibling OUTSIDE the page container; handlers bind to the modal element *}
{if $SHOW_OWN_LABELS}
    <div class="its4you-own-label-modal">{include file="Pro/partials/LabelModal.tpl"|vtemplate_path:$MODULE}</div>
{/if}
{/strip}
