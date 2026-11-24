{* Shared user/group selector. The caller supplies permission-filtered ASSIGNEE_OPTIONS. *}
<div class="core-assignee-select dropdown" data-current-user="{$ASSIGNEE_OPTIONS['current_user']|escape:'html'}" data-all-label="{vtranslate('LBL_ALL_USERS', 'Core')|escape:'html'}" data-users-label="{vtranslate('LBL_USERS', 'Core')|escape:'html'}">
    <select class="core-assignee-value d-none {$ASSIGNEE_CLASS|default:''|escape:'html'}" {if !isset($ASSIGNEE_MULTIPLE) || $ASSIGNEE_MULTIPLE}multiple{/if} {if !empty($ASSIGNEE_NAME)}name="{$ASSIGNEE_NAME|escape:'html'}"{/if} aria-label="{vtranslate('Assigned To', 'Core')|escape:'html'}">
        {foreach from=['users', 'groups'] item=OWNER_TYPE}
            <optgroup label="{if $OWNER_TYPE eq 'users'}{vtranslate('LBL_USERS', 'Core')}{else}{vtranslate('LBL_GROUPS', 'Core')}{/if}">
                {foreach from=$ASSIGNEE_OPTIONS[$OWNER_TYPE] item=OWNER}
                    <option value="{$OWNER['id']|escape:'html'}" data-image="{$OWNER['image']|escape:'html'}" {if isset($ASSIGNEE_VALUES) && in_array($OWNER['id'], $ASSIGNEE_VALUES)}selected{/if}>{$OWNER['label']|escape:'html'}</option>
                {/foreach}
            </optgroup>
        {/foreach}
    </select>
    <button type="button" class="core-assignee-toggle btn btn-outline-secondary d-flex align-items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="{vtranslate('Assigned To', 'Core')|escape:'html'}">
        <i class="fa fa-users" aria-hidden="true"></i><span class="core-assignee-summary text-truncate">{vtranslate('LBL_ALL_USERS', 'Core')}</span>
    </button>
    <div class="dropdown-menu core-assignee-menu p-0 shadow">
        <div class="d-flex align-items-center justify-content-between px-3 pt-2">
            <strong>{vtranslate('LBL_USERS', 'Core')}</strong><button type="button" class="btn-close" data-assignee-action="close" aria-label="{vtranslate('LBL_CLOSE', 'Core')|escape:'html'}"></button>
        </div>
        <div class="p-2"><input type="search" class="core-assignee-search form-control" placeholder="{vtranslate('LBL_SEARCH', 'Core')|escape:'html'}" aria-label="{vtranslate('LBL_SEARCH', 'Core')|escape:'html'}" /></div>
        <div class="d-flex gap-2 px-2 pb-2 border-bottom">
            <button type="button" class="btn btn-light border flex-fill" data-assignee-action="all">{vtranslate('LBL_ALL', 'Core')}</button>
            <button type="button" class="btn btn-light border flex-fill" data-assignee-action="mine">{vtranslate('LBL_MY', 'Core')}</button>
        </div>
        <div class="core-assignee-options overflow-auto"></div>
        <div class="d-flex justify-content-between p-2 border-top">
            <button type="button" class="btn btn-link btn-sm" data-assignee-action="clear">{vtranslate('LBL_CLEAR', 'Core')}</button>
            <button type="button" class="btn btn-primary btn-sm" data-assignee-action="apply">{vtranslate('LBL_APPLY', 'Core')}</button>
        </div>
    </div>
</div>
