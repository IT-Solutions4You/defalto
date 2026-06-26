{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<div class="countryIntegration px-4 pb-4">
    <div class="rounded bg-body">
        <div class="postalCodeDatabase container-fluid p-3 border-bottom">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h4 class="m-0">{vtranslate('LBL_POSTAL_CODE_DATABASE', $QUALIFIED_MODULE)}</h4>
                    {if $POSTAL_META}
                        <p class="m-0 text-muted">
                            {vtranslate('LBL_DATASET_VERSION', $QUALIFIED_MODULE)}: <b>{$POSTAL_META['dataset_version']}</b>
                            &middot; {$POSTAL_META['row_count']} {vtranslate('LBL_RECORDS', $QUALIFIED_MODULE)}
                            &middot; {$POSTAL_META['imported_at']}
                        </p>
                    {else}
                        <p class="m-0 text-muted">{vtranslate('LBL_NO_DATASET_IMPORTED', $QUALIFIED_MODULE)}</p>
                    {/if}
                </div>
                <div class="col-lg">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 260px;">
                            <select class="postalCountryCode form-select select2" style="width: 100%;">
                                <option value="">{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
                                {foreach from=$COUNTRIES item=ACTIVE_COUNTRY}
                                    {if 1 eq $ACTIVE_COUNTRY['is_active']}
                                        <option value="{$ACTIVE_COUNTRY['code']}">{$ACTIVE_COUNTRY['code']} - {vtranslate($ACTIVE_COUNTRY['name'], 'Country')}</option>
                                    {/if}
                                {/foreach}
                            </select>
                        </div>
                        <button type="button" class="updatePostalCodes btn btn-primary" disabled>
                            <i class="fa fa-download me-1"></i>{vtranslate('LBL_UPDATE_NOW', $QUALIFIED_MODULE)}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="addressFieldMapping container-fluid p-3 border-bottom">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h4 class="m-0">{vtranslate('LBL_ADDRESS_FIELD_MAPPING', $QUALIFIED_MODULE)}</h4>
                    <p class="m-0 text-muted">{vtranslate('LBL_ADDRESS_FIELD_MAPPING_DESC', $QUALIFIED_MODULE)}</p>
                </div>
                <div class="col-lg">
                    <div style="width: 260px;">
                        <select class="addressMapModule form-select select2" style="width: 100%;">
                            <option value="">{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}</option>
                            {foreach from=$ADDRESS_MODULES item=ADDRESS_MODULE}
                                <option value="{$ADDRESS_MODULE['name']}">{vtranslate($ADDRESS_MODULE['name'], $ADDRESS_MODULE['name'])}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
            <div class="addressMapBody d-none mt-3">
                <div class="addressMapGroups"></div>
                <button type="button" class="addAddressGroup btn btn-outline-secondary">
                    <i class="fa fa-plus me-1"></i>{vtranslate('LBL_ADD_ADDRESS_GROUP', $QUALIFIED_MODULE)}
                </button>
                <button type="button" class="saveAddressMap btn btn-primary ms-2" data-required-msg="{vtranslate('LBL_ADDRESS_ZIP_CITY_REQUIRED', $QUALIFIED_MODULE)}">
                    <i class="fa fa-save me-1"></i>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}
                </button>
            </div>
        </div>
        {* Hidden blueprint cloned by List.js for each address group; the field <option>s are injected client-side. *}
        <div class="addressGroupTemplate d-none">
            <div class="addressGroup border rounded p-3 mb-2">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">{vtranslate('LBL_GROUP_LABEL', $QUALIFIED_MODULE)}</label>
                        <input type="text" class="agLabel form-control" placeholder="{vtranslate('LBL_GROUP_LABEL_PLACEHOLDER', $QUALIFIED_MODULE)}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">{vtranslate('LBL_ZIP_FIELD', $QUALIFIED_MODULE)} *</label>
                        <select class="agZip form-select" data-role="zip">
                            <option value="">{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">{vtranslate('LBL_CITY_FIELD', $QUALIFIED_MODULE)} *</label>
                        <select class="agCity form-select" data-role="city">
                            <option value="">{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">{vtranslate('LBL_STATE_FIELD', $QUALIFIED_MODULE)}</label>
                        <select class="agState form-select" data-role="state">
                            <option value="">{vtranslate('LBL_NOT_MAPPED', $QUALIFIED_MODULE)}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">{vtranslate('LBL_COUNTRY_FIELD', $QUALIFIED_MODULE)}</label>
                        <select class="agCountry form-select" data-role="country">
                            <option value="">{vtranslate('LBL_NOT_MAPPED', $QUALIFIED_MODULE)}</option>
                        </select>
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="removeAddressGroup btn btn-outline-danger" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <form method="post" class="searchContainer">
            <div class="container-fluid p-3 border-bottom">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h4 class="m-0">{vtranslate($TITLE, $QUALIFIED_MODULE)}</h4>
                        <p>{vtranslate($DESCRIPTION, $QUALIFIED_MODULE)}</p>
                    </div>

                </div>
                <div class="row">
                    <div class="col-lg">
                        <button type="button" class="activateAll btn btn-outline-secondary">{vtranslate('LBL_ACTIVATE_ALL', $QUALIFIED_MODULE)}</button>
                        <button type="button" class="deactivateAll btn btn-outline-secondary ms-2">{vtranslate('LBL_DEACTIVATE_ALL', $QUALIFIED_MODULE)}</button>
                    </div>
                    <div class="col-lg">
                        <div class="input-group">
                            <input type="text" id="searchValues" class="searchValues inputElement form-control" placeholder="{vtranslate('LBL_SEARCH', $QUALIFIED_MODULE)}">
                            <label for="searchModule" class="input-group-text">
                                <i class="fa fa-search"></i>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-fluid pb-3">
                <div class="row">
                    {foreach from=$COUNTRIES item=COUNTRY}
                        <label class="col-lg-6 col-xl-4 col-xxl-3 p-3 border-bottom border-end" data-search-value="{strtolower(implode(' - ', [$COUNTRY['code'], $COUNTRY['name'], vtranslate($COUNTRY['name'], 'Country'), vtranslate($COUNTRY['name'], $QUALIFIED_MODULE)]))}">
                            <span class="row">
                                <span class="col-1">
                                    <input class="form-check-input updateValue" type="checkbox" {if 1 eq $COUNTRY['is_active']}checked="checked"{/if} data-value="{$COUNTRY['code']}">
                                </span>
                                <b class="col-1">{$COUNTRY['code']}</b>
                                <span class="col">{vtranslate($COUNTRY['name'], 'Country')}</span>
                                <span class="col-auto text-end">
                                    {if in_array($COUNTRY['code'], $IMPORTED_CODES)}<i class="fa fa-map-marker text-secondary" title="{vtranslate('LBL_POSTAL_CODES_AVAILABLE', $QUALIFIED_MODULE)}"></i>{/if}
                                </span>
                            </span>
                        </label>
                    {/foreach}
                </div>
            </div>
        </form>
    </div>
</div>
{/strip}
