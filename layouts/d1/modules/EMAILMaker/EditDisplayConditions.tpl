{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    <div class="main-container p-4">
        <div class="editViewPageDiv bg-body rounded p-3">
            <div>
                <form id="EditView" class="form-horizontal recordEditView" name="EditView" method="post" action="index.php">
                    <input type="hidden" name="record" value="{$RECORDID}" id="record"/>
                    <input type="hidden" name="module" value="{$MODULE}"/>
                    <input type="hidden" name="action" value="IndexAjax"/>
                    <input type="hidden" name="mode" value="SaveDisplayConditions"/>
                    <input type="hidden" name="conditions" id="advanced_filter" value=''/>
                    <div class="editViewHeader">
                        <div>
                            <div>
                                {if $RECORD_ID neq ''}
                                    <h4 class="editHeader" title="{vtranslate('LBL_EDITING', $MODULE)} {vtranslate('LBL_CONDITIONS', $MODULE)}">{vtranslate('LBL_EDITING', $MODULE)} {vtranslate('LBL_CONDITIONS', $MODULE)}</h4>
                                {else}
                                    <h4 class="editHeader">{vtranslate('LBL_CREATING_NEW', $MODULE)} {vtranslate('LBL_CONDITIONS', $MODULE)}</h4>
                                {/if}
                            </div>
                        </div>
                    </div>
                    <div class="editViewBody">
                        <div class="editViewContents" id="editViewContents">
                            {if $OLD_CONDITIONS eq "yes"}
                                <div class="alert alert-info">
                                    {vtranslate('LBL_CREATED_IN_OLD_LOOK_CANNOT_BE_EDITED',$MODULE)}
                                    <br>
                                    <br>
                                    <strong>{vtranslate('LBL_OLD_CONDITIONS', $MODULE)}:</strong>
                                    <br>
                                    {include file='DetailDisplayConditions.tpl'|@vtemplate_path:$MODULE}
                                </div>
                            {/if}
                            <div class="form-group">
                                <div id="display_condition">
                                    <div class="editViewHeader">
                                        <h5>
                                            <div class="row align-items-center">
                                                <div class="col-auto">{vtranslate('LBL_DISPLAYED',$MODULE)}:</div>
                                                <div class="col-3">
                                                    <select id="displayedValue" name="displayedValue" class="select2">
                                                        <option value="0" {if $EMAIL_TEMPLATE_RESULT.displayed eq "0"}selected{/if}>{vtranslate('LBL_YES',$MODULE)}</option>
                                                        <option value="1" {if $EMAIL_TEMPLATE_RESULT.displayed eq "1"}selected{/if}>{vtranslate('LBL_NO',$MODULE)}</option>
                                                    </select>
                                                </div>
                                                <div class="col">{vtranslate('LBL_IF',$MODULE)}:</div>
                                            </div>
                                        </h5>
                                    </div>

                                    <div id="advanceFilterContainer" class="conditionsContainer">
                                        <div class="col-sm-12">
                                            <div class="table table-bordered py-3">
                                                {include file='AdvanceFilter.tpl'|@vtemplate_path:$MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE}
                                            </div>
                                        </div>
                                        {include file="FieldExpressions.tpl"|@vtemplate_path:$MODULE}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-overlay-footer clearfix">
                        <div class="row">
                            <div class="col text-end">
                                <a class="btn btn-primary cancelLink" href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary active saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button>&nbsp;&nbsp;
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/strip}
