{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="container-fluid">
        <div class="rounded bg-body mt-3">
            <form id="detailView" class="form-horizontal" method="POST">
                <div class="container-fluid border-bottom p-3">
                    <div class="row align-items-center">
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
                {assign var=GROUPS value=$RECORD_MODEL->getData()}
                {foreach key=KEY item=GROUP from=$GROUPS}
                    <div class="container-fluid p-3">
                        <div class="row">
                            <div class="fieldLabel col-lg-3">
                                {if $KEY==1}
                                    <h5>{vtranslate('LBL_SHARING_VIEW_MEMBERS', $QUALIFIED_MODULE)}</h5>
                                {else}
                                    <h5>{vtranslate('LBL_SHARING_EDIT_MEMBERS', $MODULE)}:</h5>
                                {/if}
                            </div>
                            <div class="fieldValue col-lg">
                                {if !empty($GROUP)}
                                    <div class="collectiveGroupMembers">
                                        {foreach key=GROUP_LABEL item=GROUP_MEMBERS from=$GROUP}
                                            {if !empty($GROUP_MEMBERS)}
                                                <div class="pb-3">
                                                    <div>{vtranslate($GROUP_LABEL,$MODULE)}</div>
                                                    <div>
                                                        {foreach key=GROUP_ID item=GROUP_MEMBER_INFO from=$GROUP_MEMBERS}
                                                            <a class="btn me-2 {$GROUP_LABEL}" href="{$RECORD_MODEL->getRecordDetailViewUrl($GROUP_LABEL, $GROUP_ID)}">{$GROUP_MEMBER_INFO}</a>
                                                        {/foreach}
                                                    </div>
                                                </div>
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