{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div>
    <input type="hidden" id="widget_{$WIDGET->get('id')}_currentPage" value="{$CURRENT_PAGE}">
	{* Comupte the nubmer of columns required *}
	{assign var="SPANSIZE" value=12}
	{assign var=HEADER_COUNT value=$MINILIST_WIDGET_MODEL->getHeaderCount()}
	{if $HEADER_COUNT}
		{assign var="SPANSIZE" value=12/$HEADER_COUNT}
	{/if}

	<div class="container-fluid">
		<div class="row p-2 bg-body-secondary text-secondary">
			{assign var=HEADER_FIELDS value=$MINILIST_WIDGET_MODEL->getHeaders()}
			{foreach item=FIELD from=$HEADER_FIELDS}
				<div class="col-lg-{$SPANSIZE}"><strong>{vtranslate($FIELD->get('label'),$BASE_MODULE)}</strong></div>
			{/foreach}
		</div>
		{assign var="MINILIST_WIDGET_RECORDS" value=$MINILIST_WIDGET_MODEL->getRecords()}
		{foreach item=RECORD from=$MINILIST_WIDGET_RECORDS}
			<div class="row miniListContent p-2 border-bottom">
				{foreach item=FIELD key=NAME from=$HEADER_FIELDS name="minilistWidgetModelRowHeaders"}
					<div class="col-lg-{$SPANSIZE} text-truncate" title="{strip_tags($RECORD->get($NAME))}" style="padding-right: 5px;">
					   {if $FIELD->getFieldDataType() eq 'currency'}
							{assign var=CURRENCY_ID value=$RECORD->getCurrencyId()}
							{assign var=CURRENCY_INFO value=getCurrencySymbolandCRate($CURRENCY_ID)}
							{if !$RECORD->isEmpty($NAME)}
								{CurrencyField::appendCurrencySymbol($RECORD->get($NAME), $CURRENCY_INFO['symbol'])}
							{/if}
						{else}
							{$RECORD->get($NAME)}
						{/if}
						{if $smarty.foreach.minilistWidgetModelRowHeaders.last}
							<a href="{$RECORD->getDetailViewUrl()}" class="pull-right"><i title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS',$MODULE_NAME)}" class="fa fa-list"></i></a>
						{/if}
					</div>
				{/foreach}
			</div>
		{/foreach}
		{if $MORE_EXISTS}
			<div class="moreLinkDiv" style="padding-top:10px;padding-bottom:5px;">
				<a class="miniListMoreLink" data-linkid="{$WIDGET->get('linkid')}" data-widgetid="{$WIDGET->get('id')}" onclick="Vtiger_MiniList_Widget_Js.registerMoreClickEvent(event);">{vtranslate('LBL_MORE')}...</a>
			</div>
		{/if}
	</div>
</div>