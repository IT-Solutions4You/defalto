{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="relatedHeader p-3">
		<div class="row">
			<div class="col-auto">
				{foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
					<div class="btn-group">
						{assign var=DROPDOWNS value=$RELATED_LINK->get('linkdropdowns')}
						{if !empty($DROPDOWNS) && (php7_count($DROPDOWNS) gt 0)}
							<a class="btn dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown" data-hover="dropdown" data-delay="200" data-close-others="false" style="width:20px;height:18px;">
								<img title="{$RELATED_LINK->getLabel()}" alt="{$RELATED_LINK->getLabel()}" src="{vimage_path("{$RELATED_LINK->getIcon()}")}">
							</a>
							<ul class="dropdown-menu">
								{foreach item=DROPDOWN from=$DROPDOWNS}
									<li><a id="{$RELATED_MODULE_NAME}_relatedlistView_add_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DROPDOWN['label'])}" class="{$RELATED_LINK->get('linkclass')}" href='javascript:void(0)' data-documentType="{$DROPDOWN['type']}" data-url="{$DROPDOWN['url']}" data-name="{$RELATED_MODULE_NAME}" data-firsttime="{$DROPDOWN['firsttime']}"><i class="icon-plus"></i>&nbsp;{vtranslate($DROPDOWN['label'], $RELATED_MODULE_NAME)}</a></li>
								{/foreach}
							</ul>
						{else}
							{assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
							{assign var=LINK_LABEL value={$RELATED_LINK->get('linklabel')}}
							{if $IS_SELECT_BUTTON || $IS_CREATE_PERMITTED}
								<button type="button" module="{$RELATED_MODULE_NAME}" class="me-2 btn {$RELATED_LINK->getStyleClass()}
									{if $IS_SELECT_BUTTON eq true} selectRelation{else} addButton" name="addButton{/if}"
									{if $IS_SELECT_BUTTON eq true} data-moduleName="{$RELATED_LINK->get('_module')->get('name')}" {/if}
									{if ($RELATED_LINK->isPageLoadLink())}
										{if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
										data-url="{$RELATED_LINK->getUrl()}{if isset($SELECTED_MENU_CATEGORY)}&app={$SELECTED_MENU_CATEGORY}{/if}"
									{/if}
									>{if $IS_SELECT_BUTTON eq false}<i class="fa fa-plus me-2"></i>{/if}{$RELATED_LINK->getLabel()}</button>
							{/if}
						{/if}
					</div>
				{/foreach}
			</div>
			<div class="col-auto ms-auto text-end">
				{assign var=CLASS_VIEW_ACTION value='relatedViewActions'}
				{assign var=CLASS_VIEW_PAGING_INPUT value='relatedViewPagingInput'}
				{assign var=CLASS_VIEW_PAGING_INPUT_SUBMIT value='relatedViewPagingInputSubmit'}
				{assign var=CLASS_VIEW_BASIC_ACTION value='relatedViewBasicAction'}
				{assign var=PAGING_MODEL value=$PAGING}
				{assign var=RECORD_COUNT value=$RELATED_RECORDS|@count}
				{assign var=PAGE_NUMBER value=$PAGING->get('page')}
				{include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
			</div>
		</div>
	</div>
{/strip}