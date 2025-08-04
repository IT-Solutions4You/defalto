{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/Vtiger/views/TaxIndex.php *}
{strip}
<div class="taxRegionsContainer">
	<div class="tab-pane active">
		<div class="tab-content overflowVisible">
			{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
			<div>
				<div class="p-3">
					<button type="button" class="btn btn-outline-secondary addRegion addButton module-buttons" data-url="?module=Vtiger&parent=Settings&view=TaxAjax&mode=editTaxRegion" data-type="1">
                        <i class="fa fa-plus"></i>
						<span class="ms-2">{vtranslate('LBL_ADD_NEW_REGION', $QUALIFIED_MODULE)}</span>
					</button>
				</div>
				<table class="table table-borderless taxRegionsTable" style="table-layout: fixed">
					<tr>
						<th class="bg-body-secondary {$WIDTHTYPE}" colspan="2">
                            <strong>{vtranslate('LBL_AVAILABLE_REGIONS', $QUALIFIED_MODULE)}</strong>
						</th>
					<tr>

					{foreach item=TAX_REGION_MODEL from=$TAX_REGIONS}
						{assign var=TAX_REGION_NAME value=$TAX_REGION_MODEL->getName()}
						<tr class="opacity border-bottom" data-key-name="{$TAX_REGION_NAME}" data-key="{$TAX_REGION_NAME}">
							<td class="{$WIDTHTYPE}">
								<span class="taxRegionName">{$TAX_REGION_NAME}</span>
							</td>
							<td class="{$WIDTHTYPE}">
								<div class="text-end actions">
									<a class="btn editRegion" data-url='{$TAX_REGION_MODEL->getEditRegionUrl()}'>
										<i title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}" class="fa fa-pencil alignMiddle"></i>
									</a>
									<a class="btn deleteRegion" data-url='{$TAX_REGION_MODEL->getDeleteRegionUrl()}'>
										<i title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}" class="fa fa-trash alignMiddle"></i>
									</a>
								</div>
							</td>
						</tr>
					{/foreach}
				</table>
			</div>
			<div>
				<div class="p-3">
					<i class="fa fa-info-circle"></i>
					<span class="ms-2">{vtranslate('LBL_TAX_REGION_DESC', $QUALIFIED_MODULE)}</span>
				</div>
			</div>
		</div>
	</div>
</div>
{/strip}