{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div id="searchResults-container" class="advanceFilterContainer">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" data-result="{vtranslate('LBL_SEARCH_RESULTS', $MODULE)}" data-modify="{vtranslate('LBL_SAVE_MODIFY_FILTER', $MODULE)}">
                    {vtranslate('LBL_ADVANCE_SEARCH', $MODULE)} {vtranslate('LBL_SEARCH', $MODULE)}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="advanceSearchHolder" class="mb-4">
                    <div id="advanceSearchContainer">
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
                <div class="searchResults mt-4 pt-3 bg-light rounded">
                </div>
            </div>
            <div class="modal-overlay-footer clearfix p-0 border-0">
                <div class="clearfix">
                    <div class="actions text-center d-flex row py-2">
                        <div class="col-lg-6 text-end">
                            <button class="btn btn-primary active" id="advanceSearchButton" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if} type="submit">{vtranslate('LBL_SEARCH', $MODULE)}</button>
                        </div>
                        <div class="col-lg-6 text-start">
                            <div class="row">
                                {if $SAVE_FILTER_PERMITTED}
                                    <button class="btn btn-primary active col-auto ms-2" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if} id="advanceIntiateSave">{vtranslate('LBL_SAVE_AS_FILTER', $MODULE)}</button>
                                    <input class="hide col-4 ms-2" type="text" value="" name="viewname"/>
                                    <button class="btn btn-primary active hide col-auto ms-2" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if} id="advanceSave">{vtranslate('LBL_SAVE', $MODULE)}</button>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}

