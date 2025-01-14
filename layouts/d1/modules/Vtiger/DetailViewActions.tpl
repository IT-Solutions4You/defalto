{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="col-lg-12 detailViewButtonContainer mt-3">
        <div class="btn-toolbar">
            <div class="me-auto buttonsTypeDetailViewBasic">
                {assign var=STARRED value=$RECORD->get('starred')}
                {if $MODULE_MODEL->isStarredEnabled()}
                    <button class="btn btn-primary me-2 markStar {if $STARRED}markStarActive{/if}" id="starToggle">
                        <div class="starredStatus" title="{vtranslate('LBL_STARRED', $MODULE)}">
                            <div class="unfollowMessage">
                                <i class="bi bi-bookmark me-2"></i>
                                <span>{vtranslate('LBL_UNFOLLOW',$MODULE)}</span>
                            </div>
                            <div class="followMessage">
                                <i class="bi bi-bookmark-fill me-2"></i>
                                <span>{vtranslate('LBL_FOLLOWING',$MODULE)}</span>
                            </div>
                        </div>
                        <div class="unstarredStatus" title="{vtranslate('LBL_NOT_STARRED', $MODULE)}">
                            <span class="followMessage">
                                <i class="bi bi-bookmark me-2"></i>
                            </span>
                            <span class="unfollowMessage">
                                <i class="bi bi-bookmark-fill me-2"></i>
                            </span>
                            <span>{vtranslate('LBL_FOLLOW',$MODULE)}</span>
                        </div>
                    </button>
                {/if}
                {foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWBASIC']}
                    {if 'PDFMaker' eq $DETAIL_VIEW_BASIC_LINK->getLabel()}
                        {include file='GetPDFButtons.tpl'|vtemplate_path:'PDFMaker'}
                    {else}
                        <button class="btn btn-primary me-2" id="{$MODULE_NAME}_detailView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_BASIC_LINK->getLabel())}"
                            {if $DETAIL_VIEW_BASIC_LINK->isPageLoadLink()}
                                onclick="window.location.href = '{$DETAIL_VIEW_BASIC_LINK->getUrl()}&app={$SELECTED_MENU_CATEGORY}'"
                            {else}
                                onclick="{$DETAIL_VIEW_BASIC_LINK->getUrl()}"
                            {/if}
                            {if $MODULE_NAME eq 'Documents' && $DETAIL_VIEW_BASIC_LINK->getLabel() eq 'LBL_VIEW_FILE'}
                                data-filelocationtype="{$DETAIL_VIEW_BASIC_LINK->get('filelocationtype')}" data-filename="{$DETAIL_VIEW_BASIC_LINK->get('filename')}"
                            {/if}>
                            {$DETAIL_VIEW_BASIC_LINK->get('linkicon')}
                            <span class="ms-2">{vtranslate($DETAIL_VIEW_BASIC_LINK->getLabel(), $MODULE_NAME)}</span>
                        </button>
                    {/if}
                {/foreach}
            </div>
            <div class="btn-group ms-auto buttonsTypeDetailView">
                {if !empty($DETAILVIEW_LINKS['DETAILVIEW']) && ($DETAILVIEW_LINKS['DETAILVIEW']|@count gt 0)}
                    <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        {vtranslate('LBL_MORE', $MODULE_NAME)}&nbsp;&nbsp;<i class="caret"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        {foreach item=DETAIL_VIEW_LINK from=$DETAILVIEW_LINKS['DETAILVIEW']}
                            {if $DETAIL_VIEW_LINK->getLabel() eq ""}
                                <li class="dropdown-item divider"></li>
                            {else}
                                {assign var=DETAIL_VIEW_LINK_ICON value=$DETAIL_VIEW_LINK->get('linkicon')}
                                <li class="dropdown-item" id="{$MODULE_NAME}_detailView_moreAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_LINK->getLabel())}">
                                    {if $DETAIL_VIEW_LINK->getUrl()|strstr:"javascript"}
                                        <a href='{$DETAIL_VIEW_LINK->getUrl()}'>
                                            {if $DETAIL_VIEW_LINK_ICON}<span class="me-2 text-secondary">{$DETAIL_VIEW_LINK_ICON}</span>{/if}
                                            <span>{vtranslate($DETAIL_VIEW_LINK->getLabel(), $MODULE_NAME)}</span>
                                        </a>
                                    {else}
                                        <a href='{$DETAIL_VIEW_LINK->getUrl()}&app={$SELECTED_MENU_CATEGORY}'>
                                            {if $DETAIL_VIEW_LINK_ICON}<span class="me-2 text-secondary">{$DETAIL_VIEW_LINK_ICON}</span>{/if}
                                            <span>{vtranslate($DETAIL_VIEW_LINK->getLabel(), $MODULE_NAME)}</span>
                                        </a>
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
