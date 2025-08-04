{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {if $IS_CREATABLE}
        <div class="commentTitle">
            <div class="p-3 rounded bg-body-secondary addCommentBlock">
                <div class="row mb-3">
                    <div class="col pe-0">
                        <div class="commentTextArea">
                            <textarea name="commentcontent" class="commentcontent form-control"  placeholder="{vtranslate('LBL_POST_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary active px-5 {$SAVE_BUTTON_CLASS}" type="button" data-mode="add">{vtranslate('LBL_POST', $MODULE_NAME)}</button>
                    </div>
                </div>
                <div class="row">
                    {if $FIELD_MODEL->getProfileReadWritePermission()}
                        <div class="col">
                            {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) MODULE="ModComments"}
                        </div>
                    {/if}
                    <div class="col-auto ms-auto">
                        {if in_array($MODULE_NAME, $PRIVATE_COMMENT_MODULES)}
                            <label class="form-check form-switch form-check-reverse">
                                <input type="checkbox" class="form-check-input" id="is_private">
                                <span class="form-check-label me-2">{vtranslate('LBL_INTERNAL_COMMENT')}</span>
                                <i class="fa fa-question-circle cursorPointer" data-bs-toggle="tooltip" data-placement="top" title="{vtranslate('LBL_INTERNAL_COMMENT_INFO')}"></i>
                            </label>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    {/if}
{/strip}