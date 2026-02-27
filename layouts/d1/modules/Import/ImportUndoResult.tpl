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
        {assign var=TITLE value="{'LBL_DELETION_COMPLETED'|@vtranslate:$MODULE}"}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
    </div>
    <div class='modal-body' id ="importContainer">
        <div>
            <input type="hidden" name="module" value="{$FOR_MODULE}" />
            <div class='importBlockContainer'>
                <div class="container">
                    <div class="row py-2">
                        <div class="col">
                            <h4>{'LBL_DELETION_SUMMARY'|@vtranslate:$MODULE}</h4>
                            <hr>
                            {if !empty($ERROR_MESSAGE)}
                                <span class="alert alert-danger">{$ERROR_MESSAGE}</span>
                            {/if}
                        </div>
                    </div>
                    <div class="row py-2">
                        <div class="col">{'LBL_TOTAL_RECORDS'|@vtranslate:$MODULE}</div>
                        <div class="col">{$TOTAL_RECORDS}</div>
                    </div>
                    <div class="row py-2">
                        <div class="col">{'LBL_NUMBER_OF_RECORDS_DELETED'|@vtranslate:$MODULE}</div>
                        <div class="col">{$DELETED_RECORDS_COUNT}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class='modal-overlay-footer modal-footer'>
        <div class="container-fluid">
            <div class="row">
                <div class="col"></div>
                <div class="col">
                    <button class='btn btn-primary active undoDoneButton' data-bs-dismiss="modal" onclick="Vtiger_Import_Js.finishUndoOperation();">{vtranslate('LBL_DONE_BUTTON', $MODULE)}</button>
                </div>
            </div>
        </div>
    </div>
</div>
