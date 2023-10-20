{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{assign var=RETURN_URL value={$MODULE_MODEL->getExtensionSettingsUrl($SOURCEMODULE)}}
{if $PARENT eq 'Settings'}
    {assign var=RETURN_URL value=$MODULE_MODEL->getExtensionSettingsUrl($SOURCEMODULE)|cat:"&parent=Settings"}
{/if}
<input type="hidden" name="settingsPage" value="{$RETURN_URL}">
<div class="px-4 pb-4 extensionContents">
    <div class="rounded bg-body">
        <h3 class="module-title p-3 border-bottom"> {vtranslate('LBL_SELECT_MODULES_TO_SYNC', $MODULE)} </h3>

        <form name="settingsForm" action="index.php" method="POST">
            <input type="hidden" name="module" value="{$MODULE}"/>
            <input type="hidden" name="action" value="SaveSyncSettings"/>
            <input type="hidden" name="sourceModule" value="{$SOURCEMODULE}"/>
            <input type="hidden" name="parent" value="{$PARENT}">
            <table class="listview-table table table-borderless my-3">
                <thead>
                <tr>
                    <th class="bg-body-secondary text-secondary">
                        <span>{vtranslate($MODULE, $MODULE)} {vtranslate('LBL_DATA', $MODULE)}</span>
                    </th>
                    <th class="bg-body-secondary text-secondary">
                        <span>{vtranslate('APPTITLE', $MODULE)} {vtranslate('LBL_DATA', $MODULE)}</span>
                    </th>
                    <th class="bg-body-secondary text-secondary">
                        <span>{vtranslate('LBL_FIELD_MAPPING', $MODULE)}</span>
                    </th>
                    <th class="bg-body-secondary text-secondary">
                        <span>{vtranslate('LBL_ENABLE_SYNC', $MODULE)}</span>
                    </th>
                    <th class="bg-body-secondary text-secondary">
                        <span>{vtranslate('LBL_SYNC_DIRECTION', $MODULE)}</span>
                    </th>
                </tr>
                </thead>
                <tbody>
                    <tr class="border-bottom">
                        <td>
                            <select name="Contacts[google_group]" class="inputElement select2 row" style="min-width: 250px;">
                                <option value="all">{vtranslate('LBL_ALL',$MODULENAME)}</option>
                                {assign var=IS_GROUP_DELETED value=1}
                                {foreach item=ENTRY from=$GOOGLE_CONTACTS_GROUPS['entry']}
                                    <option value="{$ENTRY['id']}" {if $ENTRY['id'] eq $SELECTED_CONTACTS_GROUP} {assign var=IS_GROUP_DELETED value=0} selected {/if}>{$ENTRY['title']}</option>
                                {/foreach}
                                {if $IS_GROUP_DELETED && $SELECTED_CONTACTS_GROUP != 'all'}
                                    {if $SELECTED_CONTACTS_GROUP != ''}
                                        <option value="none" selected>{vtranslate('LBL_NONE',$MODULENAME)}</option>{/if}
                                {/if}
                            </select>
                        </td>
                        <td>
                            <span>{vtranslate('Contacts', 'Contacts')}</span>
                        </td>
                        <td>
                            <a id="syncSetting" class="extensionLink" data-sync-module="Contacts">{vtranslate('LBL_CONFIGURE', $MODULE)}</a>
                        </td>
                        <td>
                            <input class="form-check-input" name="Contacts[enabled]" type="checkbox" {if $CONTACTS_ENABLED} checked {/if}>
                        </td>
                        <td>
                            <select name="Contacts[sync_direction]" class="inputElement select2 row" style="min-width: 250px;">
                                <option value="11" {if $CONTACTS_SYNC_DIRECTION eq 11} selected {/if}> {vtranslate('LBL_SYNC_BOTH_WAYS', $MODULE)} </option>
                                <option value="10" {if $CONTACTS_SYNC_DIRECTION eq 10} selected {/if}> {vtranslate('LBL_SYNC_FROM_GOOGLE_TO_VTIGER', $MODULE)} </option>
                                <option value="01" {if $CONTACTS_SYNC_DIRECTION eq 01} selected {/if}> {vtranslate('LBL_SYNC_FROM_VTIGER_TO_GOOGLE', $MODULE)} </option>
                            </select>
                        </td>
                    </tr>
                    <tr class="border-bottom">
                        <td>
                            <select name="Calendar[google_group]" class="inputElement select2 row" style="min-width: 250px;">
                                {if php7_count($GOOGLE_CALENDARS) eq 0}
                                    <option value="primary">{vtranslate('LBL_PRIMARY',$MODULENAME)}</option>
                                {/if}
                                {foreach item=CALENDAR_ITEM from=$GOOGLE_CALENDARS}
                                    <option value="{if $CALENDAR_ITEM['primary'] eq 1}primary{else}{$CALENDAR_ITEM['id']}{/if}" {if $SELECTED_GOOGLE_CALENDAR eq $CALENDAR_ITEM['id']}selected{/if} {if $SELECTED_GOOGLE_CALENDAR eq 'primary' && $CALENDAR_ITEM['primary'] eq 1} selected {/if}>{$CALENDAR_ITEM['summary']}</option>
                                {/foreach}
                            </select>
                        </td>
                        <td>
                            <span>{vtranslate('Calendar', 'Calendar')}</span>
                        </td>
                        <td>
                            <a id="syncSetting" class="extensionLink" data-sync-module="Calendar">{vtranslate('LBL_VIEW', $MODULE)}</a>
                        </td>
                        <td>
                            <input class="form-check-input" name="Calendar[enabled]" type="checkbox" {if $CALENDAR_ENABLED} checked {/if}>
                        </td>
                        <td>
                            <select name="Calendar[sync_direction]" class="inputElement select2 row" style="min-width: 250px;">
                                <option value="11" {if $CALENDAR_SYNC_DIRECTION eq 11} selected {/if}> {vtranslate('LBL_SYNC_BOTH_WAYS', $MODULE)} </option>
                                <option value="10" {if $CALENDAR_SYNC_DIRECTION eq 10} selected {/if}> {vtranslate('LBL_SYNC_FROM_GOOGLE_TO_VTIGER', $MODULE)} </option>
                                <option value="01" {if $CALENDAR_SYNC_DIRECTION eq 01} selected {/if}> {vtranslate('LBL_SYNC_FROM_VTIGER_TO_GOOGLE', $MODULE)} </option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="container-fluid">
                {if !$IS_SYNC_READY}
                    <div class="row my-3">
                        <div class="col-sm-12 col-xs-12">
                            <h3 class="module-title pull-left"> {vtranslate('LBL_GOOGLE_CONNECT_MSG', $MODULE)} </h3>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <a id="authorizeButton" class="btn btn-primary btn-lg btn-block btn-social btn-google-plus" data-url='index.php?module={$MODULE}&view=List&operation=sync&sourcemodule={$SOURCEMODULE}'>
                                <i class="fa-brands fa-google"></i>
                                <span class="ms-2">{vtranslate('LBL_SIGN_IN_WITH_GOOGLE', $MODULE)}</span>
                            </a>
                        </div>
                    </div>
                {else}
                    <div class="row my-3">
                        <div class="col-sm-12 col-xs-12">
                            <h3 class="module-title pull-left"> {vtranslate('LBL_GOOGLE_ACCOUNT_DETAILS', $MODULE)} </h3>
                        </div>
                    </div>
                    {if $USER_EMAIL}
                        <div class="row my-3">
                            <div class="col-sm-3 col-xs-3">
                                <h5 class="module-title pull-left fieldLabel"> {vtranslate('LBL_GOOGLE_ACCOUNT_SYNCED_WITH', $MODULE)} </h5>
                            </div>
                            <div class="col-sm-4 col-xs-4">
                                <input class="listSearchContributor col-sm-12 col-xs-12" type="text" value="{$USER_EMAIL}" disabled="disabled" style="height: 30px;">
                            </div>
                        </div>
                    {/if}
                    <div class="row my-3">
                        <div class="col-sm-3 col-xs-3">
                            <a id="authorizeButton" class="btn btn-primary btn-lg btn-block btn-social btn-google-plus" data-url='index.php?module={$MODULE}&view=List&operation=changeUser&sourcemodule={$SOURCEMODULE}'>
                                <i class="fa fa-google-plus"></i>
                                <span class="ms-2">{vtranslate('LBL_CHANGE_USER', $MODULE)}</span>
                            </a>
                        </div>
                    </div>
                {/if}
                <div class="row my-3">
                    <div class="col-sm-12 col-xs-12">
                        <div class="vt-default-callout vt-info-callout">
                            <h4 class="vt-callout-header"><span class="fa fa-info-circle"></span>&nbsp; Info </h4><br>
                            <div>
                                {vtranslate('LBL_REDIRECT_URL_MESSAGE', $MODULE)}<br><br>
                                {vtranslate('LBL_REDIRECT_URL', $MODULE)} : <span style="color: #15c !important">{Google_Config_Connector::getRedirectUrl()}</span>
                            </div>
                            <img src="modules/Google/images/redirect_uri.png"/>
                        </div>
                    </div>
                </div>
                <div class="row modal-footer mt-3 py-3 border-top">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col text-end">
                                {if $PARENT neq 'Settings'}
                                    <a type="reset" data-url="{$MODULE_MODEL->getBaseExtensionUrl($SOURCEMODULE)}" class="btn btn-primary cancelLink navigationLink">{vtranslate('LBL_CANCEL', $MODULENAME)}</a>
                                {/if}
                            </div>
                            <div class="col">
                                <button id="saveSettings" type="submit" class="btn btn-primary active saveButton">{vtranslate('LBL_SAVE_SETTINGS', $MODULENAME)}</button>
                            </div>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>
</div>