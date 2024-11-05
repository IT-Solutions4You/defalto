<input type="hidden" name="_mlinktotype" id="_mlinktotype" data-action="{$ACTION_TYPE}" value="" class="mLinkToType">
{if isset($ACTION_BUTTONS)}
    <div>
        {foreach item=ACTION_MODULE_NAME from=$ACTION_MODULES}
            <a href="#" data-change-module="{$ACTION_MODULE_NAME}" class="btn btn-primary me-2">
                <i class="fa-solid fa-plus"></i>
                <span class="ms-2">{vtranslate("LBL_MAILMANAGER_CREATE_$ACTION_MODULE_NAME", 'MailManager')}</span>
            </a>
        {/foreach}
    </div>
{else}
    <div class="dropdown">
        <div class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">{vtranslate('LBL_ACTIONS',$MODULE)}</div>
        <ul class="dropdown-menu">
            {foreach item=ACTION_MODULE_NAME from=$ACTION_MODULES}
                <li value="{$ACTION_MODULE_NAME}">
                    <a href="#" data-change-module="{$ACTION_MODULE_NAME}" class="dropdown-item">{vtranslate("LBL_MAILMANAGER_ADD_$ACTION_MODULE_NAME", 'MailManager')}</a>
                </li>
            {/foreach}
        </ul>
    </div>
{/if}
