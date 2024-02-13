{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<div class="main-container container-fluid">
		<div class="row">
			<div id="modnavigator" class="module-nav editViewModNavigator col-lg-auto text-center bg-body d-none d-lg-block h-main">
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
								<div class="editViewContents mb-5 p-3 bg-body rounded">
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
									{include file="partials/EditViewReturn.tpl"|vtemplate_path:$MODULE}
									{include file="partials/EditViewContents.tpl"|@vtemplate_path:$MODULE}
								</div>
								<div class="modal-overlay-footer modal-footer fixed-bottom bg-body p-3">
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