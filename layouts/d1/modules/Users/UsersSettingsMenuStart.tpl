{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{include file="modules/Vtiger/partials/Topbar.tpl"}
<div class="container-fluid app-nav">
    <div class="row">
        {include file="modules/Users/UsersSidebarHeader.tpl"}
        {include file="modules/Users/UsersModuleHeader.tpl"}
    </div>
</div>
</nav>
{if $FIELDS_INFO neq null}
    <script type="text/javascript">
        var users_settings_uimeta = (function() {
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
<div class="main-container clearfix">
    <div class="module-nav clearfix" id="modnavigator">
        <div class="hidden-xs hidden-sm">
            {include file="modules/Users/UsersSidebar.tpl"}
        </div>
    </div>
    <div class="usersSettingsPageDiv row">
        <div class="col-sm-12 col-xs-12 ">
