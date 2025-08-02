{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class='fc-overlay-modal'>
        <form enctype="multipart/form-data" name="importBasic" method="POST" action="index.php">
            <input type="hidden" name="module" value="EMAILMaker">
            <input type="hidden" name="action" value="Import">
            <div class="modal-content">
                {assign var=TITLE value=vtranslate('LBL_EMAILMAKER_IMPORT', $MODULE)}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
                <div class="modal-body bg-body-secondary" id="landingPageDiv">
                    <div class="landingPage container-fluid importServiceSelectionContainer p-3 rounded bg-body">
                        <div class="importBlockContainer show" id="uploadFileContainer">
                            <table class="table table-borderless" cellpadding="30">
                                <tr id="file_type_container" style="height:50px">
                                    <td>{'LBL_SELECT_XML'|@vtranslate:$MODULE}</td>
                                    <td data-import-upload-size="{$IMPORT_UPLOAD_SIZE}" data-import-upload-size-mb="{$IMPORT_UPLOAD_SIZE_MB}">
                                        <div>
                                            <input name="type" value="xml" type="hidden">
                                            <input type="hidden" name="is_scheduled" value="1"/>
                                            <div class="fileUploadBtn btn btn-primary">
                                                <span><i class="fa fa-laptop"></i> {vtranslate('Select from My Computer', $MODULE)}</span>
                                                <input type="file" name="import_file" id="import_file" onchange="Vtiger_Import_Js.checkFileType(event)" data-file-formats="xml" data-fileFormats="xml"/>
                                            </div>
                                            <div id="importFileDetails" class="padding10"></div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col text-end">
                            <a class="btn btn-primary cancelLink" onclick="Vtiger_Import_Js.loadListRecords();" data-bs-dismiss="modal" href="#">{vtranslate('LBL_CANCEL')}</a>
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="import" id="importButton" class="btn btn-primary active" onclick="return EMAILMaker_List_Js.uploadAndParse()">
                                <strong>{vtranslate('LBL_IMPORT_BUTTON_LABEL','Import')}</strong>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
{/strip}