{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
<div class="container-fluid bg-body-secondary p-3 rounded hide basicEditCommentBlock">
    <div class="row">
        <div class="col pe-0">
            <div class="commentArea" >
                <input type="text" name="reasonToEdit" placeholder="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" class="input-block-level form-control mb-2"/>
            </div>
            <div class="commentTextArea">
                <textarea name="commentcontent" class="commentcontenthidden form-control" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
            </div>
            <div>
                <input type="hidden" name="is_private">
            </div>
        </div>
        <div class="col-auto pe-0">
            <button class="btn btn-primary active {$SAVE_BUTTON_CLASS}" type="button" data-mode="edit">{vtranslate('LBL_POST', $MODULE_NAME)}</button>
        </div>
        <div class="col-auto">
            <a href="javascript:void(0);" class="btn btn-danger closeCommentBlock cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
        </div>
    </div>
</div>