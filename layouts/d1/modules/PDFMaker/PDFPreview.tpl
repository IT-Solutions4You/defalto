{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
                            <button type="button" class="btn btn-primary active downloadButton" data-desc="{$DOWNLOAD_URL}">
                                <strong>{vtranslate('LBL_DOWNLOAD_FILE',$MODULE)}</strong>
                            </button>
                        </div>
                        {if $PRINT_ACTION eq '1'}
                            <div class="col-auto">
                                <button type="button" class="btn btn-primary printButton">
                                    <strong>{vtranslate('LBL_PRINT', $MODULE)}</strong>
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
