{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
<div class="listViewContentDiv px-4 pb-4">
    <div class="rounded bg-body">
        <h4 class="p-3 border-bottom">{vtranslate($MODULE, $QUALIFIED_MODULE)}</h4>
        {if !$RECORD_EXISTS}
            <div class="mailConveterDesc text-center">
                <div>{vtranslate('LBL_MAILCONVERTER_DESCRIPTION', $QUALIFIED_MODULE)}</div>
                <img src="{vimage_path('MailConverter.png')}" alt="Mail Converter"><br><br>
                <a onclick="window.location.href='{$MODULE_MODEL->getCreateRecordUrl()}'" style="color: #15c !important;"><u class="cursorPointer" style="font-size:12pt;">{vtranslate('LBL_CREATE_MAILBOX_NOW', $QUALIFIED_MODULE)}</u></a>
            </div>
        {else}
            <input type="hidden" id="scannerId" value="{$SCANNER_ID}"/>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-4 mailBoxDropdownWrapper">
                        <select class="mailBoxDropdown select2">
                            {foreach item=SCANNER from=$MAILBOXES}
                                <option value="{$SCANNER['scannerid']}" {if $SCANNER_ID eq $SCANNER['scannerid']}selected{/if}>{$SCANNER['scannername']}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-lg pb-3" id="mailConverterStats">
                        {if $CRON_RECORD_MODEL->isEnabled()}
                            {if $CRON_RECORD_MODEL->hadTimedout()}
                                {vtranslate('LBL_LAST_SCAN_TIMED_OUT', $QUALIFIED_MODULE_NAME)}.
                            {elseif $CRON_RECORD_MODEL->getLastEndDateTime() neq ''}
                                {vtranslate('LBL_LAST_SCAN_AT', $QUALIFIED_MODULE_NAME)}
                                {$CRON_RECORD_MODEL->getLastEndDateTime()}
                                <br/>
                                {vtranslate('LBL_FOLDERS_SCANNED', $QUALIFIED_MODULE_NAME)}&nbsp;:&nbsp;
                                {foreach from=$FOLDERS_SCANNED item=FOLDER}<strong>{$FOLDER}&nbsp;&nbsp;</strong>{/foreach}
                            {/if}
                        {/if}
                    </div>
                    <div class="col-lg-auto">
                        <div class="btn-toolbar">
                            <button class="btn btn-outline-secondary addButton" id="addRuleButton" title="{vtranslate('LBL_DRAG_AND_DROP_BLOCK_TO_PRIORITISE_THE_RULE', $QUALIFIED_MODULE)}"
                                {if stripos($SCANNER_MODEL->getCreateRuleRecordUrl(), 'javascript:')===0}
                                    onclick='{$SCANNER_MODEL->getCreateRuleRecordUrl()|substr:strlen("javascript:")}'
                                {else}
                                    onclick='window.location.href="{$SCANNER_MODEL->getCreateRuleRecordUrl()}"'
                                {/if}>
                                <i class="fa fa-plus"></i>
                                <span class="ms-2">{vtranslate('LBL_ADD_RULE', $QUALIFIED_MODULE)}</span>
                            </button>
                            <button href="javascript:void(0);" data-bs-toggle="dropdown" class="btn btn-outline-secondary dropdown-toggle ms-2">
                                {vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE_NAME)}
                            </button>
                            <ul class="dropdown-menu pull-right">
                                {foreach item=LINK from=$RECORD->getRecordLinks()}
                                    <li>
                                        <a class="dropdown-item" {if strpos($LINK->getUrl(), 'javascript:')===0} href='javascript:void(0);' onclick='{$LINK->getUrl()|substr:strlen("javascript:")};' {else}href={$LINK->getUrl()}{/if}>
                                            {vtranslate($LINK->getLabel(), $QUALIFIED_MODULE)}
                                        </a>
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div id="mailConverterBody">
                <div class="container-fluid" id="rulesList">
                    {if php7_count($RULE_MODELS_LIST)}
                        {assign var=RULE_COUNT value=1}
                        {assign var=FIELDS value=$MODULE_MODEL->getSetupRuleFields()}
                        {foreach from=$RULE_MODELS_LIST item=RULE_MODEL}
                            <div class="row-fluid row pb-3 rule" data-id="{$RULE_MODEL->get('ruleid')}" data-blockid="block_{$RULE_MODEL->get('ruleid')}">
                                {include file="Rule.tpl"|@vtemplate_path:$QUALIFIED_MODULE RULE_COUNT=$RULE_COUNT}
                            </div>
                            {assign var=RULE_COUNT value=$RULE_COUNT+1}
                        {/foreach}
                    {else}
                        <div class="details">
                            {vtranslate('LBL_NO_RULES', $QUALIFIED_MODULE)}
                        </div>
                    {/if}
                </div>
            </div>
        {/if}
    </div>
</div>
</div>
</div>
{/strip}
