{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="record-header">
		<div class="recordBasicInfo">
			<div class="recordHeaderTitle">
				<span class="fs-3 recordLabel pushDown" title="{$RECORD->getName()}">
					{foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
						{assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
						{if $FIELD_MODEL->getPermissions()}
							<span class="me-2 {$NAME_FIELD}">{trim($RECORD->get($NAME_FIELD))}</span>
						{/if}
					{/foreach}
				</span>
			</div>
		</div>
	</div>
{/strip}