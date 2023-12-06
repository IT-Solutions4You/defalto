{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/ModuleManager/views/List.php *}
{strip}
	<div class="listViewPageDiv detailViewContainer" id="moduleManagerContents">
		<div class="px-4 pb-4">
			<div id="listview-actions" class="listview-actions-container bg-body rounded">
				<div class="container-fluid p-3">
					<div class="row">
						<h4 class="col-lg">{vtranslate('LBL_MODULE_MANAGER', $QUALIFIED_MODULE)}</h4>
						<div class="col-auto ms-auto">
							<div class="btn-group">
								<button class="btn btn-outline-secondary me-2" type="button" onclick='window.location.href="{$IMPORT_USER_MODULE_FROM_FILE_URL}"'>
									{vtranslate('LBL_IMPORT_MODULE_FROM_ZIP', $QUALIFIED_MODULE)}
								</button>
							</div>
							<div class="btn-group">
								<button class="btn btn-outline-secondary" type="button" onclick='window.location.href = "{$IMPORT_MODULE_URL}"'>
									{vtranslate('LBL_EXTENSION_STORE', 'Settings:ExtensionStore')}
								</button>
							</div>
						</div>
					</div>
				</div>
				<div class="contents">
					<div class="container-fluid modulesTable">
						<div class="row">
							{foreach item=MODULE_MODEL key=MODULE_ID from=$ALL_MODULES}
								{assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
								{assign var=MODULE_ACTIVE value=$MODULE_MODEL->isActive()}
								{assign var=MODULE_LABEL value=vtranslate($MODULE_MODEL->get('label'), $MODULE_MODEL->get('name'))}
								<div class="col-lg-6 ModulemanagerSettings">
									<div class="moduleManagerBlock row align-items-center border-top h-header">
										<div class="col-1 text-center">
											<input type="checkbox" value="" class="form-check-input" name="moduleStatus" data-module="{$MODULE_NAME}" data-module-translation="{$MODULE_LABEL}" {if $MODULE_MODEL->isActive()}checked{/if} />
										</div>
										<div class="col-1 text-center moduleImage text-secondary {if !$MODULE_ACTIVE}dull{/if}">
											{$MODULE_MODEL->getModuleIcon()}
										</div>
										<div class="col-7 text-truncate moduleName {if !$MODULE_ACTIVE}dull{/if}">
											<span class="fs-5 ">{$MODULE_LABEL}</span>
										</div>
                                        {assign var=SETTINGS_LINKS value=$MODULE_MODEL->getSettingLinks()}
                                        {if !in_array($MODULE_NAME, $RESTRICTED_MODULES_LIST) && (php7_count($SETTINGS_LINKS) > 0)}
                                            <div class="col-lg-3 moduleblock text-end">
                                                <div class="btn-group actions {if !$MODULE_ACTIVE}hide{/if}">
                                                    <button class="btn btn-outline-secondary dropdown-toggle unpin hiden" data-bs-toggle="dropdown">
                                                        <span>{vtranslate('LBL_SETTINGS', $QUALIFIED_MODULE)}</span>
                                                    </button>
                                                    <ul class="dropdown-menu pull-right dropdownfields">
                                                        {foreach item=SETTINGS_LINK from=$SETTINGS_LINKS}
															<li>
																<a class="dropdown-item" {if stripos($SETTINGS_LINK['linkurl'], 'javascript:')===0}
																		onclick='{$SETTINGS_LINK['linkurl']|substr:strlen("javascript:")};'
																	{else}
																		onclick='window.location.href = "{$SETTINGS_LINK['linkurl']}"'
																	{/if}>
																	<span>{vtranslate($SETTINGS_LINK['linklabel'], $MODULE_NAME, vtranslate("SINGLE_$MODULE_NAME", $MODULE_NAME))}</span>
																</a>
															</li>
                                                        {/foreach}
                                                    </ul>
                                                </div>
                                            </div>
										{/if}
									</div>
								</div>
							{/foreach}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
{/strip}
