{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<input type="hidden" name="availableFields_{$MODULE}" value='{Vtiger_Functions::jsonEncode($ALLFIELDS)}' />
	<input type="hidden" name="selectedFields_{$MODULE}" value='{Vtiger_Functions::jsonEncode($SELECTED_FIELDS)}' />
	<input type="hidden" name="relatedModules_{$MODULE}" value='{Vtiger_Functions::jsonEncode($RELATED_MODULES[$MODULE])}' />
	<input type="hidden" name="recordPermissions_{$MODULE}" value='{Vtiger_Functions::jsonEncode($RECORD_PERMISSIONS)}'/>
	<div class="row" id="moduleData_{$MODULE}">
		<div class="col-lg-12">
			<h4>{vtranslate('LBL_PORTAL_FIELDS_PRIVILEGES',$QUALIFIED_MODULE)}</h4>
			<hr>
		</div>
		<div class="col-sm-6 col-xs-6 portal-fields-container-wrapper container-fluid">
			<div class="row">
				<div class="col-sm-6 col-xs-6">
					<label>{vtranslate('LBL_READ_ONLY',$QUALIFIED_MODULE)}</label>
					<div class="portal-fields-switch" id="readOnlySwitch" disabled></div>
				</div>
				<div class="col-sm-6 col-xs-6">
					<label>{vtranslate('LBL_READ_AND_WRITE',$QUALIFIED_MODULE)}</label>
					<div class="portal-fields-switch portal-fields-switchOn" id="readWriteSwitch" disabled></div>
				</div>
				<div class="col-sm-12 col-xs-12 py-3">
					<span class="text-danger me-2">*</span>
					<span>{vtranslate('Mandatory Fields',$QUALIFIED_MODULE)}</span>
				</div>
			</div>
			<div class="row">
				<div id="fieldRows_{$MODULE}" class="col-sm-12">

				</div>
			</div>
			<br>
			<div class="addFieldsBlock">
				<div class="input-group">
					<select class="inputElement form-select select2 addFields multiple" data-width="70%" data-maximum-selection-length="7" name="addField_{$MODULE}" id="addField_{$MODULE}" multiple="multiple">
						<option></option>
					</select>
					<button title="{vtranslate('LBL_ADD_FIELDS',$QUALIFIED_MODULE)}" class="btn btn-outline-secondary ms-2 rounded" id="addFieldButton_{$MODULE}">{vtranslate('LBL_ADD_FIELDS',$QUALIFIED_MODULE)}</button>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-xs-6 portal-related-information">
			<h4>{vtranslate('LBL_RECORD_VISIBILITY',$QUALIFIED_MODULE)}</h4>
			<div class="portal-record-privilege radio-group my-3">
				<div class="radio label-radio">
					<label class="form-check">
						<input class="form-check-input" type="radio" id="all" name="recordvisible_{$MODULE}" value="all" {if $RECORD_VISIBLE['all'] eq 1 or $MODULE eq 'Faq'}checked{/if}/>
						<span class="ms-2">
							{if $MODULE eq 'Products' or $MODULE eq 'Services'}
								{vtranslate('products_or_services',$QUALIFIED_MODULE,vtranslate($MODULE,$MODULE))}
							{elseif $MODULE eq 'Faq'}
								{vtranslate('faq',$QUALIFIED_MODULE,vtranslate($MODULE,$MODULE))}
							{else}
								{vtranslate('all',$QUALIFIED_MODULE,vtranslate($MODULE,$MODULE))}
							{/if}
						</span>
					</label>
				</div>
				{if $MODULE neq 'Faq'}
					<div class="radio label-radio">
						<label class="form-check">
							<input class="form-check-input" type="radio" id="onlymine" name="recordvisible_{$MODULE}" value="onlymine" {if $RECORD_VISIBLE['onlymine'] eq 1}checked{/if}/>
							<span class="ms-2">{vtranslate('onlymine',$QUALIFIED_MODULE,vtranslate($MODULE,$MODULE))}</span>
						</label>
					</div>
				{/if}
			</div>
			<br>
			{if $MODULE neq 'Faq'}
				<h4>{vtranslate('LBL_RELATED_INFORMATION',$QUALIFIED_MODULE)}</h4>
				<div class="portal-record-privilege">
					{if $RELATED_MODULES[$MODULE]}
						{foreach from=$RELATED_MODULES[$MODULE] key=KEY item=VALUE}
							<div class="checkbox label-checkbox"{if !vtlib_isModuleActive($VALUE['name']) AND $VALUE['name'] neq 'History'} hidden {/if}>
								<label class="form-check">
									<input class="form-check-input relmoduleinfo_{$MODULE}" data-relmodule ="{$VALUE['name']}" type="checkbox" name="{$VALUE['name']}" id="{$VALUE['name']}" value="{$VALUE['value']}" {if $VALUE['value']}checked{/if}/>
									<span>{vtranslate($VALUE['name'],$QUALIFIED_MODULE)}</span>
								</label>
							</div>
						{/foreach}
					{/if}
				</div>
			{/if}
			<br> 
			{if $MODULE eq 'HelpDesk' OR $MODULE eq 'Assets'}
				<h4>{vtranslate('LBL_RECORD_PERMISSIONS',$QUALIFIED_MODULE)}</h4>
				<div class="portal-record-privilege" id="recordPrivilege_{$MODULE}">
					{if $MODULE eq 'HelpDesk'}
						<div class="checkbox label-checkbox">
							<label class="form-check">
								<input class="recordpermissions form-check-input" name="create" id="create-permission" type="checkbox" value="{$RECORD_PERMISSIONS['create']}" {if $RECORD_PERMISSIONS['create']}checked{/if}/>
								<span class="ms-2">{vtranslate('LBL_CREATE_RECORD',$QUALIFIED_MODULE)}</span>
							</label>
						</div>
					{/if}
					<div class="checkbox label-checkbox">
						<label class="form-check">
							<input class="recordpermissions form-check-input" name="edit" id="edit-permission" type="checkbox" value="{$RECORD_PERMISSIONS['edit']}" {if $RECORD_PERMISSIONS['edit']}checked{/if}/>
							<span class="ms-2">{vtranslate('LBL_EDIT_RECORD',$QUALIFIED_MODULE)}</span>
						</label>
					</div>
				</div>
			{/if}
		</div>
	</div>
{/strip}
