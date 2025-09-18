{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/PriceBooks/views/ProductPriceBookPopup.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
	<script type="text/javascript" src="{vresource_url('layouts/d1/modules/Products/resources/ProductsPopup.js')}"></script>
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$MODULE}
            <div class="modal-body">
                <div id="popupPageContainer" class="contentsDiv col-sm-12">
                    <input type="hidden" id="parentModule" value="{$SOURCE_MODULE}"/>
                    <input type="hidden" id="module" value="{$MODULE}"/>
                    <input type="hidden" id="parent" value="{$PARENT_MODULE}"/>
                    <input type="hidden" id="sourceRecord" value="{$SOURCE_RECORD}"/>
                    <input type="hidden" id="sourceField" value="{$SOURCE_FIELD}"/>
                    <input type="hidden" id="url" value="{$GETURL}" />
                    <input type="hidden" id="multi_select" value="{$MULTI_SELECT}" />
                    <input type="hidden" id="currencyId" value="{$CURRENCY_ID}" />
                    <input type="hidden" id="relatedParentModule" value="{$RELATED_PARENT_MODULE}"/>
                    <input type="hidden" id="relatedParentId" value="{$RELATED_PARENT_ID}"/>
                    <input type="hidden" id="view" value="{$VIEW}"/>
                    <input type="hidden" id="relationId" value="{$RELATION_ID}" />
                    <input type="hidden" id="selectedIds" name="selectedIds">
                    {if !empty($POPUP_CLASS_NAME)}
                        <input type="hidden" id="popUpClassName" value="{$POPUP_CLASS_NAME}"/>
                    {/if}
                    <form id="popupPage" action="javascript:void(0)">
                        <div id="popupContents" class="">
                            {include file='ProductPriceBookPopupContents.tpl'|@vtemplate_path:$PARENT_MODULE}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
{/strip}
