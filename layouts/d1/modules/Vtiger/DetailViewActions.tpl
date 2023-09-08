{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    <div class="col-lg-12 detailViewButtonContainer mt-3">
        <div class="btn-toolbar">
            <div class="me-auto">
                <button class="showMap btn btn-primary me-2" onclick='Vtiger_Index_Js.showMap(this);' data-module='{$RECORD->getModule()->getName()}' data-record='{$RECORD->getId()}'>
                    <i class="fa fa-map-marker me-2"></i>
                    <span>{vtranslate('LBL_SHOW_MAP', $MODULE_NAME)}</span>
                </button>
                {assign var=STARRED value=$RECORD->get('starred')}
                {if $MODULE_MODEL->isStarredEnabled()}
                    <button class="btn btn-primary me-2 markStar {if $STARRED}markStarActive{/if}" id="starToggle">
                        <div class='starredStatus' title="{vtranslate('LBL_STARRED', $MODULE)}">
                            <div class='unfollowMessage'>
                                <i class="fa-regular fa-star me-2"></i>
                                <span>{vtranslate('LBL_UNFOLLOW',$MODULE)}</span>
                            </div>
                            <div class='followMessage'>
                                <i class="fa-solid fa-star me-2"></i>
                                <span>{vtranslate('LBL_FOLLOWING',$MODULE)}</span>
                            </div>
                        </div>
                        <div class='unstarredStatus' title="{vtranslate('LBL_NOT_STARRED', $MODULE)}">
                            <span>{vtranslate('LBL_FOLLOW',$MODULE)}</span>
                        </div>
                    </button>
                {/if}
                {foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWBASIC']}
                    <button class="btn btn-primary me-2" id="{$MODULE_NAME}_detailView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_BASIC_LINK->getLabel())}"
                            {if $DETAIL_VIEW_BASIC_LINK->isPageLoadLink()}
                                onclick="window.location.href = '{$DETAIL_VIEW_BASIC_LINK->getUrl()}&app={$SELECTED_MENU_CATEGORY}'"
                            {else}
                                onclick="{$DETAIL_VIEW_BASIC_LINK->getUrl()}"
                            {/if}
                            {if $MODULE_NAME eq 'Documents' && $DETAIL_VIEW_BASIC_LINK->getLabel() eq 'LBL_VIEW_FILE'}
                        data-filelocationtype="{$DETAIL_VIEW_BASIC_LINK->get('filelocationtype')}" data-filename="{$DETAIL_VIEW_BASIC_LINK->get('filename')}"
                            {/if}>
                        {vtranslate($DETAIL_VIEW_BASIC_LINK->getLabel(), $MODULE_NAME)}
                    </button>
                {/foreach}
            </div>
            <div class="btn-group ms-auto">
                {if !empty($DETAILVIEW_LINKS['DETAILVIEW']) && ($DETAILVIEW_LINKS['DETAILVIEW']|@count gt 0)}
                    <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                        {vtranslate('LBL_MORE', $MODULE_NAME)}&nbsp;&nbsp;<i class="caret"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        {foreach item=DETAIL_VIEW_LINK from=$DETAILVIEW_LINKS['DETAILVIEW']}
                            {if $DETAIL_VIEW_LINK->getLabel() eq ""}
                                <li class="dropdown-item divider"></li>
                            {else}
                                <li class="dropdown-item" id="{$MODULE_NAME}_detailView_moreAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_LINK->getLabel())}">
                                    {if $DETAIL_VIEW_LINK->getUrl()|strstr:"javascript"}
                                        <a href='{$DETAIL_VIEW_LINK->getUrl()}'>{vtranslate($DETAIL_VIEW_LINK->getLabel(), $MODULE_NAME)}</a>
                                    {else}
                                        <a href='{$DETAIL_VIEW_LINK->getUrl()}&app={$SELECTED_MENU_CATEGORY}'>{vtranslate($DETAIL_VIEW_LINK->getLabel(), $MODULE_NAME)}</a>
                                    {/if}
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                {/if}
            </div>
        </div>
        <input type="hidden" name="record_id" value="{$RECORD->getId()}">
    </div>
{/strip}
