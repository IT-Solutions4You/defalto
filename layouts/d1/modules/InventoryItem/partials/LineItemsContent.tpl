{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <td style="width: 3%" nowrap="nowrap">
        <span class="noEditLineItem">
            <a class="btn drag_drop_line_item padding0" style="cursor: all-scroll;">
                <i class="fa fa-arrows-v fa-fw text-secondary" title="{vtranslate('LBL_DRAG',$MODULE)}"></i>
            </a>
        </span>
        <span class="more dropdown action">
        <div class="btn btn-sm text-secondary" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis icon"></i></div>
        <ul class="dropdown-menu" style="">
            {if !$data.isDeleted}
            <li><a class="dropdown-item editItem"><i class="fa fa-pencil fa-fw text-secondary" title="{vtranslate('LBL_EDIT',$MODULE)}"></i><span class="ms-2">{vtranslate('LBL_EDIT',$MODULE)}</span></a></li>
            <li><a class="dropdown-item duplicateItem"><i class="fa fa-clone fa-fw text-secondary" title="{vtranslate('LBL_EDIT',$MODULE)}"></i><span class="ms-2">{vtranslate('LBL_DUPLICATE',$MODULE)}</span></a></li>
            {/if}
            <li><a class="dropdown-item deleteItem"><i class="fa fa-trash-o fa-fw text-secondary" title="{vtranslate('LBL_DELETE',$MODULE)}"></i><span class="ms-2">{vtranslate('LBL_DELETE',$MODULE)}</span></a></li>
            {if !$data.isDeleted}
            <li><a class="dropdown-item addAfter" data-modulename=""><i class="fa fa-i-cursor fa-fw text-secondary"></i><span class="ms-2">{vtranslate('Add', $MODULE)} {vtranslate('TEXT', $MODULE)}</span></a></li>
            {foreach item=ITEM_MODULE_NAME from=$ITEM_MODULES}
                <li><a class="dropdown-item addAfter" data-modulename="{$ITEM_MODULE_NAME}"><span class="text-secondary">{Vtiger_Module_Model::getModuleIconPath($ITEM_MODULE_NAME)}</span>&nbsp;<span class="ms-2">{vtranslate('Add', $MODULE)} {vtranslate($ITEM_MODULE_NAME, {$ITEM_MODULE_NAME})}</span></a></li>
            {/foreach}
            {/if}
        </ul>
    </span>
        <input type="hidden" class="rowNumber" value="{$row_no}" />
        <input type="hidden" class="lineItemId" name="lineItemId{$row_no}" value="{$data.inventoryitemid}" />
        <input type="hidden" class="rowSequence" name="sequence{$row_no}" value="{$row_no}" />
    </td>

    {foreach item=INVENTORY_ITEM_FIELD_NAME from=$INVENTORY_ITEM_COLUMNS}
        {if $INVENTORY_ITEM_RECORD_STRUCTURE[$INVENTORY_ITEM_FIELD_NAME] eq ''}
            {continue}
        {/if}
        {assign var=FIELD value=$INVENTORY_ITEM_RECORD_STRUCTURE[$INVENTORY_ITEM_FIELD_NAME]}
        {if $INVENTORY_ITEM_FIELD_NAME eq 'productid'}
            <td class="minWidth20per item_text_td" title="{$data.item_text}">
                <span class="noEditLineItem display_productid{$row_no} font-bold" {if $data.isDeleted}style="text-decoration: line-through;"{/if}>
                    <a href="javascript: void;" {if !$data.isDeleted}class="item_edit"{/if}>{$data.item_text}</a>&nbsp;&nbsp;
                    {if !$data.isDeleted}
                    <small>
                        <a class="text-primary" href="index.php?module={$data.entityType}&view=Detail&record={$data.productid}" target="_blank">
                            <i class="fa fa-external-link text-secondary" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
                        </a>
                    </small>
                    {/if}
                </span>
                <input type="hidden" id="item_text{$row_no}" name="item_text{$row_no}" value="{$data.item_text}" class="item_text" />
                <input type="hidden" id="productid{$row_no}" name="productid{$row_no}" value="{$data.productid}" class="productid" />
                <input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="{$data.entityType}" class="lineItemType" />
                {if $data.subProducts neq ''}
                    {foreach from=$data.subProducts item=subProduct}
                        <br />
                        <i>{$subProduct->get('productname')}</i>
                    {/foreach}
                {/if}
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
                <span class="noEditLineItem display_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}">{if $data.{$INVENTORY_ITEM_FIELD_NAME|cat:'_display'} neq ''}{$data.{$INVENTORY_ITEM_FIELD_NAME|cat:'_display'}}{else}{$data.$INVENTORY_ITEM_FIELD_NAME}{/if}</span>
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