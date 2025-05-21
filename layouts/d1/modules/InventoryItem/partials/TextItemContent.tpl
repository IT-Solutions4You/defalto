{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{strip}
<td style="width: 3%" nowrap="nowrap">
    <span class="noEditLineItem">
        <a class="btn drag_drop_line_item padding0">
            {*<img src="{vimage_path('drag.png')}" title="{vtranslate('LBL_DRAG',$MODULE)}"/>*}
            <i class="fa fa-arrows-v fa-fw text-secondary" title="{vtranslate('LBL_DRAG',$MODULE)}"></i>
        </a>
    </span>
    <a class="btn editItem padding0">
        <i class="fa fa-pencil fa-fw text-secondary" title="{vtranslate('LBL_EDIT',$MODULE)}"></i>
    </a>
    <a class="btn deleteRow padding0">
        <i class="fa fa-trash-o fa-fw text-secondary" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
    </a>
    <input type="hidden" class="rowNumber" value="{$row_no}" />
    <input type="hidden" class="lineItemId" name="lineItemId{$row_no}" value="{$data.inventoryitemid}" />
    <input type="hidden" class="rowSequence" name="sequence{$row_no}" value="{$row_no}" />
</td>
<td colspan="50" title="{vtranslate('Item text', 'InventoryItem')}">
    {assign var="item_text" value="item_text"|cat:$row_no}
    <span class="noEditLineItem display_{$item_text}"><strong>{$data.item_text}</strong></span>
    <span class="editLineItem hide">
        <input type="text" id="{$item_text}" name="{$item_text}" value="{$data.item_text}" class="item_text form-control" data-rule-required=true>
        <input type="hidden" id="productid{$row_no}" name="productid{$row_no}" value="{$data.productid}" class="selectedModuleId"/>
        <input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="Text" class="lineItemType"/>
    </span>
</td>

{foreach key=FIELD_NAME item=INVENTORY_ITEM_RECORD from=$INVENTORY_ITEM_RECORD_STRUCTURE}
    {if !in_array($FIELD_NAME, $EXCLUDED_FIELDS)}
        <td style="display: none;"><input id="{$FIELD_NAME|cat:$row_no}" name="{$FIELD_NAME|cat:$row_no}" type="hidden" value="{$data.$INVENTORY_ITEM_FIELD_NAME}" class="{$FIELD_NAME}"></td>
    {/if}
{/foreach}
{/strip}