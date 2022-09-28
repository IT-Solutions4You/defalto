{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<div style='padding:5px'>
{if php7_count($MODELS) > 0}
<div class='row-fluid'>
		<div class='span12'>
			<div class='row-fluid'>
				 {foreach item=HEADER from=$MODULE_HEADER}
                <div class='span4'>
					<b>{vtranslate({$HEADER}, $MODULE_NAME)}</b>
				</div>
                 {/foreach}
			</div>
		</div>
		<hr>
       {foreach item=MODEL from=$MODELS}
		    <div class='row-fluid'>
               {foreach item=HEADER key=KEY_VALUE from=$MODULE_HEADER}
                <div class='span4'>
			   {if $KEY_VALUE eq 'potentialname'}
                    <a href="{$MODEL->getDetailViewUrl()}">{$MODEL->getName()}</a>
               {else}  
                   {$MODEL->getDisplayValue({$KEY_VALUE})}
                 {/if}
                </div>
                 {/foreach}
                         
			</div>
		</div>
		{/foreach}
	</div>
{else}
	<span class="noDataMsg">
		{vtranslate('LBL_EQ_ZERO')} {vtranslate($MODULE_NAME, $MODULE_NAME)} {vtranslate('LBL_MATCHED_THIS_CRITERIA')}
	</span>
{/if}
</div>
