{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Products/views/MoreCurrenciesList.php *}

<div id="currency_class" class="multiCurrencyEditUI modelContainer">
	<div class = "modal-dialog modal-lg">
		<div class = "modal-content">
			{assign var=TITLE value=vtranslate('LBL_PRICES',$MODULE)}
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
			<div class="multiCurrencyContainer">
				<div class = "currencyContent">
					<div class = "modal-body">
						<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table listViewEntriesTable">
							<thead class="detailedViewHeader">
								<tr>
									<th class="text-secondary">{vtranslate('LBL_CURRENCY',$MODULE)}</th>
									<th class="text-secondary">{vtranslate('LBL_PRICE',$MODULE)}</th>
									<th class="text-secondary">{vtranslate('LBL_CONVERSION_RATE', 'Products')}</th>
									<th class="text-secondary">{vtranslate('LBL_RESET_PRICE',$MODULE)}</th>
									<th class="text-secondary">{vtranslate('LBL_BASE_CURRENCY',$MODULE)}</th>
								</tr>
							</thead>
							{foreach item=price key=count from=$PRICE_DETAILS}
								<tr data-currency-id={$price.curname}>
									<td class="verticalAlignMiddle">
										<div class="row">
											<div class="col-lg-10 currencyInfo text-start">
												<span class="currencyName" >{$price.currencylabel|@getTranslatedCurrencyString} (<span class="currencySymbol">{$price.currencysymbol}</span>)</span>
											</div>
											<div class="col-lg-2 text-end">
												<span>
                                                    <input type="hidden" name="cur_{$price.curid}_check" value="off"/>
													<input type="checkbox" name="cur_{$price.curid}_check" value="on" id="cur_{$price.curid}_check" class="form-check-input enableCurrency" {if $price.check_value eq 1 || $price.is_basecurrency eq 1}checked="checked"{/if}>
												</span>
											</div>
										</div>
									</td>
									<td>
										<div>
											<input type="text" size="10" class="col-lg-9 form-control convertedPrice replaceCommaWithDot" data-rule-currency ="true" name="{$price.curname}" id="{$price.curname}" value="{$price.curvalue}" data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' />
										</div>
									</td>
									<td>
										<div>
											<input readonly="" type="text" size="10" class="col-lg-9 form-control conversionRate" name="cur_conv_rate{$price.curid}" value="{$price.conversionrate}">
										</div>
									</td>
									<td>
										<div class = "textAlignCenter">
											<button type="button" class="btn btn-outline-secondary currencyReset" id="cur_reset{$price.curid}" value="{vtranslate('LBL_RESET',$MODULE)}">
												<i class="fa fa-refresh"></i>
												<span class="ms-2">{vtranslate('LBL_RESET',$MODULE)}</span>
											</button>
										</div>
									</td>
									<td class="verticalAlignMiddle">
										<div class="textAlignCenter">
											<input type="radio" class="baseCurrency form-check-input" id="base_currency{$price.curid}" name="base_currency_input" value="{$price.curname}" {if $price.is_basecurrency eq 1}checked="checked"{/if} />
										</div>
									</td>
								</tr>
							{/foreach}
						</table>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</div>
		</div>
	</div>
</div>