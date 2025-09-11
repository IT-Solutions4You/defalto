{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=CUSTOM_VIEWS value=CustomView_Record_Model::getAllByGroup($MODULE)}
    {assign var=ACTIVE_TAG value=Vtiger_Tag_Model::getInstanceById($REQUEST_INSTANCE.tag)}
    {assign var=TAGS value=Vtiger_Tag_Model::getAllByCurrentUser($MODULE)}
    {assign var=ACTIVE_CUSTOM_VIEW value=CustomView_Record_Model::getActiveInstance($MODULE)}
    {if $ACTIVE_TAG}
        {assign var=ACTIVE_CV_LABEL value=$ACTIVE_TAG->get('tag')}
    {else}
        {assign var=ACTIVE_CV_LABEL value=$ACTIVE_CUSTOM_VIEW->get('viewname')}
    {/if}
    <div class="dropdown col">
        <div class="overflow-hidden w-25vw-max cursorPointer text-secondary" data-bs-toggle="dropdown" title="{$ACTIVE_CV_LABEL}">
            <div class="d-flex align-items-center">
                <span class="current-filter-name text-truncate filter-name fs-5 d-none d-sm-inline">{$ACTIVE_CV_LABEL}</span>
                <i class="fa-solid fa-filter ms-2"></i>
            </div>
        </div>
        <div class="dropdown-menu w-30rem position-absolute">
            <div class="dropdown-header">
                <input type="text" class="form-control" data-search="1">
            </div>
            <div class="overflow-auto h-25vh-max customViewsContainer">
                {include file="partials/CustomViewRecords.tpl"|vtemplate_path:$MODULE CUSTOM_VIEWS=$CUSTOM_VIEWS['Default']}
                <div class="dropdown-header">{vtranslate('LBL_MINE', $MODULE)}</div>
                {include file="partials/CustomViewRecords.tpl"|vtemplate_path:$MODULE CUSTOM_VIEWS=$CUSTOM_VIEWS['Mine']}
                <div class="dropdown-header">{vtranslate('LBL_SHARED_LIST', $MODULE)}</div>
                {include file="partials/CustomViewRecords.tpl"|vtemplate_path:$MODULE CUSTOM_VIEWS=$CUSTOM_VIEWS['Shared']}
                <div class="dropdown-header">{vtranslate('LBL_TAGS', $MODULE)}</div>
                {foreach from=$TAGS item=TAG}
                    <div class="dropdown-item ps-4 cursorPointer {if $REQUEST_INSTANCE.tag eq $TAG->getId()}text-primary bg-body-secondary fw-bold{/if}" data-search-element="1" data-search-value="{$TAG->get('tag')}" data-open-url="{$TAG->getListViewUrl()}">{$TAG->get('tag')}</div>
                {/foreach}
            </div>
            <div class="dropdown-divider"></div>
            <div>
                <a class="dropdown-item" href="#" data-cv-create-url="{$ACTIVE_CUSTOM_VIEW->getCreateUrl()}">
                    <i class="fa-solid fa-plus"></i>
                    <span class="ms-2">{vtranslate('LBL_CREATE_LIST', $MODULE)}</span>
                </a>
            </div>
        </div>
    </div>
{/strip}