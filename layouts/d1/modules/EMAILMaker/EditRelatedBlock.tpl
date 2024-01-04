{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    <div class="app-nav"></div>
    <div class="app-menu"></div>
    <form id="NewBlock" name="NewBlock" class="form-horizontal p-3" method="POST" ENCTYPE="multipart/form-data" action="index.php">
        <input type="hidden" name="module" value="EMAILMaker">
        <input type="hidden" name="pdfmodule" value="{$REL_MODULE}">
        <input type="hidden" name="primarymodule" value="{$REL_MODULE}">
        <input type="hidden" id="saved_secmodule" name="saved_secmodule" value="{if $RECORD neq ""}{$SEC_MODULE}{/if}">
        <input type="hidden" name="record" value="{$RECORD}">
        <input type="hidden" name="action" value="SaveRelatedBlock">
        <input type="hidden" name="step" id="step" value="{$STEP}">
        <input type="hidden" name="advanced_filter" id="advanced_filter" value=""/>
        <input type="hidden" name="selected_sort_fields" id="selected_sort_fields" value=""/>
        <div id="filter_columns" style="display:none">
            <option value="">{$REP.LBL_NONE}</option>{$SECCOLUMNS}</div>
        <div class="bodyContents">
            <div class="contentsDiv">
                <div>
                    <div>
                        <h3>{vtranslate('LBL_EDIT_RELATED_BLOCK','EMAILMaker')}</h3>
                        <div>
                            {if $MODE eq "edit"}
                                {assign var=LABELS value=[3 => vtranslate('LBL_FILTERS','EMAILMaker'), 4 => vtranslate('LBL_SORTING','EMAILMaker'), 5 => vtranslate('LBL_BLOCK_STYLE','EMAILMaker')]}
                                {include file="BreadCrumbs.tpl"|vtemplate_path:$MODULE BREADCRUMB_ID='navigation_links' ACTIVESTEP=1 BREADCRUMB_LABELS=$LABELS MODULE=$MODULE}
                            {else}
                                {assign var=LABELS value=[1 => vtranslate('LBL_RELATIVE_MODULE','EMAILMaker'), 2=>vtranslate('LBL_SELECT_COLUMNS','EMAILMaker'), 3=>vtranslate('LBL_FILTERS','EMAILMaker'), 4=>vtranslate('LBL_SORTING','EMAILMaker'), 5=>vtranslate('LBL_BLOCK_STYLE','EMAILMaker')]}
                                {include file="BreadCrumbs.tpl"|vtemplate_path:$MODULE BREADCRUMB_ID='navigation_links' ACTIVESTEP=1 BREADCRUMB_LABELS=$LABELS MODULE=$MODULE}
                            {/if}
                        </div>
                    </div>
                    <div style="position: relative;" class="row">
                        <div>
                            <div class="pushDown2per">
                                <div class="summaryWidgetContainer bg-body rounded">
                                    {if $MODE eq "create"}
                                        <!-- STEP 1 -->
                                        <div id="step1" class="{if $STEP neq "1"}hide{/if}">
                                            <div class="widget_header p-3 border-bottom">
                                                <h4>{vtranslate('LBL_RELATIVE_MODULE','EMAILMaker')}</h4>
                                            </div>
                                            <div class="widget_contents p-3">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        {if $RELATED_MODULES|@count > 0}
                                                            <td class="w-25"><b>{$REP.LBL_NEW_REP0_HDR2}</b></td>
                                                            <td>

                                                                {foreach item=relmod name=relmodule from=$RELATED_MODULES}
                                                                    {if $SEC_MODULE eq $relmod}
                                                                        <div class="mb-3">
                                                                            <label>
                                                                                <input type="radio" class="form-check-input me-3" name="secondarymodule" checked value="{$relmod}"/>
                                                                                {vtranslate($relmod)}
                                                                            </label>
                                                                        </div>
                                                                    {else}
                                                                        <div class="mb-3">
                                                                            <label>
                                                                                <input type="radio" class="form-check-input me-3" name="secondarymodule" value="{$relmod}"/>
                                                                                {vtranslate($relmod)}
                                                                            </label>
                                                                        </div>
                                                                    {/if}
                                                                {/foreach}
                                                            </td>
                                                        {else}
                                                            <td class="w-25"><b>{$REP.NO_REL_MODULES}</b></td>
                                                        {/if}
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <!-- STEP 2 -->
                                        <div id="step2" class="hide">
                                            <div class="widget_header p-3 border-bottom">
                                                <h4>{vtranslate('LBL_SELECT_COLUMNS','Reports')}<span class="text-danger ms-2">*</span></h4>
                                            </div>
                                            <div class="widget_contents p-3">
                                                <div class="relatedBlockColumnsParent">
                                                    <select data-placeholder="{vtranslate('LBL_ADD_MORE_COLUMNS','Reports')}" id="relatedblockColumnsList" name="relatedblockColumnsList[]" data-rule-required="true" class="relatedblockColumns select2" multiple style="width: 100%;">
                                                        {$SECCOLUMNS}
                                                    </select>
                                                    <input name="selected_fields" id="seleted_fields" value="{if $SELECTEDCOLUMNS neq ""}{$SELECTEDCOLUMNS}{else}[]{/if}" type="hidden">
                                                </div>
                                            </div>
                                        </div>
                                    {/if}
                                    <!-- STEP 3 -->
                                    <div id="step3" class="{if $MODE neq "edit"}hide{/if}">
                                        {if $RECORD neq ""}
                                            {include file='BlockFilters.tpl'|@vtemplate_path:'EMAILMaker'}
                                        {/if}
                                    </div>
                                    <!-- STEP 4 -->
                                    <div id="step4" class="hide">
                                        <input type="hidden" name="sortColCount" id="sortColCount" value="1"/>
                                        <div class="widget_header p-3 border-bottom">
                                            <h4>{vtranslate('LBL_SORTING','EMAILMaker')}</h4>
                                        </div>
                                        <div class="widget_contents p-3">
                                            <div class="well filterConditionContainer filterConditionsDiv">
                                                <div class="form-group">
                                                    <div class="container-fluid">
                                                        <div class="row">
                                                            <label class="col-lg-6">{vtranslate('LBL_SORT_BY','EMAILMaker')}</label>
                                                            <label class="col-lg-6">{vtranslate('LBL_SORT_ORDER','Reports')}</label>
                                                        </div>
                                                        {assign var=ROW_VAL value=1}
                                                        {foreach key=SELECTED_SORT_FIELD_KEY item=SELECTED_SORT_FIELD_VALUE from=$SELECTED_SORT_FIELDS}
                                                            <div class="row sortFieldRow">
                                                                {include file='RelatedFields.tpl'|@vtemplate_path:'EMAILMaker' ROW_VAL=$ROW_VAL}
                                                                {assign var=ROW_VAL value=($ROW_VAL+1)}
                                                            </div>
                                                        {/foreach}
                                                        {assign var=SELECTED_SORT_FEILDS_ARRAY value=$SELECTED_SORT_FIELDS}
                                                        {assign var=SELECTED_SORT_FIELDS_COUNT value=count($SELECTED_SORT_FEILDS_ARRAY)}
                                                        {while $SELECTED_SORT_FIELDS_COUNT lt 3 }
                                                            <div class="row sortFieldRow">
                                                                {include file='RelatedFields.tpl'|@vtemplate_path:'EMAILMaker' ROW_VAL=$ROW_VAL}
                                                                {assign var=ROW_VAL value=($ROW_VAL+1)}
                                                                {assign var=SELECTED_SORT_FIELDS_COUNT value=($SELECTED_SORT_FIELDS_COUNT+1)}
                                                            </div>
                                                        {/while}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- STEP 5 -->
                                    <div id="step5" class="hide">
                                        <div class="widget_header p-3 border-bottom">
                                            <h4>{vtranslate('LBL_BLOCK_STYLE','EMAILMaker')}</h4>
                                        </div>
                                        <div class="widget_contents p-3">
                                            <div class="well">
                                                <div class="row">
                                                    <label class="col-lg-2 control-label textAlignLeft">{vtranslate('Name')}
                                                        <span class="text-danger ms-2">*</span>
                                                    </label>
                                                    <div class="col-lg-6">
                                                        <input class="inputElement form-control" data-rule-required="true" name="blockname" value="{$BLOCKNAME}">
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <textarea name="relatedblock" id="relatedblock" style="width:90%;height:500px" class=small tabindex="5">{$RELATEDBLOCK}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- BUTTONS -->
                                    <div class="modal-overlay-footer border-top">
                                        <div class="container-fluid p-3">
                                            <div class="row">
                                                <div class="col text-end">
                                                    <a name="cancel" class="btn btn-primary cancelLink" value="Cancel" href="javscript:;" onClick="self.close();">{vtranslate('LBL_CANCEL')}</a>
                                                </div>
                                                <div class="col-auto">
                                                    <button type="button" name="back_rep" id="back_rep" class="btn btn-primary" onclick="return EMAILMaker_RelatedBlockJs.changeStepsback('{$MODE}');" {if $STEP eq "1" || $STEP eq "3"}disabled="disabled"{/if}>
                                                        <strong>{vtranslate('LBL_BACK')}</strong>
                                                    </button>
                                                </div>
                                                <div class="col">
                                                    <button type="button" name="next" id="next" class="btn btn-primary active" onclick="return EMAILMaker_RelatedBlockJs.changeSteps('{$MODE}');">
                                                        <strong>{vtranslate('LBL_NEXT','EMAILMaker')}</strong>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </form>
    <script>
        var sortRowCount = 1;
        var sortColString = '';

        jQuery(document).ready(function () {ldelim}
            {if $MODE eq "edit"}
            EMAILMaker_RelatedBlockJs.registerEditEvents();
            {else}
            EMAILMaker_RelatedBlockJs.registerEvents();
            {/if}
            {rdelim});

        {if $BACK_WALK eq 'true'}
        hide('step1');
        show('step2');
        document.getElementById('back_rep').disabled = false;
        document.getElementById('step1label').className = 'settingsTabList';
        document.getElementById('step2label').className = 'settingsTabSelected';
        {/if}
        {if $BACK eq 'false'}
        hide('step1');
        show('step2');
        document.getElementById('back_rep').disabled = true;
        document.getElementById('step1label').className = 'settingsTabList';
        document.getElementById('step2label').className = 'settingsTabSelected';
        {/if}
    </script>
{/strip}