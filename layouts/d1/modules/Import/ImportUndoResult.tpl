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
    <div class='modal-body' id ="importContainer" style="margin-bottom:390px">
        <div style="padding-left: 15px;">
            <input type="hidden" name="module" value="{$FOR_MODULE}" />
            <div class='importBlockContainer'>
                <span>
                    <h4>&nbsp;&nbsp;&nbsp;{'LBL_DELETION_SUMMARY'|@vtranslate:$MODULE}</h4>
                </span>
                <hr style="margin-top:12px;margin-bottom:12px;">
                <table class = "table table-borderless">
                    {if $ERROR_MESSAGE neq ''}
                        <span>
                            <h4>
                                {$ERROR_MESSAGE}
                            </h4>
                        </span>
                    {/if}
                    <tr>
                        <td >
                            <table cellpadding="10" cellspacing="0" class = "table table-bordered importResultsTable">
                                <tr>
                                    <td  width="40%">{'LBL_TOTAL_RECORDS'|@vtranslate:$MODULE}</td>
                                    <td  width="50%">{$TOTAL_RECORDS}</td>
                                </tr>
                                <tr>
                                    <td width="40%">{'LBL_NUMBER_OF_RECORDS_DELETED'|@vtranslate:$MODULE}</td>
                                    <td width="60%">{$DELETED_RECORDS_COUNT}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" colspan="2">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class='modal-footer overlayFooter'>
        <footer>
            <center><button class='btn-primary btn-lg undoDoneButton' data-bs-dismiss="modal" onclick="Vtiger_Import_Js.finishUndoOperation();">{vtranslate('LBL_DONE_BUTTON', $MODULE)}</button></center>
        </footer>
    </div>
</div>
