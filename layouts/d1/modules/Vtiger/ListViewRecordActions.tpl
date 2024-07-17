{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <!--LIST VIEW RECORD ACTIONS-->
    <div class="table-actions d-flex align-items-center">
        {if !$SEARCH_MODE_RESULTS}
            <span class="input form-check">
                {assign var=LIST_VIEW_ENTRIE_IS_READONLY value=!Core_Readonly_Model::isPermitted($LISTVIEW_ENTRY->getModuleName(), $LISTVIEW_ENTRY->getId())}
                <input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox form-check-input"/>
                <input type="hidden" value="{$LIST_VIEW_ENTRIE_IS_READONLY}" class="listViewEntriesIsReadonly"/>
            </span>
        {/if}
        {if $LISTVIEW_ENTRY->get('starred') eq vtranslate('LBL_YES', $MODULE)}
            {assign var=STARRED value=true}
        {else}
            {assign var=STARRED value=false}
        {/if}
        {if isset($QUICK_PREVIEW_ENABLED ) && $QUICK_PREVIEW_ENABLED eq 'true'}
            <span class="btn btn-sm text-secondary">
                <a class="quickView fa fa-eye icon action" data-app="{$SELECTED_MENU_CATEGORY}" title="{vtranslate('LBL_QUICK_VIEW', $MODULE)}"></a>
            </span>
        {/if}
        <div class="more dropdown action">
            <div class="btn btn-sm text-secondary" data-bs-toggle="dropdown">
                <i class="fa fa-ellipsis icon"></i>
            </div>
            <ul class="dropdown-menu">
                {if $MODULE_MODEL->isStarredEnabled()}
                    <li>
                        <a class="dropdown-item markStar">
                            <span class="followButton {if $STARRED eq 1}hide{/if}" title="{vtranslate('LBL_NOT_STARRED', $MODULE)}">
                                <i class="bi bi-bookmark text-secondary"></i>
                                <span class="ms-2">{vtranslate('LBL_FOLLOW', $MODULE)}</span>
                            </span>
                            <span class="unfollowButton {if $STARRED eq 0}hide{/if}" title="{vtranslate('LBL_STARRED', $MODULE)}">
                                <i class="bi bi-bookmark-fill text-secondary"></i>
                                <span class="ms-2">{vtranslate('LBL_UNFOLLOW', $MODULE)}</span>
                            </span>
                        </a>
                    </li>
                {/if}
                <li>
                    <a class="dropdown-item" data-id="{$LISTVIEW_ENTRY->getId()}" href="{$LISTVIEW_ENTRY->getFullDetailViewUrl()}&app={$SELECTED_MENU_CATEGORY}">
                        <i class="fa-solid fa-circle-info text-secondary"></i>
                        <span class="ms-2">{vtranslate('LBL_DETAILS', $MODULE)}</span>
                    </a>
                </li>
                {if $RECORD_ACTIONS}
                    {if $RECORD_ACTIONS['edit'] && !$LIST_VIEW_ENTRIE_IS_READONLY}
                        <li>
                            <a class="dropdown-item" data-id="{$LISTVIEW_ENTRY->getId()}" href="javascript:app.controller().editRecord('{$LISTVIEW_ENTRY->getEditViewUrl()}&app={$SELECTED_MENU_CATEGORY}')">
                                <i class="fa-solid fa-pencil text-secondary"></i>
                                <span class="ms-2">{vtranslate('LBL_EDIT', $MODULE)}</span>
                            </a>
                        </li>
                    {/if}
                    {if $RECORD_ACTIONS['delete'] && !$LIST_VIEW_ENTRIE_IS_READONLY}
                        <li>
                            <a class="dropdown-item" href="javascript:app.controller().deleteRecord({$LISTVIEW_ENTRY->getId()})" data-id="{$LISTVIEW_ENTRY->getId()}">
                                <i class="fa-solid fa-trash text-secondary"></i>
                                <span class="ms-2">{vtranslate('LBL_DELETE', $MODULE)}</span>
                            </a>
                        </li>
                    {/if}
                {/if}
            </ul>
        </div>

        <div class="btn-group inline-save hide">
            <button class="button btn btn-success btn-small save" type="button" name="save">
                <i class="fa fa-check"></i>
            </button>
            <button class="button btn btn-danger btn-small cancel" type="button" name="Cancel">
                <i class="fa fa-close"></i>
            </button>
        </div>
    </div>
{/strip}
