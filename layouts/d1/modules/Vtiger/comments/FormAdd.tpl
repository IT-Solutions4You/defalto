{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
<div class="container-fluid bg-body-secondary p-3 rounded hide basicAddCommentBlock">
    <div class="row">
        <div class="col pe-0 commentTextArea">
            <textarea name="commentcontent" class="commentcontent form-control" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
        </div>
        <div class="col-auto pe-0">
            <button class="btn btn-primary active {$SAVE_BUTTON_CLASS}" type="button" data-mode="add">{vtranslate('LBL_POST', $MODULE_NAME)}</button>
        </div>
        <div class="col-auto">
            <a href="javascript:void(0);" class="btn btn-danger closeCommentBlock cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
        </div>
    </div>
    {if in_array($MODULE_NAME, $PRIVATE_COMMENT_MODULES)}
        <div class="row">
            <div class="col text-end">
                <label class="form-check form-switch form-check-reverse pt-3 containerInternalComment">
                    <span class="form-check-label">{vtranslate('LBL_INTERNAL_COMMENT')}</span>
                    <input type="checkbox" class="form-check-input" id="is_private" checked="checked">
                </label>
            </div>
        </div>
    {/if}
</div>