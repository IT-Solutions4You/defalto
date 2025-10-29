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
            <li><a class="dropdown-item editItem"><i class="fa fa-pencil fa-fw text-secondary" title="{vtranslate('LBL_EDIT',$MODULE)}"></i><span class="ms-2">{vtranslate('LBL_EDIT',$MODULE)}</span></a></li>
            <li><a class="dropdown-item duplicateItem"><i class="fa fa-clone fa-fw text-secondary" title="{vtranslate('LBL_EDIT',$MODULE)}"></i><span class="ms-2">{vtranslate('LBL_DUPLICATE',$MODULE)}</span></a></li>
            <li><a class="dropdown-item deleteItem"><i class="fa fa-trash-o fa-fw text-secondary" title="{vtranslate('LBL_DELETE',$MODULE)}"></i><span class="ms-2">{vtranslate('LBL_DELETE',$MODULE)}</span></a></li>
            <li><a class="dropdown-item addAfter" data-modulename=""><i class="fa fa-i-cursor fa-fw text-secondary"></i><span class="ms-2">{vtranslate('Add', $MODULE)} {vtranslate('TEXT', $MODULE)}</span></a></li>
            {foreach item=ITEM_MODULE_NAME from=$ITEM_MODULES}
                <li><a class="dropdown-item addAfter" data-modulename="{$ITEM_MODULE_NAME}"><span class="text-secondary">{Vtiger_Module_Model::getModuleIconPath($ITEM_MODULE_NAME)}</span>&nbsp;<span class="ms-2">{vtranslate('Add', $MODULE)} {vtranslate($ITEM_MODULE_NAME, $ITEM_MODULE_NAME)}</span></a></li>
            {/foreach}
        </ul>
    </span>
    <input type="hidden" class="rowNumber" value="{$row_no}" />
    <input type="hidden" class="lineItemId" name="lineItemId{$row_no}" value="{$data.inventoryitemid}" />
    <input type="hidden" class="rowSequence" name="sequence{$row_no}" value="{$row_no}" />
</td>
<td class="bg-secondary bg-opacity-10" colspan="50" title="{vtranslate('Item text', 'InventoryItem')}">
    {assign var="item_text" value="item_text"|cat:$row_no}
    <span class="noEditLineItem display_{$item_text}"><a href="javascript: void;" class="item_edit"><strong>{$data.item_text}</strong></a></span>
    <span class="editLineItem hide">
        <input type="text" id="{$item_text}" name="{$item_text}" value="{$data.item_text}" class="item_text form-control" data-rule-required=true>
        <input type="hidden" id="productid{$row_no}" name="productid{$row_no}" value="{$data.productid}" class="selectedModuleId"/>
        <input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="Text" class="lineItemType"/>
    </span>
</td>

{foreach key=FIELD_NAME item=INVENTORY_ITEM_RECORD from=$INVENTORY_ITEM_RECORD_STRUCTURE}
    {if !in_array($FIELD_NAME, $EXCLUDED_FIELDS) && isset($data.$INVENTORY_ITEM_FIELD_NAME)}
        <td style="display: none;"><input id="{$FIELD_NAME|cat:$row_no}" name="{$FIELD_NAME|cat:$row_no}" type="hidden" value="{$data.$INVENTORY_ITEM_FIELD_NAME}" class="{$FIELD_NAME}"></td>
    {/if}
{/foreach}
{/strip}