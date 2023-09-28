{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="main-container container-fluid">
		<div class="row">
			<div id="modnavigator" class="module-nav editViewModNavigator col-lg-auto text-center bg-white d-none d-lg-block h-main">
				<div class="hidden-xs hidden-sm mod-switcher-container">
					{include file="partials/Menubar.tpl"|vtemplate_path:$MODULE}
				</div>
			</div>
			<div class="col-lg px-0 mb-lg-4 mx-lg-4 overflow-hidden">
				<div class="editViewPageDiv viewContent">
					<div class="col-sm-12 col-xs-12 content-area">
						<form id="EditView" class="form-horizontal recordEditView" name="EditView" method="post" action="index.php">
							<div class="editViewHeader">
								<div class='row'>
									<div class="col-lg-12 col-md-12 col-lg-pull-0">
										{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
										{if $RECORD_ID neq ''}
											<h4 class="editHeader" title="{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} {$RECORD->getName()}">{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} - {$RECORD->getName()}</h4>
										{else}
											<h4 class="editHeader" >{vtranslate('LBL_CREATING_NEW', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)}</h4>
										{/if}
									</div>
								</div>
							</div>
							<div class="editViewBody">
								<div class="editViewContents mb-5 p-3 bg-white rounded">
									{assign var=QUALIFIED_MODULE_NAME value={$MODULE}}
									{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
									{assign var="IS_SYSTEM_TEMPLATE_EDIT" value=false}
									{assign var="SYSTEM_TEMPLATE" value=$RECORD->isSystemTemplate()}
									{if $SYSTEM_TEMPLATE && $MODE != ''}
										{assign var="IS_SYSTEM_TEMPLATE_EDIT" value=$SYSTEM_TEMPLATE}
									{/if}
									<input type="hidden" name="module" value="{$MODULE}" />
									<input type="hidden" name="action" value="Save" />
									<input type="hidden" name="record" value="{$RECORD_ID}" />
									<input type="hidden" class="isSystemTemplate" value="{$IS_SYSTEM_TEMPLATE_EDIT}" />
									{if $IS_SYSTEM_TEMPLATE_EDIT}
										<input type="hidden" name="subject" value="{$RECORD->get('subject')}"/>
										<input type="hidden" name="systemtemplate" value="{$SYSTEM_TEMPLATE}" />
									{/if}
									{if $RETURN_VIEW}
										<input type="hidden" name="returnmodule" value="{$RETURN_MODULE}" />
										<input type="hidden" name="returnview" value="{$RETURN_VIEW}" />
										<input type="hidden" name="returnrecord" value="{$RETURN_RECORD}" />
										<input type="hidden" name="returnpage" value="{$RETURN_PAGE}" />
										<input type="hidden" name="returnsearch_params" value='{Vtiger_Functions::jsonEncode($RETURN_SEARCH_PARAMS)}' />
										<input type="hidden" name="returnsearch_key" value={$RETURN_SEARCH_KEY} />
										<input type="hidden" name="returnsearch_value" value={$RETURN_SEARCH_VALUE} />
										<input type="hidden" name="returnoperator" value={$RETURN_SEARCH_OPERATOR} />
										<input type="hidden" name="returnsortorder" value={$RETURN_SORTBY} />
										<input type="hidden" name="returnorderby" value={$RETURN_ORDERBY} />
									{/if}
									{include file="partials/EditViewContents.tpl"|@vtemplate_path:$MODULE}
								</div>
								<div class="modal-overlay-footer modal-footer fixed-bottom bg-white p-3">
									<div class="container-fluid">
										<div class="row">
											<div class="col-6 text-end">
												<a class="cancelLink btn btn-primary" href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
											</div>
											<div class="col-6">
												<button type="submit" class="btn btn-primary active saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}