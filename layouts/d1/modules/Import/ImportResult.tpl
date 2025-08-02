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
    <div class='modal-body' style="margin-bottom:100%">
        <div class="summaryWidgetContainer">
            <input type="hidden" name="module" value="{$FOR_MODULE}" />
            <h4>{'LBL_TOTAL_RECORDS_SCANNED'|@vtranslate:$MODULE}&nbsp;&nbsp;:&nbsp;&nbsp;{$IMPORT_RESULT.TOTAL}</h4>
            {if isset($ERROR_MESSAGE) && $ERROR_MESSAGE neq ''}<span>{$ERROR_MESSAGE}</span>{/if}
            <hr>
            <div>{include file="Import_Result_Details.tpl"|@vtemplate_path:'Import'}</div>
        </div>
    </div>
    <div class='modal-overlay-footer border1px clearfix'>
       <div class="row clearfix">
            <div class='textAlignCenter col-lg-12 col-md-12 col-sm-12 '>
                <button name="next" class="btn btn-primary btn-lg"
                        onclick="return Vtiger_Import_Js.triggerImportAction();">{'LBL_IMPORT_MORE'|@vtranslate:$MODULE}</button>
                &nbsp;&nbsp;&nbsp;
                {if $MERGE_ENABLED eq '0'}
                    <button name="next" class="btn btn-danger btn-lg"
                            onclick="Vtiger_Import_Js.undoImport('index.php?module={$FOR_MODULE}&view=Import&mode=undoImport&foruser={$OWNER_ID}')">{'LBL_UNDO_LAST_IMPORT'|@vtranslate:$MODULE}</button>
                    &nbsp;&nbsp;&nbsp;
                {/if}
                <button class='btn btn-success btn-lg' data-bs-dismiss="modal" onclick="Vtiger_Import_Js.loadListRecords();">{vtranslate('LBL_FINISH', $MODULE)}</button>
            </div>
        </div>
    </div>
</div>
