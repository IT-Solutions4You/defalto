{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="col-md">
        {if !empty($MULTI_SELECT)}
            {if !empty($LISTVIEW_ENTRIES)}<button class="select btn btn-outline-secondary" disabled="disabled"><strong>{vtranslate('LBL_ADD', $MODULE)}</strong></button>{/if}
        {/if}
        {if !empty($RELATED_PARENT_ID)}
            <button type="button" class="btn btn-outline-secondary showAllRecordsRecords">
                {vtranslate('LBL_SHOW_ALL_RECORDS', $MODULE)}
            </button>
        {/if}
    </div>
    <div class="col-md-auto">
        {assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
        {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
    </div>
{/strip}