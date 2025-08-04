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
    {assign var=SEARCH_VALUES value=$SEARCH_INFO['searchValue']}
    <div class="">
    <select class="select2 listSearchContributor" name="{$FIELD_MODEL->get('name')}" style="width:90px;" data-fieldinfo='{$FIELD_INFO|escape}'>
        <option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
        <option value="1" {if $SEARCH_VALUES eq 1} selected{/if}>{vtranslate('LBL_YES',$MODULE)}</option>
        <option value="0" {if $SEARCH_VALUES eq '0'} selected{/if}>{vtranslate('LBL_NO',$MODULE)}</option>
    </select>
    </div>
{/strip}