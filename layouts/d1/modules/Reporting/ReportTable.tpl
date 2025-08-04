{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<table class="renderedTable table table-striped border">
    {foreach from=$TABLE_DATA item=TABLE_COLUMNS name=TABLE}
        <tr>
            {foreach from=$TABLE_COLUMNS item=TABLE_COLUMN name=ROW}
                {if 0 eq $smarty.foreach.TABLE.index}
                    <th style="{$TABLE_STYLE['th'][$smarty.foreach.ROW.index]}">{$TABLE_COLUMN}</th>
                {else}
                    <td style="{$TABLE_STYLE['td'][$smarty.foreach.ROW.index]}">{$TABLE_COLUMN}</td>
                {/if}
            {/foreach}
        </tr>
    {/foreach}
</table>