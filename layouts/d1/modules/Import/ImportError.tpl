{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class='fc-overlay-modal modal-content'>
    <div class="overlayHeader">
        {assign var=TITLE value="{'LBL_IMPORT'|@vtranslate:$MODULE} - {'LBL_ERROR'|@vtranslate:$MODULE}"}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE} 
    </div>
    <div class='modal-body' style="margin-bottom:380px" id = "landingPageDiv">
        <input type="hidden" name="module" value="{$FOR_MODULE}" />
        <div class="container">
            <div class="row py-2">
                <div class="col">
                    <h4>{'ERR_DETAILS_BELOW'|vtranslate:$MODULE}</h4>
                    <hr>
                    <div class="alert alert-danger">{$ERROR_MESSAGE}</div>
                </div>
            </div>
            {if $ERROR_DETAILS neq ''}
                {foreach key=_TITLE item=_VALUE from=$ERROR_DETAILS}
                    <div class="row">
                        <div class="col">{$_TITLE}</div>
                        <div class="col">{$_VALUE}</div>
                    </div>
                {/foreach}
            {/if}
        </div>
    </div>
    <div class='modal-footer modal-overlay-footer'>
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col"></div>
                <div class='col'>
                    {if $CUSTOM_ACTIONS neq ''}
                        {foreach key=_LABEL item=_ACTION from=$CUSTOM_ACTIONS}
                            <button name="{$_LABEL}" onclick="return Vtiger_Import_Js.clearSheduledImportData()" class="btn btn-danger me-2">{$_LABEL|@vtranslate:$MODULE}</button>
                        {/foreach}
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>