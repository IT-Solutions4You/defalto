{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*}

<div class="listViewPageDiv detailViewContainer px-4 pb-4" id="listViewContent">
    <div class="bg-body rounded">
        <form name="quantityDecimals" id="quantityDecimals" method="POST">
            <input type="hidden" name="module" value="InventoryItem">
            <input type="hidden" name="parent" value="Settings">
            <input type="hidden" name="action" value="QuantityDecimalsSave">
            <div class="p-3 border-bottom">
                <h4 class="m-0">{vtranslate('Quantity Decimals',$QUALIFIED_MODULE)}</h4>
            </div>
            <div class="detailViewInfo container-fluid pt-3 px-3">
                <div class="row form-group align-items-center">
                    <div class="col-sm-3 control-label fieldLabel pb-3">
                        <label class="fieldLabel ">{vtranslate('Number of decimal places in field "Quantity"',$QUALIFIED_MODULE)}</label>
                    </div>
                    <div class="fieldValue col-sm-6 pb-3">
                        <select class="select2 inputElement" id="decimals" name="decimals">
                            {for $places=0 to 4}
                                <option value="{$places}" {if $DECIMALS eq $places}selected="selected"{/if}>{$places}</option>
                            {/for}
                        </select>
                    </div>
                </div>
            </div>
            <div class="container-fluid py-3">
                <div class="row">
                    <div class="col text-end"><a class="btn btn-primary cancelLink" data-bs-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a></div>
                    <div class="col"><button type="submit" class="btn btn-primary active saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button></div>
                </div>
            </div>
        </form>
    </div>
</div>