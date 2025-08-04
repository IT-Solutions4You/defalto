{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<div class="modal-dialog modelContainer">
	<div class="modal-content" style="width:675px;">
	{assign var=HEADER_TITLE value={vtranslate('LBL_NEW_DOCUMENT', $MODULE)}}
	{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
	<div class="modal-body">
		<div class="uploadview-content container-fluid">
			<div id="create">
				<form class="form-horizontal recordEditView" name="upload" method="post" action="index.php">
					{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
						<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
					{/if}
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="action" value="SaveAjax" />
					<input type="hidden" name="document_source" value="Vtiger" />
					<input type="hidden" name='service' value="{$STORAGE_SERVICE}" />
					<input type="hidden" name='type' value="{$FILE_LOCATION_TYPE}" />
					{if $RELATION_OPERATOR eq 'true'}
						<input type="hidden" name="relationOperation" value="{$RELATION_OPERATOR}" />
						<input type="hidden" name="sourceModule" value="{$PARENT_MODULE}" />
						<input type="hidden" name="sourceRecord" value="{$PARENT_ID}" />
						{if $RELATION_FIELD_NAME}
							<input type="hidden" name="{$RELATION_FIELD_NAME}" value="{$PARENT_ID}" /> 
						{/if}
					{/if}

					<div class="massEditTable">
						<div class="row py-2 align-items-center">
							{assign var=FIELD_MODEL value=$FIELD_MODELS['notes_title']}
							<div class="fieldLabel col-lg-3 text-secondary text-end">
								{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
								{if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
							</div>
							<div class="fieldValue col-lg-9">
								{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
							</div>
						</div>
						<div class="row py-2 align-items-center">
							{if $FILE_LOCATION_TYPE eq 'W'}
								<input type="hidden" name='filelocationtype' value="I" />
								{assign var=FIELD_MODEL value=$FIELD_MODELS['notecontent']}
								{if $FIELD_MODELS['notecontent']}
									<div class="fieldLabel col-lg-3 text-secondary text-end">
										{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
										{if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
									</div>
									<div class="fieldValue col-lg-9">
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
									</div>
								{/if}
							{elseif $FILE_LOCATION_TYPE eq 'E'}
								<input type="hidden" name='filelocationtype' value="E" />
								{assign var=FIELD_MODEL value=$FIELD_MODELS['filename']}
								<div class="fieldLabel col-lg-3 text-secondary text-end">
									{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
									{if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
								</div>
								<div class="fieldValue col-lg-9">
									<input type="text" class="form-control {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_MODEL->getFieldName()}" value="{$FIELD_MODEL->get('fieldvalue')}" data-rule-required="true" data-rule-url="true"/>
								</div>
							{/if}
						</div>
						<div class="row py-2 align-items-center">
							{assign var=FIELD_MODEL value=$FIELD_MODELS['assigned_user_id']}
							<div class="fieldLabel col-lg-3 text-secondary text-end">
								{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
								{if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
							</div>
							<div class="fieldValue col-lg-9">
								{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
							</div>
						</div>
						<div class="row py-2 align-items-center">
							{assign var=FIELD_MODEL value=$FIELD_MODELS['folderid']}
							{if $FIELD_MODELS['folderid']}
								<div class="fieldLabel col-lg-3 text-secondary text-end">
									{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
									{if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
								</div>
								<div class="fieldValue col-lg-9">
									{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
								</div>
							{/if}
						</div>
						<div class="row py-2 align-items-center">
							{assign var=HARDCODED_FIELDS value=','|explode:"filename,assigned_user_id,folderid,notecontent,notes_title"}
							{assign var=COUNTER value=0}
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELD_MODELS}
								{if !in_array($FIELD_NAME,$HARDCODED_FIELDS) && $FIELD_MODEL->isQuickCreateEnabled()}
									<div class="fieldLabel col-lg-3 text-secondary text-end">
										{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
										{if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
									</div>
									<div class="fieldValue col-lg-9">
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
									</div>
								{/if}
							{/foreach}
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	{assign var=BUTTON_NAME value={vtranslate('LBL_CREATE', $MODULE)}}
	{assign var=BUTTON_ID value="js-create-document"}
	{include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
	</div>
</div>
{/strip}
