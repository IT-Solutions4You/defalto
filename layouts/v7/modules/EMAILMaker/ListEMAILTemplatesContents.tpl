{*<!--
/*********************************************************************************
* The content of this file is subject to the EMAIL Maker license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
    {if $DIR eq 'ASC'}
        {assign var='dir_img' value='<i class="fa fa-sort fa-sort-asc"></i>'}
    {else}
        {assign var='dir_img' value='<i class="fa fa-sort fa-sort-desc"></i>'}
    {/if}
    {assign var='customsort_img' value='<i class="fa fa-sort customsort"></i>'}


    {assign var='name_dir' value='ASC'}
    {assign var='module_dir' value='ASC'}
    {assign var='description_dir' value='ASC'}
    {assign var='order_dir' value='ASC'}
    {assign var='sharingtype_dir' value='ASC'}
    {assign var='category_dir' value='ASC'}

    {if $ORDERBY eq 'templatename' && $DIR eq 'ASC'}
        {assign var='name_dir' value='DESC'}
    {elseif $ORDERBY eq 'module' && $DIR eq 'ASC'}
        {assign var='module_dir' value='DESC'}
    {elseif $ORDERBY eq 'description' && $DIR eq 'ASC'}
        {assign var='description_dir' value='DESC'}
    {elseif $ORDERBY eq 'order' && $DIR eq 'ASC'}
        {assign var='order_dir' value='DESC'}
    {elseif $ORDERBY eq 'sharingtype' && $DIR eq 'ASC'}
        {assign var='sharingtype_dir' value='DESC'}
    {elseif $ORDERBY eq 'category' && $DIR eq 'ASC'}
        {assign var='category_dir' value='DESC'}
    {/if}
    <div class="col-sm-12 col-xs-12 ">

        <input type="hidden" name="idlist">
        <input type="hidden" name="module" value="{$MODULE}">
        <input type="hidden" name="parenttab" value="Tools">
        <input type="hidden" name="view" value="List">
        <input type="hidden" name="cvid" value="1"/>
        <input type="hidden" name="action" value="">
        <input type="hidden" name="orderBy" id="orderBy" value="{$ORDERBY}">
        <input type="hidden" name="sortOrder" id="sortOrder" value="{$DIR}">
        <input type="hidden" name="currentSearchParams" value="{Vtiger_Util_Helper::toSafeHTML(Zend_JSON::encode($SEARCH_DETAILS))}" id="currentSearchParams"/>

        {include file='ListEMAILActions.tpl'|@vtemplate_path:$MODULE}

        <div id="table-content" class="table-container" style="overflow: auto;">

            <form name='list' id='listedit' action='' onsubmit="return false;">
                <table id="listview-table" class="table {if $LISTVIEW_ENTRIES_COUNT eq '0'}listview-table-norecords {/if} listview-table">
                    <thead>
                    <tr class="listViewContentHeader">
                        <th>
                            <div class="table-actions">
                                <div class="dropdown" style="float:left;">
                                <span class="input dropdown-toggle" data-toggle="dropdown" title="{vtranslate('LBL_CLICK_HERE_TO_SELECT_ALL_RECORDS',$MODULE)}">
                                    <input class="listViewEntriesMainCheckBox" type="checkbox">
                                </span>
                                </div>
                            </div>
                        </th>
                        <th nowrap="nowrap"><a href="#" data-columnname="name" data-nextsortorderval="{$name_dir}" class="listViewContentHeaderValues">{if $ORDERBY eq 'templatename'}{$dir_img}{else}{$customsort_img}{/if}&nbsp;{vtranslate('LBL_EMAIL_NAME',$MODULE)}&nbsp;</a></th>
                        <th nowrap="nowrap"><a href="#" data-columnname="module" data-nextsortorderval="{$module_dir}" class="listViewContentHeaderValues">{if $ORDERBY eq 'module'}{$dir_img}{else}{$customsort_img}{/if}&nbsp;{vtranslate('LBL_MODULENAMES',$MODULE)}&nbsp;</a></th>
                        <th nowrap="nowrap"><a href="#" data-columnname="category" data-nextsortorderval="{$category_dir}" class="listViewContentHeaderValues">{if $ORDERBY eq 'category'}{$dir_img}{else}{$customsort_img}{/if}&nbsp;{vtranslate('LBL_CATEGORY',$MODULE)}&nbsp;</a></th>
                        <th nowrap="nowrap"><a href="#" data-columnname="description" data-nextsortorderval="{$description_dir}" class="listViewContentHeaderValues">{if $ORDERBY eq 'description'}{$dir_img}{else}{$customsort_img}{/if}&nbsp;{vtranslate('LBL_DESCRIPTION',$MODULE)}&nbsp;</a></th>
                        <th nowrap="nowrap"><a href="#" data-columnname="sharingtype" data-nextsortorderval="{$sharingtype_dir}" class="listViewContentHeaderValues">{if $ORDERBY eq 'sharingtype'}{$dir_img}{else}{$customsort_img}{/if}&nbsp;{vtranslate('LBL_SHARING_TAB',$MODULE)}&nbsp;</a></th>
                        <th nowrap="nowrap">{vtranslate("LBL_TEMPLATE_OWNER",$MODULE)}</th>
                        <th>{vtranslate("Status")}</th>
                    </tr>
                    <tr class="searchRow">
                        <th class="inline-search-btn">
                            <div class="table-actions">
                                <button class="btn btn-sm btn-success {if count($SEARCH_DETAILS) gt 0}hide{/if}" data-trigger="listSearch">
                                    <i class="fa fa-search"></i>&nbsp;
                                    <span class="s2-btn-text">{vtranslate("LBL_SEARCH",$MODULE)}</span>
                                </button>
                                <button class="searchAndClearButton btn btn-sm btn-danger {if count($SEARCH_DETAILS) eq 0}hide{/if}" data-trigger="clearListSearch"><i class="fa fa-close"></i>&nbsp;{vtranslate("LBL_CLEAR",$MODULE)}</button>
                            </div>
                        </th>
                        <th>
                            <input type="text" class="listSearchContributor inputElement" data-field-type="string" name="templatename" data-fieldinfo='{ldelim}"column":"templatename","type":"string","name":"templatename","label":"{vtranslate("LBL_EMAIL_NAME",$MODULE)}"{rdelim}' value="{$SEARCH_TEMPLATENAMEVAL}">
                        </th>
                        <th>
                            <div class="select2_search_div">
                                <input type="text" class="listSearchContributor inputElement select2_input_element"/>
                                <select class="select2 listSearchContributor" name="formodule" data-fieldinfo='{ldelim}"column":"formodule","type":"picklist","name":"formodule","label":"{vtranslate("LBL_MODULENAMES",$MODULE)}"{rdelim}' style="display: none">
                                    <option value=""></option>
                                    {html_options  options=$SEARCHSELECTBOXDATA.modules selected=$SEARCH_FORMODULEVAL}
                                </select>
                            </div>
                        </th>
                        <th>
                            <div>
                                <input type="text" class="listSearchContributor inputElement" name="category" data-fieldinfo='' value="{$SEARCH_CATEGORYVAL}">
                            </div>
                        </th>
                        <th>
                            <div>
                                <input type="text" class="listSearchContributor inputElement" name="description" data-fieldinfo='' value="{$SEARCH_DESCRIPTIONVAL}">
                            </div>
                        </th>
                        <th>
                            <div class="select2_search_div">
                                <input type="text" class="listSearchContributor inputElement select2_input_element"/>
                                <select class="select2 listSearchContributor" name="sharingtype" data-fieldinfo='{ldelim}"column":"sharingtype","type":"picklist","name":"sharingtype","label":"{vtranslate("LBL_SHARING_TAB",$MODULE)}"{rdelim}' style="display: none">
                                    {html_options  options=$SHARINGTYPES selected=$SEARCH_SHARINGTYPEVAL}
                                </select>
                            </div>
                        </th>
                        <th>
                            <div class="select2_search_div">
                                <input type="text" class="listSearchContributor inputElement select2_input_element"/>
                                <select class="select2 listSearchContributor" name="owner" data-fieldinfo='{ldelim}"column":"owner","type":"owner","name":"owner","label":"{vtranslate("LBL_TEMPLATE_OWNER",$MODULE)}"{rdelim}' style="display: none">
                                    <option value=""></option>
                                    {html_options  options=$SEARCHSELECTBOXDATA.owners selected=$SEARCH_OWNERVAL}
                                </select>
                            </div>
                        </th>
                        <th>
                            <div class="select2_search_div">
                                <input type="text" class="listSearchContributor inputElement select2_input_element"/>
                                <select class="select2 listSearchContributor" name="status" data-fieldinfo='{ldelim}"column":"status","type":"picklist","name":"status","label":"{vtranslate("Status",$MODULE)}"{rdelim}' style="display: none">
                                    <option value=""></option>
                                    {html_options  options=$STATUSOPTIONS selected=$SEARCH_STATUSVAL}
                                </select>
                            </div>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach item=template name=mailmerge from=$EMAILTEMPLATES}
                        <tr class="listViewEntries" {if $template.status eq 0} style="font-style:italic;" {/if} data-id="{$template.templateid}" data-recordurl="index.php?module={$MODULE}&view=Detail&templateid={$template.templateid}" id="{$MODULE}_listView_row_{$template.templateid}">
                            <td class="listViewRecordActions">
                                <div class="table-actions">
                                        <span class="input">
                                            <input type="checkbox" class="listViewEntriesCheckBox" value="{$template.templateid}">
                                        </span>
                                    <span class="more dropdown action">
                                            <span href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
                                                <i class="fa fa-ellipsis-v icon"></i></span>
                                                <ul class="dropdown-menu">
                                                    <li><a data-id="{$template.templateid}" href="index.php?module={$MODULE}&view=Detail&record={$template.templateid}&app={$SELECTED_MENU_CATEGORY}">{vtranslate('LBL_DETAILS', $MODULE)}</a></li>
                                                    {if $VERSION_TYPE neq 'deactivate'}{$template.edit}{/if}
                                                </ul>
                                        </span>
                                </div>
                            </td>
                            <td class="listViewEntryValue">{$template.templatename}</td>
                            <td class="listViewEntryValue" {if $template.status eq 0} style="color:#888;" {/if}>{$template.module}</a></td>
                            <td class="listViewEntryValue" {if $template.status eq 0} style="color:#888;" {/if}>{$template.category}&nbsp;</td>
                            <td class="listViewEntryValue" {if $template.status eq 0} style="color:#888;" {/if}>{$template.description}&nbsp;</td>
                            <td class="listViewEntryValue" {if $template.status eq 0} style="color:#888;" {/if}>{$template.sharingtype}&nbsp;</td>
                            <td class="listViewEntryValue" {if $template.status eq 0} style="color:#888;" {/if} nowrap>{$template.owner}&nbsp;</td>
                            <td class="listViewEntryValue" {if $template.status eq 0} style="color:#888;" {/if}>{$template.status_lbl}&nbsp;</td>
                        </tr>
                        {foreachelse}
                        <tr>
                            <td style="background-color:#efefef;" align="center" colspan="9">
                                <table class="emptyRecordsDiv">
                                    <tbody>
                                    <tr>
                                        <td>
                                            {vtranslate("LBL_NO")} {vtranslate("LBL_TEMPLATE",$MODULE)} {vtranslate("LBL_FOUND",$MODULE)}<br><br>
                                            <a href="index.php?module={$MODULE}&view=Edit">{vtranslate("LBL_CREATE_NEW")} {vtranslate("LBL_TEMPLATE",$MODULE)}</a>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </form>
        </div>
    </div>
{/strip}