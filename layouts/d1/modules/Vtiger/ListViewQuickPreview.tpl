{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="quickPreview modal-dialog modal-xl fixed-end ms-auto h-100 m-0 shadow">
    <input type="hidden" name="sourceModuleName" id="sourceModuleName" value="{$MODULE_NAME}"/>
    <input type="hidden" id="nextRecordId" value="{$NEXT_RECORD_ID}">
    <input type="hidden" id="previousRecordId" value="{$PREVIOUS_RECORD_ID}">

    <div class="quick-preview-modal modal-content border-0 h-100">
        <div class="detailViewContainer modal-body container-fluid overflow-auto bg-body-secondary">
            <div class="rounded bg-body p-3 mb-3">
                <div class="row">
                    <div class="col-auto col-lg">
                        <button class="btn btn-outline-secondary me-2" onclick="window.location.href = '{$RECORD->getFullDetailViewUrl()}&app={$SELECTED_MENU_CATEGORY}'">
                            {vtranslate('LBL_VIEW_DETAILS', $MODULE_NAME)}
                        </button>
                    </div>
                    <div class="col-auto ms-auto">
                        <div class="btn-toolbar flex-nowrap">
                            <div class="btn-group me-2">
                                <button class="btn btn-outline-secondary" id="quickPreviewPreviousRecordButton" data-record="{$PREVIOUS_RECORD_ID}" data-app="{$SELECTED_MENU_CATEGORY}" {if empty($PREVIOUS_RECORD_ID)} disabled="disabled" {*{else} onclick="Vtiger_List_Js.triggerPreviewForRecord({$PREVIOUS_RECORD_ID})"*}{/if} >
                                    <i class="fa fa-chevron-left"></i>
                                </button>
                                <button class="btn btn-outline-secondary" id="quickPreviewNextRecordButton" data-record="{$NEXT_RECORD_ID}" data-app="{$SELECTED_MENU_CATEGORY}" {if empty($NEXT_RECORD_ID)} disabled="disabled" {*{else} onclick="Vtiger_List_Js.triggerPreviewForRecord({$NEXT_RECORD_ID})"*}{/if}>
                                    <i class="fa fa-chevron-right"></i>
                                </button>
                            </div>
                            <button class="btn btn-outline-secondary" aria-hidden="true" data-bs-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="detailview-header-block p-3 bg-body rounded">
                <div class="detailview-header">
                    {include file='DetailViewHeaderTitle.tpl'|vtemplate_path:$QUALIFIED_MODULE MODULE_MODEL=$MODULE_MODEL RECORD=$RECORD IS_OVERLAY=true LIST_PREVIEW=false}
                </div>
            </div>
            <div class="details">
                <form method="post" id="detailView" class="clearfix mt-3 MultiFile-intercepted">
                    {if $FIELDS_INFO neq null}
                        <script type="text/javascript">
                            var quick_preview_uimeta = (function () {
                                var fieldInfo = {$FIELDS_INFO};

                                return {
                                    field: {
                                        get: function (name, property) {
                                            if (name && property === undefined) {
                                                return fieldInfo[name];
                                            }
                                            if (name && property) {
                                                return fieldInfo[name][property]
                                            }
                                        },
                                        isMandatory: function (name) {
                                            if (fieldInfo[name]) {
                                                return fieldInfo[name].mandatory;
                                            }
                                            return false;
                                        },
                                        getType: function (name) {
                                            if (fieldInfo[name]) {
                                                return fieldInfo[name].type
                                            }
                                            return false;
                                        }
                                    },
                                };
                            })();
                        </script>
                    {/if}
                    {foreach key=index item=jsModel from=$SCRIPTS}
                        <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
                    {/foreach}
                    <script type="text/javascript">
                        let detailInstance = Vtiger_Detail_Js.getInstanceByModuleName("{$MODULE_NAME}");

                        detailInstance.setContainer($('.quickPreview'));
                        detailInstance.setForm($('.quickPreview form'));
                        detailInstance.setQuickPreviewDetailMode(true);
                        detailInstance.registerEvents();
                    </script>
                    <input type="hidden" name="record" id="recordId" value="{$RECORD->getId()}"/>
                    <input type="hidden" name="module" value="{$RECORD->getModuleName()}"/>
                    <div class="row">
                        {include file='SummaryViewWidgets.tpl'|vtemplate_path:$MODULE_NAME SUMMARY_INFORMATIONS=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS']}
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

