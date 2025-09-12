{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<form id="headerForm" method="POST">
    <input type="hidden" name="record" value="{$RECORD->getId()}">
    <input type="hidden" name="module" value="{$RECORD->getModuleName()}">
    <div class="row pt-3">
        {foreach item=FIELD_CONFIG from=$MODULE_MODEL->getHeaderFieldsConfig()}
            {assign var=FIELD_MODEL value=$FIELD_CONFIG['field']}
            {if $FIELD_MODEL}
                {assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
                {assign var=FIELD_MODEL value=$FIELD_MODEL->set('fieldvalue', $RECORD->get($FIELD_NAME))}
                {assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
                {assign var=DISPLAY_VALUE value=$FIELD_MODEL->getDisplayValue($FIELD_VALUE)}
                {assign var=IS_EDITABLE value=$FIELD_MODEL->isAjaxEditable() && $LIST_PREVIEW neq true && $IS_AJAX_ENABLED eq true && $REQUEST_INSTANCE->get('displayMode') neq 'overlay'}
            {else}
                {continue}
            {/if}
            {if 'field' eq $FIELD_CONFIG['type']}
                {assign var=FIELD_DATA_TYPE value=$FIELD_MODEL->getFieldDataType()}
                <div class="col-xl-2 col-lg-6 headerAjaxEdit td">
                    <div class="fieldLabel">
                        <div class="row text-secondary fieldName h-2rem">
                            <div class="col">{vtranslate($FIELD_MODEL->get('label'),$MODULE)}</div>
                        </div>
                        <div class="row position-relative h-2rem align-items-center">
                            <div class="col value fw-semibold fs-inherit bg-inherit h-100 word-break-all {$FIELD_NAME}" title="{vtranslate($FIELD_MODEL->get('label'),$MODULE)} : {strip_tags($DISPLAY_VALUE)}">
                                {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
                            </div>
                            {if $IS_EDITABLE}
                                <div class="edit col hide">
                                    {assign var=HEADER_FIELD_NAME value=$FIELD_MODEL->get('name')}
                                    {if $FIELD_DATA_TYPE eq 'multipicklist'}
                                        <input type="hidden" class="fieldBasicData" data-name="{$HEADER_FIELD_NAME}[]" data-type="{$FIELD_MODEL->getFieldDataType()}" data-displayvalue="{Vtiger_Util_Helper::toSafeHTML($DISPLAY_VALUE)}" data-value="{$FIELD_MODEL->get('fieldvalue')}" />
                                    {else}
                                        <input type="hidden" class="fieldBasicData" data-name="{$HEADER_FIELD_NAME}" data-type="{$FIELD_MODEL->getFieldDataType()}" data-displayvalue="{Vtiger_Util_Helper::toSafeHTML($DISPLAY_VALUE)}" data-value="{$FIELD_MODEL->get('fieldvalue')}" />
                                    {/if}
                                </div>
                                <div class="action col-auto p-0">
                                    <a href="#" onclick="return false;" class="editAction bg-body p-2">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            {elseif 'check' eq $FIELD_CONFIG['type']}
                <div class="col-xl-2 col-lg-6 {if $IS_EDITABLE}cursorPointer{else}cursorDefault{/if}">
                    <div class="fieldLabel">
                        <div class="row text-secondary fieldName h-2rem">
                            <div class="col">{vtranslate($FIELD_MODEL->get('label'),$MODULE)}</div>
                        </div>
                        <div class="row">
                            {if $IS_EDITABLE}
                                <div class="col">
                                    <input type="hidden" name="{$FIELD_NAME}" value="0">
                                    <div class="form-check form-switch d-flex align-items-center p-0">
                                        <input id="headerFieldSwitch{$FIELD_NAME}" class="form-check-input m-0 float-none" data-change-check-field="1" type="checkbox" name="{$FIELD_NAME}" {if !empty($FIELD_VALUE)}checked="checked"{/if} value="1">
                                    </div>
                                </div>
                            {else}
                                <div class="col value fw-semibold fs-inherit bg-inherit h-100 word-break-all {$FIELD_NAME}" title="{vtranslate($FIELD_MODEL->get('label'),$MODULE)} : {strip_tags($DISPLAY_VALUE)}">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            {elseif 'user' eq $FIELD_CONFIG['type']}
                {assign var=ASSIGNED_USER value=Users_Record_Model::getInstanceById($FIELD_VALUE, 'Users')}
                {assign var=ASSIGNED_USER_IMAGE value=$ASSIGNED_USER->getImageUrl()}
                <div class="col-xl-2 col-lg-6 {if $IS_EDITABLE}cursorPointer{else}cursorDefault{/if}">
                    <div class="row flex-nowrap align-items-center" data-bs-toggle="dropdown">
                        <div class="col-auto pe-0">
                            <div class="d-inline-block h-3rem w-3rem rounded-circle" data-assigned-user-image=""
                                {if $ASSIGNED_USER_IMAGE}
                                    style="background: no-repeat #eee url({$ASSIGNED_USER_IMAGE}) center center / cover;"
                                {elseif $ASSIGNED_USER->get('user_name')}
                                    style="background: no-repeat #eee url(layouts/d1/modules/Users/resources/user.svg) center center / 50%;"
                                {else}
                                    style="background: no-repeat #eee url(layouts/d1/modules/Users/resources/users.svg) center center / 50%;"
                                {/if}
                            ></div>
                        </div>
                        <div class="col pe-0 overflow-hidden">
                            <div class="text-secondary h-2rem">{vtranslate($FIELD_MODEL->get('label'),$MODULE)}</div>
                            <div class="fw-bold h-2rem text-truncate" data-assigned-user-name="">{$ASSIGNED_USER->getName()}</div>
                        </div>
                        {if $IS_EDITABLE}
                            <div class="col-auto ps-0">
                                <i class="fa-solid fa-caret-down"></i>
                            </div>
                        {/if}
                    </div>
                    {if $IS_EDITABLE}
                        <div class="row">
                            <div class="col-12 position-relative">
                                <div class="dropdown-menu w-100">
                                    <div class="assignedUsersSearchContainer px-2 pb-2">
                                        <input type="text" class="assignedUsersSearch form-control">
                                    </div>
                                    <div class="assignedUsersContainer overflow-auto h-25vh-max">
                                        {assign var=ACCESSIBLE_USER_LIST value=$USER_MODEL->searchAccessibleUsersForModule($MODULE)}
                                        {assign var=ACCESSIBLE_GROUP_LIST value=$USER_MODEL->searchAccessibleGroupForModule($MODULE)}
                                        {include file='HeaderAssignedUsers.tpl'|@vtemplate_path:$MODULE}
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                </div>
            {else}
                <div class="col-xl col-lg-6"></div>
            {/if}
        {/foreach}
    </div>
</form>
{/strip}