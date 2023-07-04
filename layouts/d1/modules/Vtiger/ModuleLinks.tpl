{if $MODULE_BASIC_ACTIONS|@count gt 0}
    <div id="appnavcontent" class="d-flex" aria-expanded="false">
        {foreach item=BASIC_ACTION from=$MODULE_BASIC_ACTIONS}
            {if $BASIC_ACTION->getLabel() == 'LBL_IMPORT'}
                <button id="{$MODULE}_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($BASIC_ACTION->getLabel())}" type="button" class="btn btn-outline-secondary addButton module-buttons me-2"
                        {if stripos($BASIC_ACTION->getUrl(), 'javascript:')===0}
                    onclick='{$BASIC_ACTION->getUrl()|substr:strlen("javascript:")};'
                        {else}
                    onclick="Vtiger_Import_Js.triggerImportAction('{$BASIC_ACTION->getUrl()}')"
                        {/if}>
                    <i class="fa {$BASIC_ACTION->getIcon()}" aria-hidden="true"></i>
                    <span class="ps-2">{vtranslate($BASIC_ACTION->getLabel(), $MODULE)}</span>
                </button>
            {else}
                <button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($BASIC_ACTION->getLabel())}" type="button" class="btn btn-outline-secondary addButton module-buttons  me-2"
                        {if stripos($BASIC_ACTION->getUrl(), 'javascript:')===0}
                    onclick='{$BASIC_ACTION->getUrl()|substr:strlen("javascript:")};'
                        {else}
                    onclick='window.location.href = "{$BASIC_ACTION->getUrl()}&app={$SELECTED_MENU_CATEGORY}"'
                        {/if}>
                    <i class="fa {$BASIC_ACTION->getIcon()}" aria-hidden="true"></i>
                    <span class="ps-2">{vtranslate($BASIC_ACTION->getLabel(), $MODULE)}</span>
                </button>
            {/if}
        {/foreach}
        {if $MODULE_SETTING_ACTIONS|@count gt 0}
            <div class="dropdown settingsIcon ms-auto">
                <button type="button" class="btn btn-outline-secondary module-buttons dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="{vtranslate('LBL_SETTINGS', $MODULE)}">
                    <span class="fa fa-wrench" aria-hidden="true"></span>
                    <span class="px-2">{vtranslate('LBL_MORE', $MODULE)}</span>
                </button>
                <ul class="detailViewSetting dropdown-menu dropdown-menu-end">
                    {foreach item=SETTING from=$MODULE_SETTING_ACTIONS}
                        <li id="{$MODULE_NAME}_listview_advancedAction_{$SETTING->getLabel()}" class="dropdown-item">
                            <a href={$SETTING->getUrl()}>{vtranslate($SETTING->getLabel(), $MODULE_NAME ,vtranslate($MODULE_NAME, $MODULE_NAME))}</a>
                        </li>
                    {/foreach}
                </ul>
            </div>
        {/if}
    </div>
{/if}
