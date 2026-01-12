{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="modal-dialog modal-xl">
        <div class="modal-content filePreview">
            <div class="modal-header">
                <h5 class="modal-title" title="{vtranslate('LBL_PREVIEW',$MODULE)}">{vtranslate('LBL_PREVIEW',$MODULE)}</h5>
                <button type="button" class="btn btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row" style="height:50vh;">
                <iframe id="PDFMakerPreviewContent" src="{$FILE_PATH}" data-desc="{$FILE_PATH}" height="100%" width="100%"></iframe>
            </div>
            <div class="modal-footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col text-end">
                            <button type="button" class="btn btn-primary active" onclick="PDFMaker_FreeActions_Js.sendEmail('{$TEMPLATE}', '{$LANGUAGE}')">
                                <i class="bi bi-send-fill"></i>
                                <b class="ms-2">{vtranslate('LBL_SEND_EMAIL',$MODULE)}</b>
                            </button>
                            <button type="button" class="btn btn-primary active downloadButton ms-2" data-desc="{$DOWNLOAD_URL}">
                                <i class="bi bi-file-earmark-arrow-down-fill"></i>
                                <b class="ms-2">{vtranslate('LBL_DOWNLOAD_FILE',$MODULE)}</b>
                            </button>
                        </div>
                        {if $PRINT_ACTION eq '1'}
                            <div class="col-auto">
                                <button type="button" class="btn btn-primary printButton">
                                    <i class="bi bi-printer-fill"></i>
                                    <b class="ms-2">{vtranslate('LBL_PRINT', $MODULE)}</b>
                                </button>
                            </div>
                        {/if}
                        <div class="col-auto">
                            <a class="btn btn-primary cancelLink" href="javascript:void(0);" type="reset" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}
