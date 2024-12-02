{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}

<td class="text-center">
    <a class="btn deleteRow me-2">
        <i class="fa fa-trash" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
    </a>
    <a class="drag_drop_line_item">
        <img src="{vimage_path('drag.png')}" title="{vtranslate('LBL_DRAG',$MODULE)}"/>
    </a>
    <input type="hidden" class="rowNumber" value="{$row_no}" />
    <input type="hidden" class="lineItemId" name="lineItemId{$row_no}" value="{$data.inventoryitemid}" />
    <input type="hidden" class="rowSequence" name="sequence{$row_no}" value="{$row_no}" />
</td>
<td colspan="50">
    {assign var="item_text" value="item_text"|cat:$row_no}
    <input type="text" id="{$item_text}" name="{$item_text}" value="{$data.item_text}" class="item_text form-control autoComplete" data-rule-required=true>
    <input type="hidden" id="productid{$row_no}" name="productid{$row_no}" value="{$data.productid}" class="selectedModuleId"/>
    <input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="Text" class="lineItemType"/>
</td>

{foreach key=FIELD_NAME item=INVENTORY_ITEM_RECORD from=$INVENTORY_ITEM_RECORD_STRUCTURE}
    {if !in_array($FIELD_NAME, $EXCLUDED_FIELDS)}
        <td style="display: none;"><input id="{$FIELD_NAME|cat:$row_no}" name="{$FIELD_NAME|cat:$row_no}" type="hidden" value="{$data.$INVENTORY_ITEM_FIELD_NAME}" class="{$FIELD_NAME}"></td>
    {/if}
{/foreach}
