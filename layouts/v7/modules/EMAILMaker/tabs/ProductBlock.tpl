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
    {if $THEME_MODE neq "true"}
        <div class="tab-pane" id="pdfContentProducts">
            <div class="edit-template-content col-lg-4" style="position:fixed;z-index:1000;">
                <br>
                {*********************************************Products bloc DIV*************************************************}
                <div id="products_div">

                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-4" style="font-weight: normal">
                            {vtranslate('LBL_PRODUCT_BLOC_TPL',$MODULE)}:
                        </label>
                        <div class="controls col-sm-8">
                            <div class="input-group">
                                <select name="productbloctpl2" id="productbloctpl2" class="select2 form-control">
                                    {html_options  options=$PRODUCT_BLOC_TPL}
                                </select>
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-success InsertIntoTemplate" data-type="productbloctpl2" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {* product bloc tpl which is the same as in main Properties tab*}
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-4" style="font-weight: normal">
                            {vtranslate('LBL_ARTICLE',$MODULE)}:
                        </label>
                        <div class="controls col-sm-8">
                            <div class="input-group">
                                <select name="articelvar" id="articelvar" class="select2 form-control">
                                    {html_options  options=$ARTICLE_STRINGS}
                                </select>
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-success InsertIntoTemplate" data-type="articelvar" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {* insert products & services fields into text *}

                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-4" style="font-weight: normal">
                            *{vtranslate('LBL_PRODUCTS_AVLBL',$MODULE)}:
                        </label>
                        <div class="controls col-sm-8">
                            <div class="input-group">
                                <select name="psfields" id="psfields" class="select2 form-control">
                                    {html_options  options=$SELECT_PRODUCT_FIELD}
                                </select>
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-success InsertIntoTemplate" data-type="psfields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {* products fields *}
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-4" style="font-weight: normal">
                            *{vtranslate('LBL_PRODUCTS_FIELDS',$MODULE)}:
                        </label>
                        <div class="controls col-sm-8">
                            <div class="input-group">
                                <select name="productfields" id="productfields" class="select2 form-control">
                                    {html_options  options=$PRODUCTS_FIELDS}
                                </select>
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-success InsertIntoTemplate" data-type="productfields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {* services fields *}
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-4" style="font-weight: normal">
                            *{vtranslate('LBL_SERVICES_FIELDS',$MODULE)}:
                        </label>
                        <div class="controls col-sm-8">
                            <div class="input-group">
                                <select name="servicesfields" id="servicesfields" class="select2 form-control">
                                    {html_options  options=$SERVICES_FIELDS}
                                </select>
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-success InsertIntoTemplate" data-type="servicesfields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <label class="muted"><small>{vtranslate('LBL_PRODUCT_FIELD_INFO',$MODULE)}</small></label>
                    </br>
                </div>
            </div>
        </div>
    {/if}
{/strip}