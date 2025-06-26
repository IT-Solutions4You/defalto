{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{strip}
<td style="width: 3%" nowrap="nowrap" class="bg-primary-subtle">
    <span class="noEditLineItem">
        <a class="btn drag_drop_line_item padding0">
            <i class="fa fa-arrows-v fa-fw text-secondary" title="{vtranslate('LBL_DRAG',$MODULE)}"></i>
        </a>
    </span>
    <span class="more dropdown action">
        <div class="btn btn-sm text-secondary" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis icon"></i></div>
        <ul class="dropdown-menu" style="">
            <li><a class="dropdown-item editItem"><i class="fa fa-pencil fa-fw text-secondary" title="{vtranslate('LBL_EDIT',$MODULE)}"></i><span class="ms-2">{vtranslate('LBL_EDIT',$MODULE)}</span></a></li>
            <li><a class="dropdown-item deleteItem"><i class="fa fa-trash-o fa-fw text-secondary" title="{vtranslate('LBL_DELETE',$MODULE)}"></i><span class="ms-2">{vtranslate('LBL_DELETE',$MODULE)}</span></a></li>
            <li><a class="dropdown-item addItemAfter"><i class="fa fa-plus fa-fw text-secondary" title="{vtranslate('LBL_ADD_AFTER',$MODULE)}"></i><span class="ms-2">{vtranslate('LBL_ADD_AFTER',$MODULE)}</span></a></li>
        </ul>
    </span>
    <input type="hidden" class="rowNumber" value="{$row_no}" />
    <input type="hidden" class="lineItemId" name="lineItemId{$row_no}" value="{$data.inventoryitemid}" />
    <input type="hidden" class="rowSequence" name="sequence{$row_no}" value="{$row_no}" />
</td>
<td colspan="50" title="{vtranslate('Item text', 'InventoryItem')}" class="bg-primary-subtle">
    {assign var="item_text" value="item_text"|cat:$row_no}
    <span class="noEditLineItem display_{$item_text}"><a href="javascript: void;" class="item_edit"><strong>{$data.item_text}</strong></a></span>
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