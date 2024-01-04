{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    <div class="h-main">
        <div class="editViewContainer">
            <form class="form-horizontal" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data" data-detail-url="?module=EMAILMaker&view=ProductBlocks">
                <input type="hidden" name="module" value="EMAILMaker"/>
                <input type="hidden" name="action" value="IndexAjax"/>
                <input type="hidden" name="mode" value="SaveProductBlock"/>
                <input type="hidden" name="tplid" value="{$EDIT_TEMPLATE.id}">
                <div class="contentHeader p-3 bg-body mb-3">
                    <div class="mb-3">
                        {if $EMODE eq 'edit'}
                            {if $MODE neq "duplicate"}
                                <h3 title="{vtranslate('LBL_EDIT','EMAILMaker')} &quot;{$FILENAME}&quot;">{vtranslate('LBL_EDIT','EMAILMaker')} &quot;{$EDIT_TEMPLATE.name}&quot;</h3>
                            {else}
                                <h3 title="{vtranslate('LBL_DUPLICATE','EMAILMaker')} &quot;{$DUPLICATE_FILENAME}&quot;">{vtranslate('LBL_DUPLICATE','EMAILMaker')} &quot;{$EDIT_TEMPLATE.name}&quot;</h3>
                            {/if}
                        {else}
                            <h3>{vtranslate('LBL_NEW_TEMPLATE','EMAILMaker')}</h3>
                        {/if}
                    </div>
                    <ul class="nav nav-pills layoutTabs massEditTabs">
                        <li class="nav-item detailviewTab">
                            <a class="nav-link active" data-bs-toggle="tab" href="#pContentEdit">{vtranslate('LBL_PROPERTIES_TAB','EMAILMaker')}</a>
                        </li>
                        <li class="nav-item detailviewTab">
                            <a class="nav-link" data-bs-toggle="tab" href="#pContentLabels">{vtranslate('LBL_LABELS','EMAILMaker')}</a>
                        </li>
                    </ul>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="contents tabbable ui-sortable rounded p-3 bg-body">
                            <div class="tab-content layoutContent themeTableColor overflowVisible">
                                {********************************************* PROPERTIES DIV*************************************************}
                                <div class="tab-pane active" id="pContentEdit">
                                    <div>
                                        <div>
                                            <div class="row py-2">
                                                <div class="col-sm-4 fieldLabel text-muted">
                                                    <label>
                                                        <span>{vtranslate('LBL_EMAIL_NAME','EMAILMaker')}:</span>
                                                        <span class="text-danger ms-2">*</span>
                                                    </label>
                                                </div>
                                                <div class="col-sm fieldValue">
                                                    <input name="template_name" id="template_name" type="text" value="{if $MODE neq "duplicate"}{$EDIT_TEMPLATE.name}{/if}" class="inputElement form-control" tabindex="1" data-rule-required="true">
                                                </div>
                                            </div>
                                            <div class="row py-2">
                                                <div class="col-sm-4 fieldLabel text-muted">
                                                    <label>{vtranslate('LBL_ARTICLE','EMAILMaker')}:</label>
                                                </div>
                                                <div class="col-sm fieldValue">
                                                    <div class="input-group">
                                                        <select name="articelvar" id="articelvar" class="select2 col-sm-9">
                                                            {html_options  options=$ARTICLE_STRINGS}
                                                        </select>
                                                        <button type="button" class="btn btn-success small create" onclick="InsertIntoTemplate('articelvar');" title="{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}">
                                                            <i class="fa fa-usd"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            {* insert products & services fields into text *}
                                            <div class="row py-2">
                                                <div class="col-sm-4 fieldLabel text-muted">
                                                    <label>*{vtranslate('LBL_PRODUCTS_AVLBL','EMAILMaker')}:</label>
                                                </div>
                                                <div class="col-sm fieldValue">
                                                    <div class="input-group">
                                                        <select name="psfields" id="psfields" class="select2 col-sm-9">
                                                            {html_options  options=$SELECT_PRODUCT_FIELD}
                                                        </select>
                                                        <button type="button" class="btn btn-success small create" onclick="InsertIntoTemplate('psfields');" title="{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}">
                                                            <i class="fa fa-usd"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            {* products fields *}
                                            <div class="row py-2">
                                                <div class="col-sm-4 fieldLabel text-muted">
                                                    <label>*{vtranslate('LBL_PRODUCTS_FIELDS','EMAILMaker')}:</label>
                                                </div>
                                                <div class="col-sm fieldValue">
                                                    <div class="input-group">
                                                        <select name="productfields" id="productfields" class="select2 col-sm-9">
                                                            {html_options  options=$PRODUCTS_FIELDS}
                                                        </select>
                                                        <button type="button" class="btn btn-success small create" onclick="InsertIntoTemplate('productfields');" title="{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}">
                                                            <i class="fa fa-usd"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            {* services fields *}
                                            <div class="row py-2">
                                                <div class="col-sm-4 fieldLabel text-muted">
                                                    <label>*{vtranslate('LBL_SERVICES_FIELDS','EMAILMaker')}:</label>
                                                </div>
                                                <div class="col-sm fieldValue">
                                                    <div class="input-group">
                                                        <select name="servicesfields" id="servicesfields" class="select2 col-sm-9">
                                                            {html_options  options=$SERVICES_FIELDS}
                                                        </select>
                                                        <button type="button" class="btn btn-success small create" onclick="InsertIntoTemplate('servicesfields');" title="{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}">
                                                            <i class="fa fa-usd"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="py-2">
                                                    <small>{vtranslate('LBL_PRODUCT_FIELD_INFO','EMAILMaker')}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {********************************************* Labels DIV *************************************************}
                                <div class="tab-pane" id="pContentLabels">
                                    <div>
                                        <div class="row py-2">
                                            <div class="col-sm-4 fieldLabel text-muted"><label class="muted pull-right">{vtranslate('LBL_GLOBAL_LANG','EMAILMaker')}:</label></div>
                                            <div class="col-sm fieldValue">
                                                <div class="input-group">
                                                    <select name="global_lang" id="global_lang" class="select2 col-sm-9">
                                                        {html_options  options=$GLOBAL_LANG_LABELS}
                                                    </select>
                                                    <button type="button" class="btn btn-success small create" onclick="InsertIntoTemplate('global_lang');" title="{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}">
                                                        <i class="fa fa-usd"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row py-2">
                                            <div class="col-sm-4 fieldLabel text-muted"><label class="muted pull-right">{vtranslate('LBL_CUSTOM_LABELS','EMAILMaker')}:</label></div>
                                            <div class="col-sm fieldValue">
                                                <div class="input-group">
                                                    <select name="custom_lang" id="custom_lang" class="select2 col-sm-9">
                                                        {html_options  options=$CUSTOM_LANG_LABELS}
                                                    </select>
                                                    <button type="button" class="btn btn-success small create" onclick="InsertIntoTemplate('custom_lang');" title="{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}">
                                                        <i class="fa fa-usd"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {************************************** END OF TABS BLOCK *************************************}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg">
                        <div class="rounded p-3 bg-body">
                            {*********************************************BODY DIV*************************************************}
                            <textarea name="body" id="body" style="width:90%;height:700px" class=small tabindex="5">{$EDIT_TEMPLATE.body}</textarea>
                            <div class="modal-overlay-footer container-fluid pt-3">
                                <div class="row">
                                    <div class="col text-end">
                                        {if $smarty.request.applied eq 'true'}
                                            <a class="btn btn-primary cancelLink" type="reset" onclick="window.location.href = 'index.php?action=DetailViewEMAILTemplate&module=EMAILMaker&templateid={$SAVETEMPLATEID}&parenttab=Tools';">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                                        {else}
                                            <a class="btn btn-primary cancelLink" type="reset" onclick="window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                                        {/if}
                                    </div>
                                    <div class="col">
                                        <button class="btn btn-primary active" type="submit">
                                            <strong>{vtranslate('LBL_SAVE', $MODULE)}</strong>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <script type="text/javascript">
            {literal} jQuery(document).ready(function () {
                CKEDITOR.replace('body', {height: '65vh'});
                var EMAILMakerProductBlocksJs = EMAILMaker_ProductBlocks_Js.getInstance();
                EMAILMakerProductBlocksJs.registerEvents();
            });

            var selectedTab = "properties";

            function InsertIntoTemplate(elementType) {

                var insert_value = "";
                var selectField = document.getElementById(elementType).value;
                var oEditor = CKEDITOR.instances.body;

                if (elementType == "articelvar" || selectField == "LISTVIEWBLOCK_START" || selectField == "LISTVIEWBLOCK_END") {
                    insert_value = '#' + selectField + '#';
                } else if (elementType == "relatedmodulefields") {
                    insert_value = '$R_' + selectField + '$';
                } else if (elementType == "productbloctpl" || elementType == "productbloctpl2") {
                    insert_value = selectField;
                } else if (elementType == "global_lang") {
                    insert_value = '%G_' + selectField + '%';
                } else if (elementType == "custom_lang") {
                    insert_value = '%' + selectField + '%';
                } else {
                    insert_value = '$' + selectField + '$';
                }
                oEditor.insertHtml(insert_value);
            }
            {/literal}
        </script>
    </div>
{/strip}