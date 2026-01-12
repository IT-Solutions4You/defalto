{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var="CUSTOM_VIEW_NAMES" value=array()}
    {if $CUSTOM_VIEWS && php7_count($CUSTOM_VIEWS) > 0}
        <div class="container-fluid container-custom-views">
            <div class="row">
                <div class="col px-0">
                    <input type="hidden" name="allCvId" value="{CustomView_Record_Model::getAllFilterByModule($MODULE)->get('cvid')}"/>
                    <select name="custom_views" id="custom_views" class="select2" data-select-on-close="false" data-close-on-select="true">
                        {assign var="IS_ADMIN" value=$CURRENT_USER_MODEL->isAdminUser()} <!-- Libertus Mod -->
                        {foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
                            {if $GROUP_LABEL neq 'Default' && $GROUP_LABEL neq 'Mine' && $GROUP_LABEL neq 'Shared'}
                                {continue}
                            {/if}
                            {if php7_count($GROUP_CUSTOM_VIEWS) <=0}
                                {continue}
                            {/if}
                            <optgroup class="custom_view_group" id="{if $GROUP_LABEL eq 'Mine'}myList{else}sharedList{/if}" label="{if $GROUP_LABEL eq 'Mine'}{vtranslate('LBL_MY_LIST',$MODULE)}{elseif $GROUP_LABEL eq 'Shared'}{vtranslate('LBL_SHARED_LIST',$MODULE)}{else}{/if}">
                                {assign var=count value=0}
                                {assign var=LISTVIEW_URL value=$MODULE_MODEL->getListViewUrl()}
                                {foreach item=CUSTOM_VIEW from=$GROUP_CUSTOM_VIEWS name=customView}
                                    {assign var=IS_DEFAULT value=$CUSTOM_VIEW->isDefault()}
                                    {assign var=CUSTOME_VIEW_RECORD_MODEL value=CustomView_Record_Model::getInstanceById($CUSTOM_VIEW->getId())}
                                    {assign var=MEMBERS value=$CUSTOME_VIEW_RECORD_MODEL->getMembers()}
                                    {assign var=LIST_STATUS value=$CUSTOME_VIEW_RECORD_MODEL->get('status')}
                                    {assign var=SHARED_MEMBER_COUNT value=0}
                                    {foreach key=GROUP_LABEL item=MEMBER_LIST from=$MEMBERS}
                                        {if $MEMBER_LIST|@count gt 0}
                                            {assign var=SHARED_MEMBER_COUNT value=1}
                                        {/if}
                                    {/foreach}
                                    {assign var=VIEWNAME value={vtranslate($CUSTOM_VIEW->get('viewname'), $MODULE)}}
                                    {append var=CUSTOM_VIEW_NAMES value=$VIEWNAME}
                                    <option class="custom_view_filter" value="{$CUSTOM_VIEW->getId()}"
                                            {if $VIEWID eq $CUSTOM_VIEW->getId() && ($CURRENT_TAG eq '')} selected="selected" {/if}
                                            {if ($CUSTOM_VIEW->isMine() || $IS_ADMIN) && $CUSTOM_VIEW->get('viewname') neq 'All'}
                                                data-deletable="{if $CUSTOM_VIEW->isDeletable()}true{else}false{/if}"
                                                data-editable="{if $CUSTOM_VIEW->isEditable()}true{else}false{/if}"
                                                {if $CUSTOM_VIEW->isEditable()}
                                                    data-editurl="{$CUSTOM_VIEW->getEditUrl()}"
                                                {/if}
                                                {if $CUSTOM_VIEW->isDeletable()}
                                                    {if $SHARED_MEMBER_COUNT eq 1 or $LIST_STATUS eq 3} data-shared="1"{/if}
                                                    data-deleteurl="{$CUSTOM_VIEW->getDeleteUrl()}"
                                                {/if}
                                            {/if}
                                            toggleClass="fa {if $IS_DEFAULT}fa-check-square-o{else}fa-square-o{/if}"
                                            data-filter-name="{$VIEWNAME|@escape:'html'}"
                                            data-filter-id="{$CUSTOM_VIEW->getId()}"
                                            data-is-default="{$IS_DEFAULT}"
                                            data-defaulttoggle="{$CUSTOM_VIEW->getToggleDefaultUrl()}"
                                            data-default="{$CUSTOM_VIEW->getDuplicateUrl()}"
                                            data-isMine="{if $CUSTOM_VIEW->isMine()}true{else}false{/if}"
                                            data-isadmin="{if $IS_ADMIN}true{else}false{/if}"
                                    >{$VIEWNAME|@escape:'html'}</option>
                                {/foreach}
                            </optgroup>
                        {/foreach}
                    </select>
                </div>
                <div class="col-auto ps-1 pe-0">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-ellipsis"></i>
                        </button>
                        <ul class="customViewsActions dropdown-menu dropdown-menu-end">
                            <li class="createFilter" data-url="{CustomView_Record_Model::getCreateViewUrl($MODULE)}">
                                <a class="dropdown-item">
                                    <i class="fa fa-plus"></i>
                                    <span class="ps-2">{vtranslate('LBL_CREATE_LIST',$MODULE)}</span>
                                </a>
                            </li>
                            <li>
                                <h6 class="dropdown-header">
                                    <span class="current-filter-name pe-1"></span>
                                    <span>{vtranslate('LBL_ACTIONS', $MODULE)}</span>
                                </h6>
                            </li>
                            <li class="editFilter">
                                <a class="dropdown-item">
                                    <i class="fa fa-pencil"></i>
                                    <span class="ps-2">{vtranslate('LBL_EDIT',$MODULE)}</span>
                                </a>
                            </li>
                            <li class="deleteFilter">
                                <a class="dropdown-item">
                                    <i class="fa fa-trash"></i>
                                    <span class="ps-2">{vtranslate('LBL_DELETE',$MODULE)}</span>
                                </a>
                            </li>
                            <li class="duplicateFilter">
                                <a class="dropdown-item">
                                    <i class="fa fa-files-o"></i>
                                    <span class="ps-2">{vtranslate('LBL_DUPLICATE',$MODULE)}</span>
                                </a>
                            </li>
                            <li class="toggleDefault">
                                <a class="dropdown-item">
                                    <i class="fa-regular fa-square" data-check-icon="fa-regular fa-square-check" data-uncheck-icon="fa-regular fa-square"></i>
                                    <span class="ps-2">{vtranslate('LBL_DEFAULT',$MODULE)}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    {/if}
{/strip}