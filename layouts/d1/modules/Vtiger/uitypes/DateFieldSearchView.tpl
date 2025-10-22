{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
    {assign var="dateFormat" value=$USER_MODEL->get('date_format')}
    <div class="row-fluid">
        <input type="text" name="{$FIELD_MODEL->get('name')}" class="listSearchContributor form-control inputElement dateField" data-date-format="{$dateFormat}" data-calendar-type="range" value="{if isset($SEARCH_INFO['searchValue'])}{$SEARCH_INFO['searchValue']}{/if}" data-fieldinfo='{$FIELD_INFO|escape}'  data-field-type="{$FIELD_MODEL->getFieldDataType()}"/>
    </div>
{/strip}