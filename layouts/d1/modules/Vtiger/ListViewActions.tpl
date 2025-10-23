{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=LISTVIEW_MASSACTIONS_1 value=array()}
    <div id="listview-actions" class="listViewActionsJs listview-actions-container px-3">
        {foreach item=LIST_MASSACTION from=$LISTVIEW_MASSACTIONS name=massActions}
            {if $LIST_MASSACTION->getLabel() eq 'LBL_EDIT'}
                {assign var=editAction value=$LIST_MASSACTION}
            {elseif $LIST_MASSACTION->getLabel() eq 'LBL_DELETE'}
                {assign var=deleteAction value=$LIST_MASSACTION}
            {elseif $LIST_MASSACTION->getLabel() eq 'LBL_ADD_COMMENT'}
                {assign var=commentAction value=$LIST_MASSACTION}
            {else}
                {$a = array_push($LISTVIEW_MASSACTIONS_1, $LIST_MASSACTION)}
                {* $a is added as its print the index of the array, need to find a way around it *}
            {/if}
        {/foreach}
        {if !isset($editAction)}
            {assign var=editAction value=false}
        {/if}
        {if !isset($deleteAction)}
            {assign var=deleteAction value=false}
        {/if}
        {if !isset($commentAction)}
            {assign var=commentAction value=false}
        {/if}
        {include file='ListViewTags.tpl'|vtemplate_path:$MODULE}
        <div class="row">
            <div class="col-sm pb-3">
                <div class="listViewActionsContainer" role="group" aria-label="...">
                    {if $editAction}
                        <button type="button" class="btn btn-outline-secondary me-1" id={$MODULE}_listView_massAction_{$editAction->getLabel()}
                                {if stripos($editAction->getUrl(), 'javascript:')===0} href="javascript:void(0);" onclick='{$editAction->getUrl()|substr:strlen("javascript:")}'{else} href='{$editAction->getUrl()}' {/if} title="{vtranslate('LBL_EDIT', $MODULE)}" disabled="disabled">
                            <i class="fa fa-pencil"></i>
                        </button>
                    {/if}
                    {if $deleteAction}
                        <button type="button" class="btn btn-outline-secondary me-1" id={$MODULE}_listView_massAction_{$deleteAction->getLabel()}
                                {if stripos($deleteAction->getUrl(), 'javascript:')===0} href="javascript:void(0);" onclick='{$deleteAction->getUrl()|substr:strlen("javascript:")}'{else} href='{$deleteAction->getUrl()}' {/if} title="{vtranslate('LBL_DELETE', $MODULE)}" disabled="disabled">
                            <i class="fa fa-trash"></i>
                        </button>
                    {/if}
                    {if $commentAction}
                        <button type="button" class="btn btn-outline-secondary me-1" id="{$MODULE}_listView_massAction_{$commentAction->getLabel()}"
                                onclick="Vtiger_List_Js.triggerMassAction('{$commentAction->getUrl()}')" title="{vtranslate('LBL_COMMENT', $MODULE)}" disabled="disabled">
                            <i class="fa fa-comment"></i>
                        </button>
                    {/if}
                    {if php7_count($LISTVIEW_MASSACTIONS_1) gt 0 or $LISTVIEW_LINKS['LISTVIEW']|@count gt 0}
                        <div class="listViewMassActions d-inline-block" role="group">
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="dropdown">
                                <i class="fa fa-ellipsis"></i>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                {foreach item=LISTVIEW_MASSACTION from=$LISTVIEW_MASSACTIONS_1}
                                    {assign var=LINK_LABEL value=$LISTVIEW_MASSACTION->getLabel()}
                                    {assign var=LINK_URL value=$LISTVIEW_MASSACTION->getUrl()}
                                    <li>
                                        <a id="{$MODULE}_listView_massAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LINK_LABEL)}" class="dropdown-item text-secondary hide" href='{$LINK_URL}'>
                                            {$LISTVIEW_MASSACTION->getIconHTML()}
                                            <span class="ms-2">{vtranslate($LINK_LABEL, $MODULE)}</span>
                                        </a>
                                    </li>
                                {/foreach}
                                {if php7_count($LISTVIEW_MASSACTIONS_1) gt 0 and $LISTVIEW_LINKS['LISTVIEW']|@count gt 0}
                                    <li><hr class="dropdown-divider"></li>
                                {/if}
                                {foreach item=LISTVIEW_ADVANCEDACTIONS from=$LISTVIEW_LINKS['LISTVIEW']}
                                    {assign var=LINK_LABEL value=$LISTVIEW_ADVANCEDACTIONS->getLabel()}
                                    {assign var=LINK_URL value=$LISTVIEW_ADVANCEDACTIONS->getUrl()}
                                    <li>
                                        <a id="{$MODULE}_listView_advancedAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LINK_LABEL)}" class="dropdown-item text-secondary selectFreeRecords" {if stripos($LINK_URL, 'javascript:')===0} href="javascript:void(0);" onclick='{$LINK_URL|substr:strlen("javascript:")};'{else} href='{$LINK_URL}' {/if}>
                                            {$LISTVIEW_ADVANCEDACTIONS->getIconHTML()}
                                            <span class="ms-2">{vtranslate($LINK_LABEL, $MODULE)}</span>
                                        </a>
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                </div>
            </div>
            <div class="col-sm-auto pb-3">
                {assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
                {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
            </div>
        </div>
        <div class="row">
            <div class='col-md text-center'>
                {if $LISTVIEW_ENTRIES_COUNT eq '0' and $REQUEST_INSTANCE and $REQUEST_INSTANCE->isAjax()}
                    {if $smarty.session.lvs.$MODULE.viewname}
                        {assign var=VIEWID value=$smarty.session.lvs.$MODULE.viewname}
                    {/if}
                    {if $VIEWID}
                        {foreach item=FILTER_TYPES from=$CUSTOM_VIEWS}
                            {foreach item=FILTERS from=$FILTER_TYPES}
                                {if $FILTERS->get('cvid') eq $VIEWID}
                                    {assign var=CVNAME value=$FILTERS->get('viewname')}
                                    {break}
                                {/if}
                            {/foreach}
                        {/foreach}
                        {assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getDefaultUrl()}
                        {assign var=DEFAULT_FILTER_ID value=$MODULE_MODEL->getDefaultCustomFilter()}
                        {if $DEFAULT_FILTER_ID}
                            {assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getListViewUrl()|cat:"&viewname="|cat:$DEFAULT_FILTER_ID|cat:"&app="|cat:$SELECTED_MENU_CATEGORY}
                        {/if}
                    {/if}
                {/if}
                <div class="hide messageContainer py-2">
                    <div class="text-center"><a href="#" id="selectAllMsgDiv" class="fs-5 text-primary">{vtranslate('LBL_SELECT_ALL',$MODULE)}&nbsp;{vtranslate($MODULE ,$MODULE)}&nbsp;(<span id="totalRecordsCount" value=""></span>)</a></div>
                </div>
                <div class="hide messageContainer py-2">
                    <div class="text-center"><a href="#" id="deSelectAllMsgDiv" class="fs-5 text-primary">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></div>
                </div>
            </div>
        </div>
    </div>
{/strip}
