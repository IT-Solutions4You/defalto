{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}{strip}
<div class="modal-dialog modelContainer">
	<div class = "modal-content">
	{assign var=HEADER_TITLE value={vtranslate('LBL_ADD_NEW_FOLDER', $MODULE)}}
	{if $FOLDER_ID}
		{assign var=HEADER_TITLE value="{vtranslate('LBL_EDIT_FOLDER', $MODULE)}: {$FOLDER_NAME}"}
	{/if}
	{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
	<form class="form-horizontal" id="addDocumentsFolder" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" value="Folder" />
		<input type="hidden" name="mode" value="save" />
		{if $FOLDER_ID neq null}
			<input type="hidden" name="folderid" value="{$FOLDER_ID}" />
			<input type="hidden" name="savemode" value="{$SAVE_MODE}" />
		{/if}
		<div class="modal-body">
			<div class="container-fluid">
				<div class="form-group">
					<label class="control-label fieldLabel" for="documentsFolderName">
						<span class="me-2">{vtranslate('LBL_FOLDER_NAME', $MODULE)}</span>
						<span class="text-danger">*</span>
					</label>
					<div class="controls">
						<input class="inputElement form-control" id="documentsFolderName" data-rule-required="true" name="foldername" type="text" value="{if $FOLDER_NAME neq null}{$FOLDER_NAME}{/if}"/>
					</div>
				</div>
				<div class="form-group mt-3">
					<label class="control-label fieldLabel" for="description">
						{vtranslate('LBL_FOLDER_DESCRIPTION', $MODULE)}
					</label>
					<div class="controls">
						<textarea rows="3" class="inputElement form-control" name="folderdesc" id="description" style="resize: vertical;">{if $FOLDER_DESC neq null}{$FOLDER_DESC}{/if}</textarea>
					</div>
				</div>
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>
	</div>
</div>
{/strip}

