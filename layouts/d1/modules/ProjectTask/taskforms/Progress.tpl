{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{strip}
<details class="border-bottom mb-4 pb-3">
    <summary class="fw-semibold">{vtranslate('LBL_PROJECT_PROGRESS_CALCULATION_TITLE', 'ProjectTask')|escape:'html'}</summary>
    <div class="mt-3">{Core_Utils_Helper::markdownToHtml(vtranslate('LBL_PROJECT_PROGRESS_CALCULATION', 'ProjectTask'))}</div>
</details>
{assign var=STATUS_OPTIONS value=$TASK_OBJECT->getStatusOptions()}
{foreach from=$TASK_OBJECT->getSettingFields() key=NAME item=SETTING}
    {assign var=HELP_LABEL value=$SETTING.label|cat:'_HELP'}
    <div class="row mb-3">
        <div class="col-sm-5">
            <label class="col-form-label pb-0" for="progress_{$NAME}">{vtranslate($SETTING.label, 'ProjectTask')|escape:'html'}</label>
            <div class="form-text mt-1" id="progress_{$NAME}_help">{vtranslate($HELP_LABEL, 'ProjectTask')|escape:'html'}</div>
        </div>
        <div class="col-sm-7">
            {if $SETTING.type eq 'number'}
                <input class="form-control" id="progress_{$NAME}" name="{$NAME}" type="number" min="0.1" step="0.1" required value="{$TASK_OBJECT->$NAME|escape:'html'}" aria-describedby="progress_{$NAME}_help">
            {else}
                <select class="select2 form-select" id="progress_{$NAME}" name="{$NAME}[]" multiple aria-describedby="progress_{$NAME}_help">
                    {foreach from=$STATUS_OPTIONS item=OPTION}
                        <option value="{$OPTION.value|escape:'html'}" {if in_array($OPTION.value, (array)$TASK_OBJECT->$NAME)}selected{/if}>{vtranslate($OPTION.value, 'ProjectTask')|escape:'html'}</option>
                    {/foreach}
                </select>
            {/if}
        </div>
    </div>
{/foreach}
{/strip}
