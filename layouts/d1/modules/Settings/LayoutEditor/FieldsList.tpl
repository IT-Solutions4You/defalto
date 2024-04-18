{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}

{strip}
	{assign var=IS_SORTABLE value=$SELECTED_MODULE_MODEL->isSortableAllowed()}
	{assign var=ALL_BLOCK_LABELS value=[]}

	<div class="row fieldsListContainer">
		<div class="col-sm-6">
			<button class="btn btn-outline-secondary addButton addCustomBlock" type="button">
				<i class="fa fa-plus"></i>
				<span class="ms-2">{vtranslate('LBL_ADD_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</span>
			</button>
		</div>
		<div class="col-sm-6">
			{if $IS_SORTABLE}
				<span class="pull-right">
					<button class="btn btn-primary active saveFieldSequence" type="button" style="opacity:0;">
						{vtranslate('LBL_SAVE_LAYOUT', $QUALIFIED_MODULE)}
					</button>
				</span>
			{/if}
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div id="moduleBlocks">
				{foreach key=BLOCK_LABEL_KEY item=BLOCK_MODEL from=$BLOCKS}
					{assign var=IS_BLOCK_SORTABLE value=$SELECTED_MODULE_MODEL->isBlockSortableAllowed($BLOCK_LABEL_KEY)}
					{assign var=FIELDS_LIST value=$BLOCK_MODEL->getLayoutBlockActiveFields()}
					{assign var=BLOCK_ID value=$BLOCK_MODEL->get('id')}
					{if $BLOCK_LABEL_KEY neq 'LBL_INVITE_USER_BLOCK'}
						{$ALL_BLOCK_LABELS[$BLOCK_ID] = $BLOCK_MODEL}
					{/if}
					<div id="block_{$BLOCK_ID}" class="editFieldsTable border rounded container-fluid my-3 block_{$BLOCK_ID} {if $IS_BLOCK_SORTABLE}blockSortable{/if}" data-block-id="{$BLOCK_ID}" data-sequence="{$BLOCK_MODEL->get('sequence')}" data-custom-fields-count="{$BLOCK_MODEL->getCustomFieldsCount()}">
						<div>
							<div class="layoutBlockHeader row align-items-center py-3">
								<div class="blockLabel col-sm-3" style="word-break: break-all;">
									{if $IS_BLOCK_SORTABLE}
										<img class="cursorPointerMove p-3" src="{vimage_path('drag.png')}" />
									{/if}
									<strong class="translatedBlockLabel">{vtranslate($BLOCK_LABEL_KEY, $SELECTED_MODULE_NAME)}</strong>
								</div>
								<div class="col-sm-9 text-end">
									<div class="blockActions px-3">
										<label class="me-2 btn btn-outline-secondary" title="{vtranslate('LBL_COLLAPSE_BLOCK_DETAIL_VIEW', $QUALIFIED_MODULE)}">
											<div class="form-check form-switch form-check-reverse m-0">
												<span class="form-check-label text-secondary">
													<i class="fa fa-info-circle"></i>
													<span class="mx-2">{vtranslate('LBL_COLLAPSE_BLOCK', $QUALIFIED_MODULE)}</span>
												</span>
												<input type="checkbox" {if $BLOCK_MODEL->isHidden()} checked value='0' {else} value='1' {/if} class="form-check-input" name="collapseBlock" data-on-text="{vtranslate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{vtranslate('LBL_NO', $QUALIFIED_MODULE)}" data-on-color="primary" data-block-id="{$BLOCK_MODEL->get('id')}"/>
											</div>
										</label>
										{if $BLOCK_MODEL->isAddCustomFieldEnabled()}
											<button class="btn btn-outline-secondary addButton addCustomField ms-2" type="button">
												<i class="fa fa-plus"></i>
												<span class="ms-2">{vtranslate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</span>
											</button>
										{/if}
										{if $BLOCK_MODEL->isActionsAllowed()}
											<button class="inActiveFields addButton btn btn-outline-secondary ms-2">{vtranslate('LBL_SHOW_HIDDEN_FIELDS', $QUALIFIED_MODULE)}</button>
											{if $BLOCK_MODEL->isCustomized()}
												<button class="deleteCustomBlock addButton btn btn-outline-secondary ms-2">{vtranslate('LBL_DELETE_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</button>
											{/if}
										{/if}
									</div>
								</div>
							</div>
						</div>
						{assign var=IS_FIELDS_SORTABLE value=$SELECTED_MODULE_MODEL->isFieldsSortableAllowed($BLOCK_LABEL_KEY)}
						<div class="blockFieldsList row justify-content-around py-3 {if $IS_FIELDS_SORTABLE} blockFieldsSortable {/if}">
							<ul name="{if $IS_FIELDS_SORTABLE}sortable1{else}unSortable1{/if}" class="connectedSortable col-sm-6">
								{foreach item=FIELD_MODEL from=$FIELDS_LIST name=fieldlist}
									{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
									{if $smarty.foreach.fieldlist.index % 2 eq 0}
										<li>
											<div class="row border mb-3 rounded">
												<div class="col-sm-4 border-end">
													<div class="opacity editFields py-3 row" data-block-id="{$BLOCK_ID}" data-field-id="{$FIELD_MODEL->get('id')}" data-sequence="{$FIELD_MODEL->get('sequence')}" data-field-name="{$FIELD_MODEL->get('name')}">
														{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
														<div class="col-sm-3">
															{if $IS_FIELDS_SORTABLE}
																<img src="{vimage_path('drag.png')}" class="cursorPointerMove p-3" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
															{/if}
														</div>
														<div class="col-sm-9" style="word-wrap: break-word;">
															<div class="fieldLabelContainer text-end">
																<div class="fieldLabel">
																	<b>{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}</b>
																	{if $IS_MANDATORY}<span class="text-danger ms-2">*</span>{/if}
																</div>
																<div style="opacity:0.6;">
																	{vtranslate($FIELD_MODEL->getFieldDataTypeLabel(),$QUALIFIED_MODULE)}
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="col-sm-8 fieldPropertyContainer">
													<div class="row py-3">
														{assign var=M_FIELD_TITLE value={vtranslate('LBL_MAKE_THIS_FIELD', $QUALIFIED_MODULE, vtranslate('LBL_PROP_MANDATORY',$QUALIFIED_MODULE))}}
														{assign var=Q_FIELD_TITLE value={vtranslate('LBL_SHOW_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE))}}
														{assign var=M_E_FIELD_TITLE value={vtranslate('LBL_SHOW_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE))}}
														{assign var=S_FIELD_TITLE value={vtranslate('LBL_SHOW_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE))}}
														{assign var=H_FIELD_TITLE value={vtranslate('LBL_SHOW_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_DETAIL_HEADER',$QUALIFIED_MODULE))}}
														{assign var=NOT_M_FIELD_TITLE value={vtranslate('LBL_NOT_MAKE_THIS_FIELD', $QUALIFIED_MODULE, vtranslate('LBL_PROP_MANDATORY',$QUALIFIED_MODULE))}}
														{assign var=NOT_Q_FIELD_TITLE value={vtranslate('LBL_HIDE_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE))}}
														{assign var=NOT_M_E_FIELD_TITLE value={vtranslate('LBL_HIDE_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE))}}
														{assign var=NOT_S_FIELD_TITLE value={vtranslate('LBL_HIDE_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE))}}
														{assign var=NOT_H_FIELD_TITLE value={vtranslate('LBL_HIDE_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_DETAIL_HEADER',$QUALIFIED_MODULE))}}
														{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
														<div class="fieldProperties col-sm" data-field-id="{$FIELD_MODEL->get('id')}">
															<div class="mandatory switch text-capitalize {if (!$IS_MANDATORY)}disabled{/if} {if $FIELD_MODEL->isMandatoryOptionDisabled()}cursorPointerNotAllowed{else}cursorPointer{/if}" data-bs-toggle="tooltip" title="{if $IS_MANDATORY}{$NOT_M_FIELD_TITLE}{else}{$M_FIELD_TITLE}{/if}">
																<i class="fa fa-exclamation-circle" data-name="mandatory" data-enable-value="M" data-disable-value="O" {if $FIELD_MODEL->isMandatoryOptionDisabled()}readonly="readonly"{/if}></i>
																<span class="ms-2">{vtranslate('LBL_PROP_MANDATORY',$QUALIFIED_MODULE)}</span>
															</div>
															{assign var=IS_QUICK_EDIT_ENABLED value=$FIELD_MODEL->isQuickCreateEnabled()}
															<div class="quickCreate switch {if (!$IS_QUICK_EDIT_ENABLED)}disabled{/if} {if $FIELD_MODEL->isQuickCreateOptionDisabled() || $IS_MANDATORY }cursorPointerNotAllowed{else}cursorPointer{/if}" data-bs-toggle="tooltip" title="{if $IS_QUICK_EDIT_ENABLED}{$NOT_Q_FIELD_TITLE}{else}{$Q_FIELD_TITLE}{/if}">
																<i class="fa fa-plus" data-name="quickcreate" data-enable-value="2" data-disable-value="1" {if $FIELD_MODEL->isQuickCreateOptionDisabled() || $IS_MANDATORY }readonly="readonly"{/if} title="{vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE)}"></i>
																<span class="ms-2">{vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE)}</span>
															</div>
															{assign var=IS_MASS_EDIT_ENABLED value=$FIELD_MODEL->isMassEditable()}
															<div class="massEdit switch {if (!$IS_MASS_EDIT_ENABLED)} disabled {/if} {if $FIELD_MODEL->isMassEditOptionDisabled()}cursorPointerNotAllowed{else}cursorPointer{/if}" data-bs-toggle="tooltip" title="{if $IS_MASS_EDIT_ENABLED}{$NOT_M_E_FIELD_TITLE}{else}{$M_E_FIELD_TITLE}{/if}">
																<img src="{vimage_path('MassEdit.png')}" data-name="masseditable" data-enable-value="1" data-disable-value="2" title="{vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE)}" {if $FIELD_MODEL->isMassEditOptionDisabled()}readonly="readonly"{/if} height=14 width=14 />
																<span class="ms-2">{vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE)}</span>
															</div>
															{assign var=IS_HEADER_FIELD value=$FIELD_MODEL->isHeaderField()}
															<div class="header switch {if (!$IS_HEADER_FIELD)}disabled{/if} {if $FIELD_MODEL->isHeaderFieldOptionDisabled()}cursorPointerNotAllowed{else}cursorPointer{/if}" data-bs-toggle="tooltip" title="{if $IS_HEADER_FIELD}{$NOT_H_FIELD_TITLE}{else}{$H_FIELD_TITLE}{/if}">
																<i class="fa fa-flag-o" data-name="headerfield" data-enable-value="1" data-disable-value="0" {if $FIELD_MODEL->isHeaderFieldOptionDisabled()}readonly="readonly"{/if} title="{vtranslate('LBL_HEADER',$QUALIFIED_MODULE)}"></i>
																<span class="ms-2">{vtranslate('LBL_HEADER',$QUALIFIED_MODULE)}</span>
															</div>
															{assign var=IS_SUMMARY_VIEW_ENABLED value=$FIELD_MODEL->isSummaryField()}
															<div class="summary switch {if (!$IS_SUMMARY_VIEW_ENABLED)} disabled {/if} {if $FIELD_MODEL->isSummaryFieldOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer {/if}" data-toggle="tooltip" title="{if $IS_SUMMARY_VIEW_ENABLED}{$NOT_S_FIELD_TITLE}{else}{$S_FIELD_TITLE}{/if}">
																<i class="fa fa-key" data-name="summaryfield" data-enable-value="1" data-disable-value="0" {if $FIELD_MODEL->isSummaryFieldOptionDisabled()}readonly="readonly"{/if} title="{vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE)}"></i>
																<span class="ms-2">{vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE)}</span>
															</div>
															<div class="defaultValue col-sm-12 {if !$FIELD_MODEL->hasDefaultValue()}disabled{/if} 
																 {if $FIELD_MODEL->isDefaultValueOptionDisabled()} cursorPointerNotAllowed {/if}">
																{assign var=DEFAULT_VALUE value=$FIELD_MODEL->getDefaultFieldValueToViewInV7FieldsLayOut()}
																{if $DEFAULT_VALUE}
																	{if is_array($DEFAULT_VALUE)}
																		{foreach key=DEFAULT_FIELD_NAME item=DEFAULT_FIELD_VALUE from=$DEFAULT_VALUE}
																			<div class="defaultValueContent">
																				<span class="me-2">
																					<img src="{vimage_path('DefaultValue.png')}" {if $FIELD_MODEL->isDefaultValueOptionDisabled()}readonly="readonly"{/if} {if $FIELD_MODEL->hasDefaultValue()}title="{$DEFAULT_VALUE}"{/if} data-name="defaultValueField" height=14 width=14 />
																				</span>
																				{if $DEFAULT_FIELD_VALUE}
																					{assign var=DEFAULT_FIELD_NAME value=$DEFAULT_FIELD_NAME|upper}
																					<span class="me-2">{vtranslate('LBL_DEFAULT_VALUE',$QUALIFIED_MODULE)} {vtranslate("LBL_$DEFAULT_FIELD_NAME",$QUALIFIED_MODULE)} :</span>
																					<span data-defaultvalue-fieldname="{$DEFAULT_FIELD_NAME}" data-defaultvalue="{$DEFAULT_FIELD_VALUE}">{$DEFAULT_FIELD_VALUE}</span>
																				{else}
																					<span>{vtranslate('LBL_DEFAULT_VALUE_NOT_SET',$QUALIFIED_MODULE)}</span>
																				{/if}
																			</div>
																		{/foreach}
																	{else}
																		<div class="defaultValueContent">
																			<span class="me-2">
																				<img src="{vimage_path('DefaultValue.png')}"
																					 {if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if}
																					 {if $FIELD_MODEL->hasDefaultValue()} title="{$DEFAULT_VALUE|strip_tags}" {/if}
																					 data-name="defaultValueField" height=14 width=14 />
																			</span>
																			<span class="me-2">{vtranslate('LBL_DEFAULT_VALUE',$QUALIFIED_MODULE)} : </span>
																			<span data-defaultvalue="{$DEFAULT_VALUE|strip_tags}">{$DEFAULT_VALUE|strip_tags}</span>
																		</div>
																	{/if}
																{else}
																	<div class="defaultValueContent">
																		<span class="me-2">
																			<img src="{vimage_path('DefaultValue.png')}"
																				 {if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if}
																				 {if $FIELD_MODEL->hasDefaultValue()} title="{$DEFAULT_VALUE}" {/if}
																				 data-name="defaultValueField" height=14 width=14 />
																		</span>
																		<span>{vtranslate('LBL_DEFAULT_VALUE_NOT_SET',$QUALIFIED_MODULE)}</span>
																	</div>
																{/if}
															</div>
														</div>
														<span class="col-sm-auto actions">
															{if $FIELD_MODEL->isEditable()}
																<a href="javascript:void(0)" class="editFieldDetails btn">
																	<i class="fa fa-pencil" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></i>
																</a>
															{/if}
															{if $FIELD_MODEL->isCustomField() eq 'true'}
																<a href="javascript:void(0)" class="deleteCustomField btn" data-field-id="{$FIELD_MODEL->get('id')}"
																	data-one-one-relationship="{$FIELD_MODEL->isOneToOneRelationField()}" data-relationship-field="{$FIELD_MODEL->isRelationShipReponsibleField()}"
																	{if $FIELD_MODEL->isOneToOneRelationField()}
																		{assign var=ONE_ONE_RELATION_FIELD_LABEL value=$FIELD_MODEL->getOneToOneRelationField()->get('label')}
																		{assign var=ONE_ONE_RELATION_MODULE_NAME value=$FIELD_MODEL->getOneToOneRelationField()->getModuleName()}
																		{assign var=ONE_ONE_RELATION_FIELD_NAME value=$FIELD_MODEL->getOneToOneRelationField()->getName()}
																		data-relation-field-label="{$ONE_ONE_RELATION_FIELD_LABEL}" 
																		data-relation-module-label="{vtranslate($ONE_ONE_RELATION_MODULE_NAME,$ONE_ONE_RELATION_MODULE_NAME)}"
																		data-current-field-label ="{vtranslate($FIELD_MODEL->get('label'),$SELECTED_MODULE_NAME)}"
																		data-current-module-label="{vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME)}"
																		data-field-name="{$ONE_ONE_RELATION_FIELD_NAME}"
																	{/if}
																	{if $FIELD_MODEL->isRelationShipReponsibleField()}
																		{assign var=RELATION_MODEL value=$FIELD_MODEL->getRelationShipForThisField()}

																		data-relation-field-label="{vtranslate($FIELD_MODEL->get('label'),$RELATION_MODEL->getRelationModuleName())}" 
																		data-relation-module-label="{vtranslate($RELATION_MODEL->getRelationModuleName(),$RELATION_MODEL->getRelationModuleName())}"
																		data-current-module-label="{vtranslate($RELATION_MODEL->getParentModuleName(),$RELATION_MODEL->getParentModuleName())}"
																		data-current-tab-label="{vtranslate($RELATION_MODEL->get('label'), $RELATION_MODEL->getRelationModuleName())}"
																	{/if} >
																	<i class="fa fa-trash" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></i>
																</a>
															{/if}
														</span>
													</div>
												</div>
											</div>
										</li>
									{/if}
								{/foreach}
								{if php7_count($FIELDS_LIST)%2 eq 0 }
									{if $BLOCK_MODEL->isAddCustomFieldEnabled()}
										<li class="row dummyRow py-3 border rounded mb-3">
											<div class="col-sm-8 dragUiText fs-5 text-end">
												{vtranslate('LBL_ADD_NEW_FIELD_HERE',$QUALIFIED_MODULE)}
											</div>
											<div class="col-sm-4">
												<button class="btn btn-outline-secondary addButton">
													<i class="fa fa-plus"></i>
													<span class="ms-2">{vtranslate('LBL_ADD',$QUALIFIED_MODULE)}</span>
												</button>
											</div>
										</li>
									{/if}
								{/if}
							</ul>
							<ul name="{if $IS_FIELDS_SORTABLE}sortable2{else}unSortable2{/if}" class="connectedSortable col-sm-6">
								{foreach item=FIELD_MODEL from=$FIELDS_LIST name=fieldlist1}
									{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
									{if $smarty.foreach.fieldlist1.index % 2 neq 0}
										<li>
											<div class="row border mb-3 rounded">
												<div class="col-sm-4 border-end">
													<div class="opacity editFields py-3 row" data-block-id="{$BLOCK_ID}" data-field-id="{$FIELD_MODEL->get('id')}" data-sequence="{$FIELD_MODEL->get('sequence')}" data-field-name="{$FIELD_MODEL->get('name')}" >
														{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
														<span class="col-sm-3">
															{if $FIELD_MODEL->isEditable() && $IS_FIELDS_SORTABLE}
																<img src="{vimage_path('drag.png')}" class="cursorPointerMove p-3" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
															{/if}
														</span>
														<div class="col-sm-9" style="word-wrap: break-word;">
															<div class="fieldLabelContainer text-end">
																<div class="fieldLabel">
																	<b>{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}</b>
																	{if $IS_MANDATORY}<span class="text-danger ms-2">*</span>{/if}
																</div>
																<div style="opacity:0.6;">
																	{vtranslate($FIELD_MODEL->getFieldDataTypeLabel(),$QUALIFIED_MODULE)}
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="col-sm-8 fieldPropertyContainer">
													<div class="row py-3">
														{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
														<div class="fieldProperties col-sm" data-field-id="{$FIELD_MODEL->get('id')}">
															<div class="mandatory switch text-capitalize {if (!$IS_MANDATORY)}disabled{/if} {if $FIELD_MODEL->isMandatoryOptionDisabled()}cursorPointerNotAllowed{else}cursorPointer{/if}" data-bs-toggle="tooltip" title="{if $IS_MANDATORY}{$NOT_M_FIELD_TITLE}{else}{$M_FIELD_TITLE}{/if}">
																<i class="fa fa-exclamation-circle" data-name="mandatory" data-enable-value="M" data-disable-value="O" {if $FIELD_MODEL->isMandatoryOptionDisabled()}readonly="readonly"{/if}></i>
																<span class="ms-2">{vtranslate('LBL_PROP_MANDATORY',$QUALIFIED_MODULE)}</span>
															</div>
															{assign var=IS_QUICK_EDIT_ENABLED value=$FIELD_MODEL->isQuickCreateEnabled()}
															<div class="quickCreate switch {if (!$IS_QUICK_EDIT_ENABLED)}disabled{/if} {if $FIELD_MODEL->isQuickCreateOptionDisabled() || $IS_MANDATORY } cursorPointerNotAllowed {else} cursorPointer {/if}" data-bs-toggle="tooltip" title="{if $IS_QUICK_EDIT_ENABLED}{$NOT_Q_FIELD_TITLE}{else}{$Q_FIELD_TITLE}{/if}">
																<i class="fa fa-plus" data-name="quickcreate" data-enable-value="2" data-disable-value="1" {if $FIELD_MODEL->isQuickCreateOptionDisabled() || $IS_MANDATORY }readonly="readonly"{/if} title="{vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE)}"></i>
																<span class="ms-2">{vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE)}</span>
															</div>
															{assign var=IS_MASS_EDIT_ENABLED value=$FIELD_MODEL->isMassEditable()}
															<div class="massEdit switch {if (!$IS_MASS_EDIT_ENABLED)} disabled {/if} {if $FIELD_MODEL->isMassEditOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer {/if}" data-bs-toggle="tooltip" title="{if $IS_MASS_EDIT_ENABLED}{$NOT_M_E_FIELD_TITLE}{else}{$M_E_FIELD_TITLE}{/if}">
																<img src="{vimage_path('MassEdit.png')}" data-name="masseditable" data-enable-value="1" data-disable-value="2" title="{vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE)}" {if $FIELD_MODEL->isMassEditOptionDisabled()}readonly="readonly"{/if} height=14 width=14 />
																<span class="ms-2">{vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE)}</span>
															</div>
															{assign var=IS_HEADER_FIELD value=$FIELD_MODEL->isHeaderField()}
															<div class="header switch {if (!$IS_HEADER_FIELD)} disabled {/if} {if $FIELD_MODEL->isHeaderFieldOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer {/if}" data-bs-toggle="tooltip" title="{if $IS_HEADER_FIELD}{$NOT_H_FIELD_TITLE}{else}{$H_FIELD_TITLE}{/if}">
																<i class="fa fa-flag-o" data-name="headerfield" data-enable-value="1" data-disable-value="0" {if $FIELD_MODEL->isHeaderFieldOptionDisabled()}readonly="readonly"{/if} title="{vtranslate('LBL_HEADER',$QUALIFIED_MODULE)}"></i>
																<span class="ms-2">{vtranslate('LBL_HEADER',$QUALIFIED_MODULE)}</span>
															</div>
															{assign var=IS_SUMMARY_VIEW_ENABLED value=$FIELD_MODEL->isSummaryField()}
															<div class="summary switch {if (!$IS_SUMMARY_VIEW_ENABLED)} disabled {/if} {if $FIELD_MODEL->isSummaryFieldOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer {/if}" data-bs-toggle="tooltip" title="{if $IS_SUMMARY_VIEW_ENABLED}{$NOT_S_FIELD_TITLE}{else}{$S_FIELD_TITLE}{/if}">
																<i class="fa fa-key" data-name="summaryfield" data-enable-value="1" data-disable-value="0" {if $FIELD_MODEL->isSummaryFieldOptionDisabled()}readonly="readonly"{/if} title="{vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE)}"></i>
																<span class="ms-2">{vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE)}</span>
															</div>
															<div class="defaultValue col-sm-12 {if !$FIELD_MODEL->hasDefaultValue()}disabled{/if} {if $FIELD_MODEL->isDefaultValueOptionDisabled()}cursorPointerNotAllowed{/if}">
																{assign var=DEFAULT_VALUE value=$FIELD_MODEL->getDefaultFieldValueToViewInV7FieldsLayOut()}
																{if $DEFAULT_VALUE}
																	{if is_array($DEFAULT_VALUE)}
																		{foreach key=DEFAULT_FIELD_NAME item=DEFAULT_FIELD_VALUE from=$DEFAULT_VALUE}
																			<div class="defaultValueContent">
																				<span class="me-2">
																					<img src="{vimage_path('DefaultValue.png')}" {if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if} {if $FIELD_MODEL->hasDefaultValue()} title="{$DEFAULT_VALUE}" {/if} data-name="defaultValueField" height=14 width=14/>
																				</span>
																				{if $DEFAULT_FIELD_VALUE}
																					{assign var=DEFAULT_FIELD_NAME value=$DEFAULT_FIELD_NAME|upper}
																					<span>{vtranslate('LBL_DEFAULT_VALUE',$QUALIFIED_MODULE)} {vtranslate("LBL_$DEFAULT_FIELD_NAME",$QUALIFIED_MODULE)}:</span>
																					<span data-defaultvalue-fieldname="{$DEFAULT_FIELD_NAME}" data-defaultvalue="{$DEFAULT_FIELD_VALUE}">{$DEFAULT_FIELD_VALUE}</span>
																				{else}
																					<span>{vtranslate('LBL_DEFAULT_VALUE_NOT_SET',$QUALIFIED_MODULE)}</span>
																				{/if}
																			</div>
																		{/foreach}
																	{else}
																		<div class="defaultValueContent">
																			<span class="me-2">
																				<img src="{vimage_path('DefaultValue.png')}" height=14 width=14 {if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if} {if $FIELD_MODEL->hasDefaultValue()} title="{$DEFAULT_VALUE|strip_tags}" {/if}>
																			</span>
																			<span>{vtranslate('LBL_DEFAULT_VALUE',$QUALIFIED_MODULE)} : </span>
																			<span data-defaultvalue="{$DEFAULT_VALUE|strip_tags}">{$DEFAULT_VALUE|strip_tags}</span>
																		</div>
																	{/if}
																{else}
																	<div class="defaultValueContent">
																		<span class="me-2">
																			<img src="{vimage_path('DefaultValue.png')}" {if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if} {if $FIELD_MODEL->hasDefaultValue()} title="{$DEFAULT_VALUE}" {/if} data-name="defaultValueField" height=14 width=14 />
																		</span>
																		<span>{vtranslate('LBL_DEFAULT_VALUE_NOT_SET',$QUALIFIED_MODULE)}</span>
																	</div>
																{/if}
															</div>
														</div>
														<span class="col-sm-auto actions">
															{if $FIELD_MODEL->isEditable()}
																<a href="javascript:void(0)" class="editFieldDetails btn">
																	<i class="fa fa-pencil" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></i>
																</a>
															{/if}
															{if $FIELD_MODEL->isCustomField() eq 'true'}
																<a href="javascript:void(0)" class="deleteCustomField btn" data-field-id="{$FIELD_MODEL->get('id')}"
																	data-one-one-relationship="{$FIELD_MODEL->isOneToOneRelationField()}" data-relationship-field="{$FIELD_MODEL->isRelationShipReponsibleField()}"
																	{if $FIELD_MODEL->isOneToOneRelationField()}
																		{assign var=ONE_ONE_RELATION_FIELD_LABEL value=$FIELD_MODEL->getOneToOneRelationField()->get('label')}
																		{assign var=ONE_ONE_RELATION_MODULE_NAME value=$FIELD_MODEL->getOneToOneRelationField()->getModuleName()}
																		{assign var=ONE_ONE_RELATION_FIELD_NAME value=$FIELD_MODEL->getOneToOneRelationField()->getName()}
																		data-relation-field-label="{$ONE_ONE_RELATION_FIELD_LABEL}" 
																		data-relation-module-label="{vtranslate($ONE_ONE_RELATION_MODULE_NAME,$ONE_ONE_RELATION_MODULE_NAME)}"
																		data-current-field-label ="{vtranslate($FIELD_MODEL->get('label'),$SELECTED_MODULE_NAME)}"
																		data-current-module-label="{vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME)}"
																		data-field-name="{$ONE_ONE_RELATION_FIELD_NAME}"
																	{/if}
																	{if $FIELD_MODEL->isRelationShipReponsibleField()}
																		{assign var=RELATION_MODEL value=$FIELD_MODEL->getRelationShipForThisField()}

																		data-relation-field-label="{vtranslate($FIELD_MODEL->get('label'),$RELATION_MODEL->getRelationModuleName())}" 
																		data-relation-module-label="{vtranslate($RELATION_MODEL->getRelationModuleName(),$RELATION_MODEL->getRelationModuleName())}"
																		data-current-module-label="{vtranslate($RELATION_MODEL->getParentModuleName(),$RELATION_MODEL->getParentModuleName())}"
																		data-current-tab-label="{vtranslate($RELATION_MODEL->get('label'), $RELATION_MODEL->getRelationModuleName())}"
																	{/if} >
																	<i class="fa fa-trash" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></i>
																</a>
															{/if}
														</span>
													</div>
												</div>
											</div>
										</li>
									{/if}
								{/foreach}
								{if php7_count($FIELDS_LIST)%2 neq 0 }
									{if $BLOCK_MODEL->isAddCustomFieldEnabled()}
										<li class="row dummyRow py-3 border rounded mb-3">
											<div class="dragUiText col-sm-8 fs-5 text-end">
												{vtranslate('LBL_ADD_NEW_FIELD_HERE',$QUALIFIED_MODULE)}
											</div>
											<div class="col-sm-4">
												<button class="btn btn-outline-secondary addButton">
													<i class="fa fa-plus"></i>
													<span class="ms-2">{vtranslate('LBL_ADD',$QUALIFIED_MODULE)}</span>
												</button>
											</div>
										</li>
									{/if}
								{/if}
							</ul>
						</div>
					</div>
				{/foreach}
			</div>
		</div>
	</div>
	<input type="hidden" class="inActiveFieldsArray" value='{Vtiger_Functions::jsonEncode($IN_ACTIVE_FIELDS)}' />
	<input type="hidden" id="headerFieldsCount" value="{$HEADER_FIELDS_COUNT}">
	<input type="hidden" id="nameFields" value='{Vtiger_Functions::jsonEncode($SELECTED_MODULE_MODEL->getNameFields())}'>
	<input type="hidden" id="headerFieldsMeta" value='{Vtiger_Functions::jsonEncode($HEADER_FIELDS_META)}'>

	<div id="" class="newCustomBlockCopy border rounded container-fluid my-3 blockSortable hide" data-block-id="" data-sequence="">
		<div class="layoutBlockHeader row align-items-center py-3">
			<div class="blockLabel col-sm-3" style="word-break: break-all;">
				<img class="cursorPointerMove p-3" src="{vimage_path('drag.png')}" />
			</div>
			<div class="col-sm-9 text-end">
				<div class="blockActions px-3">
					<label class="me-2 btn btn-outline-secondary" title="{vtranslate('LBL_COLLAPSE_BLOCK_DETAIL_VIEW', $QUALIFIED_MODULE)}">
						<span class="form-check form-switch form-check-reverse m-0">
							<span class="form-check-label text-secondary">
								<i class="fa fa-info-circle"></i>
								<span class="mx-2">{vtranslate('LBL_COLLAPSE_BLOCK', $QUALIFIED_MODULE)}</span>
							</span>
							<input type="checkbox" {if $BLOCK_MODEL->isHidden()} checked value='0' {else} value='1' {/if} class="form-check-input" name="collapseBlock" id="hiddenCollapseBlock" data-on-text="{vtranslate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{vtranslate('LBL_NO', $QUALIFIED_MODULE)}" data-on-color="primary" data-block-id="{$BLOCK_MODEL->get('id')}"/>
						</span>
					</label>
					<button class="btn btn-outline-secondary addButton addCustomField ms-2" type="button">
						<i class="fa fa-plus"></i>
						<span class="ms-2">{vtranslate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</span>
					</button>
					<button class="inActiveFields addButton btn btn-outline-secondary ms-2">{vtranslate('LBL_SHOW_HIDDEN_FIELDS', $QUALIFIED_MODULE)}</button>
					<button class="deleteCustomBlock addButton btn btn-outline-secondary ms-2">{vtranslate('LBL_DELETE_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</button>
				</div>
			</div>
		</div>
		<div class="blockFieldsList row justify-content-around py-3 blockFieldsSortable">
			<ul class="connectedSortable col-sm-6 ui-sortable" name="sortable1">
				<li class="row dummyRow py-3 border rounded mb-3">
					<div class="dragUiText col-sm-8 fs-5 text-end">
						{vtranslate('LBL_ADD_NEW_FIELD_HERE',$QUALIFIED_MODULE)}
					</div>
					<div class="col-sm-4">
						<button class="btn btn-outline-secondary addButton">
							<i class="fa fa-plus"></i>
							<span class="ms-2">{vtranslate('LBL_ADD',$QUALIFIED_MODULE)}</span>
						</button>
					</div>
				</li>
			</ul>
			<ul class="connectedSortable col-sm-6 ui-sortable" name="sortable2"></ul>
		</div>
	</div>
	<li class="newCustomFieldCopy hide">
		<div class="row border rounded mb-3">
			<div class="col-sm-4 border-end">
				<div class="fieldLabelContainer py-3 text-end" data-field-id="" data-sequence="">
					<div class="row">
						<div class="col-sm-3">
							{if $IS_SORTABLE}
								<img src="{vimage_path('drag.png')}" class="dragImage p-3" border="0" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
							{/if}
						</div>
						<div class="col-sm-9" style="word-wrap: break-word;">
							<div class="fieldLabelContainer">
								<div class="fieldLabel">
									<b></b>
								</div>
								<div>
									<span class="fieldTypeLabel" style="opacity:0.6;"></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-8 fieldPropertyContainer">
				<div class="row py-3">
					<div class="fieldProperties col-sm" data-field-id="">
						<div class="mandatory switch text-capitalize">
							<i class="fa fa-exclamation-circle" data-name="mandatory" data-enable-value="M" data-disable-value="O" title="{vtranslate('LBL_MANDATORY',$QUALIFIED_MODULE)}"></i>
							<span class="ms-2">{vtranslate('LBL_PROP_MANDATORY',$QUALIFIED_MODULE)}</span>
						</div>
						<div class="quickCreate switch">
							<i class="fa fa-plus" data-name="quickcreate" data-enable-value="2" data-disable-value="1" title="{vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE)}"></i>
							<span class="ms-2">{vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE)}</span>
						</div>
						<div class="massEdit switch">
							<img src="{vimage_path('MassEdit.png')}" data-name="masseditable" data-enable-value="1" data-disable-value="2" title="{vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE)}" height=14 width=14 />
							<span class="ms-2">{vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE)}</span>
						</div>
						<div class="header switch">
							<i class="fa fa-flag-o" data-name="headerfield" data-enable-value="1" data-disable-value="0" title="{vtranslate('LBL_HEADER',$QUALIFIED_MODULE)}"></i>
							<span class="ms-2">{vtranslate('LBL_HEADER',$QUALIFIED_MODULE)}</span>
						</div>
						<div class="summary switch">
							<i class="fa fa-key" data-name="summaryfield" data-enable-value="1" data-disable-value="0" title="{vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE)}"></i>
							<span class="ms-2">{vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE)}</span>
						</div>
						<div class="defaultValue col-sm-12">
						</div>
					</div>
					<span class="col-sm-auto actions">
						<a href="javascript:void(0)" class="editFieldDetails btn">
							<i class="fa fa-pencil" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></i>
						</a>
						<a href="javascript:void(0)" class="deleteCustomField btn">
							<i class="fa fa-trash" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></i>
						</a>
					</span>
				</div>
			</div>
		</div>
	</li>

	<div class="modal-dialog modal-content addBlockModal hide">
		{assign var=HEADER_TITLE value={vtranslate('LBL_ADD_CUSTOM_BLOCK', $QUALIFIED_MODULE)}}
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
		<form class="form-horizontal addCustomBlockForm">
			<div class="modal-body container-fluid">
				<div class="form-group row my-3">
					<label class="control-label fieldLabel col-sm-5">
						<span>{vtranslate('LBL_BLOCK_NAME', $QUALIFIED_MODULE)}</span>
						<span class="text-danger ms-2">*</span>
					</label>
					<div class="controls col-sm-6">
						<input type="text" name="label" class="inputElement form-control" data-rule-required="true"/>
					</div>
				</div>
				<div class="form-group row my-3">
					<label class="control-label fieldLabel col-sm-5">
						{vtranslate('LBL_ADD_AFTER', $QUALIFIED_MODULE)}
					</label>
					<div class="controls col-sm-6">
						<select class="form-select" name="beforeBlockId">
							{foreach key=BLOCK_ID item=BLOCK_MODEL from=$ALL_BLOCK_LABELS}
								<option value="{$BLOCK_ID}" data-label="{$BLOCK_MODEL->get('label')}">{vtranslate($BLOCK_MODEL->get('label'), $SELECTED_MODULE_NAME)}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
		</form>
	</div>
	<div class="hide defaultValueIcon">
		<img src="{vimage_path('DefaultValue.png')}" height=14 width=14>
	</div>
	{assign var=FIELD_INFO value=$CLEAN_FIELD_MODEL->getFieldInfo()}
	{include file=vtemplate_path('FieldCreate.tpl','Settings:LayoutEditor') FIELD_MODEL=$CLEAN_FIELD_MODEL IS_FIELD_EDIT_MODE=false}
	<div class="modal-dialog inactiveFieldsModal hide">
		<div class="modal-content">
			{assign var=HEADER_TITLE value={vtranslate('LBL_INACTIVE_FIELDS', $QUALIFIED_MODULE)}}
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
			<form class="form-horizontal inactiveFieldsForm">
				<div class="modal-body">
					<div class="inActiveList row">
						<div class="col-sm-1"></div>
						<div class="list col-sm-10"></div>
						<div class="col-sm-1"></div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="container-fluid">
						<div class="row">
							<div class="col-6 text-end">
								<div class="cancelLinkContainer">
									<a class="cancelLink btn btn-primary" type="reset" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
								</div>
							</div>
							<div class="col-6">
								<button class="btn btn-primary active" type="submit" name="reactivateButton">
									<strong>{vtranslate('LBL_REACTIVATE', $QUALIFIED_MODULE)}</strong>
								</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="ps-scrollbar-y" style="height: 60px;">
	</div>
	{if $FIELDS_INFO neq '[]'}
		<script type="text/javascript">
			var uimeta = (function() {
				var fieldInfo = {$FIELDS_INFO};
				var newFieldInfo = {$NEW_FIELDS_INFO};
				return {
					field: {
						get: function(name, property) {
							if(name && property === undefined) {
								return fieldInfo[name];
							}
							if(name && property) {
								return fieldInfo[name][property]
							}
						},
						isMandatory : function(name){
							if(fieldInfo[name]) {
								return fieldInfo[name].mandatory;
							}
							return false;
						},
						getType : function(name){
							if(fieldInfo[name]) {
								return fieldInfo[name].type
							}
							return false;
						},
						getNewFieldInfo : function() {
							if(newFieldInfo['newfieldinfo']){
								return newFieldInfo['newfieldinfo']
							}
							return false;
						}
					},
				};
			})();
		</script>
	{/if}
{/strip}
