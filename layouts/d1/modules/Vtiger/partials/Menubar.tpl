{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {if $MENU_STRUCTURE}
        {assign var=topMenus value=$MENU_STRUCTURE->getTop()}
        {assign var=moreMenus value=$MENU_STRUCTURE->getMore()}
        <div id="modules-menu" class="modules-menu d-flex flex-column">
            {assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
            {foreach key=moduleName item=moduleModel from=$SELECTED_CATEGORY_MENU_LIST}
                {assign var=translatedModuleLabel value=vtranslate($moduleModel->get('label'),$moduleName)}
                <div class="app-module-container rounded mx-1 mb-1 {if $MODULE eq $moduleName}bg-primary bg-opacity-10 active{else}opacity-50{/if}">
                    <a class="d-flex flex-column align-items-center justify-content-center p-2" title="{$translatedModuleLabel}" href="{$moduleModel->getDefaultUrl()}&app={$SELECTED_MENU_CATEGORY}">
                        {$moduleModel->getModuleIcon('1.5rem')}
                        <div class="pt-1 fs-07rem fw-bold w-100 overflow-hidden">{$translatedModuleLabel}</div>
                    </a>
                </div>
            {/foreach}
        </div>
    {/if}
{/strip}