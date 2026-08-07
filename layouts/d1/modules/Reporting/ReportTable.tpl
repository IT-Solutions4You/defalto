{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="table-responsive">
    <table class="renderedTable table table-hover align-middle mb-0" style="{if !empty($TABLE_SCROLLABLE) && !empty($TABLE_STYLE['table'])}{$TABLE_STYLE['table']}{/if}">
        {assign var=TABLE_COLUMN_STYLES value=$TABLE_STYLE['col']}
        {if !empty($TABLE_SCROLLABLE) && !empty($TABLE_STYLE['web_col'])}
            {assign var=TABLE_COLUMN_STYLES value=$TABLE_STYLE['web_col']}
        {/if}
        {if !empty($TABLE_COLUMN_STYLES)}
            <colgroup>
                {foreach from=$TABLE_COLUMN_STYLES item=TABLE_COLUMN_STYLE}
                    <col style="{$TABLE_COLUMN_STYLE}">
                {/foreach}
            </colgroup>
        {/if}
        {assign var=TABLE_GROUP_INDEX value=-1}
        {foreach from=$TABLE_DATA item=TABLE_COLUMNS name=TABLE}
            {assign var=TABLE_ROW_TYPE value=''}
            {if isset($TABLE_ROW_TYPES[$smarty.foreach.TABLE.index])}
                {assign var=TABLE_ROW_TYPE value=$TABLE_ROW_TYPES[$smarty.foreach.TABLE.index]}
            {/if}
            {if 'group' eq $TABLE_ROW_TYPE}
                {assign var=TABLE_GROUP_INDEX value=$TABLE_GROUP_INDEX+1}
            {/if}
            <tr class="{if 'header' eq $TABLE_ROW_TYPE || (empty($TABLE_ROW_TYPES) && 0 eq $smarty.foreach.TABLE.index)}reportingTableHeader bg-body-secondary text-secondary border-bottom{elseif 'group' eq $TABLE_ROW_TYPE}reportingGroupHeader table-light fw-normal border-bottom{elseif 'record' eq $TABLE_ROW_TYPE}reportingGroupRecord border-bottom{elseif 'grand_total' eq $TABLE_ROW_TYPE}reportingGrandTotal table-primary fw-bold border-bottom{else}reportingTableRecord border-bottom{/if}"{if 'group' eq $TABLE_ROW_TYPE || 'record' eq $TABLE_ROW_TYPE} data-reporting-group="{$TABLE_GROUP_INDEX}"{/if}>
                {foreach from=$TABLE_COLUMNS item=TABLE_COLUMN name=ROW}
                    {if 'header' eq $TABLE_ROW_TYPE || (empty($TABLE_ROW_TYPES) && 0 eq $smarty.foreach.TABLE.index)}
                        <th class="reportingTableHeaderCell bg-body-secondary text-secondary text-nowrap" scope="col" style="{$TABLE_STYLE['th'][$smarty.foreach.ROW.index]}{if !empty($TABLE_SCROLLABLE) && !empty($TABLE_STYLE['min'][$smarty.foreach.ROW.index])}{$TABLE_STYLE['min'][$smarty.foreach.ROW.index]}{/if}">{$TABLE_COLUMN}</th>
                    {else}
                        <td class="reportingTableCell{if 'group' eq $TABLE_ROW_TYPE} text-secondary{/if}" style="{$TABLE_STYLE['td'][$smarty.foreach.ROW.index]}{if !empty($TABLE_SCROLLABLE) && !empty($TABLE_STYLE['min'][$smarty.foreach.ROW.index])}{$TABLE_STYLE['min'][$smarty.foreach.ROW.index]}{/if}">
                            {if !empty($TABLE_GROUPS_COLLAPSIBLE) && 'group' eq $TABLE_ROW_TYPE && 0 eq $smarty.foreach.ROW.index}
                                <button
                                    class="reportingGroupToggle btn btn-sm border-0 bg-transparent text-body-secondary p-0 me-2"
                                    type="button"
                                    data-reporting-group="{$TABLE_GROUP_INDEX}"
                                    data-expand-label="{vtranslate('LBL_EXPAND_GROUP', 'Reporting')}"
                                    data-collapse-label="{vtranslate('LBL_COLLAPSE_GROUP', 'Reporting')}"
                                    aria-expanded="true"
                                    aria-label="{vtranslate('LBL_COLLAPSE_GROUP', 'Reporting')}"
                                    title="{vtranslate('LBL_COLLAPSE_GROUP', 'Reporting')}"
                                >
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            {/if}
                            {$TABLE_COLUMN}
                        </td>
                    {/if}
                {/foreach}
            </tr>
        {/foreach}
    </table>
</div>
