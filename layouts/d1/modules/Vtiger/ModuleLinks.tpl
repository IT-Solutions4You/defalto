{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
<div id="appnavcontent" class="d-flex p-3" aria-expanded="false">
    {if php7_count($MODULE_BASIC_ACTIONS)}
        {foreach item=BASIC_ACTION from=$MODULE_BASIC_ACTIONS}
            {if $BASIC_ACTION->getLabel() == 'LBL_IMPORT'}
                <button id="{$MODULE}_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($BASIC_ACTION->getLabel())}" type="button" class="btn addButton module-buttons me-2 {$BASIC_ACTION->getStyleClass()}"
                        {if stripos($BASIC_ACTION->getUrl(), 'javascript:')===0}
                    onclick='{$BASIC_ACTION->getUrl()|substr:strlen("javascript:")};'
                        {else}
                    onclick="Vtiger_Import_Js.triggerImportAction('{$BASIC_ACTION->getUrl()}')"
                        {/if}>
                    <i class="fa {$BASIC_ACTION->getIcon()}" aria-hidden="true"></i>
                    <span class="ps-2">{vtranslate($BASIC_ACTION->getLabel(), $MODULE)}</span>
                </button>
            {else}
                <button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($BASIC_ACTION->getLabel())}" type="button" class="btn addButton module-buttons me-2 {$BASIC_ACTION->getStyleClass()}"
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
    {elseif php7_count($LISTVIEW_LINKS['LISTVIEWBASIC'])}
        {if empty($QUALIFIED_MODULE)}
            {assign var=QUALIFIED_MODULE value='Settings:'|cat:$MODULE}
        {/if}
        {foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
            {if $MODULE eq 'Users'} {assign var=LANGMODULE value=$MODULE} {/if}
            <button class="btn addButton module-buttons me-2 {$LISTVIEW_BASICACTION->getStyleClass()}"
                    id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}"
                    {if $MODULE eq 'Workflows'}
                onclick='Settings_Workflows_List_Js.triggerCreate("{$LISTVIEW_BASICACTION->getUrl()}&mode=V7Edit")'
                    {else}
                {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0}
                    onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'
                {else}
                    onclick='window.location.href = "{$LISTVIEW_BASICACTION->getUrl()}"'
                {/if}
                    {/if}>
                {if $MODULE eq 'Tags'}
                    <i class="fa fa-plus"></i>
                    <span class="ms-2">{vtranslate('LBL_ADD_TAG', $QUALIFIED_MODULE)}</span>
                {else}
                    {if $LISTVIEW_BASICACTION->getIcon()}
                        <i class="{$LISTVIEW_BASICACTION->getIcon()}"></i>
                    {/if}
                    <span class="ms-2">{vtranslate($LISTVIEW_BASICACTION->getLabel(), $QUALIFIED_MODULE)}</span>
                {/if}
            </button>
        {/foreach}
    {/if}
    {if php7_count($MODULE_SETTING_ACTIONS)}
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
    {elseif php7_count($LISTVIEW_LINKS['LISTVIEWSETTING'])}
        {if empty($QUALIFIED_MODULE)}
            {assign var=QUALIFIED_MODULE value=$MODULE_NAME}
        {/if}
        <div class="settingsIcon dropdown ms-auto">
            <button type="button" class="btn btn-outline-secondary module-buttons dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="{vtranslate('LBL_SETTINGS', $QUALIFIED_MODULE)}">
                <span class="fa fa-wrench" aria-hidden="true"></span>
            </button>
            <ul class="detailViewSetting dropdown-menu">
                {foreach item=SETTING from=$LISTVIEW_LINKS['LISTVIEWSETTING']}
                    <li id="{$MODULE}_setings_lisview_advancedAction_{$SETTING->getLabel()}">
                        <a class="dropdown-item" {if stripos($SETTING->getUrl(), 'javascript:') === 0}
                            onclick='{$SETTING->getUrl()|substr:strlen("javascript:")};'
                                {else}
                            href="{$SETTING->getUrl()}"
                                {/if}>
                            <span>{vtranslate($SETTING->getLabel(), $QUALIFIED_MODULE)}</span>
                        </a>
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}
</div>