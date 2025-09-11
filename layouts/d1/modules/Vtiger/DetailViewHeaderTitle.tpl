{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="record-header">
		<div class="recordBasicInfo">
			{if !$IS_OVERLAY}
				<div class="float-end">
					{include file='DetailViewHeaderPagination.tpl'|vtemplate_path:$QUALIFIED_MODULE}
				</div>
			{/if}
			<div class="float-start">
				{include file='DetailViewHeaderImage.tpl'|vtemplate_path:$QUALIFIED_MODULE}
			</div>
			<div class="recordHeaderTitle">
                <div class="row align-items-top">
                    <div class="col">
                        <span class="fs-2 fw-bold recordLabel pushDown" title="{$RECORD->getName()}">{$RECORD->getName()}</span>
                        {if !$IS_OVERLAY}
                            {include file='DetailViewTagList.tpl'|vtemplate_path:$QUALIFIED_MODULE}
                        {/if}
                    </div>
                    <div class="col-auto">
                        {if $DETAILVIEW_LINKS['DETAILVIEWRECORD']}
                            {foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWRECORD']}
                                <button type="button" class="btn btn-outline-secondary ms-2" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{vtranslate($DETAIL_VIEW_BASIC_LINK->getLabel(), $MODULE_NAME)}" id="{$MODULE_NAME}_detailView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_BASIC_LINK->getLabel())}"
                                        onclick="{if $DETAIL_VIEW_BASIC_LINK->isPageLoadLink()}window.location.href='{$DETAIL_VIEW_BASIC_LINK->getUrl()}&app={$SELECTED_MENU_CATEGORY}'{else}{$DETAIL_VIEW_BASIC_LINK->getUrl()}{/if}">
                                    {$DETAIL_VIEW_BASIC_LINK->get('linkicon')}
                                </button>
                            {/foreach}
                        {/if}
                    </div>
                </div>
			</div>
			{include file='DetailViewHeaderFieldsView.tpl'|vtemplate_path:$QUALIFIED_MODULE}
		</div>
	</div>
{/strip}