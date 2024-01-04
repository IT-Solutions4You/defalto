{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <input type="hidden" name="conditions" id="advanced_filter" value='' />
    <input type="hidden" id="olderConditions" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($WORKFLOW_MODEL->get('conditions')))}' />
    <input type="hidden" name="filtersavedinnew" value="{$WORKFLOW_MODEL->get('filtersavedinnew')}" />
    <div class="editViewHeader border-bottom">
        <h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate('LBL_WORKFLOW_CONDITION', $QUALIFIED_MODULE)}</h4>
    </div>
    <div class="editViewBody">
       <div class="editViewContents">
          <div class="form-group">
             <div class="py-3 px-4">
                 {if $IS_FILTER_SAVED_NEW == false}
					<div class="alert alert-info">
						{vtranslate('LBL_CREATED_IN_OLD_LOOK_CANNOT_BE_EDITED',$QUALIFIED_MODULE)}
					</div>
					<div class="row">
						<div class="col-sm-6">
                            <input type="radio" name="conditionstype" checked=""/>
                            <span class="ms-2">{vtranslate('LBL_USE_EXISTING_CONDITIONS',$QUALIFIED_MODULE)}</span>
                        </div>
						<div class="col-sm-6">
                            <input type="radio" id="enableAdvanceFilters" name="conditionstype" class="recreate"/>
                            <span class="ms-2">{vtranslate('LBL_RECREATE_CONDITIONS',$QUALIFIED_MODULE)}</span>
                        </div>
					</div>
				{/if}
                 <div id="advanceFilterContainer"  class="conditionsContainer {if $IS_FILTER_SAVED_NEW == false} zeroOpacity opacity-0 {/if}">
                     <div class="col-sm-12">
                         <div class="table table-bordered">
                             {include file='AdvanceFilter.tpl'|@vtemplate_path:$QUALIFIED_MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE}
                         </div>
                     </div>
                     {include file="FieldExpressions.tpl"|@vtemplate_path:$QUALIFIED_MODULE EXECUTION_CONDITION=$WORKFLOW_MODEL->get('execution_condition')}
                 </div>
             </div>
          </div>
       </div>
    </div>        
    <div class="editViewHeader border-bottom">
      <h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate('LBL_WORKFLOW_ACTIONS', $QUALIFIED_MODULE)}</h4>
    </div>
    <div class="editViewBody pb-3" id="workflow_action">
        <div class="p-3">
            <div class="btn-group dropdown">
               <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" type="button" aria-expanded="true">
                  <strong>{vtranslate('LBL_ADD_TASK',$QUALIFIED_MODULE)}</strong>
               </button>
               <ul class="dropdown-menu" role="menu">
                    {foreach from=$TASK_TYPES item=TASK_TYPE}
                        <li>
                            <a class="dropdown-item" data-url="index.php{$TASK_TYPE->getV7EditViewUrl()}&for_workflow={$RECORD}">{vtranslate($TASK_TYPE->get('label'),$QUALIFIED_MODULE)}</a>
                        </li>
                    {/foreach}
               </ul>
            </div>
        </div>
        <div id="taskListContainer">
           {include file='TasksList.tpl'|@vtemplate_path:$QUALIFIED_MODULE}	
        </div>
    </div>
{/strip}
