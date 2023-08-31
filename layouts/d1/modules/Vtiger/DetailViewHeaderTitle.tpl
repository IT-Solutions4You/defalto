{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="col-12">
		<div class="clearfix record-header">
			<div class="recordBasicInfo">
				<div class="row">
					<div class="col-lg-auto">
						<span class="fs-3 recordLabel pushDown" title="{$RECORD->getName()}">
							{foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
								{assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
								{if $FIELD_MODEL->getPermissions()}
									<span class="{$NAME_FIELD}">{trim($RECORD->get($NAME_FIELD))}</span>
									&nbsp;
								{/if}
							{/foreach}
						</span>
					</div>
					<div class="col-auto">
						{include file='DetailViewTagList.tpl'|vtemplate_path:$MODULE}
					</div>
					<div class="col">
						{include file='DetailViewHeaderPagination.tpl'|vtemplate_path:$MODULE}
					</div>
				</div>
				{include file='DetailViewHeaderFieldsView.tpl'|vtemplate_path:$MODULE}
			</div>
		</div>
	</div>
{/strip}