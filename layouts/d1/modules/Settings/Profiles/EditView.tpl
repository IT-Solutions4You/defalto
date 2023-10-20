{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/Profiles/views/Edit.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div class="editViewPageDiv">
    <div class="main-scroll px-4 pb-4">
        <form class="form-horizontal p-3 bg-body rounded" id="EditView" name="EditProfile" method="post" action="index.php" enctype="multipart/form-data">
            <div class="editViewHeader">
                {if $RECORD_MODEL->getId()}
                    <h4>
                        {vtranslate('LBL_EDIT_PROFILE', $QUALIFIED_MODULE)}
                    </h4>
                {else}
                    <h4>
                        {vtranslate('LBL_CREATE_PROFILE', $QUALIFIED_MODULE)}
                    </h4>
                {/if}
            </div>
            <hr>
            <div class="editViewBody">
                <div class="editViewContents">
                    <div id="submitParams">
                        <input type="hidden" name="module" value="Profiles"/>
                        <input type="hidden" name="action" value="Save"/>
                        <input type="hidden" name="parent" value="Settings"/>
                        {assign var=RECORD_ID value=$RECORD_MODEL->getId()}
                        <input type="hidden" name="record" value="{$RECORD_ID}"/>
                        <input type="hidden" name="mode" value="{$MODE}"/>
                        <input type="hidden" name="viewall" value="0"/>
                        <input type="hidden" name="editall" value="0"/>
                    </div>

                    <div name="editContent" class="container-fluid">
                        <div class="row form-group mb-3">
                            <div class="col-lg-3 col-md-3 col-sm-3 control-label fieldLabel">
                                <label>
                                    <strong>{vtranslate('LBL_PROFILE_NAME', $QUALIFIED_MODULE)}</strong>
                                    <span class="text-danger ms-2">*</span>
                                </label>
                            </div>
                            <div class="fieldValue col-lg-6 col-md-6 col-sm-6">
                                <input type="text" class="inputElement form-control" name="profilename" id="profilename" value="{$RECORD_MODEL->getName()}" data-rule-required="true"/>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-lg-3 col-md-3 col-sm-3 control-label fieldLabel">
                                <label>
                                    <strong>{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}</strong>
                                </label>
                            </div>
                            <div class="fieldValue col-lg-6 col-md-6 col-sm-6">
                                <textarea name="description" class="inputElement form-control" id="description" style="height:50px; resize: vertical;padding:5px 8px;">{$RECORD_MODEL->getDescription()}</textarea>
                            </div>
                        </div>
                        {include file='EditViewContents.tpl'|vtemplate_path:$QUALIFIED_MODULE}
                    </div>
                </div>
            </div>
            <div class="modal-overlay-footer modal-footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-6 text-end">
                            <a class="cancelLink btn btn-primary" data-dismiss="modal" href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary active saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
