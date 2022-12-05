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
    <div class="tab-pane" id="pdfContentLabels">
        <div class="edit-template-content col-lg-4" style="position:fixed;z-index:1000;">
            <br>
            {********************************************* Labels *************************************************}
            <div id="labels_div">


                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                        {vtranslate('LBL_GLOBAL_LANG',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        <div class="input-group">
                            <select name="global_lang" id="global_lang" class="select2 form-control">
                                {html_options  options=$GLOBAL_LANG_LABELS}
                            </select>
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-warning InsertIntoTemplate" data-type="global_lang" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                {if $THEME_MODE neq "true"}
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                            {vtranslate('LBL_MODULE_LANG',$MODULE)}:
                        </label>
                        <div class="controls col-sm-9">
                            <div class="input-group">
                                <select name="module_lang" id="module_lang" class="select2 form-control">
                                    {html_options  options=$MODULE_LANG_LABELS}
                                </select>
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-warning InsertIntoTemplate" data-type="module_lang" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                        {vtranslate('LBL_CUSTOM_LABELS',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        <div class="input-group">
                            <select name="custom_lang" id="custom_lang" class="select2 form-control">
                                {html_options  options=$CUSTOM_LANG_LABELS}
                            </select>
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-warning InsertIntoTemplate" data-type="custom_lang" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}