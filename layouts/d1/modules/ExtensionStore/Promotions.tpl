{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	{foreach $HEADER_SCRIPTS as $SCRIPT}
		<script type="{$SCRIPT->getType()}" src="{$SCRIPT->getSrc()}" />
	{/foreach}
	<div class="banner-container" style="margin: 0px 10px;">
		<div class="row"></div>
		<div class="banner">
			<ul class="bxslider">
				{foreach $PROMOTIONS as $PROMOTION}
					{if is_object($PROMOTION)}
					<li>
						{assign var=SUMMARY value=$PROMOTION->get('summary')}
						{assign var=EXTENSION_NAME value=$PROMOTION->get('label')}
						{if is_numeric($SUMMARY)}
							{assign var=LOCATION_URL value=$PROMOTION->getLocationUrl($SUMMARY, $EXTENSION_NAME)}
						{else}
							{assign var=LOCATION_URL value={$SUMMARY}}
						{/if}
						<a onclick="window.open('{$LOCATION_URL}')"><img src="{if $PROMOTION->get('bannerURL')}{$PROMOTION->get('bannerURL')}{/if}" title="{$PROMOTION->get('label')}" /></a>
					</li>
					{/if}
				{/foreach}
			</ul>
		</div>
	</div>
{/strip}
