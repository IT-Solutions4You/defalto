{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Vtiger/views/Popup.php *}
{strip}
<div class="modal-dialog modal-xl">
    <div class="modal-content">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate($MODULE,$MODULE)}}
        <div class="modal-body">
            <div id="popupPageContainer" class="contentsDiv col-sm-12">
                <input type="hidden" id="parentModule" value="{$SOURCE_MODULE}"/>
                <input type="hidden" id="module" value="{$MODULE}"/>
                <input type="hidden" id="parent" value="{$PARENT_MODULE}"/>
                <input type="hidden" id="sourceRecord" value="{if isset($SOURCE_RECORD)}{$SOURCE_RECORD}{/if}"/>
                <input type="hidden" id="sourceField" value="{if isset($SOURCE_FIELD)}{$SOURCE_FIELD}{/if}"/>
                <input type="hidden" id="url" value="{if isset($GETURL)}{$GETURL}{/if}" />
                <input type="hidden" id="multi_select" value="{if isset($MULTI_SELECT)}{$MULTI_SELECT}{/if}" />
                <input type="hidden" id="currencyId" value="{if isset($CURRENCY_ID)}{$CURRENCY_ID}{/if}" />
                <input type="hidden" id="relatedParentModule" value="{if isset($RELATED_PARENT_MODULE)}{$RELATED_PARENT_MODULE}{/if}"/>
                <input type="hidden" id="relatedParentId" value="{if isset($RELATED_PARENT_ID)}{$RELATED_PARENT_ID}{/if}"/>
                <input type="hidden" id="view" name="view" value="{$VIEW}"/>
                <input type="hidden" id="relationId" value="{if isset($RELATION_ID)}{$RELATION_ID}{/if}" />
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
