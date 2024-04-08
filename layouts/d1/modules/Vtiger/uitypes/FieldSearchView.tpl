{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {assign var=FIELD_INFO value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
    {assign var=FIELD_DATA_TYPE value=$FIELD_MODEL->getFieldDataType()}
    {assign var=FIELD_CLASS value=$FIELD_MODEL->getListSearchInputClass()}
    <div class="">
        <input type="text" name="{$FIELD_MODEL->get('name')}" class="listSearchContributor inputElement form-control {$FIELD_CLASS}" value="{$SEARCH_INFO['searchValue']}" data-field-type="{$FIELD_DATA_TYPE}" data-fieldinfo='{$FIELD_INFO|escape}'/>
    </div>
{/strip}