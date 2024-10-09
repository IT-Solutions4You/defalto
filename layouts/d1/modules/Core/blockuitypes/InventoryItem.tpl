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
            <tr id="row0" class="hide border-bottom lineItemCloneCopy" data-row-num="0">
                {include file="partials/LineItemsContent.tpl"|@vtemplate_path:'InventoryItem' row_no=0 data=[] IGNORE_UI_REGISTRATION=true}
            </tr>
            <tr id="dummyTextRow" class=" hide border-bottom" data-row-num="0">
                {include file="partials/TextItemContent.tpl"|@vtemplate_path:'InventoryItem' row_no=0 data=[] IGNORE_UI_REGISTRATION=true}
            </tr>
        </table>
        <div class="lineitemTableContainer">
            <table class="table table-borderless" id="lineItemTab">
                <tr class="border-bottom">
                    <td><strong>{vtranslate('LBL_TOOLS',$MODULE)}</strong></td>
                    {if isset($PRODUCT_EDITABLE)}
                        <td>
                            <span class="text-danger me-2">*</span>
                            <strong>{vtranslate({$LINEITEM_FIELDS['productid']->get('label')},$MODULE)}</strong>
                        </td>
                    {/if}
                    <td>
                        <strong>{vtranslate('LBL_QTY',$MODULE)}</strong>
                    </td>
                </tr>
                {foreach key=row_no item=data from=$RELATED_PRODUCTS}
                    <tr id="row{$row_no}" data-row-num="{$row_no}" class="border-bottom lineItemRow" {if $data["entityType$row_no"] eq 'Products'}data-quantity-in-stock={$data["qtyInStock$row_no"]}{/if}>
                        {include file="partials/LineItemsContent.tpl"|@vtemplate_path:'Inventory' row_no=$row_no data=$data}
                    </tr>
                {/foreach}
            </table>
        </div>
    </div>
    <div class="px-4">
        <div class="btn-toolbar">
            <div class="btn-group">
                <button type="button" class="btn btn-default" id="addText" data-module-name="">
                    <i class="fa fa-plus"></i><strong>&nbsp;&nbsp;{vtranslate('LBL_ADD_TEXT', $MODULE)}</strong>
                </button>
            </div>
            {if $PRODUCT_ACTIVE eq 'true'}
                <div class="btn-group">
                    <button type="button" class="btn btn-default" id="addProduct" data-module-name="Products">
                        <i class="fa fa-plus"></i><strong>&nbsp;&nbsp;{vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
                    </button>
                </div>
            {/if}
            {if $SERVICE_ACTIVE eq 'true'}
                <div class="btn-group">
                    <button type="button" class="btn btn-default" id="addService" data-module-name="Services">
                        <i class="fa fa-plus"></i><strong>&nbsp;&nbsp;{vtranslate('LBL_ADD_SERVICE',$MODULE)}</strong>
                    </button>
                </div>
            {/if}
        </div>
    </div>
</div>