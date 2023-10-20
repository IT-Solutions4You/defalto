{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
    <div class="row">
        <div class="col-sm">
            <div class="portal-annoucement-widget-container border rounded mb-3">
                <div class="portal-annoucement-widget bg-body-secondary rounded-top px-3 py-2 mb-3">
                    <h5>{vtranslate('LBL_ANNOUNCEMENT',$QUALIFIED_MODULE)}</h5>
                </div>
                <div class="portal p-3">
				<textarea class="inputElement portal form-control" name="announcement" id="portalAnnouncement" style="resize:vertical;">
					{$ANNOUNCEMENT}
				</textarea>
                </div>
            </div>
            {foreach from=$WIDGETS['widgets'] key=module item=status}
                {if $module eq 'HelpDesk' && isset($WIDGETS_MODULE_LIST['HelpDesk'])}
                    <div class="portal-record-widget-container border rounded mb-3">
                        <div class="portal-record-widget-content bg-body-secondary rounded-top px-3 py-2 mb-3">
                            <h5>{vtranslate('LBL_RECENT',$QUALIFIED_MODULE)} {vtranslate({$module},'Vtiger')} {vtranslate('LBL_REC_WIDGET',$QUALIFIED_MODULE)}</h5>
                        </div>
                        <div class="portal-record-control-container p-3">
                            <div class="checkbox label-checkbox">
                                <label class="form-check">
                                    <input id="{$module}" type="checkbox" class="widgetsInfo form-check-input" value="{$status}" name="widgets[]" {if $status}checked{/if}/>
                                    <span>{vtranslate('Enable', $QUALIFIED_MODULE)}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                {/if}
            {/foreach}
        </div>
        <div class="col-sm-5">
            {if $WIDGETS_MODULE_LIST['HelpDesk'] eq 1 || $WIDGETS_MODULE_LIST['Documents'] eq 1}
                <div class="portal-shortcuts-container border rounded mb-3">
                    <div class="portal-shortcuts-header bg-body-secondary rounded-top px-3 py-2 mb-3">
                        <h5>{vtranslate('LBL_SHORTCUTS',$QUALIFIED_MODULE)}</h5>
                    </div>
                    <div class="portal-shortcuts-content p-3">
                        <input type="hidden" name="defaultShortcuts" value='{$DEFAULT_SHORTCUTS}'/>
                        <div id="portal-shortcutsContainer">
                            <ul class="nav nav-tabs nav-stacked" id="shortcutItems">
                                {assign var="SHORT" value=json_decode($DEFAULT_SHORTCUTS,true)}
                                {foreach from=$SHORT key=key item=value}
                                    {if isset($WIDGETS_MODULE_LIST[$key])}
                                        {foreach from=$value key=key1 item=value1}
                                            {if $value1 == 1}
                                                <li class="portal-shortcut-list" data-field="{$key1}">
                                                    <div class="btn btn-large btn-outline-secondary me-2">{vtranslate({$key1},$QUALIFIED_MODULE)}</div>
                                                </li>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                {/foreach}
                            </ul>
                        </div>
                    </div>
                </div>
            {/if}
            {foreach from=$WIDGETS['widgets'] key=module item=status}
                {if $module neq 'HelpDesk' && isset($WIDGETS_MODULE_LIST[$module])}
                    <div class="portal-helpdesk-widget-container border rounded mb-3">
                        <div class="portal-helpdesk-widget-header bg-body-secondary rounded-top px-3 py-2 mb-3">
                            <h5>{vtranslate('LBL_RECENT',$QUALIFIED_MODULE)} {vtranslate({$module},'Vtiger')} {vtranslate('LBL_REC_WIDGET',$QUALIFIED_MODULE)}</h5>
                        </div>
                        <div class="portal-helpdesk-widget-controls p-3">
                            <div class="checkbox label-checkbox">
                                <label class="form-check">
                                    <input class="widgetsInfo form-check-input" id="{$module}" type="checkbox" value="{$status}" name="widgets[]" {if $status}checked{/if}/>
                                    <span>{vtranslate('Enable', $QUALIFIED_MODULE)}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                {/if}
            {/foreach}
        </div>
    </div>
{/strip}
