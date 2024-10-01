{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {assign var=IS_MAILBOX_EXISTS value=$MAILBOX->exists()}
    <input type="hidden" id="isMailBoxExists" value="{if $IS_MAILBOX_EXISTS}1{else}0{/if}">
    {if !$IS_MAILBOX_EXISTS}
        <div class="mmDescription container p-4 text-center">
            <h4 class="mb-4">{vtranslate($MODULE, $MODULE)}</h4>
            <p class="mb-4 text-start">{vtranslate('LBL_MODULE_DESCRIPTION', $MODULE)}</p>
            <div>
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
                    <div class="col-lg-6  pt-lg-0 pt-sm-5" id="mailPreviewContainer">
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
