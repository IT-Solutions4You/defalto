{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<div>
{if php7_count($MODELS) > 0}
	<div class="container-fluid">
        <div class="row p-2 bg-body-secondary text-secondary">
            <div class="col-lg-4">
                <b>{vtranslate('Potential Name', $MODULE_NAME)}</b>
            </div>
            <div class="col-lg-4">
                <b>{vtranslate('Amount', $MODULE_NAME)}</b>
            </div>
            <div class="col-lg-4">
                <b>{vtranslate('Related To', $MODULE_NAME)}</b>
            </div>
        </div>
		{foreach item=MODEL from=$MODELS}
		<div class="row border-bottom p-2">
			<div class="col-lg-4">
				<a href="{$MODEL->getDetailViewUrl()}">{$MODEL->getName()}</a>
			</div>
			<div class="col-lg-4">
				{CurrencyField::appendCurrencySymbol($MODEL->getDisplayValue('amount'), $USER_CURRENCY_SYMBOL)}
			</div>
			<div class="col-lg-4">
				{$MODEL->getDisplayValue('related_to')}
			</div>
		</div>
		{/foreach}
	</div>
{else}
	<span class="noDataMsg">
		{vtranslate('LBL_NO')} {vtranslate($MODULE_NAME, $MODULE_NAME)} {vtranslate('LBL_MATCHED_THIS_CRITERIA')}
	</span>
{/if}
</div>