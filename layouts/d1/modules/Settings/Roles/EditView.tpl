{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/Roles/views/EditAjax.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div class="editViewPageDiv viewContent px-4 pb-4">
    <div class="bg-body h-list rounded">
        <div class="editViewHeader p-3 border-bottom">
            <h4 class="m-0">
                {if $RECORD_MODEL->getId()}
                    {vtranslate('LBL_EDIT_ROLE', $QUALIFIED_MODULE)}
                {else}
                    {vtranslate('LBL_CREATE_ROLE', $QUALIFIED_MODULE)}
                {/if}
            </h4>
        </div>
        <form class="form-horizontal" id="EditView" name="EditRole" method="post" action="index.php" enctype="multipart/form-data">
            <div class="editViewBody">
                <div class="editViewContents">
                    <input type="hidden" name="module" value="Roles">
                    <input type="hidden" name="action" value="Save">
                    <input type="hidden" name="parent" value="Settings">
                    {assign var=RECORD_ID value=$RECORD_MODEL->getId()}
                    <input type="hidden" name="record" value="{$RECORD_ID}"/>
                    <input type="hidden" name="mode" value="{$MODE}">
                    <input type="hidden" name="profile_directly_related_to_role_id" value="{if isset($PROFILE_ID)}{$PROFILE_ID}{/if}"/>
                    {assign var=HAS_PARENT value="{if $RECORD_MODEL->getParent()}true{/if}"}
                    {if $HAS_PARENT}
                        <input type="hidden" name="parent_roleid" value="{$RECORD_MODEL->getParent()->getId()}">
                    {/if}
                    <div name="editContent" class="container-fluid pt-3">
                        <div class="form-group row py-2 px-3">
                            <label class="control-label fieldLabel col-lg-3 col-md-3 col-sm-3" for="profilename">
                                <strong>{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}</strong>
                                <span class="text-danger ms-2">*</span>
                            </label>
                            <div class="controls fieldValue col-lg-6 col-md-6 col-sm-6">
                                <input type="text" class="inputElement form-control" name="rolename" id="profilename" value="{$RECORD_MODEL->getName()}" data-rule-required='true'/>
                            </div>
                        </div>
                        <div class="form-group row py-2 px-3">
                            <label class="control-label fieldLabel col-lg-3 col-md-3 col-sm-3">
                                <strong>{vtranslate('LBL_REPORTS_TO', $QUALIFIED_MODULE)}</strong>
                            </label>
                            <div class="controls fieldValue col-lg-6 col-md-6 col-sm-6">
                                <input type="hidden" name="parent_roleid" {if $HAS_PARENT}value="{$RECORD_MODEL->getParent()->getId()}"{/if}>
                                <div class="">
                                    <input type="text" class="inputElement form-control" name="parent_roleid_display" {if $HAS_PARENT}value="{$RECORD_MODEL->getParent()->getName()}"{/if} readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row py-2 px-3">
                            <label class="control-label fieldLabel col-lg-3 col-md-3 col-sm-3">
                                <strong>{vtranslate('LBL_CAN_ASSIGN_RECORDS_TO', $QUALIFIED_MODULE)}</strong>
                            </label>
                            <div class="controls fieldValue col-lg-6 col-md-6 col-sm-6">
                                <div class="radio">
                                    <label>
                                        <input type="radio" value="1"{if !$RECORD_MODEL->get('allowassignedrecordsto')} checked=""{/if} {if $RECORD_MODEL->get('allowassignedrecordsto') eq '1'} checked="" {/if} name="allowassignedrecordsto" data-handler="new" class="alignTop"/>
                                        <span class="ms-2">{vtranslate('LBL_ALL_USERS',$QUALIFIED_MODULE)}</span>
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" value="2" {if $RECORD_MODEL->get('allowassignedrecordsto') eq '2'} checked="" {/if} name="allowassignedrecordsto" data-handler="new" class="alignTop"/>
                                        <span class="ms-2">{vtranslate('LBL_USERS_WITH_SAME_OR_LOWER_LEVEL',$QUALIFIED_MODULE)}</span>
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" value="3" {if $RECORD_MODEL->get('allowassignedrecordsto') eq '3'} checked="" {/if} name="allowassignedrecordsto" data-handler="new" class="alignTop"/>
                                        <span class="ms-2">{vtranslate('LBL_USERS_WITH_LOWER_LEVEL',$QUALIFIED_MODULE)}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <div class="container-fluid p-3">
                        <div class="row">
                            <div class="col-6 text-end">
                                <a class="cancelLink btn btn-primary" href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                            </div>
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary active saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>
