{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class='fc-overlay-modal' id="scheduleImportStatus">
    <div class="modal-content">
        <div class="overlayHeader">
            {assign var=TITLE value="{'LBL_IMPORT'|@vtranslate:$MODULE} {$FOR_MODULE|@vtranslate:$FOR_MODULE} {'LBL_RUNNING'|@vtranslate:$MODULE}"}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
        </div>
        <div class='modal-body' id="importStatusDiv">
            <form onsubmit="VtigerJS_DialogBox.block();" action="index.php" enctype="multipart/form-data" method="POST" name="importStatusForm" id="importStatusForm">
                <input type="hidden" name="module" value="{$FOR_MODULE}"/>
                <input type="hidden" name="view" value="Import"/>
                {if $CONTINUE_IMPORT eq 'true'}
                    <input type="hidden" name="mode" value="continueImport"/>
                {else}
                    <input type="hidden" name="mode" value=""/>
                {/if}
            </form>
            <div class="container">
                <div class="row py-2">
                    <div class="col">
                        <h4>
                            <span>{'LBL_TOTAL_RECORDS_SCANNED'|@vtranslate:$MODULE}</span>
                            <span class="mx-2">:</span>
                            <span>{$IMPORT_RESULT.IMPORTED}</span>
                        </h4>
                        <hr>
                        {if !empty($ERROR_MESSAGE)}
                            <span class="alert alert-danger">{$ERROR_MESSAGE}</span>
                        {/if}
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col">{'LBL_TOTAL_RECORDS_IMPORTED'|@vtranslate:$MODULE}</div>
                    <div class="col">
                        <b>{$IMPORT_RESULT.IMPORTED} / {$IMPORT_RESULT.TOTAL}</b>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col">{'LBL_NUMBER_OF_RECORDS_CREATED'|@vtranslate:$MODULE}</div>
                    <div class="col">
                        <b>{$IMPORT_RESULT.CREATED}</b>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col">{'LBL_NUMBER_OF_RECORDS_UPDATED'|@vtranslate:$MODULE}</div>
                    <div class="col">{$IMPORT_RESULT.UPDATED}</div>
                </div>
                <div class="row py-2">
                    <div class="col">{'LBL_NUMBER_OF_RECORDS_SKIPPED'|@vtranslate:$MODULE}</div>
                    <div class="col">
                        <b>{$IMPORT_RESULT.SKIPPED}</b>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col">{'LBL_NUMBER_OF_RECORDS_MERGED'|@vtranslate:$MODULE}</div>
                    <div class="col">
                        <b>{$IMPORT_RESULT.MERGED}</b>
                    </div>
                </div>
            </div>
        </div>
        <div class='modal-overlay-footer modal-footer'>
            <div class="container-fluid">
                <div class="row">
                    <div class="col"></div>
                    <div class="col">
                        <button name="cancel" class="btn btn-danger"
                                onclick="return Vtiger_Import_Js.cancelImport('index.php?module={$FOR_MODULE}&view=Import&mode=cancelImport&import_id={$IMPORT_ID}')">{'LBL_CANCEL_IMPORT'|@vtranslate:$MODULE}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
