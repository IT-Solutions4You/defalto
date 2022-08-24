{* /**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */ *}
{strip}
<div class="modal-dialog modelContainer">
	<div class="modal-content" style="width:675px;">
	{assign var=HEADER_TITLE value={vtranslate('LBL_PRODUCT_BREAKLINE', {$MODULE_NAME})}}
	{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
	<div class="modal-body">
		<div class="container-fluid">
			<div>
				<form id="SavePDFBreaklineForm" class="form-horizontal" name="upload" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE_NAME}" />
					<input type="hidden" name="action" value="SaveAjax" />
					<input type="hidden" name="mode" value="SavePDFBreakline" />
					<input type="hidden" name="return_id" value="{$RECORD}" />
					<h4 class="fieldBlockHeader">{vtranslate('LBL_GLOBAL_SETTINGS', $MODULE_NAME)}</h4>
					<table class="table no-border">
						<tr>
							<td class="" style="width: 1%">
							<input type="checkbox" class="settingsCheckbox" name="show_header" value="1" {$HEADER_CHECKED}/>
							</td>
							<td class="">
								{vtranslate('LBL_SHOW_HEADER', $MODULE_NAME)}
							</td>
						</tr>
						<tr>
							<td class="lineItemFieldName" style="width: 1%">
							<input type="checkbox" class="settingsCheckbox" name="show_subtotal" value="1" {$SUBTOTAL_CHECKED}/>
							</td>
							<td class="lineItemFieldName">
							{vtranslate('LBL_SHOW_SUBTOTAL', $MODULE_NAME)}
							</td>
						</tr>
					</table>

					<h4 class="fieldBlockHeader">{vtranslate('LBL_ITEM_DETAILS', $MODULE_NAME)}</h4>

					<table class="table table-bordered lineItemsTable" style = "margin-top:15px">
						<thead>
						<th class="lineItemBlockHeader">
						</th>
						<th class="lineItemBlockHeader">
                            {vtranslate('LBL_ITEM_NAME', $MODULE_NAME)}
						</th>
						</thead>
						<tbody>
                        {foreach  item=PRODUCT from=$PRODUCTS}
							<tr>
								<td class="lineItemFieldName" style="width: 1%">
									<input type="checkbox" class="LineItemCheckbox" value="1" name="ItemPageBreak_{$PRODUCT.uid}" {if $PRODUCT.checked eq "yes"}checked{/if}/>
								</td>
								<td class="lineItemFieldName">
									{$PRODUCT.name}
								</td>
							</tr>
    					{/foreach}
						</tbody>
					</table>
				</form>
			</div>
		</div>
	</div>
	{assign var=BUTTON_NAME value={vtranslate('LBL_SAVE', $MODULE)}}
	{assign var=BUTTON_ID value="js-save-button"}
	{include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
	</div>
</div>
{/strip}
