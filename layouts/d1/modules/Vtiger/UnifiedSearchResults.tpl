{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Vtiger/views/BasicAjax.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div id="searchResults-container" class="advancedFilterSearchResults">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg">
                <h4 class="m-0 fw-bold">{vtranslate('LBL_SEARCH_RESULTS', $MODULE)}</h4>
            </div>
            <div class="col-lg-auto">
                <a class="btn btn-light module-buttons" href="javascript:void(0);" id="showFilter">{vtranslate('LBL_SAVE_MODIFY_FILTER',$MODULE)}</a>
            </div>
        </div>
        <div class="row moduleResults-container">
            {include file="UnifiedSearchResultsContents.tpl"|vtemplate_path:$MODULE}
        </div>
    </div>
    {if $ADV_SEARCH_FIELDS_INFO neq null}
        <script type="text/javascript">
            var adv_search_uimeta = (function() {
                var fieldInfo = {$ADV_SEARCH_FIELDS_INFO};
                return {
                    field: {
                        get: function(name, property) {
                            if (name && property === undefined) {
                                return fieldInfo[name];
                            }
                            if (name && property) {
                                return fieldInfo[name][property]
                            }
                        },
                        isMandatory: function(name) {
                            if (fieldInfo[name]) {
                                return fieldInfo[name].mandatory;
                            }
                            return false;
                        },
                        getType: function(name) {
                            if (fieldInfo[name]) {
                                return fieldInfo[name].type;
                            }
                            return false;
                        }
                    },
                };
            })();
        </script>
{/if}
</div>

