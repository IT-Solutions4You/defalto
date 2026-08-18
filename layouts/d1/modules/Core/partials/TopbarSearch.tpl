{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div id="search-links-container" class="search-links-container collapse navbar navbar-expand col-lg-auto d-lg-block bg-body-secondary px-3 p-lg-0 h-sub-header">
        <div class="d-flex align-items-center h-100 w-100">
            <div class="search-link input-group input-group border border-secondary rounded">
                <label for="search-keyword-input" class="d-inline-block input-group-text bg-body-secondary text-secondary border-0">
                    <i class="fa fa-search"></i>
                </label>
                <input id="search-keyword-input" class="keyword-input bg-body-secondary form-control border-0" type="text" placeholder="{vtranslate('LBL_TYPE_SEARCH')}" value="{$GLOBAL_SEARCH_VALUE}" data-min-length="2" data-min-length-message="{vtranslate('LBL_GLOBAL_SEARCH_MINIMUM_LENGTH')}">
                <input id="global-search-module" type="hidden" value="">
                <div class="global-search-module-picker dropdown" data-module-label="{vtranslate('LBL_MODULE')|escape:'html'}">
                    <button type="button" class="global-search-module-button btn bg-body-secondary text-secondary border-0 h-100 d-flex align-items-center justify-content-center" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{vtranslate('LBL_MODULE')}: {vtranslate('LBL_ALL')}" title="{vtranslate('LBL_ALL')}">
                        <span class="global-search-selected-icon d-inline-flex align-items-center justify-content-center"><i class="fa fa-chevron-down"></i></span>
                        <span class="global-search-hover-icon d-inline-flex align-items-center justify-content-center" aria-hidden="true"><i class="fa fa-chevron-down"></i></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button type="button" class="global-search-filter-open dropdown-item d-flex align-items-center gap-2">
                                <i class="fa fa-filter"></i>
                                <span>{vtranslate('LBL_GLOBAL_SEARCH_FILTER')}</span>
                            </button>
                        </li>
                        {if $USER_MODEL->isAdminUser()}
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="index.php?module=GlobalSearch&parent=Settings&view=List">
                                    <i class="fa fa-cog"></i>
                                    <span>{vtranslate('LBL_SETTINGS', 'Core')}</span>
                                </a>
                            </li>
                        {/if}
                        <li><hr class="dropdown-divider"></li>
                        <li class="px-2 pb-2">
                            <label for="global-search-module-search" class="visually-hidden">{vtranslate('LBL_GLOBAL_SEARCH_MODULE_SEARCH')}</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-secondary border-end-0"><i class="fa fa-search"></i></span>
                                <input id="global-search-module-search" class="global-search-module-search form-control border-start-0" type="search" autocomplete="off" placeholder="{vtranslate('LBL_GLOBAL_SEARCH_MODULE_SEARCH')}">
                            </div>
                        </li>
                        <li>
                            <button type="button" class="global-search-module-option dropdown-item active d-flex align-items-center gap-2" data-module="" data-label="{vtranslate('LBL_ALL')|escape:'html'}" aria-current="true">
                                <span class="global-search-module-option-icon d-inline-flex align-items-center justify-content-center"><i class="fa fa-th-large"></i></span>
                                <span>{vtranslate('LBL_ALL')}</span>
                            </button>
                        </li>
                        {if isset($GLOBAL_SEARCH_MODULES)}
                            {assign var=TOPBAR_SEARCH_MODULES value=$GLOBAL_SEARCH_MODULES}
                        {else}
                            {assign var=TOPBAR_SEARCH_MODULES value=$SEARCHABLE_MODULES}
                        {/if}
                        {foreach from=$TOPBAR_SEARCH_MODULES key=SEARCH_MODULE_NAME item=SEARCH_MODULE_MODEL}
                            {assign var=SEARCH_MODULE_LABEL value=vtranslate($SEARCH_MODULE_NAME, $SEARCH_MODULE_NAME)}
                            {assign var=SEARCH_MODULE_OFFICIAL_LABEL value=$SEARCH_MODULE_MODEL->get('label')}
                            {assign var=SEARCH_MODULE_TRANSLATED_OFFICIAL_LABEL value=vtranslate($SEARCH_MODULE_OFFICIAL_LABEL, $SEARCH_MODULE_NAME)}
                            <li>
                                <button type="button" class="global-search-module-option dropdown-item d-flex align-items-center gap-2" data-module="{$SEARCH_MODULE_NAME}" data-label="{$SEARCH_MODULE_LABEL|escape:'html'}" data-search-values="{$SEARCH_MODULE_NAME|escape:'html'} {$SEARCH_MODULE_OFFICIAL_LABEL|escape:'html'} {$SEARCH_MODULE_LABEL|escape:'html'} {$SEARCH_MODULE_TRANSLATED_OFFICIAL_LABEL|escape:'html'}">
                                    <span class="global-search-module-option-icon d-inline-flex align-items-center justify-content-center">{$SEARCH_MODULE_MODEL->getModuleIcon('1rem')}</span>
                                    <span>{$SEARCH_MODULE_LABEL}</span>
                                </button>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            </div>
        </div>
    </div>
{/strip}
