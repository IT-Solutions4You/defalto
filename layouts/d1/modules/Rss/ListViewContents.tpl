{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <div class="listViewContentDiv" id="listViewContents">
                    <input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}"/>
                    <div class="listViewEntriesDiv">
                        <div class="listViewLoadingImageBlock hide modal" id="loadingListViewModal">
                            <img class="listViewLoadingImage" src="{vimage_path('loading.gif')}" alt="no-image" title="{vtranslate('LBL_LOADING', $MODULE)}"/>
                            <p class="listViewLoadingMsg">{vtranslate('LBL_LOADING_LISTVIEW_CONTENTS', $MODULE)}........</p>
                        </div>
                        <div class="feedContainer">
                            {if $RECORD}
                                <input id="recordId" type="hidden" value="{$RECORD->getId()}">
                                <div class="container-fluid">
                                    <div class="row detailViewButtoncontainer">
                                        <div class="col" id="rssFeedHeading">
                                            <h3>
                                                <span>{vtranslate('LBL_FEEDS_LIST_FROM',$MODULE)}:</span>
                                                <span class="ms-2">{$RECORD->getName()}</span>
                                            </h3>
                                        </div>
                                        <div class="col-auto text-end">
                                            <button id="deleteButton" class="btn btn-outline-secondary me-2">{vtranslate('LBL_DELETE', $MODULE)}</button>
                                            <button id="makeDefaultButton" class="btn btn-outline-secondary">{vtranslate('LBL_SET_AS_DEFAULT', $MODULE)}</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-container feedListContainer container-fluid" style="overflow: auto;">
                                    {include file='RssFeedContents.tpl'|@vtemplate_path:$MODULE}
                                </div>
                            {else}
                                <table class="table-container emptyRecordsDiv">
                                    <tbody>
                                    <tr>
                                        <td>
                                            {assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
                                            {vtranslate('LBL_NO')} {vtranslate($MODULE, $MODULE)} {vtranslate('LBL_FOUND')}. {vtranslate('LBL_CREATE')}<a class="rssAddButton" href="#" data-href="{$QUICK_LINKS['SIDEBARLINK'][0]->getUrl()}">&nbsp;{vtranslate($SINGLE_MODULE, $MODULE)}</a>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            {/if}
                        </div>
                    </div>
                    <br>
                    <div class="feedFrame">
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}
