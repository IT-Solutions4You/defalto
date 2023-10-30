{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}

<div class="modal-body " style="padding:0px;">
    <ul class="nav nav-pills" style="margin-bottom:0px;">
        <li class="active" id="properties_tab" onclick="PDFMaker_EditJs.showHideTab('properties');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_PROPERTIES_TAB','PDFMaker')}</a></li>
        <li id="company_tab" onclick="PDFMaker_EditJs.showHideTab('company');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_OTHER_INFO','PDFMaker')}</a></li>
        <li id="labels_tab" onclick="PDFMaker_EditJs.showHideTab('labels');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_LABELS','PDFMaker')}</a></li>
        <li id="products_tab" onclick="PDFMaker_EditJs.showHideTab('products');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_ARTICLE','PDFMaker')}</a></li>
        <li id="headerfooter_tab" onclick="PDFMaker_EditJs.showHideTab('headerfooter');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_HEADER_TAB','PDFMaker')} / {vtranslate('LBL_FOOTER_TAB','PDFMaker')}</a></li>
        <li id="settings_tab" onclick="PDFMaker_EditJs.showHideTab('settings');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_SETTINGS_TAB','PDFMaker')}</a></li>
        <li id="sharing_tab" onclick="PDFMaker_EditJs.showHideTab('sharing');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_SHARING_TAB','PDFMaker')}</a></li>
        {if $TYPE eq "professional"}
            <li id="display_tab" onclick="PDFMaker_EditJs.showHideTab('display');" {if $SELECTMODULE eq ""}style="display:none"{/if}><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_DISPLAY_TAB','PDFMaker')}</a></li>
        {/if}
    </ul>
</div>

