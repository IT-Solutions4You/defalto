{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Import/views/Main.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div class='fc-overlay-modal modal-content'>
    <div class="overlayHeader">
        {assign var=TITLE value="{'LBL_IMPORT_SUMMARY'|@vtranslate:$MODULE}"}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
    </div>
    <div class='modal-body'>
        <div class="summaryWidgetContainer">
            <input type="hidden" name="module" value="{$FOR_MODULE}" />
            <div class="container">
                <div class="row py-2">
                    <div class="col">
                        <h4>
                            <span>{'LBL_TOTAL_RECORDS_SCANNED'|@vtranslate:$MODULE}</span>
                            <span class="mx-2">:</span>
                            <span>{$IMPORT_RESULT.TOTAL}</span>
                        </h4>
                        <hr>
                        {if !empty($ERROR_MESSAGE)}<span>{$ERROR_MESSAGE}</span>{/if}
                    </div>
                </div>
                {include file="Import_Result_Details.tpl"|@vtemplate_path:'Import'}
            </div>
        </div>
    </div>
    <div class='modal-overlay-footer modal-footer'>
        <div class="container-fluid">
            <div class="row">
                <div class="col text-end">
                    <button name="next" class="btn btn-primary" onclick="return Vtiger_Import_Js.triggerImportAction();">{'LBL_IMPORT_MORE'|@vtranslate:$MODULE}</button>
                </div>
                <div class="col">
                    {if $MERGE_ENABLED eq '0'}
                        <button name="next" class="btn btn-danger me-2" onclick="Vtiger_Import_Js.undoImport('index.php?module={$FOR_MODULE}&view=Import&mode=undoImport&foruser={$OWNER_ID}')">{'LBL_UNDO_LAST_IMPORT'|@vtranslate:$MODULE}</button>
                    {/if}
                    <button class='btn btn-success' data-bs-dismiss="modal" onclick="Vtiger_Import_Js.loadListRecords();">{vtranslate('LBL_FINISH', $MODULE)}</button>
                </div>
            </div>
        </div>
    </div>
</div>
