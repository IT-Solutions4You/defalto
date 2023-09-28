{*<!--
/*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************/
-->*}

{strip}
    <div id="listview-actions" class="listview-actions-container container-fluid p-3">
        <div class="row">
            <div class="col-lg-auto" role="group">
                <span class="recordDependentListActions">
                    {assign var=LISTVIEW_ACTIONS value=array_reverse($LISTVIEW_MASSACTIONS)}
                    {foreach item="LISTVIEW_MASSACTION" from=$LISTVIEW_ACTIONS}
                        <button type="button" class="btn btn-outline-secondary me-2" id="{$MODULE}_listView_massAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_MASSACTION->getLabel())}"
                                {if stripos($LISTVIEW_MASSACTION->getUrl(), 'javascript:')===0}onclick='{$LISTVIEW_MASSACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick="Vtiger_List_Js.triggerMassAction('{$LISTVIEW_MASSACTION->getUrl()}')"{/if} disabled="disabled"
                                title="{if $LISTVIEW_MASSACTION->getLabel() eq 'LBL_RESTORE'}{vtranslate('LBL_RESTORE', $MODULE)}{else}{vtranslate('LBL_DELETE', $MODULE)}{/if}">
                            <i class="{if $LISTVIEW_MASSACTION->getLabel() eq 'LBL_RESTORE'} fa fa-refresh {else} fa fa-trash {/if}"></i>
                        </button>
                    {/foreach}
                </span>
                {* Fix for empty Recycle bin Button *}
                {foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
                    <span class="btn-group">
							<button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}" class="btn btn-danger clearRecycleBin" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'{else}
                                onclick='window.location.href="{$LISTVIEW_BASICACTION->getUrl()}"'{/if} {if !$IS_RECORDS_DELETED} disabled="disabled" {/if}>
								{vtranslate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}
							</button>
						</span>
                {/foreach}
            </div>
            <div class="col-lg">
                <div class="hide messageContainer">
                    <div class="text-center">
                        <a id="selectAllMsgDiv" href="#">
                            <span>{vtranslate('LBL_SELECT_ALL',$MODULE)}</span>
                            <span class="me-2">{vtranslate($MODULE ,$MODULE)}</span>
                            <span>(<span id="totalRecordsCount" value=""></span>)</span>
                        </a>
                    </div>
                </div>
                <div class="hide messageContainer">
                    <div class="text-center">
                        <a href="#" id="deSelectAllMsgDiv">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-auto">
                {assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
                {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
            </div>
        </div>
    </div>
{/strip}