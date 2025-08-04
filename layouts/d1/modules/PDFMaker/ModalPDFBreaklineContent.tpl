{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
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
					<h4 class="fieldBlockHeader pt-3">{vtranslate('LBL_GLOBAL_SETTINGS', $MODULE_NAME)}</h4>
					<label class="form-check py-2">
						<input type="checkbox" class="settingsCheckbox form-check-input" name="show_header" value="1" {$HEADER_CHECKED}/>
						<span class="form-check-label">{vtranslate('LBL_SHOW_HEADER', $MODULE_NAME)}</span>
					</label>
					<label class="form-check py-2">
						<input type="checkbox" class="settingsCheckbox form-check-input" name="show_subtotal" value="1" {$SUBTOTAL_CHECKED}/>
						<span class="form-check-label">{vtranslate('LBL_SHOW_SUBTOTAL', $MODULE_NAME)}</span>
					</label>
					<h4 class="fieldBlockHeader pt-3">{vtranslate('LBL_ITEM_DETAILS', $MODULE_NAME)}</h4>
					{foreach item=PRODUCT from=$PRODUCTS}
						<label class="form-check py-2">
							<input type="checkbox" class="LineItemCheckbox form-check-input" value="1" name="ItemPageBreak_{$PRODUCT.uid}" {if $PRODUCT.checked eq "yes"}checked{/if}/>
							<span class="form-check-label">{$PRODUCT.name}</span>
						</label>
					{/foreach}
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
