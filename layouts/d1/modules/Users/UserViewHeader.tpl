{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Users/views/Detail.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
<div class="detailViewContainer">
    <div class="mx-4 mb-4">
        <div class="detailViewTitle p-3 bg-body rounded mb-3" id="userPageHeader">
            <div class="row">
                <div class="col-lg">
                    <div class="row">
                        <div class="col-auto">
                            <div class="recordImage d-flex align-items-center justify-content-center rounded" style="height: 3rem;width: 3rem;">
                                {assign var=NOIMAGE value=0}
                                {foreach key=ITER item=IMAGE_INFO from=$RECORD->getImageDetails()}
                                    {if !empty($IMAGE_INFO.url)}
                                        <img class="rounded" height="100%" width="100%" src="{$IMAGE_INFO.url}" alt="{$IMAGE_INFO.orgname}" title="{$IMAGE_INFO.orgname}" data-image-id="{$IMAGE_INFO.id}">
                                    {else}
                                        {assign var=NOIMAGE value=1}
                                    {/if}
                                {/foreach}
                                {if $NOIMAGE eq 1}
                                    <div class="name">
                                        <span style="font-size:2rem;">
                                            <strong>{$RECORD->getName()|substr:0:2}</strong>
                                        </span>
                                    </div>
                                {/if}
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-truncate fs-3">{$RECORD->getName()}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-auto detailViewButtoncontainer">
                    <div class="btn-toolbar">
                        {foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWBASIC']}
                            <button class="btn btn-outline-secondary me-2 {if $DETAIL_VIEW_BASIC_LINK->getLabel() eq 'LBL_EDIT'}{/if}" id="{$MODULE}_detailView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_BASIC_LINK->getLabel())}"
                                    {if $DETAIL_VIEW_BASIC_LINK->isPageLoadLink()}
                                onclick="window.location.href='{$DETAIL_VIEW_BASIC_LINK->getUrl()}'"
                                    {else}
                                onclick="{$DETAIL_VIEW_BASIC_LINK->getUrl()}"
                                    {/if}>
                                {vtranslate($DETAIL_VIEW_BASIC_LINK->getLabel(), $MODULE)}
                            </button>
                        {/foreach}
                        {if $DETAILVIEW_LINKS['DETAILVIEW']|@count gt 0}
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary me-2 dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0);">
                                    {vtranslate('LBL_MORE', $MODULE)}&nbsp;<i class="caret"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    {foreach item=DETAIL_VIEW_LINK from=$DETAILVIEW_LINKS['DETAILVIEW']}
                                        {if $DETAIL_VIEW_LINK->getLabel() eq "Delete"}
                                            {if $CURRENT_USER_MODEL->isAdminUser() && $CURRENT_USER_MODEL->getId() neq $RECORD->getId()}
                                                <li id="{$MODULE}_detailView_moreAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_LINK->getLabel())}">
                                                    <a class="dropdown-item" href={$DETAIL_VIEW_LINK->getUrl()}>{vtranslate($DETAIL_VIEW_LINK->getLabel(), $MODULE)}</a>
                                                </li>
                                            {/if}
                                        {else}
                                            <li id="{$MODULE}_detailView_moreAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_LINK->getLabel())}">
                                                <a class="dropdown-item" href={$DETAIL_VIEW_LINK->getUrl()}>{vtranslate($DETAIL_VIEW_LINK->getLabel(), $MODULE)}</a>
                                            </li>
                                        {/if}
                                    {/foreach}
                                </ul>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
        <div class="detailview-content userPreferences">
            {assign var="MODULE_NAME" value=$MODULE_MODEL->get('name')}
            <input id="recordId" type="hidden" value="{$RECORD->getId()}" />
            <div class="details">
{/strip}