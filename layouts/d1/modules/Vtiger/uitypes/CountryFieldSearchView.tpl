{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<select data-fieldname="{$FIELD_MODEL->getFieldName()}" data-fieldtype="country" class="inputElement select2 form-select listSearchContributor" type="picklist" name="{$FIELD_MODEL->getFieldName()}" data-selected-value='{$FIELD_MODEL->get('fieldvalue')}'
    {if !empty($SPECIAL_VALIDATOR)} data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}' {/if}
    {if $FIELD_INFO['mandatory'] eq true} data-rule-required="true" {/if}
    {if php7_count($FIELD_INFO['validator'])} data-specific-rules='{ZEND_JSON::encode($FIELD_INFO['validator'])}' {/if}
>
    {if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION',$QUALIFIED_MODULE)}</option>{/if}
    {foreach from=$FIELD_MODEL->getUITypeModel()->getPicklistValues() key=PICKLIST_VALUE item=PICKLIST_LABEL}
        <option value="{$PICKLIST_VALUE}" {if $PICKLIST_VALUE eq $SEARCH_INFO['searchValue']} selected="selected" {/if}>{vtranslate($PICKLIST_LABEL, $QUALIFIED_MODULE)}</option>
    {/foreach}
</select>