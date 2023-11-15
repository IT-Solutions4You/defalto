{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	{assign var="deleted" value="deleted"|cat:$row_no}
	{assign var="image" value="productImage"|cat:$row_no}
	{assign var="purchaseCost" value="purchaseCost"|cat:$row_no}
	{assign var="margin" value="margin"|cat:$row_no}
    {assign var="hdnProductId" value="hdnProductId"|cat:$row_no}
    {assign var="productName" value="productName"|cat:$row_no}
    {assign var="comment" value="comment"|cat:$row_no}
    {assign var="productDescription" value="productDescription"|cat:$row_no}
    {assign var="qtyInStock" value="qtyInStock"|cat:$row_no}
    {assign var="qty" value="qty"|cat:$row_no}
    {assign var="listPrice" value="listPrice"|cat:$row_no}
    {assign var="productTotal" value="productTotal"|cat:$row_no}
    {assign var="subproduct_ids" value="subproduct_ids"|cat:$row_no}
    {assign var="subprod_names" value="subprod_names"|cat:$row_no}
	{assign var="subprod_qty_list" value="subprod_qty_list"|cat:$row_no}
    {assign var="entityIdentifier" value="entityType"|cat:$row_no}
    {assign var="entityType" value=$data.$entityIdentifier}

    {assign var="discount_type" value="discount_type"|cat:$row_no}
    {assign var="discount_percent" value="discount_percent"|cat:$row_no}
    {assign var="checked_discount_percent" value="checked_discount_percent"|cat:$row_no}
    {assign var="style_discount_percent" value="style_discount_percent"|cat:$row_no}
    {assign var="discount_amount" value="discount_amount"|cat:$row_no}
    {assign var="checked_discount_amount" value="checked_discount_amount"|cat:$row_no}
    {assign var="style_discount_amount" value="style_discount_amount"|cat:$row_no}
    {assign var="checked_discount_zero" value="checked_discount_zero"|cat:$row_no}

    {assign var="discountTotal" value="discountTotal"|cat:$row_no}
    {assign var="totalAfterDiscount" value="totalAfterDiscount"|cat:$row_no}
    {assign var="taxTotal" value="taxTotal"|cat:$row_no}
    {assign var="netPrice" value="netPrice"|cat:$row_no}
    {assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}

	{assign var="productDeleted" value="productDeleted"|cat:$row_no}
	{assign var="productId" value=$data[$hdnProductId]}
	{assign var="listPriceValues" value=Products_Record_Model::getListPriceValues($productId)}
	{if $MODULE eq 'PurchaseOrder'}
		{assign var="listPriceValues" value=array()}
		{assign var="purchaseCost" value="{if $data.$purchaseCost && $RECORD_CURRENCY_RATE}{((float)$data.$purchaseCost) / ((float)$data.$qty * (float){$RECORD_CURRENCY_RATE})}{else}0{/if}"}
		{foreach item=currency_details from=$CURRENCIES}
			{append var='listPriceValues' value=$currency_details.conversionrate * $purchaseCost index=$currency_details.currency_id}
		{/foreach}
	{/if}

	<td class="text-center">
        <a class="btn deleteRow me-2">
            <i class="fa fa-trash" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
        </a>
		<a>
            <img src="{vimage_path('drag.png')}" title="{vtranslate('LBL_DRAG',$MODULE)}"/>
        </a>
		<input type="hidden" class="rowNumber" value="{$row_no}" />
	</td>
	{if $IMAGE_EDITABLE}
		<td class="lineItemImage text-center">
			<img alt="{$data.$image}" src="{$data.$image}" height="42" width="42">
		</td>
	{/if}

	{if $PRODUCT_EDITABLE}
		<td>
			<!-- Product Re-Ordering Feature Code Addition Starts -->
			<input type="hidden" name="hidtax_row_no{$row_no}" id="hidtax_row_no{$row_no}" value="{$tax_row_no}"/>
			<!-- Product Re-Ordering Feature Code Addition ends -->
			<div class="itemNameDiv form-inline">
                <div class="input-group">
                    <input type="text" id="{$productName}" name="{$productName}" value="{$data.$productName}" class="productName form-control {if $row_no neq 0}autoComplete{/if}" placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}" data-rule-required=true {if !empty($data.$productName)}disabled="disabled"{/if}>
                    <input type="hidden" id="{$hdnProductId}" name="{$hdnProductId}" value="{$data.$hdnProductId}" class="selectedModuleId"/>
                    <input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="{$entityType}" class="lineItemType"/>
                    {if !$data.$productDeleted}
                        <span class="input-group-addon input-group-text cursorPointer clearLineItem" title="{vtranslate('LBL_CLEAR',$MODULE)}">
                            <i class="fa fa-times-circle"></i>
                        </span>
                    {/if}
                    {if $row_no eq 0}
                        <span class="input-group-text lineItemPopup cursorPointer" data-popup="ServicesPopup" title="{vtranslate('Services',$MODULE)}" data-module-name="Services" data-field-name="serviceid">{Vtiger_Module_Model::getModuleIconPath('Services')}</span>
                        <span class="input-group-text lineItemPopup cursorPointer" data-popup="ProductsPopup" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid">{Vtiger_Module_Model::getModuleIconPath('Products')}</span>
                    {elseif $entityType eq '' and $PRODUCT_ACTIVE eq 'true'}
                        <span class="input-group-text lineItemPopup cursorPointer" data-popup="ProductsPopup" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid">{Vtiger_Module_Model::getModuleIconPath('Products')}</span>
                    {elseif $entityType eq '' and $SERVICE_ACTIVE eq 'true'}
                        <span class="input-group-text lineItemPopup cursorPointer" data-popup="ServicesPopup" title="{vtranslate('Services',$MODULE)}" data-module-name="Services" data-field-name="serviceid">{Vtiger_Module_Model::getModuleIconPath('Services')}</span>
                    {else}
                        {if ($entityType eq 'Services') and (!$data.$productDeleted)}
                            <span class="input-group-text lineItemPopup cursorPointer" data-popup="ServicesPopup" title="{vtranslate('Services',$MODULE)}" data-module-name="Services" data-field-name="serviceid">{Vtiger_Module_Model::getModuleIconPath('Services')}</span>
                        {elseif (!$data.$productDeleted)}
                            <span class="input-group-text lineItemPopup cursorPointer" data-popup="ProductsPopup" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid">{Vtiger_Module_Model::getModuleIconPath('Products')}</span>
                        {/if}
                    {/if}
                </div>
			</div>
			<input type="hidden" value="{$data.$subproduct_ids}" id="{$subproduct_ids}" name="{$subproduct_ids}" class="subProductIds" />
			<div id="{$subprod_names}" name="{$subprod_names}" class="subInformation">
				<span class="subProductsContainer">
					{foreach key=SUB_PRODUCT_ID item=SUB_PRODUCT_INFO from=$data.$subprod_qty_list}
						<em> - {$SUB_PRODUCT_INFO.name} ({$SUB_PRODUCT_INFO.qty})
							{if $SUB_PRODUCT_INFO.qty > getProductQtyInStock($SUB_PRODUCT_ID)}
								&nbsp;-&nbsp;<span class="redColor">{vtranslate('LBL_STOCK_NOT_ENOUGH', $MODULE)}</span>
							{/if}
						</em><br>
					{/foreach}
				</span>
			</div>
			{if $data.$productDeleted}
				<div class="row-fluid deletedItem redColor">
					{if empty($data.$productName)}
						{vtranslate('LBL_THIS_LINE_ITEM_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_THIS_LINE_ITEM',$MODULE)}
					{else}
						{vtranslate('LBL_THIS',$MODULE)} {$entityType} {vtranslate('LBL_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_OR_REPLACE_THIS_ITEM',$MODULE)}
					{/if}
				</div>
			{else}
				{if $COMMENT_EDITABLE}
                    <div class="mt-3">
                        <textarea id="{$comment}" name="{$comment}" class="lineItemCommentBox form-control">{decode_html($data.$comment)}</textarea>
                    </div>
				{/if}
			{/if}
		</td>
	{/if}

	<td>
		<input id="{$qty}" name="{$qty}" type="text" class="qty smallInputBox inputElement form-control"
			   data-rule-required=true data-rule-positive=true data-rule-greater_than_zero=true value="{if !empty($data.$qty)}{$data.$qty}{else}1{/if}"
			   {if $QUANTITY_EDITABLE eq false} disabled=disabled {/if} />

		{if $PURCHASE_COST_EDITABLE eq false and $MODULE neq 'PurchaseOrder'}
			<input id="{$purchaseCost}" type="hidden" value="{if ((float)$data.$purchaseCost)}{((float)$data.$purchaseCost) / ((float)$data.$qty)}{else}0{/if}" />
            <span style="display:none" class="purchaseCost">0</span>
			<input name="{$purchaseCost}" type="hidden" value="{if $data.$purchaseCost}{$data.$purchaseCost}{else}0{/if}" />
		{/if}
		{if $MARGIN_EDITABLE eq false}
			<input type="hidden" name="{$margin}" value="{if $data.$margin}{$data.$margin}{else}0{/if}"></span>
			<span class="margin pull-right" style="display:none">{if $data.$margin}{$data.$margin}{else}0{/if}</span>
		{/if}
		{if $MODULE neq 'PurchaseOrder' &&  $MODULE neq 'Quotes'}
			<div class="mt-3 stockAlert text-danger {if $data.$qty <= $data.$qtyInStock}hide{/if}" >
				{vtranslate('LBL_STOCK_NOT_ENOUGH',$MODULE)}
				<br>
				{vtranslate('LBL_MAX_QTY_SELECT',$MODULE)}&nbsp;<span class="maxQuantity">{$data.$qtyInStock}</span>
			</div>
		{/if}
	</td>

	{if $PURCHASE_COST_EDITABLE}
		<td>
			<input id="{$purchaseCost}" type="hidden" value="{if $data.$purchaseCost}{((float)$data.$purchaseCost) / ((float)$data.$qty)}{else}0{/if}" />
			<input name="{$purchaseCost}" type="hidden" value="{if $data.$purchaseCost}{$data.$purchaseCost}{else}0{/if}" />
			<span class="pull-right purchaseCost">{if $data.$purchaseCost}{$data.$purchaseCost}{else}0{/if}</span>
		</td>
	{/if}

	{if $LIST_PRICE_EDITABLE}
		<td>
			<div class="input-group">
				<input id="{$listPrice}" name="{$listPrice}" value="{if !empty($data.$listPrice)}{$data.$listPrice}{else}0{/if}" type="text" data-rule-required=true data-rule-positive=true class="listPrice smallInputBox inputElement form-control" data-is-price-changed="{if $RECORD_ID && $row_no neq 0}true{else}false{/if}" list-info='{if isset($data.$listPrice)}{Zend_Json::encode($listPriceValues)}{/if}' data-base-currency-id="{getProductBaseCurrency($productId, {$entityType})}" />
				{assign var=PRICEBOOK_MODULE_MODEL value=Vtiger_Module_Model::getInstance('PriceBooks')}
				{if $PRICEBOOK_MODULE_MODEL->isPermitted('DetailView') && $MODULE != 'PurchaseOrder'}
					<span class="input-group-text priceBookPopup cursorPointer" data-popup="Popup"  title="{vtranslate('PriceBooks', $MODULE)}" data-module-name="PriceBooks">{Vtiger_Module_Model::getModuleIconPath('PriceBooks')}</span>
				{/if}
			</div>
			{if $ITEM_DISCOUNT_AMOUNT_EDITABLE || $ITEM_DISCOUNT_PERCENT_EDITABLE}
                <div class="mt-3 position-relative text-end">
                    <a href="javascript:void(0)" class="individualDiscount">
                        <strong class="me-2">{vtranslate('LBL_DISCOUNT',$MODULE)}</strong>
                        <span class="itemDiscount me-2">
                                {if $ITEM_DISCOUNT_PERCENT_EDITABLE && $data.$discount_type eq 'percentage'}
                                    ({$data.$discount_percent}%)
                                {elseif $ITEM_DISCOUNT_AMOUNT_EDITABLE && $data.$discount_type eq 'amount'}
                                    ({$data.$discount_amount})
                                {else}
                                    (0)
                                {/if}
                            </span>
                        <span>(-)</span>
                    </a>
                    <div class="discountUI validCheck hide" id="discount_div{$row_no}">
                        {assign var="DISCOUNT_TYPE" value="zero"}
                        {if !empty($data.$discount_type)}
                            {assign var="DISCOUNT_TYPE" value=$data.$discount_type}
                        {/if}
                        <input type="hidden" id="discount_type{$row_no}" name="discount_type{$row_no}" value="{$DISCOUNT_TYPE}" class="discount_type"/>
                        <p class="popover_title hide">
                            {vtranslate('LBL_SET_DISCOUNT_FOR',$MODULE)} : <span class="variable">{$data.$productTotal}</span>
                        </p>
                        <table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable">
                            <!-- TODO : discount price and amount are hide by default we need to check id they are already selected if so we should not hide them  -->
                            <tr>
                                <td>
                                    <label>
                                        <input type="radio" name="discount{$row_no}" {$data.$checked_discount_zero} {if empty($data.$discount_type)}checked{/if} class="discounts me-2" data-discount-type="zero"/>
                                        <span>{vtranslate('LBL_ZERO_DISCOUNT',$MODULE)}</span>
                                    </label>
                                </td>
                                <td>
                                    <!-- Make the discount value as zero -->
                                    <input type="hidden" class="discountVal" value="0"/>
                                </td>
                            </tr>
                            {if $ITEM_DISCOUNT_PERCENT_EDITABLE}
                                <tr>
                                    <td>
                                        <label>
                                            <input type="radio" name="discount{$row_no}" {$data.$checked_discount_percent} class="discounts me-2" data-discount-type="percentage"/>
                                            <span class="me-2">%</span>
                                            <span>{vtranslate('LBL_OF_PRICE',$MODULE)}</span>
                                        </label>
                                    </td>
                                    <td>
                                        <input type="text" data-rule-positive=true data-rule-inventory_percentage=true id="discount_percentage{$row_no}" name="discount_percentage{$row_no}" value="{$data.$discount_percent}" class="discount_percentage form-control discountVal {if empty($data.$checked_discount_percent)}hide{/if}"/>
                                    </td>
                                </tr>
                            {/if}
                            {if $ITEM_DISCOUNT_AMOUNT_EDITABLE}
                                <tr>
                                    <td class="LineItemDirectPriceReduction">
                                        <label>
                                            <input type="radio" name="discount{$row_no}" {$data.$checked_discount_amount} class="discounts me-2" data-discount-type="amount"/>
                                            <span>{vtranslate('LBL_DIRECT_PRICE_REDUCTION',$MODULE)}</span>
                                        </label>
                                    </td>
                                    <td>
                                        <input type="text" data-rule-positive=true id="discount_amount{$row_no}" name="discount_amount{$row_no}" value="{$data.$discount_amount}" class="form-control discount_amount discountVal {if empty($data.$checked_discount_amount)}hide{/if}"/>
                                    </td>
                                </tr>
                            {/if}
                        </table>
                    </div>
                </div>
                <div class="text-end">
                    <strong>{vtranslate('LBL_TOTAL_AFTER_DISCOUNT',$MODULE)}</strong>
                </div>
			{/if}
            <div class="position-relative text-end">
                <div class="individualTaxContainer {if $IS_GROUP_TAX_TYPE}opacity-0{/if}">
                    <a href="javascript:void(0)" class="individualTax">
                        <strong class="me-2">{vtranslate('LBL_TAX',$MODULE)}</strong>
                        <span>(+)</span>
                    </a>
                </div>
                <div class="taxUI hide" id="tax_div{$row_no}">
                    <p class="popover_title hide">
                        {vtranslate('LBL_SET_TAX_FOR',$MODULE)} : <span class="variable">{$data.$totalAfterDiscount}</span>
                    </p>
                    {if $data.taxes && php7_count($data.taxes) > 0}
                        <div class="individualTaxDiv">
                            <!-- we will form the table with all taxes -->
                            <table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable" id="tax_table{$row_no}">
                                {foreach key=tax_row_no item=tax_data from=$data.taxes}
                                    {assign var="taxname" value=$tax_data.taxname|cat:"_percentage"|cat:$row_no}
                                    {assign var="tax_id_name" value="hidden_tax"|cat:$tax_row_no+1|cat:"_percentage"|cat:$row_no}
                                    {assign var="taxlabel" value=$tax_data.taxlabel|cat:"_percentage"|cat:$row_no}
                                    {assign var="popup_tax_rowname" value="popup_tax_row"|cat:$row_no}
                                    <tr>
                                        <td>&nbsp;&nbsp;{$tax_data.taxlabel}</td>
                                        <td class="text-end">
                                            <div class="input-group">
                                                <input type="text" data-rule-positive=true data-rule-inventory_percentage=true name="{$taxname}" id="{$taxname}" value="{$tax_data.percentage}" data-compound-on="{if $tax_data.method eq 'Compound'}{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($tax_data.compoundon))}{/if}" data-regions-list="{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($tax_data.regionsList))}" class="form-control taxPercentage"/>
                                                <div class="input-group-text">%</div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <input type="text" name="{$popup_tax_rowname}" class="cursorPointer form-control taxTotal taxTotal{$tax_data.taxid}" value="{$tax_data.amount}" readonly/>
                                        </td>
                                    </tr>
                                {/foreach}
                            </table>
                        </div>
                    {/if}
                </div>
            </div>
		</td>
	{/if}

	<td>
		<div id="productTotal{$row_no}" class="productTotal py-2 text-end">{if $data.$productTotal}{$data.$productTotal}{else}0{/if}</div>
		{if $ITEM_DISCOUNT_AMOUNT_EDITABLE || $ITEM_DISCOUNT_PERCENT_EDITABLE}
			<div id="discountTotal{$row_no}" class="discountTotal text-end mt-3">{if $data.$discountTotal}{$data.$discountTotal}{else}0{/if}</div>
			<div id="totalAfterDiscount{$row_no}" class="totalAfterDiscount text-end">{if $data.$totalAfterDiscount}{$data.$totalAfterDiscount}{else}0{/if}</div>
		{/if}

		<div id="taxTotal{$row_no}" class="productTaxTotal text-end {if $IS_GROUP_TAX_TYPE}hide{/if}">{if $data.$taxTotal}{$data.$taxTotal}{else}0{/if}</div>
	</td>

	{if $MARGIN_EDITABLE && $PURCHASE_COST_EDITABLE}
		<td>
			<input type="hidden" name="{$margin}" value="{if $data.$margin}{$data.$margin}{else}0{/if}"></span>
		<span class="margin pull-right">{if $data.$margin}{$data.$margin}{else}0{/if}</span>
	</td>
	{/if}

	<td>
		<span id="netPrice{$row_no}" class="pull-right netPrice">{if $data.$netPrice}{$data.$netPrice}{else}0{/if}</span>
	</td>
{/strip}