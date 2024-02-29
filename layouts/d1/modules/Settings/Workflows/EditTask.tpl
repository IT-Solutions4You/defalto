{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="fc-overlay-modal">
        <form id="saveTask" method="post" action="index.php" class="modal-content">
            {assign var=HEADER_TITLE value=vtranslate('LBL_ADD_TASKS_FOR_WORKFLOW', $QUALIFIED_MODULE)}
            <div class="modal-header align-items-center">
                <div class="col-2">
                    <h5 class="modal-title m-0 text-truncate w-100">{$HEADER_TITLE}</h5>
                </div>
                <div class="col-6">
                    <input name="summary" class="inputElement form-control" data-rule-required="true" type="text" value="{if !$TASK_MODEL->isEmpty('summary')}{$TASK_MODEL->get('summary')}{else}{vtranslate($TASK_TYPE_MODEL->get('label'),$QUALIFIED_MODULE)}{/if}"/>
                </div>
                <button type="button" class="col-auto ms-auto btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body editTaskBody overflow-auto">
                <input type="hidden" name="module" value="{$MODULE}"/>
                <input type="hidden" name="parent" value="Settings"/>
                <input type="hidden" name="action" value="TaskAjax"/>
                <input type="hidden" name="mode" value="Save"/>
                <input type="hidden" name="for_workflow" value="{$WORKFLOW_ID}"/>
                <input type="hidden" name="task_id" value="{$TASK_ID}"/>
                <input type="hidden" name="taskType" id="taskType" value="{$TASK_TYPE_MODEL->get('tasktypename')}"/>
                <input type="hidden" name="tmpTaskId" value="{$TASK_MODEL->get('tmpTaskId')}"/>
                {if $TASK_MODEL->get('active') eq 'false'}<input type="hidden" name="active" value="false"/>{/if}
                <div id="scrollContainer">
                    <div class="tabbable">
                        {if $TASK_TYPE_MODEL->get('tasktypename') eq "VTEmailTask" && $TASK_OBJECT->trigger != null}
                            {if ($TASK_OBJECT->trigger!=null)}
                                {assign var=trigger value=$TASK_OBJECT->trigger}
                                {assign var=days value=$trigger['days']}
                                {if ($days < 0)}
                                    {assign var=days value=$days*-1}
                                    {assign var=direction value='before'}
                                {else}
                                    {assign var=direction value='after'}
                                {/if}
                            {/if}
                            <div class="row form-group">
                                <div class="col-sm-9 col-xs-9">
                                    <div class="row">
                                        <div class="col-sm-2 col-xs-2"> {vtranslate('LBL_DELAY_ACTION', $QUALIFIED_MODULE)} </div>
                                        <div class="col-sm-10 col-xs-10">
                                            <div class="row">
                                                <div class="col-sm-1 col-xs-1" style="margin-top: 7px;">
                                                    <input type="checkbox" class="alignTop" name="check_select_date" {if $trigger neq null}checked{/if}/>
                                                </div>
                                                <div class="col-sm-10 col-xs-10 {if $trigger neq null}show {else} hide {/if}" id="checkSelectDateContainer">
                                                    <div class="row">
                                                        <div class="col-sm-2 col-xs-2">
                                                            <div class="row">
                                                                <div class="col-sm-6 col-xs-6">
                                                                    <input class="inputElement form-control" type="text" name="select_date_days" value="{$days}" data-rule-WholeNumber=="true">
                                                                </div>
                                                                <div class="alignMiddle col-sm-5 col-xs-5">
                                                                    <span>{vtranslate('LBL_DAYS',$QUALIFIED_MODULE)}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3 col-xs-3">
                                                            <select class="select2 form-select" name="select_date_direction" style="width: 100px">
                                                                <option {if $direction eq 'after'} selected="" {/if} value="after">{vtranslate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
                                                                <option {if $direction eq 'before'} selected="" {/if} value="before">{vtranslate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-6 col-xs-6 marginLeftZero">
                                                            <select class="select2 form-select" name="select_date_field">
                                                                {foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
                                                                    <option {if $trigger['field'] eq $DATETIME_FIELD->get('name')} selected="" {/if} value="{$DATETIME_FIELD->get('name')}">{vtranslate($DATETIME_FIELD->get('label'), $DATETIME_FIELD->getModuleName())}</option>
                                                                {/foreach}
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/if}
                        <div class="taskTypeUi">
                            {include file=$TASK_TEMPLATE_PATH}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-6 text-end">
                            <a href="#" class="cancelLink btn btn-primary" type="reset" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary active">{vtranslate('LBL_SAVE', $MODULE)}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
{/strip}
