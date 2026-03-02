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
            <div class="container">
                <div class="row py-2">
                    {if !empty($ERROR_MESSAGE)}
                        <div class="alert alert-danger">{$ERROR_MESSAGE}</div>
                    {/if}
                </div>
                {if $ENABLE_SCHEDULE_IMPORT_CRON}
                    <div class="row">
                        {'LBL_ENABLE_CRON'|@vtranslate:$MODULE}
                    </div>
                {/if}
                <div class="row py-2">
                    <div class="col">{'LBL_SCHEDULED_IMPORT_DETAILS'|@vtranslate:$MODULE}</div>
                </div>
            </div>
        </div>
    </div>   
    <div class='modal-footer modal-overlay-footer border1px clearfix'>
        <div class="container">
            <div class="row">
                <div class="col text-end">
                    <button  name="cancel" value="{'LBL_CANCEL_IMPORT'|@vtranslate:$MODULE}" class="btn btn-danger"
                             onclick="Vtiger_Import_Js.cancelImport('index.php?module={$FOR_MODULE}&view=Import&mode=cancelImport&import_id={$IMPORT_ID}')">{'LBL_CANCEL_IMPORT'|@vtranslate:$MODULE}</button>
                </div>
                <div class="col">
                    <button class="btn btn-success" name="ok" onclick="Vtiger_Import_Js.scheduleImport('index.php?module={$FOR_MODULE}&view=Import')">{'LBL_OK_BUTTON_LABEL'|@vtranslate:$MODULE}</button>
                </div>
            </div>
        </div>
    </div>
</div>
