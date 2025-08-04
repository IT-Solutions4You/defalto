{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div id="searchResults-container" class="advanceFilterContainer h-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" data-result="{vtranslate('LBL_SEARCH_RESULTS', $MODULE)}" data-modify="{vtranslate('LBL_SAVE_MODIFY_FILTER', $MODULE)}">
                    {vtranslate('LBL_ADVANCE_SEARCH', $MODULE)} {vtranslate('LBL_SEARCH', $MODULE)}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body overflow-auto bg-body-secondary">
                <div id="advanceSearchHolder">
                    <div id="advanceSearchContainer" class="p-3 rounded bg-body mb-4">
                        <div class="pb-2">{vtranslate('LBL_SEARCH_IN',$MODULE)}</div>
                        <div class="searchModuleComponent">
                            <select class="select2 col-lg-3" id="searchModuleList" data-placeholder="{vtranslate('LBL_SELECT_MODULE')}">
                                <option></option>
                                {foreach key=MODULE_NAME item=fieldObject from=$SEARCHABLE_MODULES}
                                    <option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SOURCE_MODULE}selected="selected"{/if}>{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="clearfix"></div>
                        <div class="filterElements well filterConditionContainer" id="searchContainer" style="height: auto;">
                            <form name="advanceFilterForm" method="POST">
                                {if $SOURCE_MODULE eq 'Home'}
                                    <div class="textAlignCenter well contentsBackground">{vtranslate('LBL_PLEASE_SELECT_MODULE',$MODULE)}</div>
                                {else}
                                    <input type="hidden" name="labelFields" {if !empty($SOURCE_MODULE_MODEL)}  data-value='{ZEND_JSON::encode($SOURCE_MODULE_MODEL->getNameFields())}' {/if} />
                                    {include file='AdvanceFilter.tpl'|@vtemplate_path}
                                {/if}
                            </form>
                        </div>
                    </div>
                </div>
                <div class="searchResults">
                </div>
            </div>
            <div class="modal-footer">
                <div class="container-fluid">
                    <div class="actions row">
                        <div class="col-lg-6 text-end">
                            <button class="btn btn-primary active" id="advanceSearchButton" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if} type="submit">{vtranslate('LBL_SEARCH', $MODULE)}</button>
                        </div>
                        {if $SAVE_FILTER_PERMITTED}
                            <div class="col-auto">
                                <div class="input-group">
                                    <button class="btn btn-primary active" {if $SOURCE_MODULE eq 'Home'}disabled="disabled"{/if} id="advanceIntiateSave">{vtranslate('LBL_SAVE_AS_FILTER', $MODULE)}</button>
                                </div>
                                <div class="input-group">
                                    <input class="hide form-control" type="text" value="" name="viewname"/>
                                    <button class="btn btn-primary active hide" {if $SOURCE_MODULE eq 'Home'}disabled="disabled"{/if} id="advanceSave">{vtranslate('LBL_SAVE', $MODULE)}</button>
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}

