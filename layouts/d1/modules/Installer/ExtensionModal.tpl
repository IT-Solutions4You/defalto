{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{vtranslate('LBL_UPDATE_EXTENSION', $QUALIFIED_MODULE)}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body h-50vh overflow-auto">
            <h5>{vtranslate('LBL_DOWNLOAD_LOG', $QUALIFIED_MODULE)}</h5>
            <div class="downloadLog" data-download-log="{$EXTENSION_INSTALL->getDownloadUrl()}">
                <div class="text-center my-5">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{vtranslate('LBL_CLOSE', $QUALIFIELD_MODULE)}</button>
        </div>
    </div>
</div>