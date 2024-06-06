{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var="SEARCH_VALUE" value=$SEARCH_INFO['searchValue']}
{assign var="TIME_FORMAT" value=$USER_MODEL->get('hour_format')}
<div class="">
<input type="text" class="timepicker-default listSearchContributor" value="{$SEARCH_VALUE}" name="{$FIELD_MODEL->getFieldName()}" data-format="{$TIME_FORMAT}" data-field-type="{$FIELD_MODEL->getFieldDataType()}" data-fieldinfo='{$FIELD_INFO}'/>
</div>
{/strip}