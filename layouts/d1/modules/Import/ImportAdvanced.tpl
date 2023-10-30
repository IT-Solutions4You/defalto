{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<form action="index.php" enctype="multipart/form-data" method="POST" name="importAdvanced" id="importAdvanced" class="fc-overlay-modal modal-content">
    {assign var=TITLE value="{'LBL_IMPORT'|@vtranslate:$MODULE} {$FOR_MODULE|@vtranslate:$FOR_MODULE}"}
    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
    <div class="importview-content modal-body overflow-auto">
        <input type="hidden" name="module" value="{$FOR_MODULE}"/>
        <input type="hidden" name="view" value="Import"/>
        <input type="hidden" name="mode" value="import"/>
        <input type="hidden" name="type" value="{$USER_INPUT->get('type')}"/>
        <input type="hidden" name="has_header" value='{$HAS_HEADER}'/>
        <input type="hidden" name="file_encoding" value='{$USER_INPUT->get('file_encoding')}'/>
        <input type="hidden" name="delimiter" value='{$USER_INPUT->get('delimiter')}'/>
        {assign var=LABELS value=[]}
        {if $FORMAT eq 'vcf'}
            {$LABELS["step1"] = 'LBL_UPLOAD_VCF'}
        {elseif $FORMAT eq 'ics'}
            {$LABELS["step1"] = 'LBL_UPLOAD_ICS'}
        {else}
            {$LABELS["step1"] = 'LBL_UPLOAD_CSV'}
        {/if}
        {if $DUPLICATE_HANDLING_NOT_SUPPORTED eq 'true'}
            {$LABELS["step3"] = 'LBL_FIELD_MAPPING'}
        {else}
            {$LABELS["step2"] = 'LBL_DUPLICATE_HANDLING'}
            {$LABELS["step3"] = 'LBL_FIELD_MAPPING'}
        {/if}
        {include file="BreadCrumbs.tpl"|vtemplate_path:$MODULE BREADCRUMB_ID='navigation_links' ACTIVESTEP=3 BREADCRUMB_LABELS=$LABELS MODULE=$MODULE}
        <div class="importBlockContainer">
            <table class="table table-borderless">
                {if $ERROR_MESSAGE neq ''}
                    <tr>
                        <td class="text-start">
                            {$ERROR_MESSAGE}
                        </td>
                    </tr>
                {/if}
                <tr>
                    <td>
                        {include file='ImportStepThree.tpl'|@vtemplate_path:'Import'}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="modal-overlay-footer modal-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-6">
                    <a class="cancelLink btn btn-primary" data-bs-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                </div>
                <div class="col-6">
                    <button type="submit" name="import" id="importButton" class="btn btn-primary active" onclick="return Vtiger_Import_Js.sanitizeAndSubmit()">{'LBL_IMPORT_BUTTON_LABEL'|@vtranslate:$MODULE}</button>
                </div>
            </div>
        </div>
    </div>
</form>
