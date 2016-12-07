{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="col-sm-12 col-xs-12">
		<div class="container-fluid" id="AsteriskServerDetails">
			<div class="widget_header row">
				<div class="col-sm-8"><h3>{vtranslate('LBL_PBXMANAGER', $QUALIFIED_MODULE)}</h3></div>
				{assign var=MODULE_MODEL value=Settings_PBXManager_Module_Model::getCleanInstance()}
				<div class="col-sm-4"><div class="pull-right"><button class="btn editButton" data-url='{$MODULE_MODEL->getEditViewUrl()}&mode=showpopup&id={$RECORD_ID}' type="button" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"><strong>{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}</strong></button></div></div>
			</div>
			<hr>
			<div class="contents row">
				<div class="detailViewInfo">
					{assign var=FIELDS value=PBXManager_PBXManager_Connector::getSettingsParameters()}
						{foreach item=FIELD_TYPE key=FIELD_NAME from=$FIELDS}
							<div class="row form-group">
								<div class="col-lg-4 col-md-4 col-sm-4 fieldLabel">
									<label>{vtranslate($FIELD_NAME,$QUALIFIED_MODULE)}</label>
								</div>
								<div class="col-lg-8 col-md-8 col-sm-8 fieldValue break-word">
									<div>{$RECORD_MODEL->get($FIELD_NAME)}</div>
								</div>
							</div>
						{/foreach}
					<input type="hidden" name="module" value="PBXManager"/>
					<input type="hidden" name="action" value="SaveAjax"/>
					<input type="hidden" name="parent" value="Settings"/>
					<input type="hidden" class="recordid" name="id" value="{$RECORD_ID}">
				</div>
			</div>
		</div>
		<br>
		<div class="span8 alert alert-danger container-fluid">
			{vtranslate('LBL_NOTE', $QUALIFIED_MODULE)}<br>
			{vtranslate('LBL_PBXMANAGER_INFO', $QUALIFIED_MODULE)}
		</div>
	</div>
{/strip}