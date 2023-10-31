{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Vtiger/views/Popup.php *}
{strip}
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate($MODULE,$MODULE)}}
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
                <input type="hidden" id="view" name="view" value="{$VIEW}"/>
                <input type="hidden" id="relationId" value="{$RELATION_ID}" />
                <input type="hidden" id="selectedIds" name="selectedIds">
                {if !empty($POPUP_CLASS_NAME)}
                    <input type="hidden" id="popUpClassName" value="{$POPUP_CLASS_NAME}"/>
                {/if}
                <div id="popupContents" class="">
                    {include file='PopupContents.tpl'|vtemplate_path:$MODULE_NAME}
                </div>
            </div>
        </div>
    </div>
</div>
{/strip}
