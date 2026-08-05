{**
 * This file is part of Defalto â€“ a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="modal-overlay-footer clearfix fixed-bottom bg-body border-top border-1">
    <div class="container-fluid">
        <div class="row d-flex align-items-center h-header">
            <div class="col-6 text-end">
                <a class="btn btn-outline-primary cancelLink px-4" href="javascript:history.{if $DUPLICATE_RECORDS}go(-2){else}back(){/if}" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
            </div>
            <div class="col-6 text-start">
                <button type="submit" class="btn btn-primary active px-5 saveButton">
                    {if $RECORD_ID eq '' && InventoryItem_Utils_Helper::isInventoryModule($MODULE)}
                        {vtranslate('LBL_SAVE_AND_CONTINUE', $MODULE)}
                    {else}
                        {vtranslate('LBL_SAVE', $MODULE)}
                    {/if}
                </button>
            </div>
        </div>
    </div>
</div>
