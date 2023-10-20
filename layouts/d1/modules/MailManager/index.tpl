{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{strip}
    {assign var=IS_MAILBOX_EXISTS value=$MAILBOX->exists()}
    <input type="hidden" id="isMailBoxExists" value="{if $IS_MAILBOX_EXISTS}1{else}0{/if}">
    {if !$IS_MAILBOX_EXISTS}
        <div class="mmDescription">
            <div class="text-center">
                <br><br>
                <div>{vtranslate('LBL_MODULE_DESCRIPTION', $MODULE)}</div>
                <br><br><br>
                <button class="btn btn-success mailbox_setting">
                    <strong>{vtranslate('LBL_CONFIGURE_MAILBOX', $MODULE)}</strong>
                </button>
            </div>
        </div>
    {else}
        <div id="mailmanagerContainer" class="py-3 bg-body rounded h-100">
            <input type="hidden" id="refresh_timeout" value="{$MAILBOX->refreshTimeOut()}"/>
            <div class="container-fluid h-100">
                <div class="row h-100">
                    <div class="col-lg-6 border-end d-flex flex-column" id="mails_container"></div>
                    <div class="col-lg-6" id="mailPreviewContainer">
                        <div class="mmListMainContainer">
                            <div class="text-center">
                                <strong>{vtranslate('LBL_NO_MAIL_SELECTED_DESC', $MODULE)}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}
{/strip}
