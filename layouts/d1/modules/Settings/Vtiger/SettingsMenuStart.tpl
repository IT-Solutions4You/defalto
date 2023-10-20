{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/Vtiger/views/Index.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{include file="modules/Vtiger/partials/Topbar.tpl"}

<div class="container-fluid app-nav">
    <div class="row">
        {include file="modules/Settings/Vtiger/SidebarHeader.tpl"}
        {include file="modules/Settings/Vtiger/ModuleHeader.tpl"}
    </div>
</div>
</nav>
<div id='overlayPageContent' class='fade modal overlayPageContent content-area' tabindex='-1' role='dialog' aria-hidden='true'>
    <div class="data">
    </div>
    <div class="modal-dialog">
    </div>
</div>
{if $FIELDS_INFO neq null}
    <script type="text/javascript">
        var uimeta = (function() {
            var fieldInfo  = {$FIELDS_INFO};
            return {
                field: {
                    get: function(name, property) {
                        if(name && property === undefined) {
                            return fieldInfo[name];
                        }
                        if(name && property) {
                            return fieldInfo[name][property]
                        }
                    },
                    isMandatory : function(name){
                        if(fieldInfo[name]) {
                            return fieldInfo[name].mandatory;
                        }
                        return false;
                    },
                    getType : function(name){
                        if(fieldInfo[name]) {
                            return fieldInfo[name].type
                        }
                        return false;
                    }
                },
            };
        })();
    </script>
{/if}
<div class="main-container container-fluid">
    <div class="row">
        <div class="col-lg-2 px-0 bg-white rounded-end rounded-bottom-0 mb-4 mb-lg-0">
            <div class="d-block d-lg-none p-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-target="#modnavigator" data-bs-toggle="collapse">
                    <i class="fa-solid fa-gears"></i>
                    <span class="ms-2">{vtranslate('LBL_SETTINGS_MENU', $QUALIFIED_MODULE)}</span>
                </button>
            </div>
            <div class="module-nav settingsNav collapse d-lg-block" id="modnavigator">
                {include file="modules/Settings/Vtiger/Sidebar.tpl"}
            </div>
        </div>
        <div class="col-lg px-0 h-main overflow-auto">
            {if php7_count($MODULE_BASIC_ACTIONS) or php7_count($LISTVIEW_LINKS['LISTVIEWBASIC']) or php7_count($MODULE_SETTING_ACTIONS) or php7_count($LISTVIEW_LINKS['LISTVIEWSETTING'])}
                <div class="px-4 pb-3">
                    <div class="bg-white rounded p-3">
                        {include file='ModuleLinks.tpl'|vtemplate_path:$QUALIFIED_MODULE}
                    </div>
                </div>
            {/if}
            <div class="settingsPageDiv">
