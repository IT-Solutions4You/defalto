{strip}
{assign var=SAVED_FILTER_COUNT value=0}
{if $CURRENT_CV_MODEL}
    {assign var=SAVED_FILTER_COUNT value=$CURRENT_CV_MODEL->getSavedConditionCount()}
{/if}
<div class="listFilterBar d-flex flex-wrap align-items-center gap-2 fw-normal" data-saved-count="{$SAVED_FILTER_COUNT}" aria-label="{vtranslate('LBL_RECORD_FILTERS', 'Core')}">
    <input type="hidden" class="listFilterState" value="{Vtiger_Util_Helper::toSafeHTML(Zend_JSON::encode($LIST_FILTER_PARAMS))}">
    <input type="hidden" class="listFilterFields" value="{Vtiger_Util_Helper::toSafeHTML(Zend_JSON::encode($LIST_FILTER_FIELDS))}">
    <input type="hidden" class="listFilterDateFormat" value="{$CURRENT_USER_MODEL->get('date_format')|escape}">
    <input type="hidden" class="listFilterHourFormat" value="{$CURRENT_USER_MODEL->get('hour_format')|escape}">
    <div class="dropdown">
        <button type="button" class="btn btn-sm text-nowrap text-secondary listFilterAdd" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-config='{literal}{"popperConfig":{"strategy":"fixed"}}{/literal}' aria-expanded="false" title="{vtranslate('LBL_RECORD_FILTERS', 'Core')|escape}">
            <i class="fa-solid fa-filter" aria-hidden="true"></i>
            <span class="visually-hidden">{vtranslate('LBL_RECORD_FILTERS', 'Core')}</span>
            <span class="listFilterCount badge bg-primary ms-1 d-none"></span>
        </button>
        <div class="dropdown-menu p-3 shadow listFilterEditor">
            <h6 class="mb-3">{vtranslate('LBL_RECORD_FILTERS', 'Core')}</h6>
            {if $SAVED_FILTER_COUNT}
                <div class="alert alert-info mb-3">
                    <div class="fw-semibold mb-1">{$CURRENT_CV_MODEL->getDisplayName()|escape} <span class="badge bg-info text-dark">{$SAVED_FILTER_COUNT}</span></div>
                    <div>{vtranslate('LBL_SAVED_LIST_FILTER_NOTICE', 'Core')}</div>
                    {if $CURRENT_CV_MODEL->isCvEditable()}
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2 listFilterSavedEdit" data-cv-edit-url="{$CURRENT_CV_MODEL->getEditUrl()|escape}">{vtranslate('LBL_EDIT_SAVED_LIST_FILTER', 'Core')}</button>
                    {/if}
                </div>
            {/if}
            <div class="listFilterApplied d-none border-bottom pb-3 mb-3">
                <div class="listFilterChips d-flex flex-column gap-2 mb-2" aria-live="polite"></div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-link listFilterClear d-none">{if $SAVED_FILTER_COUNT}{vtranslate('LBL_CLEAR_ADDITIONAL_FILTERS', 'Core')}{else}{vtranslate('LBL_CLEAR_RECORD_FILTERS', 'Core')}{/if}</button>
                    {if $CURRENT_CV_MODEL}
                        <button type="button" class="btn btn-sm btn-outline-secondary listFilterSave d-none">{vtranslate('LBL_SAVE_RECORD_FILTER', 'Core')}</button>
                    {/if}
                </div>
            </div>
            <div>
                <div class="mb-3">
                    <label for="listFilterField" class="form-label">{vtranslate('LBL_FIELD', 'Core')}</label>
                    <select id="listFilterField" class="form-select listFilterField" form="listFilterForm" required>
                        <option value="">{vtranslate('LBL_SELECT_FIELD')}</option>
                        {foreach item=FILTER_BLOCK from=$LIST_FILTER_FIELD_GROUPS}
                            <optgroup label="{$FILTER_BLOCK.label|escape}">
                                {foreach key=FILTER_FIELD_NAME item=FILTER_FIELD from=$FILTER_BLOCK.fields}
                                    <option value="{$FILTER_FIELD_NAME|escape}">{$FILTER_FIELD.label|escape}</option>
                                {/foreach}
                            </optgroup>
                        {/foreach}
                    </select>
                </div>
                <div class="mb-3 listFilterOperatorContainer d-none">
                    <label for="listFilterOperator" class="form-label">{vtranslate('LBL_CONDITION', 'Core')}</label>
                    <select id="listFilterOperator" class="form-select listFilterOperator" form="listFilterForm" required disabled></select>
                </div>
                <div class="listFilterValueContainer mb-3"></div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary listFilterCancel">{vtranslate('LBL_CANCEL')}</button>
                    <button type="button" class="btn btn-primary listFilterApply" form="listFilterForm">{vtranslate('LBL_APPLY_RECORD_FILTER', 'Core')}</button>
                </div>
            </div>
        </div>
    </div>
    <span class="listFilterLabels d-none" data-value="{vtranslate('LBL_VALUE', 'Core')|escape}" data-remove="{vtranslate('LBL_REMOVE')|escape}" data-edit="{vtranslate('LBL_EDIT')|escape}" data-or="{vtranslate('LBL_OR')|escape}" data-and="{vtranslate('LBL_AND')|escape}" data-error="{vtranslate('LBL_FILTER_UPDATE_FAILED', 'Core')|escape}" data-search="{vtranslate('LBL_SEARCH')|escape}" data-success="{vtranslate('LBL_FILTER_UPDATED', 'Core')|escape}"></span>
</div>
{/strip}
