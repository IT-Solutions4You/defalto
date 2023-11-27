{* /**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */ *}

<div class="contents tabbable ui-sortable">


    <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
        <input type="hidden" name="module" value="PDFMaker">
        <input type="hidden" name="parenttab" value="{$PARENTTAB}">
        <input type="hidden" name="templateid" id="templateid" value="{$TEMPLATEID}">
        <input type="hidden" name="action" value="SavePDFTemplate">
        <input type="hidden" name="redirect" value="true">
        <input type="hidden" name="return_module" value="{$smarty.request.return_module}">
        <input type="hidden" name="return_view" value="{$smarty.request.return_view}">
        <input type="hidden" name="selectedTab" id="selectedTab" value="properties">
        <input type="hidden" name="selectedTab2" id="selectedTab2" value="body">

        <ul class="nav nav-tabs layoutTabs massEditTabs">
            <li class="detailviewTab active">
                <a data-toggle="tab" href="#pdfContentEdit" aria-expanded="true"><strong>{vtranslate('LBL_PROPERTIES_TAB',$MODULE)}</strong></a>
            </li>
            <li class="detailviewTab">
                <a data-toggle="tab" href="#pdfContentOther" aria-expanded="false"><strong>{vtranslate('LBL_OTHER_INFO',$MODULE)}</strong></a>
            </li>
            <li class="detailviewTab">
                <a data-toggle="tab" href="#pdfContentLabels" aria-expanded="false"><strong>{vtranslate('LBL_LABELS',$MODULE)}</strong></a>
            </li>
            <li class="detailviewTab">
                <a data-toggle="tab" href="#pdfContentProducts" aria-expanded="false"><strong>{vtranslate('LBL_ARTICLE',$MODULE)}</strong></a>
            </li>
            <li class="detailviewTab">
                <a data-toggle="tab" href="#pdfContentHeaderFooter" aria-expanded="false"><strong>{vtranslate('LBL_HEADER_TAB',$MODULE)} / {vtranslate('LBL_FOOTER_TAB',$MODULE)}</strong></a>
            </li>
            <li class="detailviewTab">
                <a data-toggle="tab" href="#editTabSettings" aria-expanded="false"><strong>{vtranslate('LBL_SETTINGS_TAB',$MODULE)}</strong></a>
            </li>
        </ul>
        <div >
            {********************************************* Settings DIV *************************************************}
            <div class="container-fluid">
                <div class="row">
                    <div class="left-block col-xs-4">
                        <div>
                            <div class="tab-content layoutContent themeTableColor overflowVisible">
                                <div class="tab-pane active" id="pdfContentEdit">
                                    <div class="edit-template-content col-lg-4" style="position:fixed;z-index:1000;">
                                        {********************************************* PROPERTIES DIV*************************************************}
                                        <div id="properties_div">
                                            <br>
                                            {* pdf source module and its available fields *}
                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('LBL_MODULENAMES',$MODULE)} {$MODULENAME}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <input type="hidden" name="modulename" id="modulename" value="{$SELECTMODULE}">
                                                        <select name="modulefields" id="modulefields" class="select2 form-control">
                                                            <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD',$MODULE)}</option>
                                                        </select>
                                                        <div class="input-group-btn">
                                                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="modulefields" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                            <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="modulefields" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {* related modules and its fields *}
                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('LBL_RELATED_MODULES',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <select name="relatedmodulesorce" id="relatedmodulesorce" class="select2 form-control">
                                                            <option value="">{vtranslate('LBL_SELECT_MODULE',$MODULE)}</option>
                                                            {foreach item=RelMod from=$RELATED_MODULES}
                                                                <option value="{$RelMod.0}" data-module="{$RelMod.3}">{$RelMod.1} ({$RelMod.2})</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <select name="relatedmodulefields" id="relatedmodulefields" class="select2 form-control">
                                                            <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD',$MODULE)}</option>
                                                        </select>
                                                        <div class="input-group-btn">
                                                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="relatedmodulefields" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                            <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="relatedmodulefields" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="pdfContentOther">
                                    <div class="edit-template-content col-lg-4" style="position:fixed;z-index:1000;">
                                        <br>
                                        {********************************************* Company and User information DIV *************************************************}
                                        <div class="form-group">
                                            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                {vtranslate('LBL_COMPANY_USER_INFO',$MODULE)}:
                                            </label>
                                            <div class="controls col-sm-9">
                                                <div class="input-group">
                                                    <select name="acc_info_type" id="acc_info_type" class="select2 form-control">
                                                        {html_options  options=$CUI_BLOCKS}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal"></label>
                                            <div class="controls col-sm-9">
                                                <div>
                                                    <div id="acc_info_div" class="au_info_div" style="display:inline;">
                                                        <div class="input-group">
                                                            <select name="acc_info" id="acc_info" class="select2 form-control">
                                                                {html_options  options=$ACCOUNTINFORMATIONS}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="acc_info" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="acc_info" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="user_info_div" class="au_info_div" style="display:none;">
                                                        <div class="input-group">
                                                            <select name="user_info" id="user_info" class="select2 form-control">
                                                                {html_options  options=$USERINFORMATIONS['a']}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="user_info" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="user_info" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="logged_user_info_div" class="au_info_div" style="display:none;">
                                                        <div class="input-group">
                                                            <select name="logged_user_info" id="logged_user_info" class="select2 form-control">
                                                                {html_options  options=$USERINFORMATIONS['l']}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn- InsertIntoTemplate" data-type="logged_user_info" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="logged_user_info" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="modifiedby_user_info_div" class="au_info_div" style="display:none;">
                                                        <div class="input-group">
                                                            <select name="modifiedby_user_info" id="modifiedby_user_info" class="select2 form-control">
                                                                {html_options  options=$USERINFORMATIONS['m']}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="modifiedby_user_info" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="modifiedby_user_info" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="smcreator_user_info_div" class="au_info_div form-control" style="display:none;">
                                                        <div class="input-group">
                                                            <select name="smcreator_user_info" id="smcreator_user_info" class="select2 form-control">
                                                                {html_options  options=$USERINFORMATIONS['c']}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="smcreator_user_info" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="smcreator_user_info" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                {vtranslate('TERMS_AND_CONDITIONS',$MODULE)}:
                                            </label>
                                            <div class="controls col-sm-9">
                                                <div class="input-group">
                                                    <select name="invterandcon" id="invterandcon" class="select2 form-control">
                                                        {html_options  options=$INVENTORYTERMSANDCONDITIONS}
                                                    </select>
                                                    <div class="input-group-btn">
                                                        <button type="button" class="btn btn-success InsertIntoTemplate" data-type="invterandcon" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                {vtranslate('LBL_CURRENT_DATE',$MODULE)}:
                                            </label>
                                            <div class="controls col-sm-9">
                                                <div class="input-group">
                                                    <select name="dateval" id="dateval" class="select2 form-control">
                                                        {html_options  options=$DATE_VARS}
                                                    </select>
                                                    <div class="input-group-btn">
                                                        <button type="button" class="btn btn-success InsertIntoTemplate" data-type="dateval" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="pdfContentLabels">
                                    <dic class="edit-template-content col-lg-4" style="position:fixed;z-index:1000;">
                                        {********************************************* Labels *************************************************}
                                        <div id="labels_div">
                                            <br>
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
                                        </div>
                                    </dic>
                                </div>
                                <div class="tab-pane" id="pdfContentProducts">
                                    <dic class="edit-template-content col-lg-4" style="position:fixed;z-index:1000;">
                                        {*********************************************Products bloc DIV*************************************************}
                                        <div id="products_div">
                                            <br>
                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('LBL_PRODUCT_BLOC_TPL',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <select name="productbloctpl2" id="productbloctpl2" class="select2 form-control">
                                                            {html_options  options=$PRODUCT_BLOC_TPL}
                                                        </select>
                                                        <div class="input-group-btn">
                                                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="productbloctpl2" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('LBL_ARTICLE',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
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
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    *{vtranslate('LBL_PRODUCTS_AVLBL',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <select name="psfields" id="psfields" class="select2 form-control">
                                                            {html_options  options=$SELECT_PRODUCT_FIELD}
                                                        </select>
                                                        <div class="input-group-btn">
                                                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="psfields" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {* products fields *}
                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    *{vtranslate('LBL_PRODUCTS_FIELDS',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <select name="productfields" id="productfields" class="select2 form-control">
                                                            {html_options  options=$PRODUCTS_FIELDS}
                                                        </select>
                                                        <div class="input-group-btn">
                                                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="productfields" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {* services fields *}
                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    *{vtranslate('LBL_SERVICES_FIELDS',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <select name="servicesfields" id="servicesfields" class="select2 form-control">
                                                            {html_options  options=$SERVICES_FIELDS}
                                                        </select>
                                                        <div class="input-group-btn">
                                                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="servicesfields" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="muted"><small>{vtranslate('LBL_PRODUCT_FIELD_INFO',$MODULE)}</small></label>
                                            </div>
                                        </div>
                                    </dic>
                                </div>
                                <div class="tab-pane" id="pdfContentHeaderFooter">
                                    <dic class="edit-template-content col-lg-4" style="position:fixed;z-index:1000;">
                                        {********************************************* Header/Footer *************************************************}
                                        <div id="headerfooter_div">
                                            <br>
                                            {* pdf header variables*}
                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('LBL_HEADER_FOOTER_VARIABLES',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <select name="header_var" id="header_var" class="select2 form-control">
                                                            {html_options  options=$HEAD_FOOT_VARS selected=""}
                                                        </select>
                                                        <div class="input-group-btn">
                                                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="header_var" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {* don't display header on first page *}
                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('LBL_DISPLAY_HEADER',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <b>{vtranslate('LBL_ALL_PAGES',$MODULE)}</b>&nbsp;<input type="checkbox" id="dh_allid" name="dh_all" onclick="PDFMaker_EditFreeJs.hf_checkboxes_changed(this, 'header');" {$DH_ALL}/>
                                                        &nbsp;&nbsp;
                                                        {vtranslate('LBL_FIRST_PAGE',$MODULE)}&nbsp;<input type="checkbox" id="dh_firstid" name="dh_first" onclick="PDFMaker_EditFreeJs.hf_checkboxes_changed(this, 'header');" {$DH_FIRST}/>
                                                        &nbsp;&nbsp;
                                                        {vtranslate('LBL_OTHER_PAGES',$MODULE)}&nbsp;<input type="checkbox" id="dh_otherid" name="dh_other" onclick="PDFMaker_EditFreeJs.hf_checkboxes_changed(this, 'header');" {$DH_OTHER}/>&nbsp;
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('LBL_DISPLAY_FOOTER',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <b>{vtranslate('LBL_ALL_PAGES',$MODULE)}</b>&nbsp;<input type="checkbox" id="df_allid" name="df_all" onclick="PDFMaker_EditFreeJs.hf_checkboxes_changed(this, 'footer');" {$DF_ALL}/>
                                                        &nbsp;&nbsp;
                                                        {vtranslate('LBL_FIRST_PAGE',$MODULE)}&nbsp;<input type="checkbox" id="df_firstid" name="df_first" onclick="PDFMaker_EditFreeJs.hf_checkboxes_changed(this, 'footer');" {$DF_FIRST}/>
                                                        &nbsp;&nbsp;
                                                        {vtranslate('LBL_OTHER_PAGES',$MODULE)}&nbsp;<input type="checkbox" id="df_otherid" name="df_other" onclick="PDFMaker_EditFreeJs.hf_checkboxes_changed(this, 'footer');" {$DF_OTHER}/>
                                                        &nbsp;&nbsp;
                                                        {vtranslate('LBL_LAST_PAGE',$MODULE)}&nbsp;<input type="checkbox" id="df_lastid" name="df_last" onclick="PDFMaker_EditFreeJs.hf_checkboxes_changed(this, 'footer');" {$DF_LAST}/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </dic>
                                </div>
                                <div class="tab-pane" id="editTabSettings">
                                    <div id="settings_div">
                                        <br>
                                        <div class="form-group">
                                            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                {vtranslate('LBL_DESCRIPTION',$MODULE)}:
                                            </label>
                                            <div class="controls col-sm-9">
                                                <input name="description" type="text" value="{$DESCRIPTION}" class="col-sm-12 form-control" tabindex="2">
                                            </div>
                                        </div>
                                        {* pdf format settings *}
                                        <div class="form-group">
                                            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                {vtranslate('LBL_PDF_FORMAT',$MODULE)}:
                                            </label>
                                            <div class="controls col-sm-9">
                                                <div class="input-group">
                                                    <select name="pdf_format" id="pdf_format" class="select2 col-sm-12" onchange="PDFMaker_EditFreeJs.CustomFormat();">
                                                        {html_options  options=$FORMATS selected=$SELECT_FORMAT}
                                                    </select>
                                                    <br>
                                                    <table class="table showInlineTable" id="custom_format_table" {if $SELECT_FORMAT neq 'Custom'}style="display:none"{/if}>
                                                        <tr>
                                                            <td align="right" nowrap>{vtranslate('LBL_WIDTH',$MODULE)}</td>
                                                            <td>
                                                                <input type="text" name="pdf_format_width" id="pdf_format_width" class="inputElement" value="{$CUSTOM_FORMAT.width}" style="width:50px">
                                                            </td>
                                                            <td align="right" nowrap>{vtranslate('LBL_HEIGHT',$MODULE)}</td>
                                                            <td>
                                                                <input type="text" name="pdf_format_height" id="pdf_format_height" class="inputElement" value="{$CUSTOM_FORMAT.height}" style="width:50px">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        {* pdf orientation settings *}
                                        <div class="form-group">
                                            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                {vtranslate('LBL_PDF_ORIENTATION',$MODULE)}:
                                            </label>
                                            <div class="controls col-sm-9">
                                                <div class="input-group">
                                                    <select name="pdf_orientation" id="pdf_orientation" class="select2 form-control">
                                                        {html_options  options=$ORIENTATIONS selected=$SELECT_ORIENTATION}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        {* ignored picklist values settings *}
                                        <div class="form-group">
                                            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                {vtranslate('LBL_IGNORE_PICKLIST_VALUES',$MODULE)}:
                                            </label>
                                            <div class="controls col-sm-9">
                                                <input type="text" name="ignore_picklist_values" value="{$IGNORE_PICKLIST_VALUES}" class="form-control"/>
                                            </div>
                                        </div>
                                        {* pdf margin settings *}
                                        {assign var=margin_input_width value='50px'}
                                        {assign var=margin_label_width value='50px'}
                                        <div class="form-group">
                                            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                {vtranslate('LBL_MARGINS',$MODULE)}:
                                            </label>
                                            <div class="controls col-sm-9">

                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-1" style="font-weight: normal">
                                                        {vtranslate('LBL_TOP',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-11">
                                                        <input type="text" name="margin_top" id="margin_top" class="inputElement" value="{$MARGINS.top}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditFreeJs.ControlNumber('margin_top', false);">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-1" style="font-weight: normal">
                                                        {vtranslate('LBL_BOTTOM',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-11">
                                                        <input type="text" name="margin_bottom" id="margin_bottom" class="inputElement" value="{$MARGINS.bottom}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditFreeJs.ControlNumber('margin_bottom', false);">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-1" style="font-weight: normal">
                                                        {vtranslate('LBL_LEFT',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-11">
                                                        <input type="text" name="margin_left"  id="margin_left" class="inputElement" value="{$MARGINS.left}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditFreeJs.ControlNumber('margin_left', false);">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-1" style="font-weight: normal">
                                                        {vtranslate('LBL_RIGHT',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-11">
                                                        <input type="text" name="margin_right" id="margin_right" class="inputElement" value="{$MARGINS.right}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditFreeJs.ControlNumber('margin_right', false);">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {* decimal settings *}
                                        <div class="form-group">
                                            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                {vtranslate('LBL_DECIMALS',$MODULE)}:
                                            </label>
                                            <div class="controls col-sm-9">

                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-4" style="font-weight: normal">
                                                        {vtranslate('LBL_DEC_POINT',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-8">
                                                        <input type="text" maxlength="2" name="dec_point" class="inputElement" value="{$DECIMALS.point}" style="width:{$margin_input_width}"/>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-4" style="font-weight: normal">
                                                        {vtranslate('LBL_DEC_DECIMALS',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-8">
                                                        <input type="text" maxlength="2" name="dec_decimals" class="inputElement" value="{$DECIMALS.decimals}" style="width:{$margin_input_width}"/>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-4" style="font-weight: normal">
                                                        {vtranslate('LBL_DEC_THOUSANDS',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-8">
                                                        <input type="text" maxlength="2" name="dec_thousands" class="inputElement" value="{$DECIMALS.thousands}" style="width:{$margin_input_width}"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                   {************************************** END OF TABS BLOCK *************************************}
                    <div class="middle-block col-xs-8">
                        <br>
                        <div id="ContentEditorTabs">
                            <ul class="nav nav-pills">
                                <li class="active" data-type="body">
                                    <a href="#body_div2" aria-expanded="false" data-toggle="tab">{vtranslate('LBL_BODY',$MODULE)}</a>
                                </li>
                                <li data-type="header">
                                    <a href="#header_div2" aria-expanded="false" data-toggle="tab">{vtranslate('LBL_HEADER_TAB',$MODULE)}</a>
                                </li>
                                <li data-type="footer">
                                    <a href="#footer_div2" aria-expanded="false" data-toggle="tab">{vtranslate('LBL_FOOTER_TAB',$MODULE)}</a>
                                </li>
                            </ul>
                        </div>
                        <br>
                        {*********************************************BODY DIV*************************************************}
                        <div class="tab-content ">
                            <div class="tab-pane active" id="body_div2">
                                <textarea name="body" id="body" style="width:90%;height:700px" class=small tabindex="5">{$BODY}</textarea>
                            </div>
                            {*********************************************Header DIV*************************************************}
                            <div class="tab-pane" id="header_div2">
                                <textarea name="header_body" id="header_body" style="width:90%;height:200px" class="small">{$HEADER}</textarea>
                            </div>
                            {*********************************************Footer DIV*************************************************}
                            <div class="tab-pane" id="footer_div2">
                                <textarea name="footer_body" id="footer_body" style="width:90%;height:200px" class="small">{$FOOTER}</textarea>
                            </div>
                        </div>
                        <br>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-overlay-footer row-fluid">
            <div class="textAlignCenter ">
                <button class="btn" type="submit" onclick="document.EditView.redirect.value = 'false';" ><strong>{vtranslate('LBL_APPLY',$MODULE)}</strong></button>&nbsp;&nbsp;
                <button class="btn btn-success" type="submit" ><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                {if $smarty.request.return_view neq ''}
                    <a class="cancelLink" type="reset" onclick="window.location.href = 'index.php?module={if $smarty.request.return_module neq ''}{$smarty.request.return_module}{else}PDFMaker{/if}&view={$smarty.request.return_view}{if $smarty.request.templateid neq ""  && $smarty.request.return_view neq "List"}&templateid={$smarty.request.templateid}{/if}';">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                {else}
                    <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                {/if}            			
            </div>
            <div align="center" class="small" style="color: rgb(153, 153, 153);">{vtranslate('PDF_MAKER',$MODULE)} {$VERSION} {vtranslate('COPYRIGHT',$MODULE)}</div>
        </div>
    </form>
</div>
<div id="company_stamp_signature_content" class="hide">
    {$COMPANY_STAMP_SIGNATURE}
</div>
<div id="companylogo_content" class="hide">
    {$COMPANYLOGO}
</div>
<div id="company_header_signature_content" class="hide">
    {$COMPANY_HEADER_SIGNATURE}
</div>
<div id="vatblock_table_content" class="hide">
    {$VATBLOCK_TABLE}
</div>



<script type="text/javascript">

    var selectedTab = 'properties';
    var selectedTab2 = 'body';
    var module_blocks = new Array();
 
    var selected_module = '{$SELECTMODULE}';

    var constructedOptionValue;
    var constructedOptionName;

    jQuery(document).ready(function() {

        jQuery.fn.scrollBottom = function() {
            return jQuery(document).height() - this.scrollTop() - this.height();
        };

        var $el = jQuery('.edit-template-content');
        var $window = jQuery(window);
        var top = 127;

        $window.bind("scroll resize", function() {

            var gap = $window.height() - $el.height() - 20;
            var scrollTop = $window.scrollTop();

            if (scrollTop < top - 125) {
                $el.css({
                    top: (top - scrollTop) + "px",
                    bottom: "auto"
                });
            } else {
                $el.css({
                    top: top  + "px",
                    bottom: "auto"
                });
            }
        }).scroll();
    });

</script>

