{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	{if !is_array($IMAGE_DETAILS)}
		{assign var=IMAGE_DETAILS value=$RECORD_STRUCTURE_MODEL->getRecord()->getImageDetails()}
	{/if}
	{if $MODULE_NAME eq 'Webforms'}
		<input type="text" readonly="" />
	{else}
		{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
		{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
		{if $FIELD_MODEL->getFieldDataType() eq 'image' || $FIELD_MODEL->getFieldDataType() eq 'file'}
			{if $MODULE neq 'Products'}
				<div class="text-danger mb-3">
					{vtranslate('LBL_NOTE_EXISTING_ATTACHMENTS_WILL_BE_REPLACED', $MODULE)}
				</div>
			{/if}
		{/if}
		<div class="fileUploadContainer mb-3">
			<div class="fileUploadBtn btn btn-primary">
				<span>
					<i class="fa fa-laptop"></i>
					<span class="ms-2">{vtranslate('LBL_UPLOAD', $MODULE)}</span>
				</span>
				<input type="file" class="inputElement {if $MODULE eq 'Products'}multi max-6{/if} {if $FIELD_MODEL->get('fieldvalue') and $FIELD_INFO["mandatory"] eq true} ignore-validation {/if}" name="{$FIELD_MODEL->getFieldName()}[]" value="{$FIELD_MODEL->get('fieldvalue')}"
					{if !empty($SPECIAL_VALIDATOR)} data-validator="{Zend_Json::encode($SPECIAL_VALIDATOR)}" {/if}
					{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
					{if php7_count($FIELD_INFO['validator'])} data-specific-rules="{ZEND_JSON::encode($FIELD_INFO["validator"])}" {/if} />
			</div>
			<div class="uploadedFileDetails {if $IS_EXTERNAL_LOCATION_TYPE}hide{/if}">
				<div class="uploadedFileSize"></div>
				<div class="uploadedFileName">
					{if !empty($FIELD_VALUE) && !$REQUEST_INSTANCE['isDuplicate']}
						[{$FIELD_MODEL->getDisplayValue($FIELD_VALUE)}]
					{/if}
				</div>
			</div>
		</div>
		{if $MODULE eq 'Products'}<div id="MultiFile1_wrap_list" class="MultiFile-list"></div>{/if}

		{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
			<div class="row mb-3">
				{if !empty($IMAGE_INFO.url)}
					<div class="col-lg-auto" name="existingImages">
						<img class="rounded" src="{$IMAGE_INFO.url}" data-image-id="{$IMAGE_INFO.id}" style="max-height: 15rem;">
					</div>
					<div class="col-lg-auto py-3">
						[{$IMAGE_INFO.name}]
					</div>
					<div class="col-lg text-end">
						<button type="button" id="file_{$ITER}" class="btn btn-secondary imageDelete">
							<i class="fa fa-trash me-2"></i>
							<span>{vtranslate('LBL_DELETE','Vtiger')}</span>
						</button>
					</div>
				{/if}
			</div>
		{/foreach}
	{/if}
{/strip}
