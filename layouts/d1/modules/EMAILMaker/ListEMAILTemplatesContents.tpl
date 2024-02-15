{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
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

        <div id="table-content" class="table-container overflow-auto pt-3">
            <form name="list" id="listedit" action="" onsubmit="return false;">
                <table id="listview-table" class="table table-borderless listview-table {if $LISTVIEW_ENTRIES_COUNT eq '0'}listview-table-norecords{/if}">
                    <thead>
                    <tr class="listViewContentHeader bg-body-secondary text-secondary">
                        <th class="text-secondary ps-3">
                            <div class="table-actions">
                                <span class="input form-check" title="{vtranslate('LBL_CLICK_HERE_TO_SELECT_ALL_RECORDS',$MODULE)}">
                                    <input class="listViewEntriesMainCheckBox form-check-input" type="checkbox">
                                </span>
                            </div>
                        </th>
                        <th nowrap="nowrap">
                            <a href="#" data-columnname="name" data-nextsortorderval="{$name_dir}" class="listViewContentHeaderValues text-secondary">
                                {if $ORDERBY eq 'templatename'}{$dir_img}{else}{$customsort_img}{/if}
                                <span class="ms-2">{vtranslate('LBL_EMAIL_NAME',$MODULE)}</span>
                            </a>
                        </th>
                        <th nowrap="nowrap">
                            <a href="#" data-columnname="module" data-nextsortorderval="{$module_dir}" class="listViewContentHeaderValues text-secondary">
                                {if $ORDERBY eq 'module'}{$dir_img}{else}{$customsort_img}{/if}
                                <span class="ms-2">{vtranslate('LBL_MODULENAMES',$MODULE)}</span>
                            </a>
                        </th>
                        <th nowrap="nowrap">
                            <a href="#" data-columnname="category" data-nextsortorderval="{$category_dir}" class="listViewContentHeaderValues text-secondary">
                                {if $ORDERBY eq 'category'}{$dir_img}{else}{$customsort_img}{/if}
                                <span class="ms-2">{vtranslate('LBL_CATEGORY',$MODULE)}</span>
                            </a>
                        </th>
                        <th nowrap="nowrap">
                            <a href="#" data-columnname="description" data-nextsortorderval="{$description_dir}" class="listViewContentHeaderValues text-secondary">
                                {if $ORDERBY eq 'description'}{$dir_img}{else}{$customsort_img}{/if}
                                <span class="ms-2">{vtranslate('LBL_DESCRIPTION',$MODULE)}</span>
                            </a>
                        </th>
                        <th nowrap="nowrap">
                            <a href="#" data-columnname="sharingtype" data-nextsortorderval="{$sharingtype_dir}" class="listViewContentHeaderValues text-secondary">
                                {if $ORDERBY eq 'sharingtype'}{$dir_img}{else}{$customsort_img}{/if}
                                <span class="ms-2">{vtranslate('LBL_SHARING_TAB',$MODULE)}</span>
                            </a>
                        </th>
                        <th nowrap="nowrap">
                            <span class="text-secondary">{vtranslate("LBL_TEMPLATE_OWNER",$MODULE)}</span>
                        </th>
                        <th>
                            <span class="text-secondary">{vtranslate("Status")}</span>
                        </th>
                    </tr>
                    <tr class="searchRow">
                        <th class="inline-search-btn">
                            <div class="table-actions">
                                <button class="btn text-secondary {if count($SEARCH_DETAILS) gt 0}hide{/if}" data-trigger="listSearch" title="{vtranslate("LBL_SEARCH",$MODULE)}">
                                    <i class="fa fa-search"></i>
                                </button>
                                <button class="searchAndClearButton btn text-secondary {if count($SEARCH_DETAILS) eq 0}hide{/if}" data-trigger="clearListSearch" title="{vtranslate("LBL_CLEAR",$MODULE)}">
                                    <i class="fa fa-close"></i>
                                </button>
                            </div>
                        </th>
                        <th>
                            <input type="text" class="listSearchContributor inputElement form-control" data-field-type="string" name="templatename" data-fieldinfo='{ldelim}"column":"templatename","type":"string","name":"templatename","label":"{vtranslate("LBL_EMAIL_NAME",$MODULE)}"{rdelim}' value="{$SEARCH_TEMPLATENAMEVAL}">
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
                                <input type="text" class="listSearchContributor inputElement form-control" name="category" data-fieldinfo='' value="{$SEARCH_CATEGORYVAL}">
                            </div>
                        </th>
                        <th>
                            <div>
                                <input type="text" class="listSearchContributor inputElement form-control" name="description" data-fieldinfo='' value="{$SEARCH_DESCRIPTIONVAL}">
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
                        <tr class="listViewEntries border-top" {if $template.status eq 0} style="font-style:italic;" {/if} data-id="{$template.templateid}" data-recordurl="index.php?module={$MODULE}&view=Detail&templateid={$template.templateid}" id="{$MODULE}_listView_row_{$template.templateid}">
                            <td class="listViewRecordActions ps-3">
                                <div class="table-actions d-flex align-items-center">
                                    <span class="input form-check">
                                        <input type="checkbox" class="listViewEntriesCheckBox form-check-input" value="{$template.templateid}">
                                    </span>
                                    <span class="more dropdown action">
                                        <span href="javascript:;" class="btn btn-sm text-secondary" data-bs-toggle="dropdown">
                                            <i class="fa fa-ellipsis-h"></i>
                                        </span>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" data-id="{$template.templateid}" href="index.php?module={$MODULE}&view=Detail&record={$template.templateid}&app={$SELECTED_MENU_CATEGORY}">
                                                    <i class="fa-solid fa-circle-info text-secondary"></i>
                                                    <span class="ms-2">{vtranslate('LBL_DETAILS', $MODULE)}</span>
                                                </a>
                                            </li>
                                            {if !empty($template['edit_url'])}
                                                <li>
                                                    <a class="dropdown-item" href="{$template['edit_url']}">
                                                        <i class="fa-solid fa-pencil text-secondary"></i>
                                                        <span class="ms-2">{vtranslate('LBL_EDIT', $MODULE)}</span>
                                                    </a>
                                                </li>
                                            {/if}
                                            {if !empty($template['duplicate_url'])}
                                                <li>
                                                    <a class="dropdown-item" href="{$template['duplicate_url']}">
                                                        <i class="fa-solid fa-copy text-secondary"></i>
                                                        <span class="ms-2">{vtranslate('LBL_DUPLICATE', $MODULE)}</span>
                                                    </a>
                                                </li>
                                            {/if}
                                            {if !empty($template['delete_id'])}
                                                <li>
                                                    <a data-id="{$template['delete_id']}" href="javascript:void(0);" class="deleteRecordButton dropdown-item">
                                                        <i class="fa-solid fa-trash text-secondary"></i>
                                                        <span class="ms-2">{vtranslate('LBL_DELETE', $MODULE)}</span>
                                                    </a>
                                                </li>
                                            {/if}
                                        </ul>
                                    </span>
                                </div>
                            </td>
                            <td class="listViewEntryValue">{$template.templatename}</td>
                            <td class="listViewEntryValue {if $template.status eq 0}text-muted{/if}">{$template.module}</td>
                            <td class="listViewEntryValue {if $template.status eq 0}text-muted{/if}">{$template.category}</td>
                            <td class="listViewEntryValue {if $template.status eq 0}text-muted{/if}">{$template.description}</td>
                            <td class="listViewEntryValue {if $template.status eq 0}text-muted{/if}">{$template.sharingtype}</td>
                            <td class="listViewEntryValue {if $template.status eq 0}text-muted{/if}" nowrap>{$template.owner}</td>
                            <td class="listViewEntryValue {if $template.status eq 0}text-muted{/if}">{$template.status_lbl}</td>
                        </tr>
                        {foreachelse}
                        <tr>
                            <td colspan="9" class="text-center text-secondary">
                                <div class="p-3">{vtranslate("LBL_NO")} {vtranslate("LBL_TEMPLATE",$MODULE)} {vtranslate("LBL_FOUND",$MODULE)}</div>
                                <a class="btn btn-outline-secondary fw-bold" href="index.php?module={$MODULE}&view=Edit">{vtranslate("LBL_CREATE_NEW")} {vtranslate("LBL_TEMPLATE",$MODULE)}</a>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </form>
        </div>
    </div>
{/strip}