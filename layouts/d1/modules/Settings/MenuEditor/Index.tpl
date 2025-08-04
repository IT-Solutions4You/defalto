{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/MenuEditor/views/Index.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
<div class="listViewPageDiv detailViewContainer px-4 pb-4" id="listViewContent">
    <div class="rounded bg-body">
        <div class="p-3 border-bottom">
            <h4 class="m-0">{vtranslate('LBL_MENU_EDITOR', $QUALIFIED_MODULE_NAME)}</h4>
        </div>
        <div class="p-3">
            <div class="alert alert-info">
                <h5>
                    <i class="fa fa-info-circle me-2"></i>
                    <span>{vtranslate('LBL_INFO', $QUALIFIED_MODULE_NAME)}</span>
                </h5>
                <p>{vtranslate('LBL_MENU_EDITOR_INFO', $QUALIFIED_MODULE_NAME)}</p>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                {assign var=APP_LIST value=Vtiger_MenuStructure_Model::getAppMenuList()}
                {foreach item=APP_IMAGE key=APP_NAME from=$APP_IMAGE_MAP name=APP_MAP}
                    {if !in_array($APP_NAME, $APP_LIST)} {continue} {/if}
                    <div class="col-lg-3 {if $smarty.foreach.APP_MAP.index eq 0 or php7_count($APP_LIST) eq 1}{/if}">
                        <div class="mb-3 p-3 text-truncate fw-bold rounded bg-menu-icon menuEditorItem app-{$APP_NAME}" data-app-name="{$APP_NAME}">
                            <i class="text-primary fa {$APP_IMAGE}"></i>
                            {assign var=TRANSLATED_APP_NAME value={vtranslate("LBL_$APP_NAME")}}
                            <span class="ms-3" title="{$TRANSLATED_APP_NAME}">{$TRANSLATED_APP_NAME}</span>
                        </div>
                        <div class="sortable appContainer" data-appname="{$APP_NAME}">
                            {foreach key=moduleName item=moduleModel from=$APP_MAPPED_MODULES[$APP_NAME]}
                                <div class="modules noConnect" data-module="{$moduleName}">
                                    <div class="menuEditorItem menuEditorModuleItem btn btn-outline-secondary mb-3 w-100 d-flex align-items-center text-secondary">
                                        {assign var='translatedModuleLabel' value=vtranslate($moduleModel->get('label'),$moduleName )}
                                        <img class="alignMiddle cursorDrag" src="{vimage_path('drag.png')}"/>
                                        <span class="ms-3">{$moduleModel->getModuleIcon()}</span>
                                        <span class="ms-3 text-truncate" title="{$translatedModuleLabel}">{$translatedModuleLabel}</span>
                                        <span data-appname="{$APP_NAME}" class="menuEditorRemoveItem ms-auto">
                                            <i class="fa fa-times"></i>
                                        </span>
                                    </div>
                                </div>
                            {/foreach}
                            <div class="menuEditorItem menuEditorModuleItem menuEditorAddItem btn btn-outline-secondary w-100 mb-3" data-appname="{$APP_NAME}">
                                <div class="text-truncate">
                                    <i class="fa fa-plus"></i>
                                    <span class="ms-2">{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE_NAME)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>
