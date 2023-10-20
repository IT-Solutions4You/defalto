{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="listViewPageDiv px-4 pb-4" id="listViewContent">
		<div class="bg-body rounded">
			<form id="customerPortalForm" name="customerPortalForm" action="index.php" method="POST" class="form-horizontal">
				<input type="hidden" name="portalModulesInfo" value="" />
				<div class="container-fluid py-3">
					<div class="form-group row">
						<label for="defaultAssignee" class="col-sm-4 control-label fieldLabel">
							<span>{vtranslate('LBL_DEFAULT_ASSIGNEE', $QUALIFIED_MODULE)}</span>
						</label>
						<div class="fieldValue col-sm-4">
							<div class="input-group">
								<select name="defaultAssignee" class="select2 inputElement form-select">
									<optgroup label="{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}" >
										{foreach item=USER_MODEL from=$USER_MODELS}
											{assign var=USER_ID value=$USER_MODEL->getId()}
											<option value="{$USER_ID}" {if $CURRENT_DEFAULT_ASSIGNEE eq $USER_ID} selected {/if}>{$USER_MODEL->getName()}</option>
										{/foreach}
									</optgroup>
									<optgroup label="{vtranslate('LBL_GROUPS', $QUALIFIED_MODULE)}">
										{foreach item=GROUP_MODEL from=$GROUP_MODELS}
											{assign var=GROUP_ID value=$GROUP_MODEL->getId()}
											<option value="{$GROUP_ID}" {if $CURRENT_DEFAULT_ASSIGNEE eq $GROUP_ID} selected {/if}>{$GROUP_MODEL->getName()}</option>
										{/foreach}
									</optgroup>
								</select>
								<div class="input-group-addon input-select-addon input-group-text" data-bs-toggle="tooltip" title="{vtranslate('LBL_DEFAULT_ASSIGNEE_MESSAGE', $QUALIFIED_MODULE)}">
									<i class="fa fa-info-circle"></i>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="container-fluid py-3">
					<div class="form-group row">
						<label for="portal-url" class="col-sm-4 control-label fieldLabel">
							<span>{vtranslate('LBL_PORTAL_URL', $QUALIFIED_MODULE)}</span>
						</label>
						<div class="col-sm-4">
							<div class="input-group">
								<input value="{$PORTAL_URL}" class="form-control" disabled="disabled">
								<a target="_blank" href="{$PORTAL_URL}" class="input-group-addon input-select-addon input-group-text">
									<i class="fa-solid fa-link"></i>
								</a>
								<div class="input-group-addon input-select-addon input-group-text" data-bs-toggle="tooltip" title="{vtranslate('LBL_PORTAL_URL_MESSAGE', $QUALIFIED_MODULE)}">
									<i class="fa fa-info-circle"></i>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="container-fluid mt-4">
					<div class="row">
						<div class="col-sm-3">
							<h4 class="pb-3 border-bottom">{vtranslate('LBL_LAYOUT_HEADER',$QUALIFIED_MODULE)}</h4>
							<ul class="nav nav-tab nav-stacked flex-column" id="portalModulesTable">
								<li class="nav-item portalModuleRow active unsortable cp-modules-home" data-id="" data-sequence="1" data-module="Dashboard">
									<a class="nav-link" href="javascript:void(0);">
										<strong class="portal-home-module">{vtranslate('LBL_HOME',$QUALIFIED_MODULE)}</strong>
									</a>
								</li>
								{foreach key=TAB_ID item=MODEL from=$MODULES_MODELS name=moduleModels}
									{assign var=MODULE_NAME value=$MODEL->get('name')}
									<li class="nav-item portalModuleRow bgColor cp-tabs" {if $smarty.foreach.moduleModels.last} style="border-color: #ddd; border-image: none; border-style: solid; border-width: 0 0 1px 1px;"{/if}
										data-id="{$TAB_ID}" data-sequence="{$MODEL->get('sequence')}"
										data-module="{$MODULE_NAME}">
										<input type="hidden" name="portalModulesInfo[{$TAB_ID}][sequence]" value="{$MODEL->get('sequence')}"/>
										<a href="javascript:void(0);" class="nav-link cp-modules py-3">
											<span class="checkbox">
												<img class="drag-portal-module me-2" src="layouts/d1/resources/Images/drag.png" border="0" title="Drag And Drop To Reorder Portal Menu In Customer Portal"/>
												<input class="enabledModules portal-module-name form-check-input" name="{$TAB_ID}" type="checkbox" value="{$MODEL->get('visible')}" {if $MODEL->get('visible')}checked{/if} />
												<span class="ms-2">{vtranslate($MODULE_NAME, $MODULE_NAME)}</span>
											</span>
										</a>
									</li>
								{/foreach}
							</ul>
						</div>
						<div class="col-sm-9 portal-dashboard">
							<div id="dashboardContent" class="show container-fluid">
								<h4 class="pb-3 border-bottom">{vtranslate('LBL_HOME_LAYOUT',$QUALIFIED_MODULE)}</h4>
								<input type="hidden" name="defaultWidgets" value='{Vtiger_Functions::jsonEncode($WIDGETS,true)}'/>
								{include file='CustomerPortalDashboard.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
							</div>
							{foreach key=TAB_ID item=MODEL from=$MODULES_MODELS}
								<div id="fieldContent_{$MODEL->get('name')}" class="hide">
									{$MODEL->get('name')}
								</div>
							{/foreach}
						</div>
						<div class="container-fluid py-3">
							<div class="row">
								<div class="col"></div>
								<div class="col">
									<button type="submit" class="btn btn-primary active saveButton" id="savePortalInfo" name="savePortalInfo" type="submit" disabled>{vtranslate('LBL_SAVE', $MODULE)}</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
