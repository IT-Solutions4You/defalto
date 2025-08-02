{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{foreach from=$ACCESSIBLE_USER_LIST key=ACCESSIBLE_USER_ID item=ACCESSIBLE_USER_NAME}
    {assign var=ACCESSIBLE_USER value=Users_Record_Model::getInstanceById($ACCESSIBLE_USER_ID, 'Users')}
    {assign var=ACCESSIBLE_USER_IMAGE value=$ACCESSIBLE_USER->getImageUrl()}
    <div class="dropdown-item" data-change-assigned-user="user" data-id="{$ACCESSIBLE_USER->getId()}" data-name="{$ACCESSIBLE_USER_NAME}" data-image="{$ACCESSIBLE_USER_IMAGE}" data-role="{$ACCESSIBLE_USER->get('roleid')}">
        <div class="row flex-nowrap align-items-center">
            <div class="col-auto pe-0">
                <div class="h-2rem w-2rem rounded-circle"
                        {if $ACCESSIBLE_USER_IMAGE}
                            style="background: no-repeat #eee url({$ACCESSIBLE_USER_IMAGE}) center center / cover;"
                        {else}
                            style="background: no-repeat #eee url(layouts/d1/modules/Users/resources/user.svg) center center / 50%;"
                        {/if}
                ></div>
            </div>
            <div class="col overflow-hidden">
                <div class="text-truncate"><span class="fw-bold me-1">{$ACCESSIBLE_USER_NAME}</span><span class="text-secondary">({$ACCESSIBLE_USER->getDisplayValue('roleid')})</span></div>
                <div class="text-secondary text-truncate">{$ACCESSIBLE_USER->get('email1')}</div>
            </div>
        </div>
    </div>
{/foreach}
{foreach from=$ACCESSIBLE_GROUP_LIST key=ACCESSIBLE_GROUP_ID item=ACCESSIBLE_GROUP_NAME}
    {assign var=ACCESSIBLE_GROUP_IMAGE value=''}
    <div class="dropdown-item" data-change-assigned-user="group" data-id="{$ACCESSIBLE_GROUP_ID}" data-name="{$ACCESSIBLE_GROUP_NAME}" data-image="{$ACCESSIBLE_GROUP_IMAGE}" data-font="">
        <div class="row flex-nowrap align-items-center">
            <div class="col-auto pe-0">
                <div class="h-2rem w-2rem rounded-circle" style="background: no-repeat #eee url(layouts/d1/modules/Users/resources/users.svg) center center / 50%;"></div>
            </div>
            <div class="col overflow-hidden">
                <div class="fw-bold text-truncate">{$ACCESSIBLE_GROUP_NAME}</div>
                <div class="text-secondary text-truncate">{vtranslate('LBL_GROUP')}</div>
            </div>
        </div>
    </div>
{/foreach}