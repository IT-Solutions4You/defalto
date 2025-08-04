{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="filePreview container-fluid">
                <div class="modal-header">
                    <h5 class="modal-title">{$FILE_NAME}</h5>
                    {if $FILE_PREVIEW_NOT_SUPPORTED neq 'yes'}
                        <a class="btn btn-primary ms-auto" href="{$DOWNLOAD_URL}">{vtranslate('LBL_DOWNLOAD_FILE',$MODULE_NAME)}</a>
                    {/if}
                    <button data-bs-dismiss="modal" class="ms-3 btn-close" title="close"></button>
                </div>
                <div class="modal-body row" style="height:550px;">
                    {if $FILE_PREVIEW_NOT_SUPPORTED eq 'yes'}
                        <div class="well" style="height:100%;">
                            <center>
                                <b>{vtranslate('LBL_PREVIEW_NOT_AVAILABLE',$MODULE_NAME)}</b>
                                <br><br><br>
                                <a class="btn btn-default btn-large" href="{$DOWNLOAD_URL}">{vtranslate('LBL_DOWNLOAD_FILE',$MODULE_NAME)}</a>
                                <br><br><br><br>
                                <div class='span11 offset1 alert-info' style="padding:10px">
                                    <span class='span offset1 alert-info'>
                                        <i class="icon-info-sign"></i>
                                        {vtranslate('LBL_PREVIEW_SUPPORTED_FILES',$MODULE_NAME)}
                                    </span>
                                </div>
                                <br>
                            </center>
                        </div>
                    {else}
                        {if $BASIC_FILE_TYPE eq 'yes'}
                            <div style="overflow:auto;height:100%;">
                                <pre>
                                    {htmlentities($FILE_CONTENTS)}
                                </pre>
                            </div>
                        {elseif $OPENDOCUMENT_FILE_TYPE eq 'yes'}
                            <iframe id="viewer" src="libraries/jquery/Viewer.js/#../../../{$DOWNLOAD_URL}" width="100%" height="100%" allowfullscreen webkitallowfullscreen></iframe>
                        {elseif $PDF_FILE_TYPE eq 'yes'}
                            <iframe id='viewer' src="libraries/jquery/pdfjs/web/viewer.html?file={$SITE_URL}/{$DOWNLOAD_URL|escape:'url'}" height="100%" width="100%"></iframe>
                        {elseif $IMAGE_FILE_TYPE eq 'yes'}
                            <div style="overflow:auto;height:100%;width:100%;float:left;background-image: url({$DOWNLOAD_URL});background-color: #EEEEEE;background-position: center 25%;background-repeat: no-repeat;display: block; background-size: contain;"></div>
                        {elseif $AUDIO_FILE_TYPE eq 'yes'}
                            <div style="overflow:auto;height:100%;width:100%;float:left;background-color: #EEEEEE;background-position: center 25%;background-repeat: no-repeat;display: block;text-align: center;">
                                <div style="display: inline-block;margin-top : 10%;">
                                    <audio controls>
                                        <source src="{$SITE_URL}/{$DOWNLOAD_URL}" type="{$FILE_TYPE}">
                                    </audio>
                                </div>
                            </div>
                        {elseif $VIDEO_FILE_TYPE eq 'yes'}
                            <div style="overflow:auto;height:100%;">
                                <link href="libraries/jquery/video-js/video-js.css" rel="stylesheet">
                                <script src="libraries/jquery/video-js/video.js"></script>
                                <video class="video-js vjs-default-skin" controls preload="auto" {literal}data-setup="{'techOrder': ['flash', 'html5']}" {/literal}width="100%" height="100%">
                                    <source src="{$SITE_URL}/{$DOWNLOAD_URL}" type='{$FILE_TYPE}' />
                                </video>
                            </div>
                        {/if}
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/strip}
