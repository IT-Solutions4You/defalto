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
    <div class="h-main p-4">
        <div class="container-fluid rounded bg-body p-3" id="CustomLabelsContainer">
            <style>
                #CustomLabelsContainer {
                    min-height: 86vh;
                }
                #CustomLabelTable {
                    padding:0;
                    margin:0;
                }
                #CustomLabelTable th:nth-child(1) {
                    width: 30%;
                }
                #CustomLabelTable th:nth-child(2) {
                    width: 50%;
                }
                #CustomLabelTable th:nth-child(3) {
                    width: 20%;
                    text-align: center;
                }
                #CustomLabelTable td:nth-child(2), #CustomLabelTable td:nth-child(3) {
                    border-right: 0;
                }
                #CustomLabelTable td:nth-child(3) {
                    min-width: 100px;
                    border-left: 0;
                }
                .addCustomLabel, .editCustomLabel, .deleteCustomLabel {
                    margin-right: 0.5em;
                }
                .CustomLabelValue {
                    white-space: pre-line;
                }
                .actionsLabel {
                    line-height: 1.5;
                    margin-top: 0.5em;
                }
            </style>
            <form name="custom_labels" action="index.php" method="post" class="form-horizontal">
                <input type="hidden" name="module" value="{$MODULE}"/>
                <input type="hidden" name="action" value="IndexAjax"/>
                <input type="hidden" name="mode" value="DeleteCustomLabels"/>
                <input type="hidden" name="newItems" value=""/>
                <h4>{vtranslate('LBL_CUSTOM_LABELS',$MODULE)}</h4>
                <p>{vtranslate('LBL_CUSTOM_LABELS_DESC',$MODULE)}</p>
                <div>
                    <div class="row">
                        <div class="col actionsLabel">
                            <b>{vtranslate('LBL_DEFINE_CUSTOM_LABELS',$MODULE)}:</b>
                        </div>
                        <div class="col-auto btn-toolbar">
                            <button type="button" class="addCustomLabel btn btn-primary active addButton btn-default" data-url="index.php?module={$MODULE}&view=IndexAjax&mode=editCustomLabel">
                                <i class="fa fa-plus"></i>
                                <span class="ms-2">{vtranslate('LBL_ADD')}</span>
                            </button>
                            <button type="reset" class="btn btn-primary" onClick="window.history.back();">{vtranslate('LBL_CANCEL')}</button>
                        </div>
                    </div>
                    <br>
                    <div>
                        <script type="text/javascript">let existingKeys = [];</script>
                        <table id="CustomLabelTable" class="table table-borderless CustomLabelTable">
                            <thead>
                                <tr>
                                    <th class="bg-body-secondary text-secondary">{vtranslate('LBL_KEY',$MODULE)}</th>
                                    <th class="bg-body-secondary text-secondary" colspan="2">{vtranslate('LBL_CURR_LANG_VALUE',$MODULE)} ({$CURR_LANG.label})</th>
                                    <th class="bg-body-secondary text-secondary text-start">{vtranslate('LBL_OTHER_LANG_VALUES',$MODULE)}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="CustomLabel opacity cloneCustomLabel hide">
                                    <td>
                                        <label class="CustomLabelKey textOverflowEllipsis"></label>
                                    </td>
                                    <td>
                                        <label class="CustomLabelValue textOverflowEllipsis"></label>
                                    </td>
                                    <td>
                                        <div class="text-end actions">
                                            <a class="btn editCustomLabel text-secondary" data-url=''>
                                                <i title="{vtranslate('LBL_EDIT_CUSTOM_LABEL','PDFMaker')}" class="fa fa-pencil"></i>
                                            </a>
                                            <a class="btn deleteCustomLabel text-secondary" data-url=''>
                                                <i title="{vtranslate('LBL_DELETE',$MODULE)}" class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <a class="showCustomLabelValues textOverflowEllipsis cursorPointer" data-url="">
                                            <i class="fa fa-list"></i>
                                            <span class="ms-2">{vtranslate('LBL_OTHER_VALS',$MODULE)}</span>
                                        </a>
                                    </td>
                                </tr>
                                <tr id="noItemFoundTr" style="display: none;">
                                    <td colspan="3" class="cellText" style="padding:10px; text-align: center;">
                                        <b>{vtranslate('LBL_NO_ITEM_FOUND',$MODULE)}</b>
                                    </td>
                                </tr>
                                {assign var="lang_id" value=$CURR_LANG.id}
                                {foreach key=label_id item=label_value from=$LABELS name=lbl_foreach}
                                    <tr class="CustomLabel opacity border-bottom">
                                        <td>
                                            <label class="CustomLabelKey textOverflowEllipsis">{$label_value.key}</label>
                                        </td>
                                        <td>
                                            <label class="CustomLabelValue textOverflowEllipsis">{$label_value.lang_values.$lang_id}</label>
                                        </td>
                                        <td>
                                            <div class="text-end actions">
                                                <a class="btn btn-sm editCustomLabel text-secondary" data-url="index.php?module={$MODULE}&view=IndexAjax&mode=editCustomLabel&labelid={$label_id}&langid={$lang_id}">
                                                    <i title="{vtranslate('LBL_EDIT_CUSTOM_LABEL','PDFMaker')}" class="fa fa-pencil"></i>
                                                </a>
                                                <a class="btn btn-sm deleteCustomLabel text-secondary" data-url="index.php?module={$MODULE}&action=IndexAjax&mode=deleteCustomLabel&labelid={$label_id}">
                                                    <i title="{vtranslate('LBL_DELETE',$MODULE)}" class="fa fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <a class="btn btn-sm showCustomLabelValues text-secondary" data-url="index.php?module={$MODULE}&view=IndexAjax&mode=showCustomLabelValues&labelid={$label_id}&langid={$lang_id}">
                                                <i class="fa fa-list"></i>
                                                <span class="ms-2">{vtranslate('LBL_OTHER_VALS',$MODULE)}</span>
                                            </a>
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <div class="clearfix">
                        <div class="pull-right btn-toolbar">
                            <button type="button" class="addCustomLabel btn btn-primary active addButton" data-url="index.php?module={$MODULE}&view=IndexAjax&mode=editCustomLabel">
                                <i class="fa fa-plus icon-white"></i>
                                <span class="ms-2">{vtranslate('LBL_ADD')}</span>
                            </button>
                            <button type="reset" class="btn btn-primary" onClick="window.history.back();">{vtranslate('LBL_CANCEL')}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/strip}