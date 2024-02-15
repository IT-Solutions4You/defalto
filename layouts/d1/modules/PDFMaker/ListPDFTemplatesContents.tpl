{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}

<div class="col-sm-12 col-xs-12 ">

    <input type="hidden" name="idlist" >
    <input type="hidden" name="module" value="PDFMaker">
    <input type="hidden" name="parenttab" value="Tools">
    <input type="hidden" name="view" value="List">
    <div id="table-content" class="table-container">

        <form name='list' id='listedit' action='' onsubmit="return false;">
            <table id="listview-table" class="table {if $LISTVIEW_ENTRIES_COUNT eq '0'}listview-table-norecords {/if} listview-table">
                <thead>
                    <tr class="listViewContentHeader">
                        <th class="bg-body-secondary"></th>
                        <th class="bg-body-secondary" nowrap="nowrap">
                            <a href="#" data-columnname="module" data-nextsortorderval="{$module_dir}" class="listViewContentHeaderValues text-nowrap text-secondary">{vtranslate("LBL_MODULENAMES",$MODULE)}</a>
                        </th>
                        <th class="bg-body-secondary" nowrap="nowrap">
                            <a href="#" data-columnname="description" data-nextsortorderval="{$description_dir}" class="listViewContentHeaderValues text-nowrap text-secondary">{vtranslate("LBL_DESCRIPTION",$MODULE)}</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                {foreach item=template name=mailmerge from=$PDFTEMPLATES}
                    <tr class="listViewEntries" data-id="{$template.templateid}" data-recordurl="index.php?module=PDFMaker&view=DetailFree&templateid={$template.templateid}" id="PDFMaker_listView_row_{$template.templateid}">
                        <td class="listViewRecordActions">
                            <div class="table-actions">
                               <div class="more dropdown action">
                                    <div class="btn btn-sm text-secondary" data-bs-toggle="dropdown">
                                        <i class="fa fa-ellipsis icon"></i>
                                    </div>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" data-id="{$template.templateid}" href="index.php?module=PDFMaker&view=DetailFree&templateid={$template.templateid}&app={$SELECTED_MENU_CATEGORY}">
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
                                    </ul>
                                </div>
                            </div>
                        </td>
                        <td class="listViewEntryValue">{$template.module}</a></td>
                        <td class="listViewEntryValue">{$template.description}&nbsp;</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </form>
    </div>
</div>