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
    <div class="container-fluid">
        <div class="rounded bg-white mt-3 p-3">
            <form id="detailView" class="form-horizontal" method="POST">
                <div class="container-fluid border-bottom border-1 pb-3 mb-3">
                    <div class="row">
                        <div class="col">
                            <h4 class="m-0">
                                {vtranslate('LBL_SHARING_RECORD', $MODULE)}
                            </h4>
                        </div>
                        <div class="col-auto text-end">
                            <span class="btn-group pull-right">
                                {if Users_Privileges_Model::isPermitted($MODULE, 'EditView', $RECORD_ID )}
                                <button class="btn btn-outline-secondary" onclick="window.location.href='{$RECORD_MODEL->getEditViewUrl($MODULE,$RECORD_ID)}'" type="button">
                                    {vtranslate('LBL_EDIT_SHARING_RECORD', $MODULE)}
                                </button>
                                {/if}
                            </span>
                        </div>
                    </div>
                </div>
                {assign var="GROUPS" value=$RECORD_MODEL->getData()}
                {foreach key=KEY item=GROUP from=$GROUPS}
                    <div class="container-fluid my-3">
                        <div class="row">
                            <div class="fieldLabel col-lg-3 col-md-3 col-sm-3">
                                {if $KEY==1}
                                <h5>{vtranslate('LBL_SHARING_VIEW_MEMBERS', $QUALIFIED_MODULE)}</h5>
                                {else}
                                <h5>{vtranslate('LBL_SHARING_EDIT_MEMBERS', $MODULE)}:</h5>
                                {/if}
                            </div>
                            <div class="fieldValue col-lg-9 col-md-9 col-sm-9">
                                {if !empty($GROUP)}
                                <div class="collectiveGroupMembers">
                                    {foreach key=GROUP_LABEL item=GROUP_MEMBERS from=$GROUP}
                                        {if !empty($GROUP_MEMBERS)}
                                            <ul class="nav border-1 border p-0 mb-3">
                                                <li class="nav-item groupLabel bg-body-secondary border-end">
                                                    <div class="p-2">{vtranslate($GROUP_LABEL,$MODULE)}</div>
                                                </li>
                                                {foreach key=GROUP_ID item=GROUP_MEMBER_INFO from=$GROUP_MEMBERS}
                                                    <li class="nav-item border-end">
                                                        <a class="d-block p-2" href="{$RECORD_MODEL->getRecordDetailViewUrl($GROUP_LABEL, $GROUP_ID)}">{$GROUP_MEMBER_INFO}</a>
                                                    </li>
                                                {/foreach}
                                            </ul>
                                        {/if}
                                    {/foreach}

                                </div>
                                {/if}
                            </div>
                        </div>
                    </div>
                {/foreach}


            </form>
        </div>
    </div>
{/strip}