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
                    <div class="col-12">
                        <div class="commentTextArea">
                            <textarea name="commentcontent" class="commentcontent form-control" onfocusout="if(!this.value) this.style.height = null;" onfocus="this.style.height = '9em';"  placeholder="{vtranslate('LBL_POST_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
                        </div>
                    </div>
                </div>
                {if in_array($MODULE_NAME, $PRIVATE_COMMENT_MODULES)}
                    <div class="row justify-content-end mb-3">
                        <div class="col-auto">
                            <label class="form-check form-switch m-0">
                                <input type="checkbox" class="form-check-input" id="is_private">
                                <span class="form-check-label me-2">{vtranslate('LBL_INTERNAL_COMMENT')}</span>
                                <i class="fa fa-question-circle cursorPointer text-secondary" data-bs-toggle="tooltip" data-placement="top" title="{vtranslate('LBL_INTERNAL_COMMENT_INFO')}"></i>
                            </label>
                        </div>
                    </div>
                {/if}
                <div class="row">
                    {if $FIELD_MODEL->getProfileReadWritePermission()}
                        <div class="col-8">
                            {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) MODULE="ModComments"}
                        </div>
                    {/if}
                    <div class="col-12 mt-2 col-sm-auto mt-sm-0 ms-auto">
                        <button class="btn btn-primary active px-5 w-100 {$SAVE_BUTTON_CLASS}" type="button" data-mode="add">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span class="d-none d-lg-inline ms-2">{vtranslate('LBL_POST', $MODULE_NAME)}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    {/if}
{/strip}