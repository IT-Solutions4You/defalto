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
                        <label class="col-lg-6 col-xl-4 col-xxl-3 p-3 border-bottom border-end" data-search-value="{strtolower(implode(' - ', [$COUNTRY['code'], $COUNTRY['name'], vtranslate($COUNTRY['name'], $QUALIFIED_MODULE)]))}">
                            <span class="row">
                                <span class="col-1">
                                    <input class="form-check-input updateValue" type="checkbox" {if 1 eq $COUNTRY['is_active']}checked="checked"{/if} data-value="{$COUNTRY['code']}">
                                </span>
                                <b class="col-1">{$COUNTRY['code']}</b>
                                <span class="col">{vtranslate($COUNTRY['name'], $QUALIFIED_MODULE)}</span>
                            </span>
                        </label>
                    {/foreach}
                </div>
            </div>
        </form>
    </div>
</div>
{/strip}