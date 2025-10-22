{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=FIELD_INFO value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
    {assign var=FIELD_DATA_TYPE value=$FIELD_MODEL->getFieldDataType()}
    {assign var=FIELD_CLASS value=$FIELD_MODEL->getListSearchInputClass()}
    <div class="">
        <input type="text" name="{$FIELD_MODEL->get('name')}" class="listSearchContributor inputElement form-control {$FIELD_CLASS}" value="{if isset($SEARCH_INFO['searchValue'])}{$SEARCH_INFO['searchValue']}{/if}" data-field-type="{$FIELD_DATA_TYPE}" data-fieldinfo='{$FIELD_INFO|escape}'/>
    </div>
{/strip}