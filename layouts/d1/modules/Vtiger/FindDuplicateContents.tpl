{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Vtiger/views/FindDuplicates.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div>
	{include file="FindDuplicateHeader.tpl"|vtemplate_path:$MODULE}
</div>
<div id="findDuplicateContents" class="container-fluid">
	<div class="row">
		<div class="col-lg-12">
			<input type="hidden" id="listViewEntriesCount" value="{$LISTVIEW_ENTRIES_COUNT}" />
			<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
			<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
			<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
			<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
			<input type="hidden" id="pageNumber" value= "{$PAGE_NUMBER}"/>
			<input type="hidden" id="pageLimit" value= "{$PAGING_MODEL->getPageLimit()}" />
			<input type="hidden" id="noOfEntries" value= "{$LISTVIEW_ENTRIES_COUNT}" />
			<input type="hidden" id="duplicateSearchFields" value={Zend_Json::encode($DUPLICATE_SEARCH_FIELDS)} />
			<input type="hidden" id="viewName" value="{$VIEW_NAME}" />
			<input type="hidden" id="totalCount" value="{$TOTAL_COUNT}" />
			<input type='hidden' id='ignoreEmpty' value="{$IGNORE_EMPTY}" />
			<input type="hidden" id="mergeSelectedIds" />
			{assign var=IS_EDITABLE value=$CURRENT_USER_PRIVILAGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(), 'EditView')}
			{assign var=IS_DELETABLE value=$CURRENT_USER_PRIVILAGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(), 'Delete')}

			<table id="listview-table" class="listview-table table">
				<thead class="border-bottom">
					<tr class="listViewContentHeader">
						{if $IS_DELETABLE}
							<th class="text-center text-middle">
                                <input type="checkbox" class="listViewEntriesMainCheckBox form-check-input" />
							</th>
						{/if}
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							<th class="text-secondary">
                                {vtranslate($LISTVIEW_HEADER->get('label'), $MODULE)}
							</th>
						{/foreach}
						{if $IS_EDITABLE && $IS_DELETABLE}
							<th class="text-center text-secondary">{vtranslate('LBL_MERGE_SELECT', $MODULE)}</th>
							<th class="text-center text-secondary">{vtranslate('LBL_ACTION', $MODULE)}</th>
						{/if}
					</tr>
				</thead>
				{foreach item=LISTVIEW_ENTRY key=GROUP_NAME from=$LISTVIEW_ENTRIES}
					{assign var=groupCount value=$LISTVIEW_ENTRY|@php7_count}
					{assign var=recordCount value=0}
					{foreach item=RECORD from=$LISTVIEW_ENTRY name=listview}
						<tr class="listViewEntries border-top" data-id='{$RECORD.recordid}' id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
							{if $IS_DELETABLE}
								<td class="text-center text-middle">
                                    <input type="checkbox" value="{$RECORD.recordid}" class="listViewEntriesCheckBox form-check-input"/>
								</td>
							{/if}
							{assign var=sameRowValues value=true}
							{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                                {if $LISTVIEW_HEADER->get('name') eq 'recordid'}
                                    <td>
                                        <a href="{$MODULE_MODEL->getDetailViewUrl($RECORD.recordid)}">{$RECORD[$LISTVIEW_HEADER->get('name')]}</a>
                                    </td>
                                {else}
                                    <td name="{$LISTVIEW_HEADER->get('name')}" class="border-bottom" data-value="{strip_tags($LISTVIEW_HEADER->getDisplayValue($RECORD[$LISTVIEW_HEADER->get('column')], $RECORD.recordid))}">
                                        {strip_tags($LISTVIEW_HEADER->getDisplayValue($RECORD[$LISTVIEW_HEADER->get('column')], $RECORD.recordid))}
                                    </td>
                                {/if}
							{/foreach}
							{if $IS_EDITABLE && $IS_DELETABLE}
								<td class="text-middle border-end">
                                    <input class="form-check-input" type="checkbox" data-id='{$RECORD.recordid}' name="mergeRecord" data-group="{$GROUP_NAME}"/>
								</td>
								{if isset($recordCount) && $recordCount eq 0}
									<td class="text-center border-end text-middle" rowspan="{$groupCount}">
                                        <input type="button" value="{vtranslate('Merge', $MODULE)}" name="merge" class="btn btn-success" data-group="{$GROUP_NAME}">
									</td>
								{/if}
							{/if}
							{assign var=recordCount value=$recordCount+1}
						</tr>
					{/foreach}
				{/foreach}
			</table>
			{if isset($recordCount) && $recordCount eq 0}
				<div class="col-md-12 col-sm-12 col-xs-12 col-lg-12 listViewContentDiv list-table-wrapper" id="listViewContents">
					<table class="emptyRecordsDiv">
						<tbody class="overflow-y">
							<tr class="emptyRecordDiv">
								<td colspan="8">
									<div class="emptyRecordsContent portal-empty-records-content">
										{vtranslate('LBL_NO_DUPLICATED_FOUND')}.
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			{/if}
		</div>
	</div>
</div>