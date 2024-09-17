{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    {assign var=IS_CREATABLE value=$COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
    {assign var=IS_EDITABLE value=$COMMENTS_MODULE_MODEL->isPermitted('EditView')}

    {if !empty($PARENT_COMMENTS) && is_array($PARENT_COMMENTS)}
        {foreach key=Index item=COMMENT from=$PARENT_COMMENTS}
            {include file='comments/Comment.tpl'|@vtemplate_path:$MODULE_NAME}
        {/foreach}
    {else}
        <div class="noCommentsMsgContainer p-3 my-3">
            <p class="text-center">{vtranslate('LBL_NO_COMMENTS',$MODULE_NAME)}</p>
        </div>
    {/if}
{/strip}