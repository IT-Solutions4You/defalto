{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="kanbanFieldValuesContainer container-fluid d-flex flex-column w-100 h-100 overflow-auto">
        <div class="kanbanFilterContainer row align-items-center py-1 bg-body rounded">
            <div class="col-lg-auto py-2">
                {if $NEW_RECORD_LINK}
                    <a href="{$NEW_RECORD_LINK}" class="btn btn-outline-secondary me-2" target="_blank">
                        <i class="fa fa-plus"></i>
                        <span class="ms-2">{vtranslate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}</span>
                    </a>
                {/if}
                {if $LIST_VIEW_LINK}
                    <a href="{$LIST_VIEW_LINK}&viewname={$CUSTOM_VIEW_ID}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-bars"></i>
                        <span class="ms-2">{vtranslate('LBL_LIST_VIEW', $QUALIFIED_MODULE)}</span>
                    </a>
                {/if}
            </div>
            <div class="col-lg-4 ms-auto py-2">
                <div class="input-group">
                    <div class="input-group-text text-secondary">
                        <i class="fa-solid fa-filter"></i>
                    </div>
                    <select name="custom_view" id="custom_view" class="select2" data-placeholder="{vtranslate('LBL_FILTER', $QUALIFIED_MODULE)}">
                        {foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
                            {if $GROUP_LABEL neq 'Default' && $GROUP_LABEL neq 'Mine' && $GROUP_LABEL neq 'Shared'}
                                {continue}
                            {/if}
                            {if php7_count($GROUP_CUSTOM_VIEWS) <=0}
                                {continue}
                            {/if}
                            <optgroup class="custom_view_group" id="{if $GROUP_LABEL eq 'Mine'}myList{else}sharedList{/if}" label="{if $GROUP_LABEL eq 'Mine'}{vtranslate('LBL_MY_LIST',$MODULE)}{elseif $GROUP_LABEL eq 'Shared'}{vtranslate('LBL_SHARED_LIST',$MODULE)}{else}{/if}">
                                {foreach item=CUSTOM_VIEW from=$GROUP_CUSTOM_VIEWS name=customView}
                                    {assign var=IS_DEFAULT value=$CUSTOM_VIEW->isDefault()}
                                    {assign var=VIEWNAME value=vtranslate($CUSTOM_VIEW->get('viewname'), $MODULE)}
                                    <option class="custom_view_filter" value="{$CUSTOM_VIEW->getId()}" {if $CUSTOM_VIEW_ID eq $CUSTOM_VIEW->getId()} selected="selected" {/if}>{$VIEWNAME|@escape:'html'}</option>
                                {/foreach}
                            </optgroup>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="col-lg-4 py-2">
                <div class="input-group">
                    <div class="input-group-text text-secondary">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <select name="assigned_user" id="assigned_user" class="select2" multiple="multiple" data-placeholder="{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}, {vtranslate('LBL_GROUPS', $QUALIFIED_MODULE)}">
                        <optgroup label="{vtranslate('LBL_USERS')}">
                            {foreach item=VALUE from=$ASSIGNED_USERS['users']}
                                <option value="{$VALUE}">{$VALUE}</option>
                            {/foreach}
                        </optgroup>
                        <optgroup label="{vtranslate('LBL_GROUPS')}">
                            {foreach item=VALUE from=$ASSIGNED_USERS['groups']}
                                <option value="{$VALUE}">{$VALUE}</option>
                            {/foreach}
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>
        <div class="kanbanFieldValuesScroll row flex-nowrap h-100 overflow-auto">
            {foreach from=$FIELD_VALUES item=FIELD_VALUE}
                {assign var=FIELD_VALUE_BG value=$FIELD_VALUES_COLOR[$FIELD_VALUE]}
                {assign var=FIELD_VALUE_RGB value=Core_Kanban_Model::getRGBFromHex($FIELD_VALUES_COLOR[$FIELD_VALUE])}
                {assign var=FIELD_VALUE_COLOR value=Settings_Picklist_Module_Model::getTextColor($FIELD_VALUE_BG)}
                <div class="kb-container col-xl-2 col-lg-2 col-sm-3 pt-3 ps-0">
                    <div class="h-100">
                        <div class="kb-droppable rounded d-flex flex-column w-100 h-100">
                            <div class="kb-header rounded p-2 bg-primary" {if $FIELD_VALUE_COLOR}style="--bs-bg-opacity: 0.5; --bs-primary-rgb: {$FIELD_VALUE_RGB}; color: {$FIELD_VALUE_COLOR};"{/if}>
                                <div class="fs-5 fw-bold">
                                    <span class="kb-value-label">{vtranslate($FIELD_VALUE, $QUALIFIED_MODULE)}</span>
                                    <span class="ms-2">(<span class="kb-value-count">{$RECORDS_COUNT[$FIELD_VALUE]}</span>)</span>
                                </div>
                                {if $RECORDS_HEADER[$FIELD_VALUE]}
                                    <div class="opacity-75">
                                        {foreach from=$RECORDS_HEADER[$FIELD_VALUE] key=HEADER_NAME item=HEADER_INFO}
                                            <div class="kb-value-header" data-name="{$HEADER_NAME}">{$HEADER_INFO['label']}: {$HEADER_INFO['display_value']}</div>
                                        {/foreach}
                                    </div>
                                {/if}
                            </div>
                            <div class="kb-content pt-3 overflow-y-auto h-100" data-picklist_value="{$FIELD_VALUE}" data-list_page="1">
                            </div>
                            <div class="kb-footer text-center">
                                <button type="button" class="kb-more-records btn w-100" {if $FIELD_VALUE_COLOR}style="background: rgba({$FIELD_VALUE_RGB}, 0.5); color: {$FIELD_VALUE_COLOR};"{/if}>
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
            <div class="kb-records hide">
                <textarea class="kb-records-info">{json_encode($RECORDS_INFO)}</textarea>
                <input class="kb-records-field" value="{$FIELD_NAME}">
                <input class="kb-records-id" value="{$KANBAN_ID}">
                <div class="kb-draggable border p-3 mb-3 bg-body rounded kb-record-clone">
                    <div class="kb-task" data-record_id="" data-picklist_field="" data-picklist_value="">
                        <div class="kb-title fw-bold fs-6 kb-record-value" data-name="_recordLabel"></div>
                        <div class="kb-headers text-secondary">
                            <div class="kb-header-clone kb-record-value"></div>
                        </div>
                        <div class="kb-links">
                            <div class="row pt-2">
                                <div class="col">
                                    <i class="kb-user-icon fa fa-user hide"></i>
                                    <i class="kb-group-icon fa fa-users hide"></i>
                                    <img class="kb-user-image rounded-circle hide" src="" width="24" height="24">
                                </div>
                                <div class="col-auto">
                                    <a href="#" class="kb-detail-link quickPreview text-secondary" title="{vtranslate('LBL_QUICK_VIEW', $QUALIFIED_MODULE)}">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <a href="#" target="_blank" class="kb-edit-link quickEdit text-secondary" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}