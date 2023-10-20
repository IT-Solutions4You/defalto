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
    {assign var="MODULE_NAME" value=$MODULE_MODEL->get('name')}
    <input id="recordId" type="hidden" value="{$RECORD->getId()}"/>
    <div class="detailViewContainer">
    <div class="detailViewTitle px-4 pb-4" id="prefPageHeader">
    <div class="rounded bg-body mb-3">
        <div class="container-fluid p-3">
            <div class="row">
                <div class="col-lg">
                    <div class="row">
                        {assign var=IMAGE_DETAILS value=$RECORD->getImageDetails()}
                        {foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
                            {if !empty($IMAGE_INFO.url)}
                                <span class="logo col-lg-auto">
                                    <img class="rounded" style="width: 8rem; height: 8rem" src="{$IMAGE_INFO.url}" alt="{$IMAGE_INFO.orgname}" title="{$IMAGE_INFO.orgname}" data-image-id="{$IMAGE_INFO.id}">
                                </span>
                            {/if}
                        {/foreach}
                        {if $IMAGE_DETAILS[0]['id'] eq null}
                            <span class="logo col-lg-auto">
                                <i class="fa fa-user" style="font-size: 8rem"></i>
                            </span>
                        {/if}
                        <span class="col-lg-9">
                            <span id="myPrefHeading">
                                <h3>{vtranslate('LBL_MY_PREFERENCES', $MODULE_NAME)} </h3>
                            </span>
                            <span>
                                {vtranslate('LBL_USERDETAIL_INFO', $MODULE_NAME)}&nbsp;&nbsp;"<b>{$RECORD->getName()}</b>"
                            </span>
                        </span>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="detailViewButtoncontainer">
                        <div class="btn-toolbar">
                            {foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWPREFERENCE']}
                                <button class="btn btn-outline-secondary"
                                    {if $DETAIL_VIEW_BASIC_LINK->isPageLoadLink()}
                                        onclick="window.location.href='{$DETAIL_VIEW_BASIC_LINK->getUrl()}'"
                                    {else}
                                        onclick={$DETAIL_VIEW_BASIC_LINK->getUrl()}
                                    {/if}>
                                    {vtranslate($DETAIL_VIEW_BASIC_LINK->getLabel(), $MODULE_NAME)}
                                </button>
                            {/foreach}
                            {if $DETAILVIEW_LINKS['DETAILVIEW']|@count gt 0}
                                <button class="btn btn-outline-secondary ms-2 dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0);">
                                    {vtranslate('LBL_MORE', $MODULE)}
                                </button>
                                <ul class="dropdown-menu">
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
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="detailViewInfo userPreferences">
        <div class="details col-xs-12">
{/strip}