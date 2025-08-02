{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{strip}
    <div class="modal-dialog modal-lg mapcontainer">
        <form id="EditView" method="POST">
            <div class="modal-content">
                {if $MAILBOX->exists()}
                    {assign var=MODAL_TITLE value=vtranslate('LBL_EDIT_MAILBOX', $MODULE)}
                {else}
                    {assign var=MODAL_TITLE value=vtranslate('LBL_CREATE_MAILBOX', $MODULE)}
                {/if}
                {include file="ModalHeader.tpl"|vtemplate_path:$SOURCE_MODULE TITLE=$MODAL_TITLE}
                <div class="modal-body" id="mmSettingEditModal">
                    <div class="container-fluid">
                        <div class="row py-2">
                            <div class="fieldLabel col-lg-4">
                                <label class="detailViewButtoncontainer">{vtranslate('LBL_SELECT_ACCOUNT',$MODULE)}</label>
                            </div>
                            <div class="fieldValue col-lg">
                                <select id="serverType" class="select2 col-lg-9">
                                    <option></option>
                                    <option value='gmail' {if $SERVERNAME eq 'gmail'} selected {/if}>{vtranslate('JSLBL_Gmail',$MODULE)}</option>
                                    <option value='yahoo' {if $SERVERNAME eq 'yahoo'} selected {/if}>{vtranslate('JSLBL_Yahoo',$MODULE)}</option>
                                    <option value='fastmail' {if $SERVERNAME eq 'fastmail'} selected {/if}>{vtranslate('JSLBL_Fastmail',$MODULE)}</option>
                                    <option value='other' {if $SERVERNAME eq 'other'} selected {/if}>{vtranslate('JSLBL_Other',$MODULE)}</option>
                                </select>
                            </div>
                        </div>
                        <div class="row py-2 settings_details {if $SERVERNAME eq ''}hide{/if}">
                            <div class="fieldLabel col-lg-4">
                                <label class="detailViewButtoncontainer">
                                    <span>{vtranslate('LBL_Mail_Server',$MODULE)}</span>
                                    <span class="text-danger ms-2">*</span>
                                </label>
                            </div>
                            <div class="fieldValue col-lg">
                                <input name="_mbox_server" id="_mbox_server" class="inputElement form-control" value="{$MAILBOX->server()}" type="text" placeholder="mail.company.com or 192.168.X.X">
                            </div>
                        </div>
                        <div class="row py-2 settings_details {if $SERVERNAME eq ''}hide{/if}">
                            <div class="fieldLabel col-lg-4">
                                <label class="detailViewButtoncontainer">
                                    <span>{vtranslate('LBL_Username',$MODULE)}</span>
                                    <span class="text-danger ms-2"></span>
                                </label>
                            </div>
                            <div class="fieldValue col-lg">
                                <input name="_mbox_user" class="inputElement form-control" id="_mbox_user" value="{$MAILBOX->username()}" type="text" placeholder="{vtranslate('LBL_Your_Mailbox_Account',$MODULE)}">
                            </div>
                        </div>
                        <div class="row py-2 settings_password {if $SERVERNAME eq '' or $MAILBOX->isOAuth()}hide{/if}">
                            <div class="fieldLabel col-lg-4">
                                <label class="detailViewButtoncontainer">
                                    <span>{vtranslate('LBL_Password',$MODULE)}</span>
                                    <span class="text-danger ms-2">*</span>
                                </label>
                            </div>
                            <div class="fieldValue col-lg">
                                <input name="_mbox_pwd" class="inputElement form-control" id="_mbox_pwd" value="{$MAILBOX->password()}" type="password" placeholder="{vtranslate('LBL_Account_Password',$MODULE)}">
                            </div>
                        </div>
                        <div class="row py-2 oauth2_settings {if !$MAILBOX->isOAuth()}hide{/if}">
                            <div class="fieldLabel col-lg-4">
                                <label class="detailViewButtoncontainer">
                                    <span>{vtranslate('LBL_CLIENT_ID',$MODULE)}</span>
                                    <span class="text-danger ms-2">*</span>
                                </label>
                            </div>
                            <div class="fieldValue col-lg">
                                <input name="_mbox_client_id" class="inputElement form-control" id="_mbox_client_id" value="{$MAILBOX->getClientId()}" type="text">
                            </div>
                        </div>
                        <div class="row py-2 oauth2_settings {if !$MAILBOX->isOAuth()}hide{/if}">
                            <div class="fieldLabel col-lg-4">
                                <label class="detailViewButtoncontainer">
                                    <span>{vtranslate('LBL_CLIENT_SECRET',$MODULE)}</span>
                                    <span class="text-danger ms-2">*</span>
                                </label>
                            </div>
                            <div class="fieldValue col-lg">
                                <input name="_mbox_client_secret" class="inputElement form-control" id="_mbox_client_secret" value="{$MAILBOX->getClientSecret()}" type="password">
                            </div>
                        </div>
                        <div class="row py-2 oauth2_settings {if !$MAILBOX->isOAuth()}hide{/if}">
                            <div class="fieldLabel col-lg-4">
                                <label class="detailViewButtoncontainer">
                                    <span>{vtranslate('LBL_CLIENT_TOKEN',$MODULE)}</span>
                                    <span class="text-danger ms-2">*</span>
                                </label>
                            </div>
                            <div class="fieldValue col-lg">
                                <div class="input-group">
                                    <input name="_mbox_client_token" class="inputElement form-control" id="_mbox_client_token" value="{$MAILBOX->getClientToken()}" type="password">
                                    <button class="input-group-text refreshToken" type="button">
                                        <i class="fa fa-download"></i>
                                    </button>
                                    <button class="input-group-text retrieveToken" type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row py-2 oauth2_settings {if !$MAILBOX->isOAuth()}hide{/if}">
                            <div class="fieldLabel col-lg-4">
                                <label class="detailViewButtoncontainer">
                                    <span>{vtranslate('LBL_CLIENT_ACCESS_TOKEN',$MODULE)}</span>
                                    <span class="text-danger ms-2">*</span>
                                </label>
                            </div>
                            <div class="fieldValue col-lg">
                                <input name="_mbox_client_access_token" class="inputElement form-control" id="_mbox_client_access_token" value="{$MAILBOX->getClientAccessToken()}" type="password">
                            </div>
                        </div>
                        <div class="row py-2 additional_settings {if $SERVERNAME neq 'other'}hide{/if}">
                            <div class="fieldLabel col-lg-4">
                                <label class="detailViewButtoncontainer">{vtranslate('LBL_Protocol',$MODULE)}</label>
                            </div>
                            <div class="fieldValue col-lg">
                                <div class="input-group">
                                    <label class="form-check me-4 w-25">
                                        <input type="radio" name="_mbox_protocol" class="mbox_protocol form-check-input" value="IMAP2" {if strcasecmp($MAILBOX->protocol(), 'imap2')===0}checked=true{/if}>
                                        <span class="form-check-label">{vtranslate('LBL_Imap2',$MODULE)}</span>
                                    </label>
                                    <label class="form-check me-4">
                                        <input type="radio" name="_mbox_protocol" class="mbox_protocol form-check-input" value="IMAP4" {if strcasecmp($MAILBOX->protocol(), 'imap4')===0}checked=true{/if} >
                                        <span class="form-check-label">{vtranslate('LBL_Imap4',$MODULE)}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row py-2 additional_settings {if $SERVERNAME neq 'other'}hide{/if}">
                            <div class="fieldLabel col-lg-4">
                                <label class="detailViewButtoncontainer">{vtranslate('LBL_SSL_Options',$MODULE)}</label>
                            </div>
                            <div class="fieldValue col-lg">
                                <div class="input-group">
                                    <label class="form-check me-4 w-25">
                                        <input type="radio" name="_mbox_ssltype" class="mbox_ssltype form-check-input" value="notls" {if strcasecmp($MAILBOX->ssltype(), 'notls')===0}checked=true{/if}>
                                        <span class="form-check-label">{vtranslate('LBL_No_TLS',$MODULE)}</span>
                                    </label>
                                    <label class="form-check me-4 w-25">
                                        <input type="radio" name="_mbox_ssltype" class="mbox_ssltype form-check-input" value="tls" {if strcasecmp($MAILBOX->ssltype(), 'tls')===0}checked=true{/if} >
                                        <span class="form-check-label">{vtranslate('LBL_TLS',$MODULE)}</span>
                                    </label>
                                    <label class="form-check me-4 w-25">
                                        <input type="radio" name="_mbox_ssltype" class="mbox_ssltype form-check-input" value="ssl" {if strcasecmp($MAILBOX->ssltype(), 'ssl')===0}checked=true{/if} >
                                        <span class="form-check-label">{vtranslate('LBL_SSL',$MODULE)}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row py-2 additional_settings {if $SERVERNAME neq 'other'}hide{/if}">
                            <div class="fieldLabel col-lg-4">
                                <label class="detailViewButtoncontainer">{vtranslate('LBL_Certificate_Validations',$MODULE)}</label>
                            </div>
                            <div class="fieldValue col-lg">
                                <div class="input-group">
                                    <label class="form-check me-4 w-25">
                                        <input type="radio" name="_mbox_certvalidate" class="mbox_certvalidate form-check-input" value="validate-cert" {if strcasecmp($MAILBOX->certvalidate(), 'validate-cert')===0}checked=true{/if} >
                                        <span class="form-check-label">{vtranslate('LBL_Validate_Cert',$MODULE)}</span>
                                    </label>
                                    <label class="form-check me-4">
                                        <input type="radio" name="_mbox_certvalidate" class="mbox_certvalidate form-check-input" value="novalidate-cert" {if strcasecmp($MAILBOX->certvalidate(), 'novalidate-cert')===0}checked=true{/if} >
                                        <span class="form-check-label">{vtranslate('LBL_Do_Not_Validate_Cert',$MODULE)}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row py-2 refresh_settings {if $MAILBOX && $MAILBOX->exists()}{else}hide{/if}">
                            <div class="fieldLabel col-lg-4">
                                <label class="detailViewButtoncontainer">{vtranslate('LBL_REFRESH_TIME',$MODULE)}</label>
                            </div>
                            <div class="fieldValue col-lg">
                                <select name="_mbox_refresh_timeout" class="select2 col-lg-9">
                                    <option value="" {if $MAILBOX->refreshTimeOut() eq ''}selected{/if}>{vtranslate('LBL_NONE',$MODULE)}</option>
                                    <option value="300000" {if strcasecmp($MAILBOX->refreshTimeOut(), '300000')==0}selected{/if}>{vtranslate('LBL_5_MIN',$MODULE)}</option>
                                    <option value="600000" {if strcasecmp($MAILBOX->refreshTimeOut(), '600000')==0}selected{/if}>{vtranslate('LBL_10_MIN',$MODULE)}</option>
                                </select>
                            </div>
                        </div>

                        <div class="row py-2 settings_details {if $SERVERNAME eq ''}hide{/if}">
                            <div class="fieldLabel col-lg-4">
                                <label class="detailViewButtoncontainer">{vtranslate('LBL_SAVE_SENT_MAILS_IN',$MODULE)}</label>
                            </div>
                            <div class="fieldValue col-lg selectFolderValue {if !$MAILBOX->exists()}hide{/if}">
                                <div class="input-group">
                                    <select name="_mbox_sent_folder" class="select2 form-select">
                                        {foreach item=FOLDER from=$FOLDERS}
                                            <option value="{$FOLDER->getName()}" {if $FOLDER->getName() eq $MAILBOX->getFolder()} selected {/if}>{$FOLDER->getName()}</option>
                                        {/foreach}
                                    </select>
                                    <span class="input-group-text" title="{vtranslate('LBL_CHOOSE_FOLDER',$MODULE)}">
                                        <i class="fa fa-info-circle" id="mmSettingInfo"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="fieldValue col-lg selectFolderDesc {if $MAILBOX->exists()}hide{/if}">
                                <div class="alert alert-info">{vtranslate('LBL_CHOOSE_FOLDER_DESC',$MODULE)}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col text-end">
                                <a href="#" class="btn btn-primary cancelLink" type="reset" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                            </div>
                            {if $MAILBOX->exists()}
                                <div class="col-auto">
                                    <button class="btn btn-danger" id="deleteMailboxBtn">
                                        <strong>{vtranslate('LBL_DELETE_Mailbox',$MODULE)}</strong>
                                    </button>
                                </div>
                            {/if}
                            <div class="col-auto">
                                <button class="btn btn-primary active" id="saveMailboxBtn" type="submit" name="saveButton">{vtranslate('LBL_SAVE',$MODULE)}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
{/strip}
