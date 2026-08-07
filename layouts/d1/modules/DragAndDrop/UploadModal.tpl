{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="modal-dialog modelContainer modal-lg">
        <div class="modal-content">
            <form class="form-horizontal recordEditView" name="dragAndDropUpload" method="post" action="index.php">
                {assign var=HEADER_TITLE value={vtranslate('LBL_ATTACH_DOCUMENTS', $OUR_MODULE)}}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

                <input type="hidden" name="module" value="Documents"/>
                <input type="hidden" name="document_source" value="Vtiger"/>
                <input type="hidden" name="filelocationtype" value="I"/>
                <input type="hidden" name="filestatus" value="1"/>
                <input type="hidden" name="relationOperation" value="true"/>
                <input type="hidden" name="sourceModule" value="{$PARENT_MODULE}"/>
                <input type="hidden" name="sourceRecord" value="{$PARENT_ID}"/>
                <input type="hidden" name="max_upload_limit" value="{$MAX_UPLOAD_LIMIT_BYTES}"/>

                <div class="modal-body">
                    <div class="container-fluid">
                        <div id="dragAndDropFileList"
                             class="drag-and-drop-file-list list-group mb-2"
                             aria-live="polite"></div>
                        <script type="text/template" id="dragAndDropFileRowTemplate">
                            <div class="drag-and-drop-batch-row list-group-item d-flex align-items-center gap-2 px-3 py-2 position-relative">
                                <span class="drag-and-drop-batch-status text-secondary flex-shrink-0">
                                    <i class="fa-solid fa-file"></i>
                                </span>
                                <span class="drag-and-drop-batch-name text-truncate flex-grow-1"></span>
                                <span class="drag-and-drop-batch-size small text-secondary flex-shrink-0 d-none d-sm-inline"></span>
                                <button type="button"
                                        class="drag-and-drop-batch-remove btn btn-sm btn-link text-danger p-1 flex-shrink-0">
                                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                </button>
                                <div class="drag-and-drop-batch-progress progress position-absolute bottom-0 start-0 w-100 rounded-0">
                                    <div class="drag-and-drop-batch-progress-bar progress-bar"
                                         role="progressbar"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         aria-valuenow="0"></div>
                                </div>
                            </div>
                        </script>
                        <div id="dragAndDropTitleHint" class="small text-secondary mb-2 d-none">
                            {vtranslate('LBL_TITLE_FROM_FILENAME', $OUR_MODULE)}
                            <i class="fa-solid fa-circle-info ms-1"
                               data-bs-toggle="tooltip"
                               tabindex="0"
                               title="{vtranslate('LBL_MAX_UPLOAD_SIZE_INFO', $OUR_MODULE)} {$MAX_UPLOAD_LIMIT_MB} MB"></i>
                        </div>

                        <div class="massEditTable">
                            <div id="dragAndDropTitleRow" class="row py-2 align-items-center">
                                {assign var=FIELD_MODEL value=$FIELD_MODELS['notes_title']}
                                <div class="fieldLabel col-12 col-lg-3 text-secondary text-lg-end mb-1 mb-lg-0">
                                    {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                    {if $FIELD_MODEL->isMandatory() eq true}
                                        <span class="text-danger ms-2">*</span>
                                    {/if}
                                </div>
                                <div class="fieldValue col-12 col-lg-9">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE)}
                                </div>
                            </div>

                            <div class="row py-2 align-items-center">
                                {assign var=FIELD_MODEL value=$FIELD_MODELS['assigned_user_id']}
                                <div class="fieldLabel col-12 col-lg-3 text-secondary text-lg-end mb-1 mb-lg-0">
                                    {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                    {if $FIELD_MODEL->isMandatory() eq true}
                                        <span class="text-danger ms-2">*</span>
                                    {/if}
                                </div>
                                <div class="fieldValue col-12 col-lg-9">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE)}
                                </div>
                            </div>

                            {if $FIELD_MODELS['folderid']}
                                <div class="row py-2 align-items-center">
                                    {assign var=FIELD_MODEL value=$FIELD_MODELS['folderid']}
                                    <div class="fieldLabel col-12 col-lg-3 text-secondary text-lg-end mb-1 mb-lg-0">
                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                        {if $FIELD_MODEL->isMandatory() eq true}
                                            <span class="text-danger ms-2">*</span>
                                        {/if}
                                    </div>
                                    <div class="fieldValue col-12 col-lg-9">
                                        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE)}
                                    </div>
                                </div>
                            {/if}

                            {if $FIELD_MODELS['notecontent']}
                                <div class="row py-2 align-items-start">
                                    {assign var=FIELD_MODEL value=$FIELD_MODELS['notecontent']}
                                    <div class="fieldLabel col-12 col-lg-3 text-secondary text-lg-end mb-1 mb-lg-0 pt-lg-2">
                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                        {if $FIELD_MODEL->isMandatory() eq true}
                                            <span class="text-danger ms-2">*</span>
                                        {/if}
                                    </div>
                                    <div class="fieldValue col-12 col-lg-9">
                                        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE)}
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>

                {assign var=BUTTON_NAME value={vtranslate('LBL_UPLOAD', $MODULE)}}
                {assign var=BUTTON_ID value="dragAndDropUploadBtn"}
                {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
            </form>
        </div>
    </div>
{/strip}
