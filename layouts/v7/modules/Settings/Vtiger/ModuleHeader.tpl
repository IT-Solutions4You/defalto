{*<!--
/*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************/
-->*}

{strip}
	<div class="col-sm-12 col-xs-12 module-action-bar clearfix coloredBorderTop">
		<div class="module-action-content clearfix">
			<div class="col-lg-7 col-md-7">
				{if $USER_MODEL->isAdminUser()}
					<a title="{vtranslate('Home', $MODULE)}" href='index.php?module=Vtiger&parent=Settings&view=Index'>
						{if $VIEW eq 'Index' && $MODULE eq 'Vtiger'}
							<h4 class="module-title pull-left text-uppercase">{vtranslate('LBL_SETTINGS', $MODULE)} </h4>
						{/if}
					</a>
				{/if}
				{if $MODULE eq 'Vtiger' and $smarty.request.view eq 'Index'}

				{else}
					<h4 title="{vtranslate($ACTIVE_BLOCK['block'], $QUALIFIED_MODULE)}" class="module-title pull-left text-uppercase">{vtranslate($ACTIVE_BLOCK['block'], $QUALIFIED_MODULE)}</h4>
					{if $MODULE neq 'Vtiger'}
						{if $smarty.request.view eq 'PreferenceDetail' or $smarty.request.view eq 'PreferenceEdit'}
							{assign var=SELECTED_MODULE value='My Preferences'}
						{elseif $smarty.request.view eq 'Calendar'}
							{assign var=SELECTED_MODULE value='Calendar Settings'}
						{elseif $smarty.request.module eq 'LayoutEditor'}
							{assign var=SELECTED_MODULE value='LBL_MODULE_CUSTOMIZATION'}
						{elseif $smarty.request.module eq 'Currency'}
							{assign var=SELECTED_MODULE value='LBL_CURRENCY_SETTINGS'}
						{elseif $smarty.request.module eq 'Picklist'}
							{assign var=SELECTED_MODULE value='LBL_PICKLIST_EDITOR'}
						{elseif $smarty.request.view eq 'MappingDetail' and $smarty.request.module eq 'Potentials'}
							{assign var=SELECTED_MODULE value='LBL_OPPORTUNITY_MAPPING'}
						{elseif $smarty.request.view eq 'MappingDetail' and $smarty.request.module eq 'Leads'}
							{assign var=SELECTED_MODULE value='LBL_LEAD_MAPPING'}
						{elseif $smarty.request.module eq 'MenuEditor'}
							{assign var=SELECTED_MODULE value='LBL_MENU_EDITOR'}
						{elseif $smarty.request.module eq 'Tags'}
							{assign var=SELECTED_MODULE value='LBL_MY_TAGS'}
						{else}
							{assign var=SELECTED_MODULE value=$smarty.request.module}
						{/if}
						<span class="current-filter-name filter-name pull-left" style='width:50%;'>&nbsp;&nbsp;<span class="fa fa-angle-right" aria-hidden="true"></span>&nbsp;{vtranslate($SELECTED_MODULE, $QUALIFIED_MODULE)}</span>
					{else}
						{if $smarty.request.view eq 'TaxIndex'}
							{assign var=SELECTED_MODULE value='LBL_TAX_MANAGEMENT'}
						{elseif $smarty.request.view eq 'TermsAndConditionsEdit'}
							{assign var=SELECTED_MODULE value='LBL_TERMS_AND_CONDITIONS'}
						{else}
							{assign var=SELECTED_MODULE value=$ACTIVE_BLOCK['menu']}
						{/if}
						<span class="current-filter-name filter-name pull-left" style='width:50%;'>&nbsp;&nbsp;<span class="fa fa-angle-right" aria-hidden="true"></span>&nbsp;{vtranslate({$SELECTED_MODULE}, $QUALIFIED_MODULE)}</span>
					{/if}
				{/if}
				</div>
				<div class="col-lg-5 col-md-5 pull-right">
					<div id="appnav" class="navbar-right">
						<ul class="nav navbar-nav">
							{foreach item=BASIC_ACTION from=$MODULE_BASIC_ACTIONS}
								{if $BASIC_ACTION->getLabel() == 'LBL_IMPORT'}
									<li>
										<button id="{$MODULE}_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($BASIC_ACTION->getLabel())}" type="button" class="btn addButton btn-default module-buttons" 
											{if stripos($BASIC_ACTION->getUrl(), 'javascript:')===0}
												onclick='{$BASIC_ACTION->getUrl()|substr:strlen("javascript:")};'
											{else} 
												onclick="Vtiger_Import_Js.triggerImportAction('{$BASIC_ACTION->getUrl()}')"
											{/if}>
											<div class="fa {$BASIC_ACTION->getIcon()}" aria-hidden="true"></div>&nbsp;&nbsp;
											{vtranslate($BASIC_ACTION->getLabel(), $MODULE)}
										</button>
									</li>
								{else}
									<li>
										<button type="button" class="btn addButton btn-default module-buttons" 
											{if $MODULE eq 'SLA' || $MODULE eq 'BusinessHours'}
												id="addRecord" data-url="{$BASIC_ACTION->getUrl()}"
											{else}
												id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($BASIC_ACTION->getLabel())}"
												{if stripos($BASIC_ACTION->getUrl(), 'javascript:')===0}
													onclick='{$BASIC_ACTION->getUrl()|substr:strlen("javascript:")};'
												{else} 
													onclick='window.location.href="{$BASIC_ACTION->getUrl()}"'
												{/if}
											{/if}
											>
											<div class="fa {$BASIC_ACTION->getIcon()}" aria-hidden="true"></div>
											&nbsp;&nbsp;{vtranslate($BASIC_ACTION->getLabel(), $MODULE)}
										</button>
									</li>
								{/if}

							{/foreach}

							{if $LISTVIEW_LINKS['LISTVIEWSETTING']|@count gt 0}
								{if empty($QUALIFIEDMODULE)} 
									{assign var=QUALIFIEDMODULE value=$MODULE}
								{/if}
								<li>
									<div class="settingsIcon">
										<button type="button" class="btn btn-default module-buttons dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
											<span class="fa fa-wrench" aria-hidden="true" title="{vtranslate('LBL_SETTINGS', $MODULE)}"></span>&nbsp; <span class="caret"></span>
										</button>
										<ul class="detailViewSetting dropdown-menu">
											{foreach item=SETTING from=$LISTVIEW_LINKS['LISTVIEWSETTING']}
												<li id="{$MODULE}_setings_lisview_advancedAction_{$SETTING->getLabel()}"><a href="javascript:void(0);" onclick="{$SETTING->getUrl()};">{vtranslate($SETTING->getLabel(), $QUALIFIEDMODULE)}</a></li>
											{/foreach}
										</ul>
									</div>
								</li>
							{/if}

							{assign var=RESTRICTED_MODULE_LIST value=['Users', 'EmailTemplates']}
							{if $LISTVIEW_LINKS['LISTVIEWBASIC']|@count gt 0 and !in_array($MODULE, $RESTRICTED_MODULE_LIST)}
								{if empty($QUALIFIED_MODULE)} 
									{assign var=QUALIFIED_MODULE value='Settings:'|cat:$MODULE}
								{/if}
								{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
									{if $MODULE eq 'Users'} {assign var=LANGMODULE value=$MODULE} {/if}
									<li>
										<button class="btn btn-default addButton module-buttons"
											id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}" 
											{if $MODULE eq 'Workflows'}
												onclick='Settings_Workflows_List_Js.triggerCreate("{$LISTVIEW_BASICACTION->getUrl()}&mode=V7Edit")'
											{else}
												{if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0}
													onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'
												{else}
													onclick='window.location.href="{$LISTVIEW_BASICACTION->getUrl()}"'
												{/if}
											{/if}>
											<i class="fa fa-plus"></i>&nbsp;&nbsp;
											{if $MODULE eq 'Tags'}
												{vtranslate('LBL_ADD_TAG', $QUALIFIED_MODULE)}
											{else}
												{vtranslate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}
											{/if}
										</button>
									</li>
								{/foreach}
							{/if}
						</ul>
					</div>
				</div>
			</div>
			{if $FIELDS_INFO neq null}
				<script type="text/javascript">
					var uimeta = (function () {
						var fieldInfo = {$FIELDS_INFO};
						return {
							field: {
								get: function (name, property) {
									if (name && property === undefined) {
										return fieldInfo[name];
									}
									if (name && property) {
										return fieldInfo[name][property]
									}
								},
								isMandatory: function (name) {
									if (fieldInfo[name]) {
										return fieldInfo[name].mandatory;
									}
									return false;
								},
								getType: function (name) {
									if (fieldInfo[name]) {
										return fieldInfo[name].type
									}
									return false;
								}
							},
						};
					})();
				</script>
			{/if}
		</div>
		{/strip}
