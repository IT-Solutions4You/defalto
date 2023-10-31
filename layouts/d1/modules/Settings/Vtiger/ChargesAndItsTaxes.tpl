{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/Vtiger/views/TaxIndex.php *}
{strip}
<div class="chargesContainer">
	{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
	{assign var=CREATE_TAX_URL value=$TAX_RECORD_MODEL->getCreateTaxUrl()}
	<div>
		<div class="p-3">
			<button type="button" class="btn btn-outline-secondary addCharge addButton module-buttons" data-url="{Inventory_Charges_Model::getCreateChargeUrl()}" data-type="1">
                <i class="fa fa-plus"></i>
				<span class="ms-2">{vtranslate('LBL_ADD_NEW_CHARGE', $QUALIFIED_MODULE)}</span>
			</button>
		</div>
		<table class="table table-borderless inventoryChargesTable">
			<tr>
				<th class="bg-body-secondary {$WIDTHTYPE}">
					<strong>{vtranslate('LBL_CHARGE_NAME', $QUALIFIED_MODULE)}</strong>
				</th>
				<th class="bg-body-secondary {$WIDTHTYPE}">
					<strong>{vtranslate('LBL_VALUE', $QUALIFIED_MODULE)}</strong>
				</th>
				<th class="bg-body-secondary {$WIDTHTYPE}">
					<strong>{vtranslate('LBL_IS_TAXABLE', $QUALIFIED_MODULE)}</strong>
				</th>
				<th class="bg-body-secondary {$WIDTHTYPE}" colspan="2">
					<strong>{vtranslate('LBL_TAXES', $QUALIFIED_MODULE)}</strong>
				</th>
			</tr>
			{foreach item=CHARGE_MODEL from=$CHARGE_MODELS_LIST}
				<tr class="opacity border-bottom" data-charge-id="{$CHARGE_MODEL->getId()}">
					<td class="{$WIDTHTYPE}">
						<span class="chargeName">{$CHARGE_MODEL->getName()}</span>
					</td>
					<td class="{$WIDTHTYPE}">
						<span class="chargeValue">{$CHARGE_MODEL->getDisplayValue()}</span>
					</td>
					<td class="{$WIDTHTYPE}">
						<span class="chargeIsTaxable">{if $CHARGE_MODEL->isTaxable()}{vtranslate('LBL_YES', $QUALIFIED_MODULE)}{else}{vtranslate('LBL_NO', $QUALIFIED_MODULE)}{/if}</span>
					</td>
					<td class="{$WIDTHTYPE}">
						<span class="chargeTaxes">
							{assign var=TAXES value=''}
							{foreach item=TAX_MODEL from=$CHARGE_MODEL->getSelectedTaxes()}
								{assign var=TAXES value="{$TAXES}, {$TAX_MODEL->getName()}"}
							{/foreach}
							{trim($TAXES, ', ')}
						</span>
					</td>
					<td class="{$WIDTHTYPE}">
						<div class="actions text-end">
							<a class="editCharge cursorPointer" data-url="{$CHARGE_MODEL->getEditChargeUrl()}">
								<i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil"></i>
							</a>
						</div>
					</td>
				</tr>
			{/foreach}
		</table>
	</div>

	<div>
		<div class="p-3">
			<button type="button" class="btn btn-outline-secondary addChargeTax addButton module-buttons" data-url="{$CREATE_TAX_URL}" data-type="1">
                <i class="fa fa-plus"></i>
				<span class="ms-2">{vtranslate('LBL_ADD_NEW_TAX_FOR_CHARGE', $QUALIFIED_MODULE)}</span>
			</button>
		</div>
		<table class="table table-borderless shippingTaxTable">
			<tr>
				<th class="bg-body-secondary {$WIDTHTYPE}">
					<strong>{vtranslate('LBL_TAX_NAME', $QUALIFIED_MODULE)}</strong></th>
				<th class="bg-body-secondary {$WIDTHTYPE}"><strong>{vtranslate('LBL_TYPE', $QUALIFIED_MODULE)}</strong>
				</th>
				<th class="bg-body-secondary {$WIDTHTYPE}">
					<strong>{vtranslate('LBL_CALCULATION', $QUALIFIED_MODULE)}</strong>
				</th>
				<th class="bg-body-secondary {$WIDTHTYPE}">
					<strong>{vtranslate('LBL_TAX_VALUE', $QUALIFIED_MODULE)}</strong>
				</th>
				<th class="bg-body-secondary {$WIDTHTYPE}" colspan="2">
					<strong>{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</strong>
				</th>
			</tr>
			{foreach item=CHARGE_TAX_MODEL from=$CHARGE_TAXES}
				<tr class="opacity border-bottom" data-taxid="{$CHARGE_TAX_MODEL->get('taxid')}" data-taxtype="{$CHARGE_TAX_MODEL->getType()}">
					<td class="{$WIDTHTYPE}">
						<span class="taxLabel" style="width:150px">{$CHARGE_TAX_MODEL->getName()}</span>
					</td>
					<td class="{$WIDTHTYPE}">
						<span class="taxType">{$CHARGE_TAX_MODEL->getTaxType()}</span>
					</td>
					<td class="{$WIDTHTYPE}">
						<span class="taxMethod">{$CHARGE_TAX_MODEL->getTaxMethod()}</span>
					</td>
					<td class="{$WIDTHTYPE}">
						<span class="taxPercentage">{$CHARGE_TAX_MODEL->getTax()}%</span>
					</td>
					<td class="{$WIDTHTYPE}">
						<input type="checkbox" class="editTaxStatus form-check-input" {if !$CHARGE_TAX_MODEL->isDeleted()}checked{/if} />
					</td>
					<td class="{$WIDTHTYPE}">
						<div class="text-end actions">
							<a class="btn editChargeTax cursorPointer" data-url="{$CHARGE_TAX_MODEL->getEditTaxUrl()}">
								<i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil"></i>
							</a>
						</div>
					</td>
				</tr>
			{/foreach}
		</table>
	</div>
</div>
{/strip}
