{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
    {assign var="dateFormat" value=$USER_MODEL->get('date_format')}
    <div class="row-fluid">
        <input type="text" name="{$FIELD_MODEL->get('name')}" class="listSearchContributor form-control inputElement dateField" data-date-format="{$dateFormat}" data-calendar-type="range" value="{$SEARCH_INFO['searchValue']}" data-fieldinfo='{$FIELD_INFO|escape}'  data-field-type="{$FIELD_MODEL->getFieldDataType()}"/>
    </div>
{/strip}