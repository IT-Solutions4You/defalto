{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<input type="hidden" name="merge_type" value='{$USER_INPUT->get('merge_type')}' />
	<input type="hidden" name="merge_fields" value='{$MERGE_FIELDS}' />
	<input type="hidden" name="lineitem_currency" value="{if isset($LINEITEM_CURRENCY)}{$LINEITEM_CURRENCY}{else}''{/if}">
	<input type="hidden" id="mandatory_fields" name="mandatory_fields" value='{$ENCODED_MANDATORY_FIELDS}' />
	<input type="hidden" name="field_mapping" id="field_mapping" value="" />
	<input type="hidden" name="default_values" id="default_values" value="" />
	<table width="100%" class="table table-bordered">
		<thead>
			<tr>
				{if $HAS_HEADER eq true}
					<th width="25%">{'LBL_FILE_COLUMN_HEADER'|@vtranslate:$MODULE}</th>
					{/if}
				<th width="25%">{'LBL_ROW_1'|@vtranslate:$MODULE}</th>
				<th width="23%">{'LBL_CRM_FIELDS'|@vtranslate:$MODULE}</th>
				<th width="27%">{'LBL_DEFAULT_VALUE'|@vtranslate:$MODULE}</th>
			</tr>
		</thead>
		<tbody>
			{foreach key=_HEADER_NAME item=_FIELD_VALUE from=$ROW_1_DATA name="headerIterator"}
				{assign var="_COUNTER" value=$smarty.foreach.headerIterator.iteration}
				<tr class="fieldIdentifier" id="fieldIdentifier{$_COUNTER}">
					{if $HAS_HEADER eq true}
						<td>
							<span style="word-break:break-all" name="header_name">{$_HEADER_NAME}</span>
						</td>
					{/if}
					<td>
						<span>{$_FIELD_VALUE|@textlength_check}</span>
					</td>
					<td>
						<input type="hidden" name="row_counter" value="{$_COUNTER}" />
						<select name="mapped_fields" class="select2 mappedFieldsSelect" style="width:100%" onchange="Vtiger_Import_Js.loadDefaultValueWidget('fieldIdentifier{$_COUNTER}')">
							<option value="">{'LBL_SELECT_OPTION'|@vtranslate:$FOR_MODULE}</option>
							{foreach key=_FIELD_NAME item=_FIELD_INFO from=$AVAILABLE_FIELDS}
								{assign var="_TRANSLATED_FIELD_LABEL" value=$_FIELD_INFO->getFieldLabelKey()|@vtranslate:$FOR_MODULE}
								<option value="{$_FIELD_NAME}" {if strtolower(decode_html($_HEADER_NAME)) eq strtolower($_TRANSLATED_FIELD_LABEL)} selected {/if}
										data-label="{$_TRANSLATED_FIELD_LABEL}">{$_TRANSLATED_FIELD_LABEL}{if $_FIELD_INFO->isMandatory() eq 'true'}&nbsp; (*){/if}</option>
							{/foreach}
						</select>
					</td>
					<td name="default_value_container"></td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/strip}