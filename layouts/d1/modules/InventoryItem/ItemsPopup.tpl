{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<div class = "productsBundlePopup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$RELATED_MODULE}
                <form id="popupPage" action="javascript:void(0)">
                    <div class="modal-body">
                        <div id="popupPageContainer" class="contentsDiv">
                            <input type="hidden" id="parentModule" value="{$SOURCE_MODULE}"/>
                            <input type="hidden" id="item_module" value="{$RELATED_MODULE}"/>
                            <input type="hidden" id="module" value="{$MODULE}"/>
                            <input type="hidden" id="parent" value="{$PARENT_MODULE}"/>
                            <input type="hidden" id="sourceRecord" value="{$SOURCE_RECORD}"/>
                            <input type="hidden" id="src_record" value="{$SOURCE_RECORD}"/>
                            <input type="hidden" id="src_module" value="{$SOURCE_MODULE}"/>
                            <input type="hidden" id="sourceField" value="{$SOURCE_FIELD}"/>
                            <input type="hidden" id="url" value="{$GETURL}" />
                            <input type="hidden" id="multi_select" value="{$MULTI_SELECT}" />
                            <input type="hidden" id="currencyId" value="{$CURRENCY_ID}" />
                            <input type="hidden" id="relatedParentModule" value="{$RELATED_PARENT_MODULE}"/>
                            <input type="hidden" id="relatedParentId" value="{$RELATED_PARENT_ID}"/>
                            <input type="hidden" id="view" value="{$VIEW}"/>
                            <input type="hidden" id="relationId" value="{$RELATION_ID}" />
                            <input type="hidden" id="selectedIds" name="selectedIds">
                            <div id="popupContents">
                                {include file='ItemsPopupContents.tpl'|vtemplate_path:$MODULE_NAME}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                    </div>
                </form>
        </div>
    </div>
</div>
{/strip}