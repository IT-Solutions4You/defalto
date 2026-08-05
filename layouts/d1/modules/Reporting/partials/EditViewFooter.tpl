{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="modal-overlay-footer clearfix fixed-bottom bg-body border-top border-1">
    <div class="container-fluid">
        <div class="row d-flex align-items-center h-header">
            <div class="col text-center">
                <a class="btn btn-outline-primary cancelLink px-4 me-2" href="javascript:history.{if $DUPLICATE_RECORDS}go(-2){else}back(){/if}" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                <button type="button" class="btn btn-outline-primary editViewBackButton px-4 me-2 d-none"><i class="fa-solid fa-arrow-left me-2"></i>{vtranslate('LBL_BACK', $MODULE)}</button>
                <button type="button" class="btn btn-primary active editViewNextButton px-4 me-2 d-none">{vtranslate('LBL_NEXT', $MODULE)}<i class="fa-solid fa-arrow-right ms-2"></i></button>
                <button type="submit" class="btn btn-primary active px-5 saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button>
            </div>
        </div>
    </div>
</div>
