{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}

{strip}
	<div class="modal-dialog modal-lg createFieldModal modelContainer {if !$IS_FIELD_EDIT_MODE}hide{/if}">
		<div class="modal-content">
			{if !$IS_FIELD_EDIT_MODE}
				{assign var=TITLE value={vtranslate('LBL_CREATE_CUSTOM_FIELD', $QUALIFIED_MODULE)}}
			{else}
				{assign var=TITLE value={vtranslate('LBL_EDIT_FIELD', $QUALIFIED_MODULE,vtranslate($FIELD_MODEL->get('label'),$SELECTED_MODULE_NAME))}}
			{/if}
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
			<form class="form-horizontal createCustomFieldForm">
				<input type="hidden" name="fieldid" value="{$FIELD_MODEL->getId()}" />
				<input type="hidden" name="addToBaseTable" value="{$ADD_TO_BASE_TABLE}" />
				<input type="hidden" name="_source" value="{$SOURCE}" />
				<input type="hidden" name="fieldname" value="{$FIELD_MODEL->get('name')}" />
				<input type="hidden" id="headerFieldsCount" value="{$HEADER_FIELDS_COUNT}" />
				<div class="modal-body overflow-auto container-fluid">
					{*<!-- To add block lables only for create view, which will be used while double clicking on uitype --> *}
					{if !$IS_FIELD_EDIT_MODE}
						<div class="form-group py-3 row blockControlGroup hide">
							<label class="control-label fieldLabel col-lg-4 text-end">
								{vtranslate('LBL_SELECT_BLOCK', $QUALIFIED_MODULE)}
							</label>
							<div class="controls col-lg-6">
								<select class="blockList" name="blockid">
									{foreach key=BLOCK_ID item=BLOCK_MODEL from=$ALL_BLOCK_LABELS}
										{if $BLOCK_MODEL->isAddCustomFieldEnabled()}
											<option value="{$BLOCK_ID}" data-label="{$BLOCK_MODEL->get('label')}">{vtranslate($BLOCK_MODEL->get('label'), $SELECTED_MODULE_NAME)}</option>
										{/if}
									{/foreach}
								</select>
							</div>
						</div> 
					{/if}
					<div class="form-group py-3 row">
						<label class="control-label fieldLabel col-lg-4 text-end">
							{vtranslate('LBL_SELECT_FIELD_TYPE', $QUALIFIED_MODULE)}
						</label>
						<div class="controls col-lg-6">
							<select class="fieldTypesList" name="fieldType" {if $IS_FIELD_EDIT_MODE} disabled="disabled"{/if}>
								{foreach item=FIELD_TYPE from=$ADD_SUPPORTED_FIELD_TYPES}
									{if !$IS_FIELD_EDIT_MODE and $FIELD_TYPE eq 'Relation'} {continue}{/if}
									<option value="{$FIELD_TYPE}" 
											{if ($FIELD_MODEL->getFieldDataTypeLabel() eq $FIELD_TYPE)}selected='selected'{/if}
											{foreach key=TYPE_INFO item=TYPE_INFO_VALUE from=$FIELD_TYPE_INFO[$FIELD_TYPE]}
												data-{$TYPE_INFO}="{$TYPE_INFO_VALUE}"
											{/foreach}>
										{vtranslate($FIELD_TYPE, $QUALIFIED_MODULE)}
									</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="form-group py-3 row">
						<label class="control-label fieldLabel col-lg-4 text-end">
							<span class="me-2">{vtranslate('LBL_LABEL_NAME', $QUALIFIED_MODULE)}</span>
							<span class="text-danger">*</span>
						</label>
						<div class="controls col-lg-6">
							<input type="text" class="inputElement form-control" maxlength="50" {if $IS_FIELD_EDIT_MODE}disabled="disabled"{/if} name="fieldLabel" value="{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}" data-rule-required="true" />
						</div>
					</div>
					{if !$IS_FIELD_EDIT_MODE}
						<div class="form-group py-3 row supportedType lengthsupported row">
							<label class="control-label fieldLabel col-lg-4 text-end">
								<span class="me-2">{vtranslate('LBL_LENGTH', $QUALIFIED_MODULE)}</span>
								<span class="text-danger">*</span>
							</label>
							<div class="controls col-lg-6">
								<input type="text" name="fieldLength" class="inputElement form-control" value="" data-rule-required="true" data-rule-positive="true" data-rule-WholeNumber="true" data-rule-illegal="true"/>
							</div>
						</div>
						<div class="form-group py-3 row supportedType decimalsupported hide">
							<label class="control-label fieldLabel col-lg-4 text-end">
								<span class="me-2">{vtranslate('LBL_DECIMALS', $QUALIFIED_MODULE)}</span>
								<span class="text-danger">*</span>
							</label>
							<div class="controls col-lg-6">
								<input type="text" name="decimal" class="inputElement form-control" value="" data-rule-required="true"/>
							</div>
						</div>
						<div class="form-group py-3 row supportedType preDefinedValueExists hide">
							<label class="control-label fieldLabel col-lg-4 text-end">
								<span class="me-2">{vtranslate('LBL_PICKLIST_VALUES', $QUALIFIED_MODULE)}</span>
								<span class="text-danger">*</span>
							</label>
							<div class="controls col-lg-6">
								<select type="text" id="picklistUi" name="pickListValues" placeholder="{vtranslate('LBL_ENTER_PICKLIST_VALUES', $QUALIFIED_MODULE)}" data-tags="true" multiple="multiple" data-rule-required="true" data-rule-picklist="true">
								</select>
							</div>
						</div>
						<div class="form-group py-3 row supportedType picklistOption hide">
							<label class="control-label fieldLabel col-lg-4 text-end">
								{vtranslate('LBL_ROLE_BASED_PICKLIST',$QUALIFIED_MODULE)}
							</label>
							<div class="controls col-lg-6">
								<div class="checkbox form-check">
									<input type="checkbox" class="form-check-input" name="isRoleBasedPickList" value="1" >
								</div>
							</div>
						</div>
						<div class="form-group py-3 row supportedType relationModules hide">
							<label class="control-label fieldLabel col-lg-4 text-end">
								<span class="me-2">{vtranslate('SELECT_MODULE', $QUALIFIED_MODULE)}</span>
								<span class="text-danger">*</span>
							</label>
							<div class="controls col-lg-6">
								<select class="relationModule" name="relationmodule[]" multiple data-rule-required="true">
									{foreach item=RELATION_MODULE_NAME from=$FIELD_TYPE_INFO['Relation']['relationModules']}
										<option value="{$RELATION_MODULE_NAME}">{vtranslate($RELATION_MODULE_NAME,$RELATION_MODULE_NAME)}</option>
									{/foreach}
								</select>
							</div>
						</div>
					{/if}
					{if $FIELD_MODEL->getFieldDataType() != 'reference'}
						{include file=vtemplate_path('DefaultValueUi.tpl', $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL}
					{/if}
					{if $IS_FIELD_EDIT_MODE}
						<div class="form-group py-3 row">
							<label class="control-label fieldLabel col-lg-4 text-end">
								{vtranslate('LBL_SHOW_FIELD', $QUALIFIED_MODULE)}
							</label>
							<div class="controls col-lg-6">
								<input type="hidden" name="presence" value="1"/>
								<label class="checkbox form-switch">
									<input type="checkbox" class="cursorPointer form-check-input" id="fieldPresence" name="presence" {if $FIELD_MODEL->isViewable()}checked="checked"{/if} {if $FIELD_MODEL->isActiveOptionDisabled()}readonly="readonly"{/if} {if $FIELD_MODEL->isMandatory()}readonly="readonly"{/if} data-on-text="Yes" data-off-text="No" value="{$FIELD_MODEL->get('presence')}"/>
								</label>
							</div>
						</div>
					{else}
						<input type="hidden" name="presence" value="2" />
					{/if}
					<div class="well fieldProperty">
						<div class="properties">
							<div class="form-group py-3 row">
								<div class="controls col-lg-4"></div>
								<label class="control-label fieldLabel col-lg-6 fs-5 fw-bold">
									{vtranslate('LBL_ENABLE_OR_DISABLE_FIELD_PROP',$QUALIFIED_MODULE)}
								</label>
							</div>
							<div class="form-group py-3 row">
								<label class="control-label fieldLabel col-lg-4 text-end">
									<i class="fa fa-exclamation-circle"></i>
									<span class="ms-2">{vtranslate('LBL_MANDATORY_FIELD',$QUALIFIED_MODULE)}</span>
								</label>
								<div class="controls col-lg-6">
									<input type="hidden" name="mandatory" value="O"/>
									<label class="checkbox form-check">
										<input type="checkbox" name="mandatory" class="form-check-input {if $FIELD_MODEL->isMandatoryOptionDisabled()}cursorPointerNotAllowed{else}cursorPointer{/if}" value="M" {if $FIELD_MODEL->isMandatory()}checked="checked"{/if} {if $FIELD_MODEL->isMandatoryOptionDisabled()}readonly="readonly"{/if}/>
									</label>
								</div>
							</div>
							<div class="form-group py-3 row">
								<label class="control-label fieldLabel col-lg-4 text-end">
									<i class="fa fa-plus"></i>
									<span class="ms-2">{vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE)}</span>
								</label>
								<div class="controls col-lg-6">
									{if $FIELD_MODEL->isQuickCreateOptionDisabled()}
										<input type="hidden" name="quickcreate" value={$FIELD_MODEL->get('quickcreate')} />
									{else}
										<input type="hidden" name="quickcreate" value="1" />
									{/if}
									{assign var="IS_QUICKCREATE_SUPPORTED" value="{$FIELD_MODEL->getModule()->isQuickCreateSupported()}"}
									<input type="hidden" name="isquickcreatesupported" value="{$IS_QUICKCREATE_SUPPORTED}">
									<label class="checkbox form-check">
										<input type="checkbox" class="form-check-input {if $FIELD_MODEL->isMandatory() || $FIELD_MODEL->isQuickCreateOptionDisabled()}cursorPointerNotAllowed{else}cursorPointer{/if}" name="quickcreate" value="2" {if ($FIELD_MODEL->get('quickcreate') eq '2' || $FIELD_MODEL->isMandatory()) && $IS_QUICKCREATE_SUPPORTED}checked="checked"{/if} {if $FIELD_MODEL->isMandatory() || $FIELD_MODEL->isQuickCreateOptionDisabled()}readonly="readonly"{/if}/>
									</label>
								</div>
							</div>
							<div class="form-group py-3 row">
								<label class="control-label fieldLabel col-lg-4 text-end">
									<i class="fa fa-key"></i> &nbsp; {vtranslate('LBL_KEY_FIELD_VIEW',$QUALIFIED_MODULE)}
								</label>
								<div class="controls col-lg-6">
									<input type="hidden" name="summaryfield" value="0"/>
									<label class="checkbox form-check">
										<input type="checkbox" class="form-check-input {if $FIELD_MODEL->isSummaryFieldOptionDisabled()}cursorPointerNotAllowed{else}cursorPointer{/if}" name="summaryfield" value="1" {if $FIELD_MODEL->get('summaryfield') eq '1'}checked="checked"{/if}
											{if $FIELD_MODEL->isSummaryFieldOptionDisabled()}readonly="readonly"{/if} />
									</label>
								</div>
							</div>
							<div class="form-group py-3 row">
								<label class="control-label fieldLabel col-lg-4 text-end">
									<i class="fa fa-flag-o"></i> &nbsp; <span>{vtranslate('LBL_HEADER_FIELD',$QUALIFIED_MODULE)}</span>
								</label>
								<div class="controls col-lg-6">
									<input type="hidden" name="headerfield" value="0"/>
									<label class="checkbox form-check">
										<input type="checkbox" class="form-check-input {if $FIELD_MODEL->isHeaderFieldOptionDisabled()}cursorPointerNotAllowed{else}cursorPointer{/if}" name="headerfield" value="1" {if $FIELD_MODEL->get('headerfield') eq '1'}checked="checked"{/if} {if $FIELD_MODEL->isHeaderFieldOptionDisabled() || $IS_NAME_FIELD}readonly="readonly"{/if} />
									</label>
								</div>
							</div>
							<div class="form-group py-3 row">
								<label class="control-label fieldLabel col-lg-4 text-end">
									<img src="{vimage_path('MassEdit.png')}" height=14 width=14/>
									<span class="ms-2">{vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE)}</span>
								</label>
								<div class="controls col-lg-6">
									{if $FIELD_MODEL->isMassEditOptionDisabled()}
										<input type="hidden" name="masseditable" value={$FIELD_MODEL->get('masseditable')} />
									{else}
										<input type="hidden" name="masseditable" value="2" />
									{/if}
									<label class="checkbox form-check">
										<input type="checkbox" class="form-check-input {if $FIELD_MODEL->isMassEditOptionDisabled()}cursorPointerNotAllowed{else}cursorPointer{/if}" name="masseditable" value="1" {if $FIELD_MODEL->get('masseditable') eq '1'}checked="checked"{/if} {if $FIELD_MODEL->isMassEditOptionDisabled()}readonly="readonly"{/if}/>
									</label>
								</div>
							</div>
						</div>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
			</form>
		</div>
		{if $FIELDS_INFO neq '[]'}
			<script type="text/javascript">
				var uimeta = (function () {
					var fieldInfo = {$FIELDS_INFO};
					var newFieldInfo = {$NEW_FIELDS_INFO};
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
							},
							getNewFieldInfo: function () {
								if (newFieldInfo['newfieldinfo']) {
									return newFieldInfo['newfieldinfo']
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