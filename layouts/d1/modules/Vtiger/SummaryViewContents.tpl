{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
   <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
{/if}
<div class="summary-table no-border">
	<div class="container-fluid">
	{* Address-block fields configured as key fields are collapsed into the whole
	   formatted address (like the detail view), one entry per present group, with the
	   same inline ajax-edit as the detail view. *}
	{assign var=ADDR_PLAN value=Core_Address_BlockUIType::getSummaryAddressPlan($MODULE_NAME, $RECORD, $SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'])}
	{assign var=ADDR_CAN_EDIT value=$IS_AJAX_ENABLED && $RECORD->isEditable()}
	{if $ADDR_CAN_EDIT && ($ADDR_PLAN['render']|@count) gt 0}
	    {assign var=ADDR_EDIT_GROUPS value=Core_Address_BlockUIType::getEditGroups($MODULE_NAME)}
	    {assign var=ADDR_FIELD_MODELS value=Core_Address_BlockUIType::getEditFieldModels($MODULE_NAME, $RECORD)}
	{/if}
	{foreach item=FIELD_MODEL key=FIELD_NAME from=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS']}
        {assign var=fieldDataType value=$FIELD_MODEL->getFieldDataType()}
        {if isset($ADDR_PLAN['skip'][$FIELD_NAME])}
            {* Folded into its group's address entry above — do not render individually. *}
            {continue}
        {/if}
        {if isset($ADDR_PLAN['render'][$FIELD_NAME])}
            {assign var=ADDR value=$ADDR_PLAN['render'][$FIELD_NAME]}
            {assign var=ZIP_CITY value=php7_trim("`$ADDR.zip` `$ADDR.city`")}
            {assign var=ADDR_PARTS value=[$ADDR.street, $ZIP_CITY, $ADDR.state, $ADDR.country]}
            {assign var=ADDR_EDITABLE value=($ADDR_CAN_EDIT && $ADDR_EDIT_GROUPS[$ADDR.index])}
            <div id="{$MODULE_NAME}_Detail_field_addressGroup{$ADDR.index}" class="summaryViewEntries row align-items-center py-2 addressGroup" data-address-group-index="{$ADDR.index}">
                <div class="col-lg-4 fieldLabel">
                    <label class="d-block w-100 muted text-break" title="{$ADDR.label|escape}">{$ADDR.label|escape}</label>
                </div>
                <div class="col-lg-8 fieldValue">
                    <div class="row align-items-center justify-content-between">
                        <div class="col fw-semibold value addressFormatted text-break-all">
                            {assign var=FIRST value=true}
                            {foreach $ADDR_PARTS as $PART}{if php7_trim($PART) ne ''}{if !$FIRST}<br>{/if}{$PART|escape}{assign var=FIRST value=false}{/if}{/foreach}
                        </div>
                        {if $ADDR_EDITABLE}
                            <div class="action col-auto p-0">
                                <a href="#" onclick="return false;" class="editAddressGroup bg-body p-2 rounded" data-address-group-index="{$ADDR.index}">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            </div>
                        {/if}
                    </div>
                    {if $ADDR_EDITABLE}
                        {include file=vtemplate_path('blockuitypes/AddressGroupEditForm.tpl',$MODULE_NAME) MODULE_NAME=$MODULE_NAME GROUP_INDEX=$ADDR.index EDIT_GROUP=$ADDR_EDIT_GROUPS[$ADDR.index] FIELD_MODELS=$ADDR_FIELD_MODELS}
                    {/if}
                </div>
            </div>
            {continue}
        {/if}
			<div class="summaryViewEntries row align-items-center py-2">
				<div class="col-lg-4 fieldLabel" >
                    <label class="d-block w-100 muted text-break" title="{vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}">
                        {vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
                        {if $FIELD_MODEL->getFieldDataType() eq 'currency'}
                            {assign var=CURRENCY_INFO value=getCurrencySymbolandCRate($RECORD->getCurrencyId())}
                            <span class="ms-2">({$CURRENCY_INFO['symbol']})</span>
                        {/if}
                    </label>
                </div>
				<div class="col-lg-8 fieldValue">
                    <div class="row align-items-center justify-content-between">
                        {assign var=DISPLAY_VALUE value="{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get("fieldvalue"))}"}                  
                        <div class="col fw-semibold value text-break-all" title="{strip_tags($DISPLAY_VALUE)}">
                            {include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path:$MODULE_NAME FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
                        </div>
                        {if $FIELD_MODEL->isEditable() && $IS_AJAX_ENABLED && $FIELD_MODEL->isAjaxEditable() && $FIELD_MODEL->get('uitype') neq 69}
                            <div class="edit col hide">
                                {if $FIELD_MODEL->getFieldDataType() eq 'multipicklist'}
                                <input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}[]' data-type="{$fieldDataType}" data-displayvalue='{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}' data-value="{$FIELD_MODEL->get('fieldvalue')}" />
                                {else}
                                <input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}' data-type="{$fieldDataType}" data-displayvalue='{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}' data-value="{$FIELD_MODEL->get('fieldvalue')}" />
                                {/if}
                            </div>
                            <div class="action col-auto p-0">
                                <a href="#" onclick="return false;" class="editAction bg-body p-2">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            </div>
                        {/if}
                    </div>
				</div>
			</div>
	{/foreach}
	</div>
</div>

{/strip}
