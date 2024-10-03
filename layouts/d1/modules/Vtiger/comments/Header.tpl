{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
<div class="recentCommentsHeader p-3">
    <div class="row">
        <div class="col-lg">
            <h4 class="m-0">{if $ROLLUP_VIEW eq 'summary'}{vtranslate('LBL_RECENT_COMMENTS', $MODULE_NAME)}{else}{vtranslate('All Comments', $MODULE_NAME)}{/if}</h4>
        </div>
        {if $MODULE_NAME ne 'Leads'}
            <div class="commentHeader col-lg-auto">
                <div class="form-check form-switch form-check-reverse">
                    <label class="form-check-label" for="rollupcomments">
                        <span class="me-2">{vtranslate('LBL_SHOW_RELATED_COMMENTS',$QUALIFIED_MODULE)}</span>
                        <span class="fa fa-question-circle" data-bs-toggle="tooltip" data-placement="top" title="{vtranslate('LBL_SHOW_RELATED_COMMENTS_INFO',$QUALIFIED_MODULE)}"></span>
                    </label>
                    <input type="checkbox" class="form-check-input" id="rollupcomments" role="switch" hascomments="1" startindex="{$STARTINDEX}" data-view="{$ROLLUP_VIEW}" rollupid="{$ROLLUPID}" rollup-status="{$ROLLUP_STATUS}" module="{$MODULE_NAME}" record="{$PARENT_RECORD}" {if 1 eq $ROLLUP_STATUS}checked="checked"{/if} data-on-color="success"/>
                </div>
            </div>
        {/if}
    </div>
</div>