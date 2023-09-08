{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
{foreach key=index item=jsModel from=$SCRIPTS}
    <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}

<input type="hidden" id="recordId" value="{$RECORD->getId()}"/>
{if $FIELDS_INFO neq null}
    <script type="text/javascript">
        var related_uimeta = (function() {
            var fieldInfo = {$FIELDS_INFO};
            return {
                field: {
                    get: function(name, property) {
                        if (name && property === undefined) {
                            return fieldInfo[name];
                        }
                        if (name && property) {
                            return fieldInfo[name][property]
                        }
                    },
                    isMandatory: function(name) {
                        if (fieldInfo[name]) {
                            return fieldInfo[name].mandatory;
                        }
                        return false;
                    },
                    getType: function(name) {
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

<div class="fc-overlay-modal overlayDetail">
    <div class="modal-content border-0">
        <div class="overlayDetailHeader modal-header border-bottom">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-2 order-lg-2 text-end">
                        <button class="btn btn-primary fullDetailsButton me-2" onclick="window.location.href = '{$RECORD->getFullDetailViewUrl()}&app={$SELECTED_MENU_CATEGORY}'">{vtranslate('LBL_DETAILS',$MODULE_NAME)}</button>
                        {foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWBASIC']}
                            {if $DETAIL_VIEW_BASIC_LINK && $DETAIL_VIEW_BASIC_LINK->getLabel() == 'LBL_EDIT'}
                                <button class="btn btn-primary editRelatedRecord me-2" value="{$RECORD->getEditViewUrl()}">{vtranslate('LBL_EDIT',$MODULE_NAME)}</button>
                            {/if}
                        {/foreach}
                        <button type="button" class="btn btn-close ms-4" aria-label="Close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="col-lg-10 order-lg-1">
                        {include file="DetailViewHeaderTitle.tpl"|vtemplate_path:$MODULE_NAME MODULE_MODEL=$MODULE_MODEL RECORD=$RECORD IS_OVERLAY=true}
                    </div>
                </div>
            </div>
        </div>
        <div class="overlayDetailBody modal-body overflow-auto bg-body-secondary">
            <div class="detailViewContainer">
                {include file='DetailViewFullContents.tpl'|vtemplate_path:$MODULE_NAME RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
            </div>
        </div>
    </div>
</div>
{/strip}