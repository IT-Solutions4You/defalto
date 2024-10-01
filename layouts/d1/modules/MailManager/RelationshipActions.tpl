<input type="hidden" name="_mlinktotype" id="_mlinktotype" data-action="{$ACTION_TYPE}" value="" class="mLinkToType">
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
