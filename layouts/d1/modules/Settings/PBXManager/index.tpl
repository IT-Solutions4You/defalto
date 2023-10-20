{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="px-4 pb-4">
		<div class="rounded bg-body">
			<div class="container-fluid" id="AsteriskServerDetails">
				<input type="hidden" name="module" value="PBXManager"/>
				<input type="hidden" name="action" value="SaveAjax"/>
				<input type="hidden" name="parent" value="Settings"/>
				<input type="hidden" class="recordid" name="id" value="{$RECORD_ID}">
				{assign var=MODULE_MODEL value=Settings_PBXManager_Module_Model::getCleanInstance()}
				<div class="widget_header row py-3 border-bottom">
					<div class="col-sm">
						<h4>{vtranslate('LBL_PBXMANAGER', $QUALIFIED_MODULE)}</h4>
					</div>
					<div class="col-sm-auto">
						<div class="btn-group editbutton-container">
							<button class="btn btn-outline-secondary editButton" data-url="{$MODULE_MODEL->getEditViewUrl()}&mode=showpopup&id={$RECORD_ID}" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}">{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}</button>
						</div>
					</div>
				</div>
				<div class="contents row">
					<div class="col-12 py-3">
						<div class="container-fluid">
							<div class="row">
								{assign var=FIELDS value=PBXManager_PBXManager_Connector::getSettingsParameters()}
								{foreach item=FIELD_TYPE key=FIELD_NAME from=$FIELDS}
									<div class="row py-2">
										<div class="col-lg-3 fieldLabel"><label>{vtranslate($FIELD_NAME, $QUALIFIED_MODULE)}</label></div>
										<div class="col-lg-4">{$RECORD_MODEL->get($FIELD_NAME)}</div>
									</div>
								{/foreach}
								<div class="row py-2">
									<div class="col-lg-3 fieldLabel"></div>
									<div class="col-lg-4">
										<div class="alert alert-danger">
											<b>{vtranslate('LBL_NOTE', $QUALIFIED_MODULE)}</b>
											<span class="ms-2">{vtranslate('LBL_PBXMANAGER_INFO', $QUALIFIED_MODULE)}</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}