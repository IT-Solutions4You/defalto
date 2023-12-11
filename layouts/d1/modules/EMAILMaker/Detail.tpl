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
    <div class="detailview-content container-fluid">
        <div class="details">
            <form id="detailView" method="post" action="index.php" name="etemplatedetailview" onsubmit="VtigerJS_DialogBox.block();">
                <input type="hidden" name="action" value="">
                <input type="hidden" name="view" value="">
                <input type="hidden" name="module" value="EMAILMaker">
                <input type="hidden" name="retur_module" value="EMAILMaker">
                <input type="hidden" name="return_action" value="EMAILMaker">
                <input type="hidden" name="return_view" value="Detail">
                <input type="hidden" name="record" value="{$TEMPLATEID}">
                <input type="hidden" name="templateid" value="{$TEMPLATEID}">
                <input type="hidden" name="parenttab" value="{$PARENTTAB}">
                <input type="hidden" name="isDuplicate" value="false">
                <input type="hidden" name="subjectChanged" value="">
                <input id="recordId" value="{$TEMPLATEID}" type="hidden">
                <div class="row">
                    <div class="left-block col-lg-4">
                        <div class="summaryView rounded bg-body my-3">
                            <div class="summaryViewHeader p-3 border-bottom">
                                <h4>{vtranslate('LBL_TEMPLATE_INFORMATIONS','EMAILMaker')}</h4>
                            </div>
                            <div class="summaryViewFields">
                                <div class="recordDetails">
                                    <div class="container-fluid p-3">
                                        <div class="row summaryViewEntries py-2">
                                            <div class="col-4 fieldLabel">
                                                <label class="text-truncate text-muted">{vtranslate('LBL_EMAIL_NAME','EMAILMaker')}</label>
                                            </div>
                                            <div class="col-lg fieldValue">{$TEMPLATENAME}</div>
                                        </div>
                                        <div class="row summaryViewEntries py-2">
                                            <div class="col-4 fieldLabel">
                                                <label class="text-truncate text-muted">{vtranslate('LBL_DESCRIPTION','EMAILMaker')}</label>
                                            </div>
                                            <div class="col-lg fieldValue" valign=top>{$DESCRIPTION}</div>
                                        </div>
                                        {if $MODULENAME neq ""}
                                            <div class="row summaryViewEntries py-2">
                                                <div class="col-4 fieldLabel">
                                                    <label class="text-truncate text-muted">{vtranslate('LBL_MODULENAMES','EMAILMaker')}</label>
                                                </div>
                                                <div class="col-lg fieldValue" valign=top>{$MODULENAME}</div>
                                            </div>
                                        {/if}
                                        <div class="row summaryViewEntries py-2">
                                            <div class="col-4 fieldLabel"><label class="muted textOverflowEllipsis">{vtranslate('Status')}</label></div>
                                            <div class="col-lg fieldValue" valign=top>{$IS_ACTIVE}</div>
                                        </div>
                                        <div class="row summaryViewEntries py-2">
                                            <div class="col-4 fieldLabel"><label class="muted textOverflowEllipsis">{vtranslate('LBL_SETASDEFAULT','EMAILMaker')}</label></div>
                                            <div class="col-lg fieldValue" valign=top>{$IS_DEFAULT}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {if $MODULENAME neq ""}
                            <div class="summaryView rounded bg-body my-3">
                                <div class="summaryViewHeader p-3 border-bottom d-flex">
                                    <h4>{vtranslate('LBL_DISPLAY_TAB',$MODULE)}</h4>
                                    <div class="ms-auto">
                                        <button type="button" class="btn btn-outline-secondary editDisplayConditions" data-url="index.php?module=EMAILMaker&view=EditDisplayConditions&record={$TEMPLATEID}">
                                            <span>{vtranslate('LBL_EDIT',$MODULE)}</span>
                                            <span class="ms-2">{vtranslate('LBL_CONDITIONS',$MODULE)}</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="summaryViewFields">
                                    <div class="recordDetails">
                                        {include file='DetailDisplayConditions.tpl'|@vtemplate_path:$MODULE}
                                    </div>
                                </div>
                            </div>
                        {/if}
                        {if $ISSTYLESACTIVE eq "yes"}
                            <div class="summaryView rounded bg-body my-3">
                                <div class="summaryViewHeader p-3 border-bottom d-flex">
                                    <h4>{vtranslate('LBL_CSS_STYLE_TAB',$MODULE)}</h4>
                                    <div class="ms-auto">
                                        <button type="button" class="btn btn-outline-secondary addButton addStyleContentBtn" data-modulename="ITS4YouStyles">{vtranslate('LBL_ADD')}&nbsp;{vtranslate('SINGLE_ITS4YouStyles','ITS4YouStyles')}</button>&nbsp;&nbsp;
                                        <button type="button" class="btn btn-outline-secondary addButton selectTemplateRelation" data-modulename="ITS4YouStyles">&nbsp;{vtranslate('LBL_SELECT')}&nbsp;{vtranslate('SINGLE_ITS4YouStyles','ITS4YouStyles')}</button>
                                    </div>
                                </div>
                                <div class="summaryWidgetContainer noContent">
                                    {if $STYLES_LIST}
                                        <div id="table-content" class="table-container">
                                            <table id="listview-table" class="table listview-table">
                                                <thead>
                                                <tr class="listViewContentHeader">
                                                    <th></th>
                                                    <th>{vtranslate('Name','ITS4YouStyles')}</th>
                                                    <th>{vtranslate('Priority','ITS4YouStyles')}</th>
                                                </tr>
                                                </thead>
                                                <tbody class="overflow-y">
                                                {foreach item=style_data  from=$STYLES_LIST}
                                                    <tr class="" data-id="{$style_data.id}" data-module="ITS4YouStyles">
                                                        <td style="width:55px">
                                                            {if $style_data.iseditable eq "yes"}
                                                                <span class="actionImages">&nbsp;&nbsp;&nbsp;
                                                        <a name="styleEdit" data-url="index.php?module=ITS4YouStyles&view=Edit&record={$style_data.id}">
                                                            <i title="Edit" class="fa fa-pencil"></i></a> &nbsp;&nbsp;
                                                        <a class="relationDelete">
                                                            <i title="{vtranslate('LBL_UNLINK', $MODULE)}" class="vicon-linkopen"></i></a>
                                                    </span>
                                                            {/if}
                                                        </td>
                                                        <td class="listViewEntryValue textOverflowEllipsis " width="%" nowrap><a name="styleEdit" data-url="index.php?module=ITS4YouStyles&view=Detail&record={$style_data.id}">{$style_data.name}</a></td>
                                                        <td class="listViewEntryValue textOverflowEllipsis " width="%" nowrap>{$style_data.priority}</td>
                                                    </tr>
                                                {/foreach}
                                                </tbody>
                                            </table>
                                        </div>
                                    {else}
                                        <p class="textAlignCenter">{vtranslate('LBL_NO_RELATED',$MODULE)} {vtranslate('LBL_STYLES',$MODULE)}</p>
                                    {/if}
                                </div>
                            </div>
                            <br>
                        {/if}
                        {if $ISDOCUMENTSACTIVE eq "yes"}
                            <div class="summaryView rounded bg-body my-3">
                                <div class="summaryViewHeader p-3 border-bottom d-flex">
                                    <h4>{vtranslate('Documents',$MODULE)}</h4>
                                    <div class="ms-auto">
                                        <button type="button" class="btn btn-outline-secondary addButton selectTemplateRelation" data-modulename="Documents">
                                            <span>{vtranslate('LBL_SELECT')}</span>
                                            <span class="ms-2">{vtranslate('SINGLE_Documents','Documents')}</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="summaryWidgetContainer noContent p-3">
                                    {if $DOCUMENTS_RECORDS neq ""}
                                        <div id="table-content" class="table-container">
                                            <table id="listview-table" class="table listview-table">
                                                <thead>
                                                <tr class="listViewContentHeader">
                                                    {if $IS_DELETABLE}
                                                        <th style="width:20px"></th>
                                                    {/if}
                                                    {foreach item=HEADER_LABEL key=HEADER_FIELD name="docforeach"  from=$DOCUMENTS_HEADERS}
                                                        <th {if $smarty.foreach.docforeach.last} colspan="2" {/if} nowrap class="{$WIDTHTYPE}">{vtranslate($HEADER_LABEL, $MODULE)}</th>
                                                    {/foreach}
                                                </tr>
                                                </thead>
                                                <tbody class="overflow-y">

                                                {foreach item=DOCUMENTS_RECORD from=$DOCUMENTS_RECORDS}
                                                    <tr class="listViewEntries" data-module="Documents" data-id="{$DOCUMENTS_RECORD.id}" data-recordurl="index.php?module=Documents&amp;view=Detail&amp;record={$DOCUMENTS_RECORD.id}">
                                                        {if $IS_DELETABLE}
                                                            <td nowrap style="width:20px">
                                                                <span class="actionImages">
                                                                    <a class="relationDelete"><i title="{vtranslate('LBL_UNLINK', $MODULE)}" class="vicon-linkopen alignMiddle"></i></a>
                                                                </span>
                                                            </td>
                                                        {/if}
                                                        {foreach item=HEADER_LABEL key=HEADER_FIELD name="docrecordsforeach" from=$DOCUMENTS_HEADERS}
                                                            <td class="{$WIDTHTYPE}" nowrap>
                                                                {$DOCUMENTS_RECORD[$HEADER_FIELD]}
                                                            </td>
                                                        {/foreach}
                                                    </tr>
                                                {/foreach}
                                                </tbody>
                                            </table>
                                        </div>
                                    {else}
                                        <p class="textAlignCenter">{vtranslate('LBL_NO_RELATED',$MODULE)} {vtranslate('Documents',$MODULE)}</p>
                                    {/if}
                                </div>
                            </div>
                            <br>
                        {/if}
                    </div>
                    <div class="middle-block col-lg-8">
                        <div class="summaryView rounded bg-body my-3 p-3">
                            <div class="summaryViewFields">
                                <div class="recordDetails">
                                    <b>{vtranslate('LBL_EMAIL_SUBJECT',$MODULE)}:</b>&nbsp;{$SUBJECT}
                                </div>
                            </div>
                        </div>
                        <div class="rounded bg-body my-3 p-3">
                            <div id="ContentEditorTabs">
                                <ul class="nav nav-pills">
                                    <li class="nav-item" data-type="body">
                                        <a class="nav-link active" href="#body_div2" aria-expanded="false" data-bs-toggle="tab">{vtranslate('LBL_BODY',$MODULE)}</a>
                                    </li>
                                </ul>
                            </div>
                            {*********************************************BODY DIV*************************************************}
                            <div class="tab-content">
                                <div class="tab-pane active" id="body_div2">
                                    <div id="previewcontent_body" class="hide">{$BODY}</div>
                                    <iframe id="preview_body" class="col-lg-12" style="height:1200px;"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            EMAILMaker_Detail_Js.setPreviewContent('body');
        });
    </script>
{/strip}