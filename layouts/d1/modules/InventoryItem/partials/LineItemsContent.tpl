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
        <a class="btn deleteItem padding0">
            <i class="fa fa-trash-o fa-fw text-secondary" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
        </a>
        <a class="btn addItemAfter padding0">
            <i class="fa fa-plus fa-fw text-secondary" title="{vtranslate('LBL_ADD_AFTER',$MODULE)}"></i>
        </a>
        <input type="hidden" class="rowNumber" value="{$row_no}" />
        <input type="hidden" class="lineItemId" name="lineItemId{$row_no}" value="{$data.inventoryitemid}" />
        <input type="hidden" class="rowSequence" name="sequence{$row_no}" value="{$row_no}" />
    </td>

    {foreach item=INVENTORY_ITEM_FIELD_NAME from=$INVENTORY_ITEM_COLUMNS}
        {assign var=FIELD value=$INVENTORY_ITEM_RECORD_STRUCTURE[$INVENTORY_ITEM_FIELD_NAME]}
        {if $INVENTORY_ITEM_FIELD_NAME eq 'productid'}
            <td class="minWidth20per item_text_td" title="{$data.item_text}">
                <span class="noEditLineItem display_productid{$row_no} font-bold"><a href="javascript: void;" class="item_edit">{$data.item_text}</a>&nbsp;&nbsp;<small><a class="text-primary" href="index.php?module={$data.entityType}&view=Detail&record={$data.productid}" target="_blank"><i class="fa fa-external-link text-secondary" title="{vtranslate('LBL_DELETE',$MODULE)}"></i></a></small></span>
                <input type="hidden" id="item_text{$row_no}" name="item_text{$row_no}" value="{$data.item_text}" class="item_text" />
                <input type="hidden" id="productid{$row_no}" name="productid{$row_no}" value="{$data.productid}" class="productid" />
                <input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="{$data.entityType}" class="lineItemType" />
            </td>
        {elseif in_array($INVENTORY_ITEM_FIELD_NAME, $SPECIAL_TREATMENT_FIELDS)}
            <td style="display: none;"><input id="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" name="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" class="{$INVENTORY_ITEM_FIELD_NAME}" type="hidden" value="{$data.$INVENTORY_ITEM_FIELD_NAME}"></td>
        {elseif $INVENTORY_ITEM_FIELD_NAME eq 'discount_amount'}
            <td class="textAlignRight" title="{vtranslate({$FIELD->get('label')}, 'InventoryItem')}" style="min-width: 80px;" nowrap="nowrap">
                <span class="noEditLineItem display_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no} ">{if $data.{$INVENTORY_ITEM_FIELD_NAME|cat:'_display'} neq ''}{$data.{$INVENTORY_ITEM_FIELD_NAME|cat:'_display'}}{else}{$data.$INVENTORY_ITEM_FIELD_NAME}{/if}</span>
                <input id="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" name="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" type="hidden" class="{$INVENTORY_ITEM_FIELD_NAME} inputElement form-control textAlignRight" value="{$data.$INVENTORY_ITEM_FIELD_NAME}" readonly="readonly" />
            </td>
        {elseif $INVENTORY_ITEM_FIELD_NAME eq 'price'}
            <td class="textAlignRight" title="{vtranslate({$FIELD->get('label')}, 'InventoryItem')}" nowrap="nowrap" style="min-width: 80px;" nowrap="nowrap">
                <span class="noEditLineItem display_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no} ">{if $data.{$INVENTORY_ITEM_FIELD_NAME|cat:'_display'} neq ''}{$data.{$INVENTORY_ITEM_FIELD_NAME|cat:'_display'}}{else}{$data.$INVENTORY_ITEM_FIELD_NAME}{/if}</span>
                <input id="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" name="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" type="hidden" class="{$INVENTORY_ITEM_FIELD_NAME} inputElement form-control replaceCommaWithDot allowOnlyNumbers textAlignRight" value="{$data.$INVENTORY_ITEM_FIELD_NAME}" />
                <input id="pricebookid{$row_no}" name="pricebookid{$row_no}" class="pricebookid" type="hidden" value="{$data.pricebookid}" />
            </td>
        {elseif $INVENTORY_ITEM_FIELD_NAME eq 'tax' || $INVENTORY_ITEM_FIELD_NAME eq 'margin'}
            <td class="textAlignRight" title="{vtranslate({$FIELD->get('label')}, 'InventoryItem')}" nowrap="nowrap">
                <span class="noEditLineItem display_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no} ">{if $data.{$INVENTORY_ITEM_FIELD_NAME|cat:'_display'} neq ''}{$data.{$INVENTORY_ITEM_FIELD_NAME|cat:'_display'}}{else}{$data.$INVENTORY_ITEM_FIELD_NAME}{/if}&nbsp;%</span>
            </td>
        {elseif $FIELD->getFieldDataType() eq 'integer' or $FIELD->getFieldDataType() eq 'double' or $FIELD->getFieldDataType() eq 'currency' or $FIELD->getFieldDataType() eq 'percentage'}
            <td class="textAlignRight" title="{vtranslate({$FIELD->get('label')}, 'InventoryItem')}" nowrap="nowrap">
                <span class="noEditLineItem display_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no} ">{if $data.{$INVENTORY_ITEM_FIELD_NAME|cat:'_display'} neq ''}{$data.{$INVENTORY_ITEM_FIELD_NAME|cat:'_display'}}{else}{$data.$INVENTORY_ITEM_FIELD_NAME}{/if}</span>
                <input id="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" name="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" type="hidden" class="{$INVENTORY_ITEM_FIELD_NAME} inputElement form-control replaceCommaWithDot allowOnlyNumbers textAlignRight" value="{$data.$INVENTORY_ITEM_FIELD_NAME}" />
            </td>
        {else}
            <td title="{vtranslate({$FIELD->get('label')}, 'InventoryItem')}">
                <span class="noEditLineItem display_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}">{$data.$INVENTORY_ITEM_FIELD_NAME}</span>
                <input id="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" name="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" type="hidden" class="{$INVENTORY_ITEM_FIELD_NAME} inputElement form-control" value="{$data.$INVENTORY_ITEM_FIELD_NAME}" value="{$data.$INVENTORY_ITEM_FIELD_NAME}"/>
            </td>
        {/if}
    {/foreach}

    {foreach key=FIELD_NAME item=INVENTORY_ITEM_RECORD from=$INVENTORY_ITEM_RECORD_STRUCTURE}
        {if !in_array($FIELD_NAME, $INVENTORY_ITEM_COLUMNS) and !in_array($FIELD_NAME, $EXCLUDED_FIELDS)}
            <td style="display: none;"><input id="{$FIELD_NAME|cat:$row_no}" name="{$FIELD_NAME|cat:$row_no}" class="{$FIELD_NAME}" type="hidden" value="{$data.$FIELD_NAME}"></td>
        {/if}
    {/foreach}

{/strip}