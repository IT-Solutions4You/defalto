{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	{assign var=SELECTED_FIELDS value=$CUSTOMVIEW_MODEL->getSelectedFields()}
	{assign var=MODULE_FIELDS value=$MODULE_MODEL->getFields()}
	<div id="filterContainer" class="h-100">
		<form id="CustomView" class="h-100">
			<div class="modal-content border-0" >
				{if $RECORD_ID}
					{assign var="TITLE" value={vtranslate('LBL_EDIT_CUSTOM',$MODULE)}}
				{else}
					{assign var="TITLE" value={vtranslate('LBL_CREATE_LIST',$MODULE)}}
				{/if}
				{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
				<div class="modal-body overflow-auto p-3">
					<div class="customview-content row">
						<input type=hidden name="record" id="record" value="{$RECORD_ID}" />
						<input type="hidden" name="module" value="{$MODULE}" />
						<input type="hidden" name="action" value="Save" />
						<input type="hidden" id="sourceModule" name="source_module" value="{$SOURCE_MODULE}"/>
						<input type="hidden" id="stdfilterlist" name="stdfilterlist" value=""/>
						<input type="hidden" id="advfilterlist" name="advfilterlist" value=""/>
						<input type="hidden" name="status" value="{$CV_PRIVATE_VALUE}"/>
						{if $RECORD_ID}
							<input type="hidden" name="status" value="{$CUSTOMVIEW_MODEL->get('status')}" />
						{/if}
						<input type="hidden" name="date_filters" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATE_FILTERS))}' />
						<div class="form-group p-2">
							<label class="py-2">
								<span>{vtranslate('LBL_VIEW_NAME',$MODULE)}</span>
								<span class="text-danger ms-2">*</span>
							</label>
							<div class="row">
								<div class="col-lg-6">
									<input class="form-control" type="text" data-record-id="{$RECORD_ID}" id="viewname" name="viewname" value="{$CUSTOMVIEW_MODEL->get('viewname')}" data-rule-required="true" data-rule-maxsize="100" data-rule-check-filter-duplicate='{Vtiger_Util_Helper::toSafeHTML(Zend_JSON::encode($CUSTOM_VIEWS_LIST))}'>
								</div>
								<div class="col-lg-6">
									<label class="checkbox-inline p-2">
										<input class="form-check-input" type="checkbox" name="setdefault" value="1" {if $CUSTOMVIEW_MODEL->isDefault()} checked="checked"{/if}>
										<span class="ms-2">{vtranslate('LBL_SET_AS_DEFAULT',$MODULE)}</span>
									</label>
									<label class="checkbox-inline p-2">
										<input class="form-check-input" id="setmetrics" name="setmetrics" type="checkbox" value="1" {if $CUSTOMVIEW_MODEL->get('setmetrics') eq '1'} checked="checked"{/if}>
										<span class="ms-2">{vtranslate('LBL_LIST_IN_METRICS',$MODULE)}</span>
									</label>
								</div>
							</div>
						</div>
						<div class="form-group p-2">
							<label class="py-2">
								{vtranslate('LBL_CHOOSE_COLUMNS',$MODULE)} ({vtranslate('LBL_MAX_NUMBER_FILTER_COLUMNS')})
							</label>
							<div class="columnsSelectDiv clearfix">
								{assign var=MANDATORY_FIELDS value=array()}
								{assign var=NUMBER_OF_COLUMNS_SELECTED value=0}
								{assign var=MAX_ALLOWED_COLUMNS value=15}
								<select name="selectColumns" data-rule-required="true" data-msg-required="{vtranslate('LBL_PLEASE_SELECT_ATLEAST_ONE_OPTION',$SOURCE_MODULE)}" data-placeholder="{vtranslate('LBL_ADD_MORE_COLUMNS',$MODULE)}" multiple class="select2 columnsSelect col-lg-10" id="viewColumnsSelect" >
									{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
										<optgroup label='{vtranslate($BLOCK_LABEL, $SOURCE_MODULE)}'>
											{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
												{* To not show star field in filter select view*}
												{assign var=CV_COLUMN_NAME value=decode_html($FIELD_MODEL->getCustomViewColumnName())}
												{if $FIELD_MODEL->getDisplayType() == '6'}
													{continue}
												{/if}
												{if $FIELD_MODEL->isMandatory()}
													{assign var=MANDATORY_ID value=array_push($MANDATORY_FIELDS, $CV_COLUMN_NAME)}
												{/if}
												{assign var=FIELD_MODULE_NAME value=$FIELD_MODEL->getModule()->getName()}
												<option value="{$CV_COLUMN_NAME}" data-field-name="{$FIELD_NAME}"
													{if in_array(decode_html($CV_COLUMN_NAME), $SELECTED_FIELDS)}
														selected="selected"
													{elseif (!$RECORD_ID) && ($FIELD_MODEL->isSummaryField() || $FIELD_MODEL->isHeaderField()) && ($FIELD_MODULE_NAME eq $SOURCE_MODULE) && (!(preg_match("/\([A-Za-z_0-9]* \; \([A-Za-z_0-9]*\) [A-Za-z_0-9]*\)/", $FIELD_NAME))) && $NUMBER_OF_COLUMNS_SELECTED < $MAX_ALLOWED_COLUMNS}
														selected="selected"
														{assign var=NUMBER_OF_COLUMNS_SELECTED value=$NUMBER_OF_COLUMNS_SELECTED + 1}
														{assign var=SELECTED_ID value=array_push($SELECTED_FIELDS, $CV_COLUMN_NAME)}
													{/if}
													>{Vtiger_Util_Helper::toSafeHTML(vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE))}
													{if $FIELD_MODEL->isMandatory() eq true} <span>*</span> {/if}
												</option>
											{/foreach}
										</optgroup>
									{/foreach}
								</select>
								<input type="hidden" name="columnslist" value='{Vtiger_Functions::jsonEncode($SELECTED_FIELDS)}' />
								<input id="mandatoryFieldsList" type="hidden" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($MANDATORY_FIELDS))}' />
							</div>
							<div class="col-lg-2 col-md-2 col-sm-2"></div>
						</div>
						<div class="form-group p-2">
							<label class="filterHeaders py-2">{vtranslate('LBL_CHOOSE_FILTER_CONDITIONS', $MODULE)}:</label>
							<div class="filterElements well filterConditionContainer filterConditionsDiv">
								{include file='AdvanceFilter.tpl'|@vtemplate_path}
							</div>
						</div>
						<div class="form-group p-2">
							<label class="filterHeaders py-2">{vtranslate('LBL_FILTER_SORTING', $MODULE)}: </label>
							<div class="well">
								{include file='SortBy.tpl'|@vtemplate_path:$MODULE}
							</div>
						</div>
						<div class="form-group p-2">
							<label class="py-2">
								<input type="hidden" name="sharelist" value="0" />
								<input class="form-check-input" type="checkbox" data-toogle-members="true" name="sharelist" value="1" {if $LIST_SHARED} checked="checked"{/if}>
								<span class="ms-2">{vtranslate('LBL_SHARE_THIS_LIST',$MODULE)}</span>
							</label>
							<select id="memberList" class="col-lg-7 col-md-7 col-sm-7 select2 members op0{if $LIST_SHARED} fadeInx{/if}" multiple="true" name="members[]" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $MODULE)}" style="margin-bottom: 10px;" data-rule-required="{if $LIST_SHARED}true{else}false{/if}">
								<optgroup label="{vtranslate('LBL_ALL',$MODULE)}">
									<option value="All::Users" data-member-type="{vtranslate('LBL_ALL',$MODULE)}"
											{if ($CUSTOMVIEW_MODEL->get('status') == $CV_PUBLIC_VALUE)} selected="selected"{/if}>
										{vtranslate('LBL_ALL_USERS',$MODULE)}
									</option>
								</optgroup>
								{foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
									{assign var=TRANS_GROUP_LABEL value=$GROUP_LABEL}
									{if $GROUP_LABEL eq 'RoleAndSubordinates'}
										{assign var=TRANS_GROUP_LABEL value='LBL_ROLEANDSUBORDINATE'}
									{/if}
									{assign var=TRANS_GROUP_LABEL value={vtranslate($TRANS_GROUP_LABEL)}}
									<optgroup label="{$TRANS_GROUP_LABEL}">
										{foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
											<option value="{$MEMBER->getId()}" data-member-type="{$GROUP_LABEL}" {if isset($SELECTED_MEMBERS_GROUP[$GROUP_LABEL][$MEMBER->getId()])}selected="true"{/if}>{$MEMBER->getName()}</option>
										{/foreach}
									</optgroup>
								{/foreach}
							</select>
							<input type="hidden" name="status" id="allUsersStatusValue" value="" data-public="{$CV_PUBLIC_VALUE}" data-private="{$CV_PRIVATE_VALUE}"/>
						</div>
					</div>
				</div>
				<div class="modal-footer modal-overlay-footer border-top border-1 p-3">
					<div class="container-fluid">
						<div class="row">
							<div class="col-6">
								<a class="btn btn-primary cancelLink" href="javascript:void(0);" type="reset" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
							</div>
							<div class="col-6 text-end">
								<button type='submit' class='btn btn-primary active saveButton' id="customViewSubmit">{vtranslate('LBL_SAVE', $MODULE)}</button>&nbsp;&nbsp;
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
{/strip}
