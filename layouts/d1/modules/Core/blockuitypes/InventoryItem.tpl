{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}

<div class="mt-3 bg-body rounded block block_{$BLOCK_LABEL_KEY}" data-block="{$BLOCK_LABEL_KEY}" data-blockid="{$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}">
    {assign var=IS_HIDDEN value=$BLOCK->isHidden()}
    {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
    <input type=hidden name="timeFormatOptions" data-value='{if isset($DAY_STARTS)}{$DAY_STARTS}{else}""{/if}' />
    <div class="p-3">
        <div class="text-truncate d-flex align-items-center">
            <span class="btn btn-outline-secondary blockToggle {if !$IS_HIDDEN}hide{/if}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
                <i class="fa fa-plus"></i>
            </span>
            <span class="btn btn-outline-secondary blockToggle {if $IS_HIDDEN}hide{/if}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
                <i class="fa fa-minus"></i>
            </span>
            <span class="ms-3 fs-4 fw-bold">{vtranslate({$BLOCK_LABEL_KEY},{$MODULE_NAME})}</span>
        </div>
    </div>
    <div class="blockData p-3 border-top border-light-subtle {if $IS_HIDDEN}hide{/if}">
        <table id="dummyLineItemTable" style="display: none;">
            <tr id="dummyItemRow" class="hide border-bottom lineItemCloneCopy" data-row-num="0">
                {include file="partials/LineItemsContent.tpl"|@vtemplate_path:'InventoryItem' row_no=0 data=[]}
            </tr>
            <tr id="dummyTextRow" class=" hide border-bottom" data-row-num="0">
                {include file="partials/TextItemContent.tpl"|@vtemplate_path:'InventoryItem' row_no=0 data=[]}
            </tr>
        </table>
        <div class="lineitemTableContainer">
            <table class="table table-borderless" id="lineItemTab">
                <thead>
                <tr class="border-bottom">
                    <td><strong>{vtranslate('LBL_TOOLS',$MODULE)}</strong></td>
                    {foreach item=INVENTORY_ITEM_FIELD_NAME from=$INVENTORY_ITEM_COLUMNS}
                        {if !in_array($INVENTORY_ITEM_FIELD_NAME, $SPECIAL_TREATMENT_FIELDS)}
                        {assign var=FIELD value=$INVENTORY_ITEM_RECORD_STRUCTURE[$INVENTORY_ITEM_FIELD_NAME]}
                        <td{if $FIELD->getFieldDataType() eq 'currency' or $FIELD->getFieldDataType() eq 'double' or $FIELD->getFieldDataType() eq 'integer' or $FIELD->getFieldDataType() eq 'percentage'} class="textAlignRight"{/if}>
                            <strong>{vtranslate({$FIELD->get('label')}, 'InventoryItem')}</strong>
                        </td>
                        {/if}
                    {/foreach}
                </tr>
                </thead>
                <tbody>
                {foreach key=row_no item=data from=$INVENTORY_ITEMS}
                    <tr id="row{$row_no}" data-row-num="{$row_no}" class="border-bottom lineItemRow">
                        {if $data.entityType eq 'Text'}
                            {include file="partials/TextItemContent.tpl"|@vtemplate_path:'InventoryItem' row_no=$row_no data=$data}
                        {else}
                            {include file="partials/LineItemsContent.tpl"|@vtemplate_path:'InventoryItem' row_no=$row_no data=$data}
                        {/if}
                    </tr>
                {/foreach}
                </tbody>
                <tfoot>
                <tr>
                    <td></td>
                    {foreach item=INVENTORY_ITEM_FIELD_NAME from=$INVENTORY_ITEM_COLUMNS}
                        {if in_array($INVENTORY_ITEM_FIELD_NAME, $SPECIAL_TREATMENT_FIELDS)}
                            {continue}
                        {/if}
                        {assign var=FIELD value=$INVENTORY_ITEM_RECORD_STRUCTURE[$INVENTORY_ITEM_FIELD_NAME]}
                        <td{if $FIELD->getFieldDataType() eq 'currency' or $FIELD->getFieldDataType() eq 'double' or $FIELD->getFieldDataType() eq 'integer' or $FIELD->getFieldDataType() eq 'percentage'} class="textAlignRight"{/if} style="font-weight: bold;">
                            {if $INVENTORY_ITEM_FIELD_NAME eq 'productid'}
                                {vtranslate('Total', 'InventoryItem')}
                             {elseif in_array($INVENTORY_ITEM_FIELD_NAME, $COMPUTED_FIELDS)}
                                <span class="total_{$INVENTORY_ITEM_FIELD_NAME}"></span>
                            {/if}
                        </td>
                    {/foreach}
                    {foreach key=FIELD_NAME item=FIELD from=$INVENTORY_ITEM_RECORD_STRUCTURE}
                        {if !in_array($FIELD_NAME, $INVENTORY_ITEM_COLUMNS) and !in_array($FIELD_NAME, $EXCLUDED_FIELDS)}
                            <td style="display: none;">
                                {if $FIELD->getFieldDataType() eq 'currency'}
                                    <span class="total_{$INVENTORY_ITEM_FIELD_NAME}"></span>
                                {/if}
                            </td>
                        {/if}
                    {/foreach}
                </tr>
                {if !empty($SPECIAL_TREATMENT_FIELDS)}
                    {if in_array('overall_discount', $SPECIAL_TREATMENT_FIELDS) and in_array('overall_discount', $INVENTORY_ITEM_COLUMNS)}
                        <tr>
                            <td colspan="{$INVENTORY_ITEM_FIELD_NAME@total - 1}" class="textAlignRight">
                                <div class="position-relative">
                                <strong>{vtranslate('Overal Discount %', 'InventoryItem')}</strong>&nbsp;&nbsp;<i class="fa fa-pencil fa-fw text-secondary editOverallDiscount" title="{vtranslate('LBL_EDIT',$MODULE)}"></i>
                                <div class="popover lineItemPopover border-1 bs-popover-auto fade" role="tooltip" id="overallDiscountSettingDiv" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; opacity: 1; visibility: visible; transform: translate(-51px, -126px); display: none;" data-popper-placement="left">
                                    <h3 class="popover-header p-3 m-0 border-bottom">{vtranslate('Overal Discount %', 'InventoryItem')}</h3>
                                    <div class="popover-body popover-content">
                                        <div class="finalTaxUI validCheck" id="group_tax_div">
                                            <table class="table table-borderless popupTable m-0">
                                                <tbody>
                                                <tr>
                                                    <td class="lineOnTop p-3">{vtranslate('Discount ', 'InventoryItem')}</td>
                                                    <td class="lineOnTop">
                                                        <div class="input-group">
                                                            <input type="text" size="5" data-compound-on="" name="overall_discount_percent" id="overall_discount_percent" value="" class="form-control overallDiscountPercent replaceCommaWithDot textAlignRight" data-rule-positive="true" data-rule-inventory_percentage="true" aria-invalid="false">
                                                            <input type="hidden" id="original_overall_discount_percent" name="original_overall_discount_percent" value="" class="original_overall_discount_percent">
                                                            <div class="input-group-text">%</div>
                                                        </div>
                                                    </td>
                                                    <td class="lineOnTop text-end">
                                                        <input type="text" size="6" name="overall_discount_amount" id="overall_discount_amount" style="cursor:pointer;" value="" readonly="" class="form-control overallDiscountAmount textAlignRight" aria-invalid="false">
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="modal-footer lineItemPopupModalFooter p-3">
                                        <div class="container-fluid p-0">
                                            <div class="row">
                                                <div class="col-6 text-end">
                                                    <a class="btn btn-outline-primary popoverCancel closeOverallDiscountDiv">Cancel</a>
                                                </div>
                                                <div class="col-6 text-start">
                                                    <a class="btn btn-primary active popoverButton saveOverallDiscount"><strong>Save</strong></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </td>
                            <td class="textAlignRight"><span class="overal_dicsount_percent">7</span></td>
                        </tr>
                    {/if}
                {/if}
                </tfoot>
            </table>
        </div>
    </div>
    <div class="px-4">
        <div class="btn-toolbar inventoryItemAddButtons" style="display:inline;">
            <div class="recordLabel verticalAlignMiddle me-2" style="display:inline;"><strong>Add </strong></div>
            <button type="button" class="btn btn-outline-primary mb-1 me-1" id="addText" data-module-name="">
                <i class="fa fa-i-cursor"></i><strong>&nbsp;&nbsp;{vtranslate('TEXT', $MODULE)}</strong>
            </button>
            {foreach from=$ITEM_MODULES item=ITEM_MODULE_NAME}
                <button type="button" class="btn btn-outline-primary mb-1 me-1" id="add{$ITEM_MODULE_NAME}" data-module-name="{$ITEM_MODULE_NAME}">
                    {Vtiger_Module_Model::getModuleIconPath($ITEM_MODULE_NAME)}<strong>&nbsp;&nbsp;{vtranslate($ITEM_MODULE_NAME, {$ITEM_MODULE_NAME})}</strong>
                </button>
            {/foreach}
        </div>
    </div>
</div>