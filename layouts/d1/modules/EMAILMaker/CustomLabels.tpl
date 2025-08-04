{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="h-main p-4">
        <div class="container-fluid rounded bg-body p-3" id="CustomLabelsContainer">
            <form name="custom_labels" action="index.php" method="post" class="form-horizontal">
                <input type="hidden" name="module" value="{$MODULE}"/>
                <input type="hidden" name="action" value="IndexAjax"/>
                <input type="hidden" name="mode" value="DeleteCustomLabels"/>
                <input type="hidden" name="newItems" value=""/>
                <h4>{vtranslate('LBL_CUSTOM_LABELS',$MODULE)}</h4>
                <p>{vtranslate('LBL_CUSTOM_LABELS_DESC',$MODULE)}</p>
                <div class="row">
                    <div class="col actionsLabel">
                        <b>{vtranslate('LBL_DEFINE_CUSTOM_LABELS',$MODULE)}:</b>
                    </div>
                    <div class="col-auto btn-toolbar">
                        <button type="button" class="addCustomLabel btn btn-primary active addButton btn-default me-2" data-url="index.php?module={$MODULE}&view=IndexAjax&mode=editCustomLabel">
                            <i class="fa fa-plus"></i>
                            <span class="ms-2">{vtranslate('LBL_ADD')}</span>
                        </button>
                        <button type="reset" class="btn btn-primary" onClick="window.history.back();">{vtranslate('LBL_CANCEL')}</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col py-3">
                        <script type="text/javascript">let existingKeys = [];</script>
                        <table id="CustomLabelTable" class="table table-borderless CustomLabelTable">
                            <thead>
                            <tr>
                                <th class="bg-body-secondary text-secondary w-25">{vtranslate('LBL_KEY',$MODULE)}</th>
                                <th class="bg-body-secondary text-secondary w-50" colspan="2">{vtranslate('LBL_CURR_LANG_VALUE',$MODULE)} ({$CURR_LANG.label})</th>
                                <th class="bg-body-secondary text-secondary text-start w-25">{vtranslate('LBL_OTHER_LANG_VALUES',$MODULE)}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="CustomLabel opacity border-bottom cloneCustomLabel hide">
                                <td>
                                    <label class="CustomLabelKey textOverflowEllipsis"></label>
                                </td>
                                <td>
                                    <label class="CustomLabelValue textOverflowEllipsis"></label>
                                </td>
                                <td>
                                    <div class="text-end actions">
                                        <a class="btn btn-sm editCustomLabel text-secondary" data-url=''>
                                            <i title="{vtranslate('LBL_EDIT_CUSTOM_LABEL','PDFMaker')}" class="fa fa-pencil"></i>
                                        </a>
                                        <a class="btn btn-sm deleteCustomLabel text-secondary" data-url=''>
                                            <i title="{vtranslate('LBL_DELETE',$MODULE)}" class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <a class="btn btn-sm showCustomLabelValues text-secondary" data-url="">
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
                </div>
                <div class="row">
                    <div class="ms-auto col-auto btn-toolbar">
                        <button type="button" class="addCustomLabel btn btn-primary active addButton me-2" data-url="index.php?module={$MODULE}&view=IndexAjax&mode=editCustomLabel">
                            <i class="fa fa-plus icon-white"></i>
                            <span class="ms-2">{vtranslate('LBL_ADD')}</span>
                        </button>
                        <button type="reset" class="btn btn-primary" onClick="window.history.back();">{vtranslate('LBL_CANCEL')}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/strip}