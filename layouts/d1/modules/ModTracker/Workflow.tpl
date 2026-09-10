{**
* This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
*
* (c) IT-Solutions4You s.r.o
*
* This file is licensed under the GNU AGPL v3 License.
* See LICENSE-AGPLv3.txt for more details.
*}
{if $RECENT_ACTIVITY->get('workflow_id')}
    {assign var=WORKFLOW_MODEL value=$RECENT_ACTIVITY->getWorkflow()}
    <span class="dropdown d-inline-block">
        <span class="text-primary cursorPointer p-0 border-0 text-primary fw-bold fs-5 text-start text-wrap align-baseline" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{vtranslate('LBL_WORKFLOW_DETAILS', 'ModTracker')|escape:'html'}">
            {if $WORKFLOW_MODEL && $WORKFLOW_MODEL->get('workflowname')}
                {$WORKFLOW_MODEL->get('workflowname')|escape:'html'}
            {else}
                {vtranslate('LBL_HISTORY_WORKFLOW', 'ModTracker')} {if Users_Record_Model::getCurrentUserModel()->isAdminUser()}#{$RECENT_ACTIVITY->get('workflow_id')|escape:'html'}{/if}
            {/if}
        </span>
        <span class="dropdown-menu p-3 shadow text-wrap text-break fw-normal" style="width: 24rem; max-width: calc(100vw - 2rem);">
            <span class="d-block mb-2"><strong>{vtranslate('LBL_HISTORY_USER', 'ModTracker')}:</strong> {$RECENT_ACTIVITY->getModifiedBy()->getDisplayName()|escape:'html'}</span>
            {if $WORKFLOW_MODEL}
                {assign var=WORKFLOW_TASK value=$RECENT_ACTIVITY->getWorkflowTask()}
                <span class="d-block mb-2"><strong>{vtranslate('LBL_HISTORY_WORKFLOW', 'ModTracker')}:</strong> #{$RECENT_ACTIVITY->get('workflow_id')|escape:'html'}</span>
                {if $WORKFLOW_MODEL->get('summary')}
                    <span class="d-block mb-2"><strong>{vtranslate('LBL_WORKFLOW_DESCRIPTION', 'ModTracker')}:</strong> {$WORKFLOW_MODEL->get('summary')|escape:'html'}</span>
                {/if}
                {if $WORKFLOW_TASK}
                    <span class="d-block mb-2"><strong>{vtranslate('LBL_HISTORY_TASK', 'ModTracker')}:</strong> {vtranslate($WORKFLOW_TASK->getTaskType()->getLabel(), 'Settings:Workflows')|escape:'html'} (#{$WORKFLOW_TASK->getId()|escape:'html'})</span>
                    <span class="d-block mb-2"><strong>{vtranslate('LBL_TASK_DESCRIPTION', 'ModTracker')}:</strong> {$WORKFLOW_TASK->getName()|escape:'html'}</span>
                {elseif $RECENT_ACTIVITY->get('task_id')}
                    <span class="d-block mb-2"><strong>{vtranslate('LBL_HISTORY_TASK', 'ModTracker')}:</strong> #{$RECENT_ACTIVITY->get('task_id')|escape:'html'} — {vtranslate('LBL_HISTORY_UNAVAILABLE', 'ModTracker')}</span>
                {/if}
                <span class="dropdown-divider my-2"></span>
                {if $WORKFLOW_TASK}
                    <a class="dropdown-item d-flex align-items-center gap-2 text-primary rounded px-2 py-2" href="{$WORKFLOW_MODEL->getEditViewUrl()|escape:'html'}&amp;mode=V7Edit&amp;history_task_id={$WORKFLOW_TASK->getId()|escape:'html'}">
                        <i class="fa fa-tasks fa-fw" aria-hidden="true"></i>
                        <span>{vtranslate('LBL_OPEN_TASK', 'ModTracker')}</span>
                    </a>
                {/if}
                <a class="dropdown-item d-flex align-items-center gap-2 text-primary rounded px-2 py-2" href="{$WORKFLOW_MODEL->getEditViewUrl()|escape:'html'}&amp;mode=V7Edit">
                    <i class="fa fa-cogs fa-fw" aria-hidden="true"></i>
                    <span>{vtranslate('LBL_OPEN_WORKFLOW', 'ModTracker')}</span>
                </a>
            {/if}
        </span>
    </span>
{/if}
