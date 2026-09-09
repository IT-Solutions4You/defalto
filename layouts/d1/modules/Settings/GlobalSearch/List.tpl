{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="globalSearchSettings px-4 pb-4">
        <div class="rounded bg-body">
            <div class="container-fluid p-3 border-bottom">
                <div class="row align-items-center g-3">
                    <div class="col-lg">
                        <h4 class="m-0">{vtranslate('LBL_GLOBAL_SEARCH_SETTINGS', $QUALIFIED_MODULE)}</h4>
                        <p class="m-0 text-muted">{vtranslate('LBL_GLOBAL_SEARCH_SETTINGS_DESCRIPTION', $QUALIFIED_MODULE)}</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="input-group">
                            <label for="global-search-settings-filter" class="input-group-text">
                                <i class="fa fa-search"></i>
                            </label>
                            <input id="global-search-settings-filter" class="globalSearchFilter form-control" type="search" placeholder="{vtranslate('LBL_SEARCH', $QUALIFIED_MODULE)}">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="saveGlobalSearchSettings btn btn-primary">
                            <i class="fa fa-save me-1"></i>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}
                        </button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">{vtranslate('LBL_MODULE', $QUALIFIED_MODULE)}</th>
                            <th>{vtranslate('LBL_SEARCH_FIELDS', $QUALIFIED_MODULE)}</th>
                            <th class="text-center">{vtranslate('LBL_ACTIVE', $QUALIFIED_MODULE)}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$MODULE_ROWS item=MODULE_ROW}
                            {assign var=MODULE_MODEL value=$MODULE_ROW['module']}
                            {assign var=MODULE_NAME value=$MODULE_MODEL->getName()}
                            {assign var=MODULE_LABEL value=vtranslate($MODULE_NAME, $MODULE_NAME)}
                            {assign var=MODULE_OFFICIAL_LABEL value=$MODULE_MODEL->get('label')}
                            {assign var=MODULE_TRANSLATED_OFFICIAL_LABEL value=vtranslate($MODULE_OFFICIAL_LABEL, $MODULE_NAME)}
                            <tr class="globalSearchModuleRow" data-tab-id="{$MODULE_MODEL->getId()}" data-search-value="{$MODULE_NAME|escape:'html'} {$MODULE_OFFICIAL_LABEL|escape:'html'} {$MODULE_LABEL|escape:'html'} {$MODULE_TRANSLATED_OFFICIAL_LABEL|escape:'html'}">
                                <td class="ps-3 fw-semibold">{$MODULE_LABEL}</td>
                                <td>
                                    <select class="globalSearchFields form-select" multiple data-placeholder="{vtranslate('LBL_SELECT_FIELDS', $QUALIFIED_MODULE)}">
                                        {foreach from=$MODULE_ROW['field_blocks'] key=BLOCK_LABEL item=BLOCK_FIELDS}
                                            <optgroup label="{vtranslate($BLOCK_LABEL, $MODULE_NAME)|escape:'html'}">
                                                {foreach from=$BLOCK_FIELDS item=FIELD_MODEL}
                                                    <option value="{$FIELD_MODEL->getId()}" {if in_array($FIELD_MODEL->getId(), $MODULE_ROW['selected_field_ids'])}selected{/if}>
                                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE_NAME)}
                                                    </option>
                                                {/foreach}
                                            </optgroup>
                                        {/foreach}
                                    </select>
                                </td>
                                <td class="text-center">
                                    <input class="globalSearchActive form-check-input" type="checkbox" {if $MODULE_ROW['is_active']}checked{/if} aria-label="{vtranslate('LBL_ACTIVE', $QUALIFIED_MODULE)}">
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
{/strip}