{********************************************* PROPERTIES DIV*************************************************}
<table class="table table-bordered blockContainer ">
    <tbody id="properties_div">
    {* pdf module name and description *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px"><span class="redColor">*</span>{vtranslate('LBL_PDF_NAME','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3"><input name="filename" id="filename" type="text" value="{$FILENAME}" class="detailedViewTextBox" tabindex="1">&nbsp;

            <span class="muted">&nbsp;&nbsp;&nbsp;{vtranslate('LBL_DESCRIPTION','PDFMaker')}:&nbsp;</span>

            <span class="small cellText">
                            <input name="description" type="text" value="{$DESCRIPTION}" class="detailedViewTextBox span5" tabindex="2">
                        </span>
        </td>
    </tr>
    {* pdf source module and its available fields *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{if $TEMPLATEID eq ""}<span class="redColor">*</span>{/if}{vtranslate('LBL_MODULENAMES','PDFMaker')}:</label></td>
        <td class="fieldValue row-fluid" colspan="3">
            <select name="modulename" id="modulename" class="chzn-select span4">
                {if $TEMPLATEID neq "" || $SELECTMODULE neq ""}
                    {html_options  options=$MODULENAMES selected=$SELECTMODULE}
                {else}
                    {html_options  options=$MODULENAMES}
                {/if}
            </select>
            &nbsp;&nbsp;
            <select name="modulefields" id="modulefields" class="chzn-select span5">
                {if $TEMPLATEID eq "" && $SELECTMODULE eq ""}
                    <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD','PDFMaker')}</option>
                {else}
                    {html_options  options=$SELECT_MODULE_FIELD}
                {/if}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('modulefields');" >{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
        </td>
    </tr>
    {* related modules and its fields *}
    <tr id="body_variables">
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_RELATED_MODULES','PDFMaker')}:</label></td>
        <td class="fieldValue row-fluid" colspan="3">

            <select name="relatedmodulesorce" id="relatedmodulesorce" class="chzn-select span4">
                <option value="">{vtranslate('LBL_SELECT_MODULE','PDFMaker')}</option>
                {foreach item=RelMod from=$RELATED_MODULES}
                    <option value="{$RelMod.0}" data-module="{$RelMod.3}">{$RelMod.1} ({$RelMod.2})</option>
                {/foreach}
            </select>
            &nbsp;&nbsp;

            <select name="relatedmodulefields" id="relatedmodulefields" class="chzn-select span5">
                <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD','PDFMaker')}</option>
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('relatedmodulefields');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
        </td>
    </tr>
    {* related bloc tpl *}
    <tr id="related_block_tpl_row">
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_RELATED_BLOCK_TPL','PDFMaker')}:</label></td>
        <td class="fieldValue row-fluid" colspan="3">
            <select name="related_block" id="related_block" class="chzn-select span4" >
                {html_options options=$RELATED_BLOCKS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="PDFMaker_EditJs.InsertRelatedBlock();">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
            <button type="button" class="btn addButton marginLeftZero" onclick="PDFMaker_EditJs.CreateRelatedBlock();"><i class="icon-plus icon-white"></i>&nbsp;<strong>{vtranslate('LBL_CREATE')}</strong></button>
            <button type="button" class="btn marginLeftZero" onclick="PDFMaker_EditJs.EditRelatedBlock();">{vtranslate('LBL_EDIT')}</button>
            <button class="btn btn-danger marginLeftZero" class="crmButton small delete" onclick="PDFMaker_EditJs.DeleteRelatedBlock();">{vtranslate('LBL_DELETE')}</button>
        </td>
    </tr>

    <tr id="listview_block_tpl_row">
        <td class="fieldLabel">
            <label class="muted pull-right marginRight10px"><input type="checkbox" name="is_listview" id="isListViewTmpl" {$IS_LISTVIEW_CHECKED} onclick="PDFMaker_EditJs.isLvTmplClicked();" title="{vtranslate('LBL_LISTVIEW_TEMPLATE','PDFMaker')}" />
                {vtranslate('LBL_LISTVIEWBLOCK','PDFMaker')}:</label>
        </td>
        <td class="fieldValue" colspan="3">
                        <span>
                        <select name="listviewblocktpl" id="listviewblocktpl" class="chzn-select">
                            {html_options  options=$LISTVIEW_BLOCK_TPL}
                        </select>
                        </span>
            <button type="button" id="listviewblocktpl_butt" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('listviewblocktpl');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
        </td>
    </tr>
    </tbody>
    {********************************************* Labels *************************************************}
    <tbody style="display:none;" id="labels_div">
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_GLOBAL_LANG','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="global_lang" id="global_lang" class="chzn-select span9">
                {html_options  options=$GLOBAL_LANG_LABELS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('global_lang');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
        </td>
    </tr>
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_MODULE_LANG','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="module_lang" id="module_lang" class="chzn-select span9">
                {html_options  options=$MODULE_LANG_LABELS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('module_lang');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
        </td>
    </tr>
    {if $TYPE eq "professional"}
        <tr>
            <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_CUSTOM_LABELS','PDFMaker')}:</label></td>
            <td class="fieldValue" colspan="3">
                <select name="custom_lang" id="custom_lang" class="chzn-select span9">
                    {html_options  options=$CUSTOM_LANG_LABELS}
                </select>
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('custom_lang');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
            </td>
        </tr>
    {/if}
    </tbody>
    {********************************************* Company and User information DIV *************************************************}
    <tbody style="display:none;" id="company_div">
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_COMPANY_USER_INFO','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="acc_info_type" id="acc_info_type" class="chzn-select span4" onChange="PDFMaker_EditJs.change_acc_info(this)">
                {html_options  options=$CUI_BLOCKS}
            </select>
            <div id="acc_info_div" class="au_info_div" style="display:inline;">
                <select name="acc_info" id="acc_info" class="chzn-select span5">
                    {html_options  options=$ACCOUNTINFORMATIONS}
                </select>
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('acc_info');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
            </div>
            <div id="user_info_div" class="au_info_div" style="display:none;">
                <select name="user_info" id="user_info" class="chzn-select span5">
                    {html_options  options=$USERINFORMATIONS['a']}
                </select>
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('user_info');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
            </div>
            <div id="logged_user_info_div" class="au_info_div" style="display:none;">
                <select name="logged_user_info" id="logged_user_info" class="chzn-select span5">
                    {html_options  options=$USERINFORMATIONS['l']}
                </select>
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('logged_user_info');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
            </div>
            <div id="modifiedby_user_info_div" class="au_info_div" style="display:none;">
                <select name="modifiedby_user_info" id="modifiedby_user_info" class="chzn-select span5">
                    {html_options  options=$USERINFORMATIONS['m']}
                </select>
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('modifiedby_user_info');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
            </div>
            <div id="smcreator_user_info_div" class="au_info_div" style="display:none;">
                <select name="smcreator_user_info" id="smcreator_user_info" class="chzn-select span5">
                    {html_options  options=$USERINFORMATIONS['c']}
                </select>
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('smcreator_user_info');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
            </div>
        </td>
    </tr>
    {if $MULTICOMPANYINFORMATIONS neq ''}
        <tr>
            <td class="fieldLabel"><label class="muted pull-right marginRight10px">{$LBL_MULTICOMPANY}:</label></td>
            <td class="fieldValue" colspan="3">
                <select name="multicomapny" id="multicomapny" class="chzn-select span4">
                    {html_options  options=$MULTICOMPANYINFORMATIONS}
                </select>
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('multicomapny');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
            </td>
        </tr>
    {/if}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('TERMS_AND_CONDITIONS','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="invterandcon" id="invterandcon" class="chzn-select span4">
                {html_options  options=$INVENTORYTERMSANDCONDITIONS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('invterandcon');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
        </td>
    </tr>
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_CURRENT_DATE','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="dateval" id="dateval" class="chzn-select span4">
                {html_options  options=$DATE_VARS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('dateval');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
        </td>
    </tr>
    {***** BARCODES *****}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_BARCODES','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="barcodeval" id="barcodeval" class="chzn-select span4">
                <optgroup label="{vtranslate('LBL_BARCODES_TYPE1','PDFMaker')}">
                    <option value="EAN13">EAN13</option>
                    <option value="ISBN">ISBN</option>
                    <option value="ISSN">ISSN</option>
                </optgroup>

                <optgroup label="{vtranslate('LBL_BARCODES_TYPE2','PDFMaker')}">
                    <option value="UPCA">UPCA</option>
                    <option value="UPCE">UPCE</option>
                    <option value="EAN8">EAN8</option>
                </optgroup>

                <optgroup label="{vtranslate('LBL_BARCODES_TYPE3','PDFMaker')}">
                    <option value="EAN2">EAN2</option>
                    <option value="EAN5">EAN5</option>
                    <option value="EAN13P2">EAN13P2</option>
                    <option value="ISBNP2">ISBNP2</option>
                    <option value="ISSNP2">ISSNP2</option>
                    <option value="UPCAP2">UPCAP2</option>
                    <option value="UPCEP2">UPCEP2</option>
                    <option value="EAN8P2">EAN8P2</option>
                    <option value="EAN13P5">EAN13P5</option>
                    <option value="ISBNP5">ISBNP5</option>
                    <option value="ISSNP5">ISSNP5</option>
                    <option value="UPCAP5">UPCAP5</option>
                    <option value="UPCEP5">UPCEP5</option>
                    <option value="EAN8P5">EAN8P5</option>
                </optgroup>

                <optgroup label="{vtranslate('LBL_BARCODES_TYPE4','PDFMaker')}">
                    <option value="IMB">IMB</option>
                    <option value="RM4SCC">RM4SCC</option>
                    <option value="KIX">KIX</option>
                    <option value="POSTNET">POSTNET</option>
                    <option value="PLANET">PLANET</option>
                </optgroup>

                <optgroup label="{vtranslate('LBL_BARCODES_TYPE5','PDFMaker')}">
                    <option value="C128A">C128A</option>
                    <option value="C128B">C128B</option>
                    <option value="C128C">C128C</option>
                    <option value="EAN128C">EAN128C</option>
                    <option value="C39">C39</option>
                    <option value="C39+">C39+</option>
                    <option value="C39E">C39E</option>
                    <option value="C39E+">C39E+</option>
                    <option value="S25">S25</option>
                    <option value="S25+">S25+</option>
                    <option value="I25">I25</option>
                    <option value="I25+">I25+</option>
                    <option value="I25B">I25B</option>
                    <option value="I25B+">I25B+</option>
                    <option value="C93">C93</option>
                    <option value="MSI">MSI</option>
                    <option value="MSI+">MSI+</option>
                    <option value="CODABAR">CODABAR</option>
                    <option value="CODE11">CODE11</option>
                </optgroup>

                <optgroup label="{vtranslate('LBL_QRCODE','PDFMaker')}">
                    <option value="QR">QR</option>
                </optgroup>
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('barcodeval');">{vtranslate('LBL_INSERT_BARCODE_TO_TEXT','PDFMaker')}</button>&nbsp;&nbsp;<a href="index.php?module=PDFMaker&view=IndexAjax&mode=showBarcodes" target="_new"><i class="icon-info-sign"></i></a>
        </td>
    </tr>
    {************************************ Custom Functions *******************************************}
    {if $TYPE eq "professional"}
        <tr>
            <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('CUSTOM_FUNCTIONS','PDFMaker')}:</label></td>
            <td class="fieldValue" colspan="3">
                <select name="customfunction" id="customfunction" class="chzn-select span4">
                    {html_options options=$CUSTOM_FUNCTIONS}
                </select>
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('customfunction');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
            </td>
        </tr>
    {/if}
    </tbody>
    {********************************************* Header/Footer *************************************************}
    <tbody style="display:none;" id="headerfooter_div">
    {* pdf header variables*}
    <tr id="header_variables">
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_HEADER_FOOTER_VARIABLES','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="header_var" id="header_var" class="classname">
                {html_options  options=$HEAD_FOOT_VARS selected=""}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('header_var');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
        </td>
    </tr>
    {* don't display header on first page *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_DISPLAY_HEADER','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <b>{vtranslate('LBL_ALL_PAGES','PDFMaker')}</b>&nbsp;<input type="checkbox" id="dh_allid" name="dh_all" onclick="PDFMaker_EditJs.hf_checkboxes_changed(this, 'header');" {$DH_ALL}/>
            &nbsp;&nbsp;
            {vtranslate('LBL_FIRST_PAGE','PDFMaker')}&nbsp;<input type="checkbox" id="dh_firstid" name="dh_first" onclick="PDFMaker_EditJs.hf_checkboxes_changed(this, 'header');" {$DH_FIRST}/>
            &nbsp;&nbsp;
            {vtranslate('LBL_OTHER_PAGES','PDFMaker')}&nbsp;<input type="checkbox" id="dh_otherid" name="dh_other" onclick="PDFMaker_EditJs.hf_checkboxes_changed(this, 'header');" {$DH_OTHER}/>
            &nbsp;&nbsp;
        </td>
    </tr>
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_DISPLAY_FOOTER','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <b>{vtranslate('LBL_ALL_PAGES','PDFMaker')}</b>&nbsp;<input type="checkbox" id="df_allid" name="df_all" onclick="PDFMaker_EditJs.hf_checkboxes_changed(this, 'footer');" {$DF_ALL}/>
            &nbsp;&nbsp;
            {vtranslate('LBL_FIRST_PAGE','PDFMaker')}&nbsp;<input type="checkbox" id="df_firstid" name="df_first" onclick="PDFMaker_EditJs.hf_checkboxes_changed(this, 'footer');" {$DF_FIRST}/>
            &nbsp;&nbsp;
            {vtranslate('LBL_OTHER_PAGES','PDFMaker')}&nbsp;<input type="checkbox" id="df_otherid" name="df_other" onclick="PDFMaker_EditJs.hf_checkboxes_changed(this, 'footer');" {$DF_OTHER}/>
            &nbsp;&nbsp;
            {vtranslate('LBL_LAST_PAGE','PDFMaker')}&nbsp;<input type="checkbox" id="df_lastid" name="df_last" onclick="PDFMaker_EditJs.hf_checkboxes_changed(this, 'footer');" {$DF_LAST}/>
            &nbsp;&nbsp;
        </td>
    </tr>
    </tbody>

    {*********************************************Products bloc DIV*************************************************}
    <tbody style="display:none;" id="products_div">
    {* product bloc tpl which is the same as in main Properties tab*}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_PRODUCT_BLOC_TPL','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="productbloctpl2" id="productbloctpl2" class="classname">
                {html_options  options=$PRODUCT_BLOC_TPL}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('productbloctpl2');"/>{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
        </td>
    </tr>
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_ARTICLE','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="articelvar" id="articelvar" class="classname">
                {html_options  options=$ARTICLE_STRINGS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('articelvar');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
        </td>
    </tr>
    {* insert products & services fields into text *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">*{vtranslate('LBL_PRODUCTS_AVLBL','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="psfields" id="psfields" class="classname">
                {html_options  options=$SELECT_PRODUCT_FIELD}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('psfields');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
        </td>
    </tr>
    {* products fields *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">*{vtranslate('LBL_PRODUCTS_FIELDS','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="productfields" id="productfields" class="classname">
                {html_options  options=$PRODUCTS_FIELDS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('productfields');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
        </td>
    </tr>
    {* services fields *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">*{vtranslate('LBL_SERVICES_FIELDS','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="servicesfields" id="servicesfields" class="classname">
                {html_options  options=$SERVICES_FIELDS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('servicesfields');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
        </td>
    </tr>
    <tr>
        <td class="fieldLabel" colspan="4"><label class="muted marginRight10px"><small>{vtranslate('LBL_PRODUCT_FIELD_INFO','PDFMaker')}</small></label></td>
    </tr>
    </tbody>

    {********************************************* Settings DIV *************************************************}
    <tbody style="display:none;" id="settings_div">
    {* file name settings *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_FILENAME','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <input type="text" name="nameOfFile" value="{$NAME_OF_FILE}" id="nameOfFile" class="detailedViewTextBox" style="width:50%;"/>
            <select name="filename_fields" id="filename_fields" class="chzn-select span6" onchange="PDFMaker_EditJs.insertFieldIntoFilename(this.value);">
                <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD','PDFMaker')}</option>
                <optgroup label="{vtranslate('LBL_COMMON_FILEINFO','PDFMaker')}">
                    {html_options  options=$FILENAME_FIELDS}
                </optgroup>
                {if $TEMPLATEID neq "" || $SELECTMODULE neq ""}
                    {html_options  options=$SELECT_MODULE_FIELD_FILENAME}
                {/if}
            </select>
        </td>
    </tr>
    {* pdf format settings *}
    <tr>
        <td class="fieldLabel">
            <label class="muted pull-right marginRight10px">{vtranslate('LBL_PDF_FORMAT','PDFMaker')}:</label>
        </td>
        <td class="fieldValue" colspan="3">
            <table style="padding:0px; margin:0px;" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <select name="pdf_format" id="pdf_format" class="chzn-select" onchange="PDFMaker_EditJs.CustomFormat();">
                            {html_options  options=$FORMATS selected=$SELECT_FORMAT}
                        </select>
                    </td>
                    <td style="padding:0">
                        <table class="table showInlineTable" id="custom_format_table" {if $SELECT_FORMAT neq 'Custom'}style="display:none"{/if}>
                            <tr>
                                <td align="right" nowrap>{vtranslate('LBL_WIDTH','PDFMaker')}</td>
                                <td>
                                    <input type="text" name="pdf_format_width" id="pdf_format_width" class="detailedViewTextBox" value="{$CUSTOM_FORMAT.width}" style="width:50px">
                                </td>
                                <td align="right" nowrap>{vtranslate('LBL_HEIGHT','PDFMaker')}</td>
                                <td>
                                    <input type="text" name="pdf_format_height" id="pdf_format_height" class="detailedViewTextBox" value="{$CUSTOM_FORMAT.height}" style="width:50px">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
    {* pdf orientation settings *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_PDF_ORIENTATION','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="pdf_orientation" id="pdf_orientation" class="chzn-select">
                {html_options  options=$ORIENTATIONS selected=$SELECT_ORIENTATION}
            </select>
        </td>
    </tr>
    {* ignored picklist values settings *}
    <tr>
        <td class="fieldLabel" title="{vtranslate('LBL_IGNORE_PICKLIST_VALUES_DESC','PDFMaker')}"><label class="muted pull-right marginRight10px">{vtranslate('LBL_IGNORE_PICKLIST_VALUES','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3" title="{vtranslate('LBL_IGNORE_PICKLIST_VALUES_DESC','PDFMaker')}"><input type="text" name="ignore_picklist_values" value="{$IGNORE_PICKLIST_VALUES}" class="detailedViewTextBox"/></td>
    </tr>
    {* pdf margin settings *}
    {assign var=margin_input_width value='50px'}
    {assign var=margin_label_width value='50px'}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_MARGINS','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <table>
                <tr>
                    <td align="right" nowrap>{vtranslate('LBL_TOP','PDFMaker')}</td>
                    <td>
                        <input type="text" name="margin_top" id="margin_top" class="detailedViewTextBox" value="{$MARGINS.top}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditJs.ControlNumber('margin_top', false);">
                    </td>
                    <td align="right" nowrap>{vtranslate('LBL_BOTTOM','PDFMaker')}</td>
                    <td>
                        <input type="text" name="margin_bottom" id="margin_bottom" class="detailedViewTextBox" value="{$MARGINS.bottom}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditJs.ControlNumber('margin_bottom', false);">
                    </td>
                    <td align="right" nowrap>{vtranslate('LBL_LEFT','PDFMaker')}</td>
                    <td>
                        <input type="text" name="margin_left"  id="margin_left" class="detailedViewTextBox" value="{$MARGINS.left}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditJs.ControlNumber('margin_left', false);">
                    </td>
                    <td align="right" nowrap>{vtranslate('LBL_RIGHT','PDFMaker')}</td>
                    <td>
                        <input type="text" name="margin_right" id="margin_right" class="detailedViewTextBox" value="{$MARGINS.right}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditJs.ControlNumber('margin_right', false);">
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    {* decimal settings *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_DECIMALS','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <table>
                <tr>
                    <td align="right" nowrap>{vtranslate('LBL_DEC_POINT','PDFMaker')}</td>
                    <td><input type="text" maxlength="2" name="dec_point" class="detailedViewTextBox" value="{$DECIMALS.point}" style="width:{$margin_input_width}"/></td>

                    <td align="right" nowrap>{vtranslate('LBL_DEC_DECIMALS','PDFMaker')}</td>
                    <td><input type="text" maxlength="2" name="dec_decimals" class="detailedViewTextBox" value="{$DECIMALS.decimals}" style="width:{$margin_input_width}"/></td>

                    <td align="right" nowrap>{vtranslate('LBL_DEC_THOUSANDS','PDFMaker')}</td>
                    <td><input type="text" maxlength="2" name="dec_thousands"  class="detailedViewTextBox" value="{$DECIMALS.thousands}" style="width:{$margin_input_width}"/></td>
                </tr>
            </table>
        </td>
    </tr>
    {* status settings *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_STATUS','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="is_active" id="is_active" class="classname" onchange="PDFMaker_EditJs.templateActiveChanged(this);">
                {html_options options=$STATUS selected=$IS_ACTIVE}
            </select>
        </td>
    </tr>
    {* is default settings *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_SETASDEFAULT','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            {vtranslate('LBL_FOR_DV','PDFMaker')}&nbsp;&nbsp;<input type="checkbox" id="is_default_dv" name="is_default_dv" {$IS_DEFAULT_DV_CHECKED}/>
            &nbsp;&nbsp;
            {vtranslate('LBL_FOR_LV','PDFMaker')}&nbsp;&nbsp;<input type="checkbox" id="is_default_lv" name="is_default_lv" {$IS_DEFAULT_LV_CHECKED}/>
            {* hidden variable for template order settings *}
            <input type="hidden" name="tmpl_order" value="{$ORDER}" />
        </td>
    </tr>
    {* is designated for customerportal *}
    <tr id="is_portal_row" {if $SELECTMODULE neq "Invoice" && $SELECTMODULE neq "Quotes"}style="display: none;"{/if}>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_SETFORPORTAL','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <input type="checkbox" id="is_portal" name="is_portal" {$IS_PORTAL_CHECKED} onclick="return PDFMaker_EditJs.ConfirmIsPortal(this);"/>
        </td>
    </tr>
    </tbody>
    {********************************************* Sharing DIV *************************************************}
    <tbody style="display:none;" id="sharing_div">
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_TEMPLATE_OWNER','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="template_owner" id="template_owner" class="classname">
                {html_options  options=$TEMPLATE_OWNERS selected=$TEMPLATE_OWNER}
            </select>
        </td>
    </tr>
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_SHARING_TAB','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="sharing" id="sharing" class="classname" onchange="PDFMaker_EditJs.sharing_changed();">
                {html_options options=$SHARINGTYPES selected=$SHARINGTYPE}
            </select>

            <div id="sharing_share_div" style="display:none; border-top:2px dotted #DADADA; margin-top:10px; width:100%;">
                <table width="100%"  border="0" align="center" cellpadding="5" cellspacing="0">
                    <tr>
                        <td width="40%" valign=top class="cellBottomDotLinePlain small"><strong>{vtranslate('LBL_MEMBER_AVLBL','PDFMaker')}</strong></td>
                        <td width="10%">&nbsp;</td>
                        <td width="40%" class="cellBottomDotLinePlain small"><strong>{vtranslate('LBL_MEMBER_SELECTED','PDFMaker')}</strong></td>
                    </tr>
                    <tr>
                        <td valign=top class="small">
                            {vtranslate('LBL_ENTITY','PDFMaker')}:&nbsp;
                            <select id="sharingMemberType" name="sharingMemberType" class="small" onchange="PDFMaker_EditJs.showSharingMemberTypes()">
                                <option value="groups" selected>{$APP.LBL_GROUPS}</option>
                                <option value="roles">{vtranslate('LBL_ROLES','PDFMaker')}</option>
                                <option value="rs">{vtranslate('LBL_ROLES_SUBORDINATES','PDFMaker')}</option>
                                <option value="users">{$APP.LBL_USERS}</option>
                            </select>
                            <input type="hidden" name="sharingFindStr" id="sharingFindStr">&nbsp;
                        </td>
                        <td width="50">&nbsp;</td>
                        <td class="small">&nbsp;</td>
                    </tr>
                    <tr class="small">
                        <td valign=top>{vtranslate('LBL_MEMBER_OF','PDFMaker')} {vtranslate('LBL_ENTITY','PDFMaker')}<br>
                            <select id="sharingAvailList" name="sharingAvailList" multiple size="10" class="small crmFormList"></select>
                        </td>
                        <td width="50">
                            <div align="center">
                                <input type="button" name="sharingAddButt" value="&nbsp;&rsaquo;&rsaquo;&nbsp;" onClick="PDFMaker_EditJs.sharingAddColumn()" class="crmButton small"/><br /><br />
                                <input type="button" name="sharingDelButt" value="&nbsp;&lsaquo;&lsaquo;&nbsp;" onClick="PDFMaker_EditJs.sharingDelColumn()" class="crmButton small"/>
                            </div>
                        </td>
                        <td class="small" style="background-color:#ddFFdd" valign=top>{vtranslate('LBL_MEMBER_OF','PDFMaker')} &quot;{$GROUPNAME}&quot; <br>
                            <select id="sharingSelectedColumns" name="sharingSelectedColumns" multiple size="10" class="small crmFormList">
                                {foreach item=element from=$MEMBER}
                                    <option value="{$element.0}">{$element.1}</option>
                                {/foreach}
                            </select>
                            <input type="hidden" name="sharingSelectedColumnsString" id="sharingSelectedColumnsString" value="" />
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
    </tbody>
    {if $TYPE eq "professional"}
        {********************************************* Display DIV *************************************************}
        <tbody id="display_div">
        <tr>
            <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_DISPLAYED','PDFMaker')}:</label></td>
            <td class="fieldValue" colspan="3">
                <select id="displayedValue" name="displayedValue" class="small">
                    <option value="0" {if $PDF_TEMPLATE_RESULT.displayed neq "1"}selected{/if}>{vtranslate('LBL_YES','PDFMaker')}</option>
                    <option value="1" {if $PDF_TEMPLATE_RESULT.displayed eq "1"}selected{/if}>{vtranslate('LBL_NO','PDFMaker')}</option>
                </select>
                &nbsp;{vtranslate('LBL_IF','PDFMaker')}:
            </td>
        </tr>
        <tr>
            <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_CONDITIONS','PDFMaker')}:</label></td>
            <td class="fieldValue" colspan="3">
                <input type="hidden" name="display_conditions" id="advanced_filter" value='' />
                <div id="advanceFilterContainer" class="conditionsContainer">
                    {include file='AdvanceFilter.tpl'|@vtemplate_path RECORD_STRUCTURE=$RECORD_STRUCTURE}
                </div>
            </td>
        </tr>
        </tbody>
    {/if}
</table>