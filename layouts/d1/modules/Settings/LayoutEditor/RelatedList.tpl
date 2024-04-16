{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/LayoutEditor/views/Index.php *}

{strip}
	{assign var=ModulesList value=[]}
	{assign var=removedModuleIds value=array()}
	<div class="relatedTabModulesList" style="padding:1% 0">
		<div>
			{if empty($RELATED_MODULES) && empty($RELATION_FIELDS)} 
				<div class="emptyRelatedTabs" style="margin-top:100px;">
					<div class="recordDetails">
						<div class="textAlignCenter" style="font-size:20px;opacity:0.7">{vtranslate('LBL_NO_RELATED_INFO',$QUALIFIED_MODULE)}.</div>
					</div>
				</div>
			{else}
				<div class="relatedListContainer">
					<div class="row">
						<div class="col-sm" id="ONE_ONE_AND_MANY_ONE_RELATIONSHIP">
							<div class="mb-3 fs-5">{vtranslate('ONE_ONE_AND_MANY_ONE_RELATIONSHIP',$QUALIFIED_MODULE)}</div>
							<div class="row">
								<div class="col-sm-6">
									{if php7_count($RELATION_FIELDS) eq 0}
										<div class="well fs-6 text-primary">
											<div>{vtranslate('LBL_NO_RELATION_TYPE',$QUALIFIED_MODULE)}.</div>
										</div>
									{/if}

									{foreach item=RELATION_FIELD key=FIELD_NAME from=$RELATION_FIELDS}
										{assign var=REFERENCE_LIST value=$RELATION_FIELD->getReferenceList()}
										{foreach item=REFERENCE_MODULE from=$REFERENCE_LIST}
											<div class="contentsBackground container-fluid ONE_TO_ONE"
												 data-relation-type="{$RELATION_FIELD->get('_relationType')}" data-field-name="{$FIELD_NAME}"
												 data-module="{$REFERENCE_MODULE}">
												<div class="row py-2 my-2 border rounded align-items-center">
													<div class="col">
														<div class="text-truncate" title="{vtranslate($RELATION_FIELD->get('label'),$SELECTED_MODULE_NAME)}">{vtranslate($RELATION_FIELD->get('label'),$SELECTED_MODULE_NAME)}</div>
														<span class="referenceModule">{vtranslate($REFERENCE_MODULE,$REFERENCE_MODULE)}</span>
													</div>
													<div class="col-auto">
														{if $RELATION_FIELD->get('_relationType') eq Settings_LayoutEditor_Module_Model::MANY_TO_ONE}
															<img src="{vimage_path('N-1.png')}" width="100" height="50" />
														{else}
															<img src="{vimage_path('1-1.png')}" width="100" height="50" />
														{/if}
													</div>
												</div>
											</div>
										{/foreach}
									{/foreach}
								</div>
							</div>
						</div>
						<div class="col-sm" id="ONE_MANY_RELATIONSHIP">
							<div class="mb-3 fs-5">{vtranslate('ONE_MANY_RELATIONSHIP',$QUALIFIED_MODULE)}</div>
							<div class="row">
								{if php7_count($RELATED_MODULES) eq 0}
									<div class="fs-6 text-primary">
										<div> {vtranslate('LBL_NO_RELATION_TYPE',$QUALIFIED_MODULE)}.</div>
									</div>
								{else}
									<div class="col-sm-6">
										<ul class="relatedModulesList" style="list-style: none;margin:0px;padding-left:0px;">
											{foreach item=MODULE_MODEL from=$RELATED_MODULES}
												{if $MODULE_MODEL->isActive()}
													{assign var=RELATION_FIELD_MODEL value=$MODULE_MODEL->getRelationField()}
													<li class="relatedModule container-fluid ONE_TO_MANY module_{$MODULE_MODEL->getId()}"
														data-relation-id="{$MODULE_MODEL->getId()}" data-module="{$MODULE_MODEL->getRelationModuleName()}" 
														data-relation-type="{$MODULE_MODEL->get('relationtype')}"
														{if $RELATION_FIELD_MODEL} data-field-name="{$RELATION_FIELD_MODEL->getName()}"{/if}>
														<div class="row py-2 my-2 border rounded align-items-center">
															<span class="col-auto">
																<img class="cursorPointerMove" src="{vimage_path('drag.png')}" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
															</span>
															<div class="col">
																<div class="text-truncate">
																	<span class="moduleLabel" title="{vtranslate($MODULE_MODEL->get('label'), $MODULE_MODEL->getRelationModuleName())}">{vtranslate($MODULE_MODEL->get('label'), $MODULE_MODEL->getRelationModuleName())}</span>
																</div>
																<span class="moduletranslatedLabel">{vtranslate($MODULE_MODEL->getRelationModuleName(),$MODULE_MODEL->getRelationModuleName())}</span>
															</div>
															<div class="col-auto">
																{if $MODULE_MODEL->get('relationtype') eq '1:N'}
																	<img src="{vimage_path('1-N.png')}" width="100" height="50" />
																{else}
																	<img src="{vimage_path('N-N.png')}" width="100" height="50" />
																{/if}
															</div>
															<div class="col-auto deleteButton" data-relation="1">
																<button class="btn btn-close" data-dismiss="modal" title="{vtranslate('LBL_CLOSE')}"></button>
															</div>
														</div>
													</li>
												{/if}
											{/foreach}
										</ul>
									</div>
									<div class="col-sm-6">
										<div class="p-3">
											<div>
												<img src="{vimage_path('Square.png')}" />&nbsp;&nbsp;&nbsp;
												{vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME)}
											</div>
											<div>
												<img src="{vimage_path('Circle.png')}" />&nbsp;&nbsp;&nbsp;
												{vtranslate('LBL_RELATED_MODULE',$QUALIFIED_MODULE)}
											</div>
										</div>
										<div class="relationListInfoWrapper p-3">
											<div class="relationListInfo">
												{if php7_count($RELATED_MODULES) neq 0}
													<div class="alert alert-primary">
														<div class="fs-5 mb-2 relatedListInfoHeader">
															<i class="fa fa-info-circle"></i>
															<span class="ms-2">{vtranslate('LBL_INFO', $QUALIFIED_MODULE)}</span>
														</div>
														<div>{vtranslate('LBL_RELATED_LIST_INFO', $QUALIFIED_MODULE)}.</div>
													</div>
												{/if}
											</div>
										</div>
									</div>
								{/if}
							</div>
							<div class="row py-2 hiddenModulesContainer {if !$HIDDEN_TAB_EXISTS}hide{/if}">
								<div class="col-lg-6" style="padding-right: 0px;">
									<select class="select2 inputElement" multiple name="addToList" placeholder="{vtranslate('LBL_SELECT_HIDDEN_MODULE', $QUALIFIED_MODULE)}">
										{foreach item=MODULE_MODEL from=$RELATED_MODULES}
											{$ModulesList[$MODULE_MODEL->getId()] = vtranslate($MODULE_MODEL->get('label'), $MODULE_MODEL->getRelationModuleName())}
											{if !$MODULE_MODEL->isActive()}
												{array_push($removedModuleIds, $MODULE_MODEL->getId())}
												<option value="{$MODULE_MODEL->getId()}" data-module-translated-label="{vtranslate($MODULE_MODEL->getRelationModuleName(),$MODULE_MODEL->getRelationModuleName())}">
													{vtranslate($MODULE_MODEL->get('label'), $MODULE_MODEL->getRelationModuleName())}
												</option>
											{/if}
										{/foreach}
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row py-2 saveRelatedListContainer hide">
						<div class="col text-center">
							<button type="submit" class="btn btn-success saveButton saveRelatedList" >{vtranslate('LBL_SAVE', $MODULE)}</button>
						</div>
					</div>
					<li class="container-fluid moduleCopy hide">
						<div class="row py-2 my-2 border rounded align-items-center">
							<span class="col-auto">
								<img class="cursorPointerMove" src="{vimage_path('drag.png')}" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
							</span>
							<div class="col">
								<div class="text-truncate">
									<span class="moduleLabel"></span>
								</div>
								<span class="moduletranslatedLabel"></span>
							</div>
							<div class="col-auto">
								<img src="{vimage_path('N-N.png')}" width="100" height="50" />
							</div>
							<div class="col-auto deleteButton" data-relation="1">
								<button class="btn btn-close" data-dismiss="modal" title="{vtranslate('LBL_CLOSE')}"></button>
							</div>
						</div>
					</li>
				</div>
			{/if}
			<input type="hidden" class="ModulesListArray" value='{ZEND_JSON::encode($ModulesList)}' />
			<input type="hidden" class="RemovedModulesListArray" value='{ZEND_JSON::encode($removedModuleIds)}' />
		</div>
	</div>
{/strip}