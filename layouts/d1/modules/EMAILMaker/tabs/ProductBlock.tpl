{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    {if $THEME_MODE neq "true"}
        <div class="tab-pane" id="pdfContentProducts">
            <div class="edit-template-content">
                {*********************************************Products bloc DIV*************************************************}
                <div id="products_div">
                    <div class="form-group row py-2">
                        <label class="control-label fieldLabel col-sm-4 text-muted">
                            {vtranslate('LBL_PRODUCT_BLOC_TPL',$MODULE)}:
                        </label>
                        <div class="controls col-sm-8">
                            <div class="input-group">
                                <select name="productbloctpl2" id="productbloctpl2" class="select2 form-control" data-width="50%">
                                    {html_options  options=$PRODUCT_BLOC_TPL}
                                </select>
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="productbloctpl2" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-usd"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    {* product bloc tpl which is the same as in main Properties tab*}
                    <div class="form-group row py-2">
                        <label class="control-label fieldLabel col-sm-4 text-muted">
                            {vtranslate('LBL_ARTICLE',$MODULE)}:
                        </label>
                        <div class="controls col-sm-8">
                            <div class="input-group">
                                <select name="articelvar" id="articelvar" class="select2 form-control" data-width="50%">
                                    {html_options  options=$ARTICLE_STRINGS}
                                </select>
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="articelvar" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-usd"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    {* insert products & services fields into text *}
                    <div class="form-group row py-2">
                        <label class="control-label fieldLabel col-sm-4 text-muted">
                            *{vtranslate('LBL_PRODUCTS_AVLBL',$MODULE)}:
                        </label>
                        <div class="controls col-sm-8">
                            <div class="input-group">
                                <select name="psfields" id="psfields" class="select2 form-control" data-width="50%">
                                    {html_options  options=$SELECT_PRODUCT_FIELD}
                                </select>
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="psfields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-usd"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    {* products fields *}
                    <div class="form-group row py-2">
                        <label class="control-label fieldLabel col-sm-4 text-muted">
                            *{vtranslate('LBL_PRODUCTS_FIELDS',$MODULE)}:
                        </label>
                        <div class="controls col-sm-8">
                            <div class="input-group">
                                <select name="productfields" id="productfields" class="select2 form-control" data-width="50%">
                                    {html_options  options=$PRODUCTS_FIELDS}
                                </select>
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="productfields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-usd"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    {* services fields *}
                    <div class="form-group row py-2">
                        <label class="control-label fieldLabel col-sm-4 text-muted">
                            *{vtranslate('LBL_SERVICES_FIELDS',$MODULE)}:
                        </label>
                        <div class="controls col-sm-8">
                            <div class="input-group">
                                <select name="servicesfields" id="servicesfields" class="select2 form-control" data-width="50%">
                                    {html_options  options=$SERVICES_FIELDS}
                                </select>
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="servicesfields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-usd"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="text-muted my-2">
                        <small>{vtranslate('LBL_PRODUCT_FIELD_INFO',$MODULE)}</small>
                    </div>
                </div>
            </div>
        </div>
    {/if}
{/strip}