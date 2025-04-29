{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
   <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
{/if}
<div class="summary-table no-border">
	<div class="container-fluid">
	{foreach item=FIELD_MODEL key=FIELD_NAME from=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS']}
        {assign var=fieldDataType value=$FIELD_MODEL->getFieldDataType()}
			<div class="summaryViewEntries row align-items-center py-2">
				<div class="col-lg-4 fieldLabel" >
                    <label class="muted text-truncate" title="{vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}">
                        {vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
                        {if $FIELD_MODEL->getFieldDataType() eq 'currency'}
                            {assign var=CURRENCY_INFO value=getCurrencySymbolandCRate($RECORD->getCurrencyId())}
                            <span class="ms-2">({$CURRENCY_INFO['symbol']})</span>
                        {/if}
                    </label>
                </div>
				<div class="col-lg-8 fieldValue ">
                    <div>
                        {assign var=DISPLAY_VALUE value="{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get("fieldvalue"))}"}                  
                        <span class="fw-semibold value text-break-all" title="{strip_tags($DISPLAY_VALUE)}">
                            {include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path:$MODULE_NAME FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
                        </span>
                        {if $FIELD_MODEL->isEditable() eq 'true' && $IS_AJAX_ENABLED && $FIELD_MODEL->isAjaxEditable() eq 'true' && $FIELD_MODEL->get('uitype') neq 69}
                            <span class="hide edit">
                                {if $FIELD_MODEL->getFieldDataType() eq 'multipicklist'}
                                <input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}[]' data-type="{$fieldDataType}" data-displayvalue='{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}' data-value="{$FIELD_MODEL->get('fieldvalue')}" />
                                {else}
                                <input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}' data-type="{$fieldDataType}" data-displayvalue='{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}' data-value="{$FIELD_MODEL->get('fieldvalue')}" />
                                {/if}
                            </span>
                            <span class="action"><a href="#" onclick="return false;" class="editAction fa fa-pencil"></a></span>
                        {/if}
                    </div>
				</div>
			</div>
	{/foreach}
	</div>
</div>

{/strip}