{**
This file is part of the IT-Solutions4You CRM Software.

(c) IT-Solutions4You s.r.o [info@its4you.sk]

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
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
                        {*if $LISTVIEW_ENTRIES_COUNT neq '0'}
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-6 text-end">
                                        <a class="btn btn-primary cancelLink" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                                    </div>
                                    <div class="col-6 text-start">
                                        <button class="btn btn-primary active addProducts me-2" type="submit">
                                            <i class="fa fa-plus"></i>&nbsp;&nbsp;
                                            <strong>{vtranslate('LBL_ADD_TO_PRODUCTS',$MODULE)}</strong>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        {/if*}
                    </div>
                </form>
        </div>
    </div>
</div>
{/strip}