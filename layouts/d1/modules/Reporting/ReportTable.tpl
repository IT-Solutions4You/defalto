{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
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