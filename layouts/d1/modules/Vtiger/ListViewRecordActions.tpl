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
    <span class="input form-check" >
        <input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox form-check-input"/>
    </span>
    {/if}
    {if $LISTVIEW_ENTRY->get('starred') eq vtranslate('LBL_YES', $MODULE)}
        {assign var=STARRED value=true}
    {else}
        {assign var=STARRED value=false}
    {/if}
    {if $QUICK_PREVIEW_ENABLED eq 'true'}
		<span class="btn btn-sm text-secondary">
			<a class="quickView fa fa-eye icon action" data-app="{$SELECTED_MENU_CATEGORY}" title="{vtranslate('LBL_QUICK_VIEW', $MODULE)}"></a>
		</span>
    {/if}
	{if $MODULE_MODEL->isStarredEnabled()}
		<span class="btn btn-sm text-secondary">
			<a class="markStar fa icon action {if $STARRED} fa-star active {else} fa-star-o{/if}" title="{if $STARRED} {vtranslate('LBL_STARRED', $MODULE)} {else} {vtranslate('LBL_NOT_STARRED', $MODULE)}{/if}"></a>
		</span>
	{/if}
    <div class="more dropdown action">
        <div class="btn btn-sm text-secondary" data-bs-toggle="dropdown">
            <i class="fa fa-ellipsis icon"></i>
        </div>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item" data-id="{$LISTVIEW_ENTRY->getId()}" href="{$LISTVIEW_ENTRY->getFullDetailViewUrl()}&app={$SELECTED_MENU_CATEGORY}">{vtranslate('LBL_DETAILS', $MODULE)}</a>
            </li>
			{if $RECORD_ACTIONS}
				{if $RECORD_ACTIONS['edit']}
					<li>
                        <a class="dropdown-item" data-id="{$LISTVIEW_ENTRY->getId()}" href="javascript:app.controller().editRecord('{$LISTVIEW_ENTRY->getEditViewUrl()}&app={$SELECTED_MENU_CATEGORY}')">{vtranslate('LBL_EDIT', $MODULE)}</a>
                    </li>
				{/if}
				{if $RECORD_ACTIONS['delete']}
					<li>
                        <a class="dropdown-item" href="javascript:app.controller().deleteRecord({$LISTVIEW_ENTRY->getId()})" data-id="{$LISTVIEW_ENTRY->getId()}" href="#">{vtranslate('LBL_DELETE', $MODULE)}</a>
                    </li>
				{/if}
			{/if}
        </ul>
    </div>

    <div class="btn-group inline-save hide">
        <button class="button btn btn-success btn-small save" type="button" name="save"><i class="fa fa-check"></i></button>
        <button class="button btn btn-danger btn-small cancel" type="button" name="Cancel"><i class="fa fa-close"></i></button>
    </div>
</div>
{/strip}
