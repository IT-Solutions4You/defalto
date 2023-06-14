{*<!--
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
-->*}
{strip}
    <div class="">
        <form id="detailView" class="form-horizontal" style="padding-top: 20px;" method="POST">
            <div class="clearfix">
                <h4 class="pull-left">
                    {vtranslate('LBL_SHARING_RECORD', $MODULE)}
                </h4>
                <span class="btn-group pull-right">
                    {if Users_Privileges_Model::isPermitted($MODULE, 'EditView', $RECORD_ID )}
                    <button class="btn" onclick="window.location.href='{$RECORD_MODEL->getEditViewUrl($MODULE,$RECORD_ID)}'" type="button">
                        {vtranslate('LBL_EDIT_SHARING_RECORD', $MODULE)}
                    </button>
                    {/if}
                </span>
            </div><hr>
            {assign var="GROUPS" value=$RECORD_MODEL->getData()}
            {foreach key=KEY item=GROUP from=$GROUPS}
                <div class="form-group">
                    <span class="fieldLabel col-lg-3 col-md-3 col-sm-3 ">
                        {if $KEY==1}
                        <b> {vtranslate('LBL_SHARING_VIEW_MEMBERS', $QUALIFIED_MODULE)}</b>
                        {else}
                        <b> {vtranslate('LBL_SHARING_EDIT_MEMBERS', $MODULE)}:</b>
                        {/if}
                    </span>
                    <div class="fieldValue">
                        {if !empty($GROUP)}
                        <div class="col-lg-6 col-md-6 col-sm-6 collectiveGroupMembers" style="width:auto;min-width:300px">
                            <ul class="nav">
                            {foreach key=GROUP_LABEL item=GROUP_MEMBERS from=$GROUP}
                                {if !empty($GROUP_MEMBERS)}
                                    <li class="groupLabel">
                                            {vtranslate($GROUP_LABEL,$MODULE)}
                                    </li>
                                    {foreach key=GROUP_ID item=GROUP_MEMBER_INFO from=$GROUP_MEMBERS}
                                        <li >
                                            <a href="{$RECORD_MODEL->getRecordDetailViewUrl($GROUP_LABEL, $GROUP_ID)}">{$GROUP_MEMBER_INFO}</a>
                                        </li>
                                    {/foreach}
                                {/if}
                            {/foreach}
                            </ul>

                        </div>
                        {/if}
                    </div>
                </div>
            {/foreach}

            
        </form>
    </div>

{/strip}