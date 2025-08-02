{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/Profiles/views/Detail.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
	<div class="detailViewContainer px-4 pb-4">
		<div class="bg-body rounded">
			<div class="detailViewTitle form-horizontal" id="profilePageHeader">
				<div class="p-3 border-bottom">
					<div class="row">
						<div class="col-sm">
							<h4>{vtranslate('LBL_PROFILE_VIEW', $QUALIFIED_MODULE)}</h4>
						</div>
						<div class="col-sm-auto">
							<div class="btn-group">
								<button class="btn btn-primary" type="button" onclick='window.location.href = "{$RECORD_MODEL->getEditViewUrl()}"'>{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}</button>
							</div>
						</div>
					</div>
				</div>
				<div class="profileDetailView detailViewInfo container-fluid">
					<div class="row form-group py-2">
						<div class="col-lg-2 col-sm-2 fieldLabel">
							<label>{vtranslate('LBL_PROFILE_NAME', $QUALIFIED_MODULE)}</label>
						</div>
						<div class="col-lg col-sm fieldValue"  name="profilename" id="profilename" value="{$RECORD_MODEL->getName()}">
							<strong>{$RECORD_MODEL->getName()}</strong>
						</div>
					</div>
					<div class="row form-group py-2">
						<div class="col-lg-2 col-sm-2 fieldLabel">
							<label>{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}:</label>
						</div>
						<div class="col-lg col-sm fieldValue" name="description" id="description">
							<strong>{$RECORD_MODEL->getDescription()}</strong>
						</div>
					</div>
					{assign var=ENABLE_IMAGE_PATH value=vimage_path('Enable.png')}
					{assign var=DISABLE_IMAGE_PATH value=vimage_path('Disable.png')}
					{if $RECORD_MODEL->hasGlobalReadPermission()}
						<div class="row form-group py-2">
							<div class="col-lg-2 col-sm-2 fieldLabel">
								<img class="alignMiddle" src="{if $RECORD_MODEL->hasGlobalReadPermission()}{$ENABLE_IMAGE_PATH}{else}{$DISABLE_IMAGE_PATH}{/if}" />
								<span class="ms-2">{vtranslate('LBL_VIEW_ALL',$QUALIFIED_MODULE)}</span>
							</div>
							<div class="col-lg col-sm fieldValue">
								<span class="input-info-addon">
									<i class="fa fa-info-circle"></i>
									<span class="ms-2">{vtranslate('LBL_VIEW_ALL_DESC',$QUALIFIED_MODULE)}</span>
								</span>
							</div>
						</div>
						<div class="row form-group py-2">
							<div class="col-lg-2 col-sm-2 fieldLabel">
								<img class="alignMiddle" src="{if $RECORD_MODEL->hasGlobalWritePermission()}{$ENABLE_IMAGE_PATH}{else}{$DISABLE_IMAGE_PATH}{/if}" />
								<span class="ms-2">{vtranslate('LBL_EDIT_ALL',$QUALIFIED_MODULE)}</span>
							</div>
							<div class="col-lg col-sm fieldValue">
								<span class="input-info-addon">
									<i class="fa fa-info-circle"></i>&nbsp;
									<span class="ms-2">{vtranslate('LBL_EDIT_ALL_DESC',$QUALIFIED_MODULE)}</span>
								</span>
							</div>
						</div>
					{/if}
					<br>
					<div class="row">
						<div class="col-lg col-sm">
							<table class="table table-bordered">
								<thead>
									<tr class='blockHeader'>
										<th width="27%" style="text-align: left !important">
											{vtranslate('LBL_MODULES', $QUALIFIED_MODULE)}
										</th>
										<th width="11%">
											{'LBL_VIEW_PRVILIGE'|vtranslate:$QUALIFIED_MODULE}
										</th>
										<th width="11%">
											{'LBL_CREATE'|vtranslate:$QUALIFIED_MODULE}
										</th>
										<th width="11%">
											{'LBL_EDIT'|vtranslate:$QUALIFIED_MODULE}
										</th>
										<th width="11%">
											{'LBL_DELETE_PRVILIGE'|vtranslate:$QUALIFIED_MODULE}
										</th>
										<th width="29%" nowrap="nowrap">
											{'LBL_FIELD_AND_TOOL_PRIVILEGES'|vtranslate:$QUALIFIED_MODULE}
										</th>
									</tr>
								</thead>
								<tbody>
									{foreach from=$RECORD_MODEL->getModulePermissions() key=TABID item=PROFILE_MODULE}
										<tr>
											{assign var=MODULE_PERMISSION value=$RECORD_MODEL->hasModulePermission($PROFILE_MODULE)}
											<td data-module-name='{$PROFILE_MODULE->getName()}' data-module-status='{$MODULE_PERMISSION}'>
												<img src="{if $MODULE_PERMISSION}{$ENABLE_IMAGE_PATH}{else}{$DISABLE_IMAGE_PATH}{/if}"/>&nbsp;{$PROFILE_MODULE->get('label')|vtranslate:$PROFILE_MODULE->getName()}
											</td>
											{assign var="BASIC_ACTION_ORDER" value=array(2,3,0,1)}
											{foreach from=$BASIC_ACTION_ORDER item=ACTION_ID}
												{assign var="ACTION_MODEL" value=$ALL_BASIC_ACTIONS[$ACTION_ID]}
												{assign var=MODULE_ACTION_PERMISSION value=$RECORD_MODEL->hasModuleActionPermission($PROFILE_MODULE, $ACTION_MODEL)}
												<td data-action-state='{$ACTION_MODEL->getName()}' data-moduleaction-status='{$MODULE_ACTION_PERMISSION}' style="text-align: center;">
													{if $ACTION_MODEL->isModuleEnabled($PROFILE_MODULE)}
														<img src="{if $MODULE_ACTION_PERMISSION}{$ENABLE_IMAGE_PATH}{else}{$DISABLE_IMAGE_PATH}{/if}" />
													{/if}
												</td>
											{/foreach}
											<td class="textAlignCenter">
												{if ($PROFILE_MODULE->getFields() && $PROFILE_MODULE->isEntityModule()) || $PROFILE_MODULE->isUtilityActionEnabled()}
													<button type="button" data-handlerfor="fields" data-togglehandler="{$TABID}-fields" class="btn btn-sm btn-default" style="padding-right: 20px; padding-left: 20px;">
														<i class="fa fa-chevron-down"></i>
													</button>
												{/if}
											</td>
										</tr>
										<tr class="hide">
											<td colspan="6">
												<div class="container-fluid pb-3">
													<div class="row py-2" data-togglecontent="{$TABID}-fields" style="display: none">
														{if $PROFILE_MODULE->getFields() && $PROFILE_MODULE->isEntityModule()}
															<label class="col-lg col-sm fs-5">
																<strong>{vtranslate('LBL_FIELDS',$QUALIFIED_MODULE)}</strong>
															</label>
															<div class="col-lg-auto col-sm-auto">
																<i class="fa-solid fa-circle text-black" data-value="0"></i>
																<span class="ms-2">{vtranslate('LBL_INIVISIBLE',$QUALIFIED_MODULE)}</span>
															</div>
															<div class="col-lg-auto col-sm-auto" data-value="1">
																<i class="fa-solid fa-circle text-warning"></i>
																<span class="ms-2">{vtranslate('LBL_READ_ONLY',$QUALIFIED_MODULE)}</span>
															</div>
															<div class="col-lg-auto col-sm-auto" data-value="2">
																<i class="fa-solid fa-circle text-success"></i>
																<span class="ms-2">{vtranslate('LBL_WRITE',$QUALIFIED_MODULE)}</span>
															</div>
													</div>
													<div class="row py-2">
														{assign var=COUNTER value=0}
														{foreach from=$PROFILE_MODULE->getFields() key=FIELD_NAME item=FIELD_MODEL name="fields"}
															{if $FIELD_MODEL->isActiveField() && $FIELD_MODEL->get('displaytype') neq '6'}
																{assign var=FIELD_ID value=$FIELD_MODEL->getId()}
																<div class="col-sm-4">
																	{assign var=DATA_VALUE value=$RECORD_MODEL->getModuleFieldPermissionValue($PROFILE_MODULE, $FIELD_MODEL)}
																	{if $DATA_VALUE eq 0}
																		<i class="fa-solid fa-circle text-black"></i>
																	{elseif $DATA_VALUE eq 1}
																		<i class="fa-solid fa-circle text-warning"></i>
																	{else}
																		<i class="fa-solid fa-circle text-success"></i>
																	{/if}&nbsp;
																	<span class="ms-2">{vtranslate($FIELD_MODEL->get('label'), $PROFILE_MODULE->getName())}</span>
																	{if $FIELD_MODEL->isMandatory()}
																		<span class="text-danger ms-2">*</span>
																	{/if}
																</div>
																{assign var=COUNTER value=$COUNTER+1}
															{/if}
														{/foreach}
													</div>
												</div>
												{/if}
											</td>
										</tr>
										<tr class="hide">
											<td colspan="6">
												<div class="container-fluid pb-3">
													<div class="row py-2" data-togglecontent="{$TABID}-fields" style="display: none">
														<div class="col-sm-12">
															<label class="fs-5"><strong>{vtranslate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></label>
														</div>
													</div>
													<div class="row py-2">
														{foreach from=$ALL_UTILITY_ACTIONS item=ACTION_MODEL}
															{if !$ACTION_MODEL->isModuleEnabled($PROFILE_MODULE)}
																{continue}
															{/if}
															{assign var=ACTION_ID value=$ACTION_MODEL->get('actionid')}
															{assign var=ACTIONNAME_STATUS value=$RECORD_MODEL->hasModuleActionPermission($PROFILE_MODULE, $ACTION_ID)}
															<div class="col-lg-4" data-action-name='{$ACTION_MODEL->getName()}' data-actionname-status='{$ACTIONNAME_STATUS}'>
																<img class="alignMiddle" src="{if $ACTIONNAME_STATUS}{$ENABLE_IMAGE_PATH}{else}{$DISABLE_IMAGE_PATH}{/if}"  alt="img"/>
																<span class="ms-2">{$ACTION_MODEL->getName()}</span>
															</div>
														{/foreach}
													</div>
												</div>
											</td>
										</tr>
									{/foreach}
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
{/strip}
