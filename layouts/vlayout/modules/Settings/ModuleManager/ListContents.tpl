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
{strip}
	<div class="container-fluid" id="moduleManagerContents">
		<div class="widget_header row-fluid">
			<div class="span6"><h3>{vtranslate('LBL_MODULE_MANAGER', $QUALIFIED_MODULE)}</h3></div>
			<div class="span6">
				<span class="btn-toolbar pull-right">
					<span class="btn-group">
                                            <button class="btn" type="button" onclick='window.location.href="index.php?module=ExtensionStore&parent=Settings&view=ExtensionStore"'>
                                                    <strong>{vtranslate('LBL_EXTENSION_STORE', $QUALIFIED_MODULE)}</strong>
                                            </button>
					</span>
				</span>
			</div>
		</div>
		<hr>
		
		<div class="contents">
			{assign var=COUNTER value=0}
			<table class="table table-bordered equalSplit">
				<tr>
				{foreach item=MODULE_MODEL key=MODULE_ID from=$ALL_MODULES}
					{assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
					{assign var=MODULE_ACTIVE value=$MODULE_MODEL->isActive()}
					{if $COUNTER eq 2}
						</tr><tr>
						{assign var=COUNTER value=0}
					{/if}

					<td class="opacity">
						<div class="row-fluid moduleManagerBlock">
							<span class="span1">
								<input type="checkbox" value="" name="moduleStatus" data-module="{$MODULE_NAME}" data-module-translation="{vtranslate($MODULE_NAME, $MODULE_NAME)}" {if $MODULE_MODEL->isActive()}checked{/if} />
							</span>
							<span class="span1">
								{if $MODULE_MODEL->isExportable()}
									<a href="index.php?module=ModuleManager&parent=Settings&action=ModuleExport&mode=exportModule&forModule={$MODULE_MODEL->get('name')}"><i class="icon icon-download"></i></a>
								{/if}&nbsp;
							</span>
							<span class="span2 moduleImage {if !$MODULE_ACTIVE}dull {/if}">
								{if vimage_path($MODULE_NAME|cat:'.png') != false}
									<img class="alignMiddle" src="{vimage_path($MODULE_NAME|cat:'.png')}" alt="{vtranslate($MODULE_NAME, $MODULE_NAME)}" title="{vtranslate($MODULE_NAME, $MODULE_NAME)}"/>
								{else}
									<img class="alignMiddle" src="{vimage_path('DefaultModule.png')}" alt="{vtranslate($MODULE_NAME, $MODULE_NAME)}" title="{vtranslate($MODULE_NAME, $MODULE_NAME)}"/>
								{/if}	
							</span>
							<span class="span5 moduleName {if !$MODULE_ACTIVE}dull {/if}"><h4>{vtranslate($MODULE_NAME, $MODULE_NAME)}</h4></span>
                            {assign var=SETTINGS_LINKS value=$MODULE_MODEL->getSettingLinks()}
							{if !in_array($MODULE_NAME, $RESTRICTED_MODULES_LIST) && (php7_count($SETTINGS_LINKS) > 0)}
								<span class="span3">
									<span class="btn-group pull-right actions {if !$MODULE_ACTIVE}hide{/if}">
										<button class="btn dropdown-toggle" data-toggle="dropdown">
											<strong>{vtranslate('LBL_SETTINGS', $QUALIFIED_MODULE)}</strong>&nbsp;<i class="caret"></i>
										</button>
										<ul class="dropdown-menu pull-right">
											{foreach item=SETTINGS_LINK from=$SETTINGS_LINKS}
											<li>
												<a {if stripos($SETTINGS_LINK['linkurl'], 'javascript:')===0} onclick='{$SETTINGS_LINK['linkurl']|substr:strlen("javascript:")};'{else} onclick='window.location.href="{$SETTINGS_LINK['linkurl']}"'{/if}>{vtranslate($SETTINGS_LINK['linklabel'], $MODULE_NAME)}</a>
											</li>
											{/foreach}
										</ul>
									</span>
								</span>
							{/if}
						</div>
						{assign var=COUNTER value=$COUNTER+1}
					</td>
				{/foreach}
				</tr>
			</table>
		</div>
                <div class="row-fluid" style="padding: 20px 0px;">
                    <a href="{$IMPORT_USER_MODULE_FROM_FILE_URL}">{vtranslate('LBL_INSTALL_FROM_ZIP', $QUALIFIED_MODULE)}</a>
                </div>
	</div>
{/strip}
