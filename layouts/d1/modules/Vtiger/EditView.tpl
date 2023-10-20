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
			<div id="modnavigator" class="module-nav editViewModNavigator col-lg-auto text-center bg-body d-none d-lg-block h-main">
				<div class="hidden-xs hidden-sm mod-switcher-container">
					{include file="partials/Menubar.tpl"|vtemplate_path:$MODULE}
				</div>
			</div>
			<div class="col-lg px-0 mb-lg-4 mx-lg-4 overflow-hidden">
				<div class="editViewPageDiv viewContent col p-0 rounded bg-body">
					<div class="content-area {if $LEFTPANELHIDE eq '1'} full-width {/if}">
						<form class="form-horizontal recordEditView" id="EditView" name="edit" method="post" action="index.php" enctype="multipart/form-data">
							<div class="editViewBody">
								<div class="editViewContents">
									{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
									{assign var=QUALIFIED_MODULE_NAME value={$MODULE}}
									{assign var=IS_PARENT_EXISTS value=strpos($MODULE,":")}
									{if $IS_PARENT_EXISTS}
										{assign var=SPLITTED_MODULE value=":"|explode:$MODULE}
										<input type="hidden" name="module" value="{$SPLITTED_MODULE[1]}" />
										<input type="hidden" name="parent" value="{$SPLITTED_MODULE[0]}" />
									{else}
										<input type="hidden" name="module" value="{$MODULE}" />
									{/if}
									<input type="hidden" name="action" value="Save" />
									<input type="hidden" name="record" value="{$RECORD_ID}" />
									<input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
									<input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
									<input type="hidden" name="appName" value="&app={$SELECTED_MENU_CATEGORY}" />
									{if $IS_RELATION_OPERATION }
										<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />
										<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
										<input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}" />
									{/if}
									{if $RETURN_VIEW}
										<input type="hidden" name="returnmodule" value="{$RETURN_MODULE}" />
										<input type="hidden" name="returnview" value="{$RETURN_VIEW}" />
										<input type="hidden" name="returnrecord" value="{$RETURN_RECORD}" />
										<input type="hidden" name="returntab_label" value="{$RETURN_RELATED_TAB}" />
										<input type="hidden" name="returnrelatedModule" value="{$RETURN_RELATED_MODULE}" />
										<input type="hidden" name="returnpage" value="{$RETURN_PAGE}" />
										<input type="hidden" name="returnviewname" value="{$RETURN_VIEW_NAME}" />
										<input type="hidden" name="returnsearch_params" value='{Vtiger_Functions::jsonEncode($RETURN_SEARCH_PARAMS)}' />
										<input type="hidden" name="returnsearch_key" value={$RETURN_SEARCH_KEY} />
										<input type="hidden" name="returnsearch_value" value={$RETURN_SEARCH_VALUE} />
										<input type="hidden" name="returnoperator" value={$RETURN_SEARCH_OPERATOR} />
										<input type="hidden" name="returnsortorder" value={$RETURN_SORTBY} />
										<input type="hidden" name="returnorderby" value="{$RETURN_ORDERBY}" />
										<input type="hidden" name="returnmode" value={$RETURN_MODE} />
										<input type="hidden" name="returnrelationId" value="{$RETURN_RELATION_ID}" />
										<input type="hidden" name="returnparent" value="{$RETURN_PARENT_MODULE}" />
									{/if}
									{include file="partials/EditViewContents.tpl"|@vtemplate_path:$MODULE}
								</div>
							</div>
							<div class='modal-overlay-footer clearfix fixed-bottom bg-body border-top border-1'>
								<div class="container-fluid">
									<div class="row d-flex align-items-center h-header">
										<div class="col-6 text-end">
											<a class='btn btn-outline-primary cancelLink px-4' href="javascript:history.{if $DUPLICATE_RECORDS}go(-2){else}back(){/if}" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
										</div>
										<div class="col-6 text-start">
											<button type='submit' class='btn btn-primary active px-5 saveButton'>{vtranslate('LBL_SAVE', $MODULE)}</button>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="h-header"></div>
			</div>
		</div>
	</div>
{/strip}