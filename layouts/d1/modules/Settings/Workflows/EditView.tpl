{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="editViewPageDiv px-4 pb-4">
        <div class="bg-body rounded" id="EditView">
            <form name="EditWorkflow" action="index.php" method="post" id="workflow_edit" class="form-horizontal">
                {assign var=WORKFLOW_MODEL_OBJ value=$WORKFLOW_MODEL->getWorkflowObject()}
                <input type="hidden" name="record" value="{$RECORDID}" id="record"/>
                <input type="hidden" name="module" value="Workflows"/>
                <input type="hidden" name="action" value="SaveWorkflow"/>
                <input type="hidden" name="parent" value="Settings"/>
                <input type="hidden" name="returnsourcemodule" value="{$RETURN_SOURCE_MODULE}"/>
                <input type="hidden" name="returnpage" value="{$RETURN_PAGE}"/>
                <input type="hidden" name="returnsearch_value" value="{$RETURN_SEARCH_VALUE}"/>
                <div class="editViewHeader border-bottom">
                    <h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate('LBL_BASIC_INFORMATION', $QUALIFIED_MODULE)}</h4>
                </div>
                <div class="editViewBody container-fluid py-3 px-4">
                    <div class="editViewContents">
                        <div class="form-group row mb-3">
                            <label for="name" class="col-sm-3 control-label">
                                {vtranslate('LBL_WORKFLOW_NAME', $QUALIFIED_MODULE)}
                                <span class="redColor">*</span>
                            </label>
                            <div class="col-sm-5 controls">
                                <input class="form-control" id="name" name="workflowname" value="{$WORKFLOW_MODEL_OBJ->workflowname}" data-rule-required="true">
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="name" class="col-sm-3 control-label">
                                {vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}
                            </label>
                            <div class="col-sm-5 controls">
                                <textarea class="form-control" name="summary" id="summary">{$WORKFLOW_MODEL->get('summary')}</textarea>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="module_name" class="col-sm-3 control-label">
                                {vtranslate('LBL_TARGET_MODULE', $QUALIFIED_MODULE)}
                            </label>
                            <div class="col-sm-5 controls">
                                {if $MODE eq 'edit'}
                                    <div>
                                        <input type='text' disabled='disabled' class="inputElement form-control" value="{vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}">
                                        <input type='hidden' id="module_name" name="module_name" value="{$MODULE_MODEL->get('name')}">
                                    </div>
                                {else}
                                    <select class="select2 col-sm-6 pull-left" id="module_name" name="module_name" required="true" data-placeholder="Select Module..." style="text-align: left">
                                        {foreach from=$ALL_MODULES key=TABID item=MODULE_MODEL}
                                            {assign var=TARGET_MODULE_NAME value=$MODULE_MODEL->getName()}
                                            {assign var=SINGLE_MODULE value="SINGLE_$TARGET_MODULE_NAME"}
                                            <option value="{$MODULE_MODEL->getName()}" {if $SELECTED_MODULE == $MODULE_MODEL->getName()} selected {/if}
                                                    data-create-label="{vtranslate($SINGLE_MODULE, $TARGET_MODULE_NAME)} {vtranslate('LBL_CREATION', $QUALIFIED_MODULE)}"
                                                    data-update-label="{vtranslate($SINGLE_MODULE, $TARGET_MODULE_NAME)} {vtranslate('LBL_UPDATED', $QUALIFIED_MODULE)}"
                                            >
                                                {if $MODULE_MODEL->getName() eq 'Calendar'}
                                                    {vtranslate('LBL_TASK', $MODULE_MODEL->getName())}
                                                {else}
                                                    {vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}
                                                {/if}
                                            </option>
                                        {/foreach}
                                    </select>
                                {/if}
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="status" class="col-sm-3 control-label">
                                {vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}
                            </label>
                            <div class="col-sm-5 controls">
                                <div>
                                    <label>
                                       <input name="status" type="radio" value="active" {if $WORKFLOW_MODEL_OBJ->status eq '1'} checked="" {/if}>
                                       <span class="ms-2">{vtranslate('Active', $QUALIFIED_MODULE)}</span>
                                    </label>
                                </div>
                                <div>
                                    <label>
                                       <input name="status" type="radio" value="inActive" {if $WORKFLOW_MODEL_OBJ->status eq '0' or empty($WORKFLOW_MODEL_OBJ)} checked="" {/if}>
                                       <span class="ms-2">{vtranslate('InActive', $QUALIFIED_MODULE)}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="editViewHeader border-bottom">
                    <h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate('LBL_WORKFLOW_TRIGGER', $QUALIFIED_MODULE)}</h4>
                </div>
                <div class="editViewBody">
                    <div class="editViewContents">
                        {include file='WorkFlowTrigger.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
                    </div>
                </div>
                <div id="workflow_condition">
                </div>
                <div class="modal-overlay-footer modal-footer">
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
            </form>
        </div>
    </div>
{/strip}