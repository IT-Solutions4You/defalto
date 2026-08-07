{*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div id="dragAndDropOverlay">
        <div class="drag-and-drop-box drag-and-drop-enabled">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <h4>{$DRAG_AND_DROP_STRINGS['JS_DND_DROP_FILES_HERE']}</h4>
            <span>{$DRAG_AND_DROP_STRINGS['JS_DND_FILES_ATTACHED_INFO']}</span>
        </div>
        <div class="drag-and-drop-box drag-and-drop-disabled">
            <i class="fa-solid fa-ban"></i>
            <h4>{$DRAG_AND_DROP_STRINGS['JS_DND_UPLOAD_NOT_AVAILABLE']}</h4>
            <span>{$DRAG_AND_DROP_STRINGS['JS_DND_NO_DOCUMENTS_RELATION']}</span>
        </div>
    </div>
{/strip}
