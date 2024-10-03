{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	{assign var=APP_ARRAY value=Vtiger_MenuStructure_Model::getAppMenuList()}
	<div class="modal-dialog modal-lg addModuleContainer">
		<div class="modal-content">
			<input id="appname" type="hidden" name="appname" value="{$SELECTED_APP_NAME}" />
			{assign var=APP_SELECTED_LABEL value=vtranslate('LBL_SELECT_'|cat:$SELECTED_APP_NAME|cat:'_MODULES', $QUALIFIED_MODULE)}
			{include file="ModalHeader.tpl"|vtemplate_path:$QUALIFIED_MODULE TITLE=$APP_SELECTED_LABEL}
			<div class="modal-body form-horizontal">
				{foreach item=APP_NAME from=$APP_ARRAY}
					{assign var=HIDDEN_MODULES value=Settings_MenuEditor_Module_Model::getHiddenModulesForApp($APP_NAME)}
					<div class="row modulesContainer {if $APP_NAME neq $SELECTED_APP_NAME} hide {/if}" data-appname="{$APP_NAME}">
						{if php7_count($HIDDEN_MODULES) gt 0}
							{foreach item=MODULE_NAME from=$HIDDEN_MODULES}
								<div class="col-lg-3 mb-3">
									<span class="btn-group w-100">
										<button class="btn btn-outline-secondary module-buttons addButton addModule text-start" data-module="{$MODULE_NAME}">
											<span class="me-2">{vtranslate($MODULE_NAME, $MODULE_NAME)}</span>
											<span class="float-end">
												<i class="fa fa-plus"></i>
											</span>
										</button>
									</span>
								</div>
							{/foreach}
						{else}
							<h5 class="text-center">{vtranslate('LBL_NO', $QUALIFIED_MODULE)} {vtranslate('LBL_MODULES', $QUALIFIED_MODULE)} {vtranslate('LBL_FOUND', $QUALIFIED_MODULE)}.</h5>
						{/if}
					</div>
				{/foreach}
			</div>
			{include file="ModalFooter.tpl"|vtemplate_path:$QUALIFIED_MODULE}
		</div>
	</div>
{/strip}