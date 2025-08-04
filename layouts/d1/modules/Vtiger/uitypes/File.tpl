{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	<div class="fileUploadContainer text-left">
		<div class="btn-group">
			<div class="fileUploadBtn btn btn-primary me-2">
				<i class="fa fa-laptop me-2"></i>
				<span class="me-2">{vtranslate('LBL_ATTACH_FILES', $MODULE)}</span>
				<input type="file" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" class="inputElement w-100 {if $MODULE eq 'ModComments'} multi {/if} " maxlength="6" name="{if $MODULE eq 'ModComments'}{$FIELD_MODEL->getFieldName()}[]{else}{$FIELD_MODEL->getFieldName()}{/if}"
						value="{$FIELD_VALUE}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
						{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
						{if php7_count($FIELD_INFO['validator'])}
							data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
						{/if}
						/>
			</div>
			<div class="uploadFileSizeLimit btn" data-bs-toggle="tooltip" data-placement="top" title="{vtranslate('LBL_MAX_UPLOAD_SIZE',$MODULE)} {$MAX_UPLOAD_LIMIT_MB} {vtranslate('MB',$MODULE)}">
				<span class="maxUploadSize" data-value="{$MAX_UPLOAD_LIMIT_BYTES}">
					<i class="fa fa-info-circle"></i>
				</span>
			</div>
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
{/strip}
