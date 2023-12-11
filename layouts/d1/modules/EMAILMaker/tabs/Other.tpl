{*<!--
/*********************************************************************************
* The content of this file is subject to the EMAIL Maker license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
    <div class="tab-pane" id="pdfContentOther">
        <div class="edit-template-content">
            {********************************************* Company and User information DIV *************************************************}
            <div id="listview_block_tpl_row">
                {if $THEME_MODE neq "true"}
                    <div class="form-group row py-2">
                        <label class="control-label fieldLabel col-sm-3 text-muted">
                            <input type="checkbox" name="is_listview" id="isListViewTmpl" class="form-check-input" {if $IS_LISTVIEW_CHECKED eq "yes"}checked="checked"{/if} onclick="EMAILMaker_EditJs.isLvTmplClicked();" title="{vtranslate('LBL_LISTVIEW_TEMPLATE',$MODULE)}"/>
                            <span class="ms-2">{vtranslate('LBL_LISTVIEWBLOCK',$MODULE)}:</span>
                        </label>
                        <div class="controls col-sm">
                            <div class="input-group">
                                <select name="listviewblocktpl" id="listviewblocktpl" class="select2 form-control" {if $IS_LISTVIEW_CHECKED neq "yes"}disabled{/if}>
                                    {html_options  options=$LISTVIEW_BLOCK_TPL}
                                </select>
                                <button type="button" id="listviewblocktpl_butt" class="btn btn-success InsertIntoTemplate" data-type="listviewblocktpl" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}" {if $IS_LISTVIEW_CHECKED neq "yes"}disabled{/if}>
                                    <i class="fa fa-usd"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                {/if}

                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted">
                        {vtranslate('TERMS_AND_CONDITIONS',$MODULE)}:
                    </label>
                    <div class="controls col-sm">
                        <div class="input-group">
                            <select name="invterandcon" id="invterandcon" class="select2 form-control">
                                {html_options  options=$INVENTORYTERMSANDCONDITIONS}
                            </select>
                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="invterandcon" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                <i class="fa fa-usd"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted">
                        {vtranslate('LBL_CURRENT_DATE',$MODULE)}:
                    </label>
                    <div class="controls col-sm">
                        <div class="input-group">
                            <select name="dateval" id="dateval" class="select2 form-control">
                                {html_options  options=$DATE_VARS}
                            </select>
                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="dateval" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                <i class="fa fa-usd"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted">
                        {vtranslate('LBL_GENERAL_FIELDS',$MODULE)}:
                    </label>
                    <div class="controls col-sm">
                        <div class="input-group">
                            <select name="general_fields" id="general_fields" class="select2 form-control">
                                {html_options options=$GENERAL_FIELDS}
                            </select>
                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="general_fields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                <i class="fa fa-usd"></i>
                            </button>
                        </div>
                    </div>
                </div>
                {************************************ Custom Functions *******************************************}
                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-12 text-muted pb-2">
                        {vtranslate('CUSTOM_FUNCTIONS',$MODULE)}:
                    </label>
                    <div class="col-sm-3">
                        <select name="custom_function_type" id="custom_function_type" class="select2">
                            <option value="before">{vtranslate('LBL_BEFORE','EMAILMaker')}</option>
                            <option value="after">{vtranslate('LBL_AFTER','EMAILMaker')}</option>
                        </select>
                    </div>
                    <div class="controls col-sm">
                        <div class="input-group">
                            <select name="customfunction" id="customfunction" class="select2 form-control">
                                {html_options options=$CUSTOM_FUNCTIONS}
                            </select>
                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="customfunction" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}">
                                <i class="fa fa-usd"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}