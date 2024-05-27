{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	{if empty($CRUMB_NAME)}
		{assign var=CRUMB_NAME value='crumb'}
	{/if}
	<div id="{$BREADCRUMB_ID}" class="breadcrumb">
		<ul class="crumbs p-0 m-0 d-flex">
			{assign var=ZINDEX value=9}
			{foreach key=CRUMBID item=STEPTEXT from=$BREADCRUMB_LABELS name=breadcrumbLabels}
				{assign var=INDEX value=$smarty.foreach.breadcrumbLabels.index}
				{assign var=INDEX value=$INDEX+1}
				<li class="step {if $smarty.foreach.breadcrumbLabels.first} first {if isset($FIRSTBREADCRUMB)} {$FIRSTBREADCRUMB} {/if} {else} {if isset($ADDTIONALCLASS)} {$ADDTIONALCLASS} {/if}{/if} {if $smarty.foreach.breadcrumbLabels.last} last {/if} {if $ACTIVESTEP eq $INDEX}active{/if}"
					id="{$CRUMBID}" data-value="{$INDEX}" style="z-index:{$ZINDEX}">
					<a href="#" class="h-100 d-flex align-items-center">
						<div class="fw-bold stepNum px-3 fs-5">{$INDEX}.</div>
						<div class="stepText fs-6 text-nowrap" title="{vtranslate($STEPTEXT,$MODULE)}">{vtranslate($STEPTEXT,$MODULE)}</div>
					</a>
				</li>
				{assign var=ZINDEX value=$ZINDEX-1}
			{/foreach}
		</ul>
	</div>
{/strip}