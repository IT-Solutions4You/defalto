{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{if $MODULE_BASIC_ACTIONS|@count gt 0}
    <div id="appnavcontent" class="d-flex p-3" aria-expanded="false">
        {foreach item=BASIC_ACTION from=$MODULE_BASIC_ACTIONS}
            {if $BASIC_ACTION->getLabel() eq 'LBL_ADD_RECORD'}
                <div class="dropdown">
                    <button type="button" class="btn module-buttons me-2 {$BASIC_ACTION->getStyleClass()}" data-bs-toggle="dropdown">
                        <span class="fa fa-plus" title="{vtranslate('LBL_NEW_DOCUMENT', $MODULE)}"></span>
                        <span class="mx-2">{vtranslate('LBL_NEW_DOCUMENT', $MODULE)}</span>
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li class="dropdown-header">
                            <i class="fa fa-upload"></i>
                            <span class="ms-2">{vtranslate('LBL_FILE_UPLOAD', $MODULE)}</span>
                        </li>
                        <li id="VtigerAction">
                            <a class="dropdown-item" href="javascript:Documents_Index_Js.uploadTo('Vtiger')">
                                <i class="fa fa-home"></i>
                                <span class="ms-2">{vtranslate('LBL_TO_SERVICE', $MODULE_NAME, {vtranslate('LBL_VTIGER', $MODULE_NAME)})}</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li class="dropdown-header">
                            <i class="fa fa-link"></i>
                            <span class="ms-2">{vtranslate('LBL_LINK_EXTERNAL_DOCUMENT', $MODULE)}</span>
                        </li>
                        <li id="shareDocument">
                            <a class="dropdown-item" href="javascript:Documents_Index_Js.createDocument('E')">
                                <i class="fa fa-external-link"></i>
                                <span class="ms-2">{vtranslate('LBL_FROM_SERVICE', $MODULE_NAME, {vtranslate('LBL_FILE_URL', $MODULE_NAME)})}</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li id="createDocument">
                            <a class="dropdown-item" href="javascript:Documents_Index_Js.createDocument('W')">
                                <i class="fa fa-file-text"></i>
                                <span class="ms-2">{vtranslate('LBL_CREATE_NEW', $MODULE_NAME, {vtranslate('SINGLE_Documents', $MODULE_NAME)})}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            {elseif $BASIC_ACTION->getLabel() == 'LBL_IMPORT'}
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
