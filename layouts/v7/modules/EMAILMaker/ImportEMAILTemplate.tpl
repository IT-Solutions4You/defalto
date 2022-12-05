{*<!--
/*********************************************************************************
* The content of this file is subject to the EMAIL Maker license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
    <div class='fc-overlay-modal'>
        <div class="modal-content">
            <div class="overlayHeader">
                {assign var=TITLE value="{'LBL_IMPORT'|@vtranslate:$MODULE}"}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
            </div>
            <div class='modal-body' style="margin-bottom:100%" id="landingPageDiv">
                <hr>
                <div class="landingPage container-fluid importServiceSelectionContainer">

                    <form enctype="multipart/form-data" name="importBasic" method="POST" action="index.php">
                        <input type="hidden" name="module" value="EMAILMaker">
                        <input type="hidden" name="action" value="Import">

                        <div class="importBlockContainer show" id="uploadFileContainer">
                            <table class="table table-borderless" cellpadding="30">
                                                <span>
                                                        <h4>&nbsp;&nbsp;&nbsp;{'LBL_EMAILMAKER_IMPORT'|@vtranslate:$MODULE}</h4>
                                                </span>
                                <hr>
                                <tr id="file_type_container" style="height:50px">
                                    <td></td>
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
                        <div class="modal-overlay-footer border1px clearfix">
                            <div class="row clearfix">
                                <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12 ">
                                    <button type="submit" name="import" id="importButton" class="btn btn-success btn-lg" onclick="return EMAILMaker_List_Js.uploadAndParse()"><strong>{vtranslate('LBL_IMPORT_BUTTON_LABEL','Import')}</strong></button> &nbsp;&nbsp;
                                    <a class="cancelLink" onclick="Vtiger_Import_Js.loadListRecords();" data-dismiss="modal" href="#">{vtranslate('LBL_CANCEL')}</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
{/strip}