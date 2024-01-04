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
    <div class="container-fluid" id="CustomLabelsContainer">
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
            .CustomLabelTable thead {
                border-bottom: 1px solid #ccc;
                background: #F5F5F5;
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
            <br>
            <h4>{vtranslate('LBL_CUSTOM_LABELS',$MODULE)}</h4>
            {vtranslate('LBL_CUSTOM_LABELS_DESC',$MODULE)}
            <hr>
            <div>
                <div class="clearfix">
                    <div class="pull-left actionsLabel">
                        <b>{vtranslate('LBL_DEFINE_CUSTOM_LABELS',$MODULE)}:</b>
                    </div>
                    <div class="pull-right btn-group">
                        <button type="button" class="addCustomLabel btn addButton btn-default" data-url="index.php?module={$MODULE}&view=IndexAjax&mode=editCustomLabel"><i class="fa fa-plus"></i>&nbsp;<span> {vtranslate('LBL_ADD')}</span></button>
                        <button type="reset" class="btn btn-default" onClick="window.history.back();">{vtranslate('LBL_CANCEL')}</button>
                    </div>
                </div>
                <br>
                <div class="clearfix">
                    <script type="text/javascript">let existingKeys = [];</script>
                    <table id="CustomLabelTable" class="table table-bordered table-condensed CustomLabelTable">
                        <thead>
                            <tr>
                                <th>{vtranslate('LBL_KEY',$MODULE)}</th>
                                <th colspan="2">{vtranslate('LBL_CURR_LANG_VALUE',$MODULE)} ({$CURR_LANG.label})</th>
                                <th>{vtranslate('LBL_OTHER_LANG_VALUES',$MODULE)}</th>
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
                                    <div class="pull-right actions">
                                        <a class="editCustomLabel cursorPointer" data-url=''>
                                            <i title="{vtranslate('LBL_EDIT_CUSTOM_LABEL','PDFMaker')}" class="fa fa-pencil"></i>
                                        </a>
                                        <a class="deleteCustomLabel cursorPointer" data-url=''>
                                            <i title="{vtranslate('LBL_DELETE',$MODULE)}" class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <a class="showCustomLabelValues textOverflowEllipsis cursorPointer" data-url="">{vtranslate('LBL_OTHER_VALS',$MODULE)}</a>
                                </td>
                            </tr>
                            <tr id="noItemFoundTr" style="display: none;">
                                <td colspan="3" class="cellText" style="padding:10px; text-align: center;">
                                    <b>{vtranslate('LBL_NO_ITEM_FOUND',$MODULE)}</b>
                                </td>
                            </tr>
                            {assign var="lang_id" value=$CURR_LANG.id}
                            {foreach key=label_id item=label_value from=$LABELS name=lbl_foreach}
                                <tr class="CustomLabel opacity">
                                    <td>
                                        <label class="CustomLabelKey textOverflowEllipsis">{$label_value.key}</label>
                                    </td>
                                    <td>
                                        <label class="CustomLabelValue textOverflowEllipsis">{$label_value.lang_values.$lang_id}</label>
                                    </td>
                                    <td>
                                        <div class="pull-right actions">
                                            <a class="editCustomLabel cursorPointer" data-url="index.php?module={$MODULE}&view=IndexAjax&mode=editCustomLabel&labelid={$label_id}&langid={$lang_id}">
                                                <i title="{vtranslate('LBL_EDIT_CUSTOM_LABEL','PDFMaker')}" class="fa fa-pencil"></i>
                                            </a>
                                            <a class="deleteCustomLabel cursorPointer" data-url="index.php?module={$MODULE}&action=IndexAjax&mode=deleteCustomLabel&labelid={$label_id}">
                                                <i title="{vtranslate('LBL_DELETE',$MODULE)}" class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td><a class="showCustomLabelValues textOverflowEllipsis cursorPointer" data-url="index.php?module={$MODULE}&view=IndexAjax&mode=showCustomLabelValues&labelid={$label_id}&langid={$lang_id}">{vtranslate('LBL_OTHER_VALS',$MODULE)}</a>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
                <br>
                <div class="clearfix">
                    <div class="pull-right btn-group">
                        <button type="button" class="addCustomLabel btn addButton btn-default" data-url="index.php?module={$MODULE}&view=IndexAjax&mode=editCustomLabel"><i class="fa fa-plus icon-white"></i>&nbsp;<span> {vtranslate('LBL_ADD')}</span></button>
                        <button type="reset" class="btn btn-default" onClick="window.history.back();">{vtranslate('LBL_CANCEL')}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
{/strip}