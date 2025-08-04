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
        {assign var=HEADER_TITLE value={'LBL_IMPORT_SCHEDULED'|@vtranslate:$MODULE}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
    </div>
    <div class='modal-body' style="margin-bottom:250px">
        <div>
            <table class="table table-borderless">
                {if $ERROR_MESSAGE neq ''}
                    <tr>
                        <td>
                            {$ERROR_MESSAGE}
                        </td>
                    </tr>
                {/if}
                {if $ENABLE_SCHEDULE_IMPORT_CRON}
                    <tr>
                        <td style="padding-left: 15px;">
                            {'LBL_ENABLE_CRON'|@vtranslate:$MODULE}
                        </td>
                    </tr>
                {/if}
                <tr>
                    <td>
                        <table cellpadding="10" cellspacing="0" align="center" class="table table-borderless">
                            <tr>
                                <td>{'LBL_SCHEDULED_IMPORT_DETAILS'|@vtranslate:$MODULE}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>   
    <div class='modal-overlay-footer border1px clearfix'>
        <div class="row clearfix">
            <div class='textAlignCenter col-lg-12 col-md-12 col-sm-12 '>
                <button  name="cancel" value="{'LBL_CANCEL_IMPORT'|@vtranslate:$MODULE}" class="btn btn-lg btn-danger"
                         onclick="Vtiger_Import_Js.cancelImport('index.php?module={$FOR_MODULE}&view=Import&mode=cancelImport&import_id={$IMPORT_ID}')">{'LBL_CANCEL_IMPORT'|@vtranslate:$MODULE}</button>
                <button class="btn btn-success btn-lg" name="ok" onclick="Vtiger_Import_Js.scheduleImport('index.php?module={$FOR_MODULE}&view=Import')">{'LBL_OK_BUTTON_LABEL'|@vtranslate:$MODULE}</button> 
            </div>
        </div>
    </div>
</div>
