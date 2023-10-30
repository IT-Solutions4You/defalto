{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{strip}
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="filePreview container-fluid">
                <div class="modal-header row">
                    <div class="filename col-lg-8">
                        <h4 class="text-truncate maxWidth50" title="{vtranslate('LBL_PREVIEW',$MODULE)}"><b>{vtranslate('LBL_PREVIEW',$MODULE)}</b></h4>
                    </div>
                    <div class="col-lg-1 pull-right">
                        <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                                <span aria-hidden="true" class='fa fa-close'></span>
                        </button>
                    </div>
                </div>
                <div class="modal-body row" style="height:550px;">
                    <iframe id='PDFMakerPreviewContent' src="{$FILE_PATH}" data-desc="{$FILE_PATH}" height="100%" width="100%"></iframe>
                </div>
            </div>
            <div class="modal-footer">
                <div class='clearfix modal-footer-overwrite-style'>
                    <div class="row clearfix ">
                            <div class=' textAlignCenter col-lg-12 col-md-12 col-sm-12 '>
                                    <button type='button' class='btn btn-success downloadButton' data-desc="{$DOWNLOAD_URL}"><strong>{vtranslate('LBL_DOWNLOAD_FILE',$MODULE)}</strong></button>&nbsp;&nbsp;
                                    {if $PRINT_ACTION eq "1"}
                                            <button type='button' class='btn btn-success printButton'><strong>{vtranslate('LBL_PRINT', $MODULE)}</strong></button>&nbsp;&nbsp;
                                    {/if}
                                    <a class='cancelLink' href="javascript:void(0);" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}
