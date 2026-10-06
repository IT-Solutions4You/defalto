{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <p class="text-secondary">{vtranslate('LBL_TRASH_TASK_DESCRIPTION', $QUALIFIED_MODULE)}</p>
    <div class="row mb-3">
        <label for="trashTaskDeletionMode" class="col-sm-3 col-form-label">{vtranslate('LBL_TRASH_TASK_MODE', $QUALIFIED_MODULE)}</label>
        <div class="col-sm-6">
            <select id="trashTaskDeletionMode" name="deletionMode" class="form-select">
                <option value="trash" {if !$TASK_OBJECT->isPermanentDelete()}selected{/if}>{vtranslate('LBL_MOVE_TO_RECYCLEBIN', $QUALIFIED_MODULE)}</option>
                <option value="permanent" {if $TASK_OBJECT->isPermanentDelete()}selected{/if}>{vtranslate('LBL_TRASH_TASK_PERMANENT', $QUALIFIED_MODULE)}</option>
            </select>
            <p class="small text-secondary mt-2">{vtranslate('LBL_TRASH_TASK_PERMANENT_DESCRIPTION', $QUALIFIED_MODULE)}</p>
        </div>
    </div>
{/strip}