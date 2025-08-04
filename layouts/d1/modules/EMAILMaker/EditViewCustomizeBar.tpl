{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="modal-body " style="padding:0px;">
    <ul class="nav nav-pills" style="margin-bottom:0px;">
        <li class="active" id="properties_tab" onclick="EMAILMaker_EditJs.showHideTab('properties');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_PROPERTIES_TAB','EMAILMaker')}</a></li>
        <li id="company_tab" onclick="EMAILMaker_EditJs.showHideTab('company');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_OTHER_INFO','EMAILMaker')}</a></li>
        <li id="labels_tab" onclick="EMAILMaker_EditJs.showHideTab('labels');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_LABELS','EMAILMaker')}</a></li>
        <li id="products_tab" onclick="EMAILMaker_EditJs.showHideTab('products');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_ARTICLE','EMAILMaker')}</a></li>
        <li id="headerfooter_tab" onclick="EMAILMaker_EditJs.showHideTab('headerfooter');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_HEADER_TAB','EMAILMaker')} / {vtranslate('LBL_FOOTER_TAB','EMAILMaker')}</a></li>
        <li id="settings_tab" onclick="EMAILMaker_EditJs.showHideTab('settings');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_SETTINGS_TAB','EMAILMaker')}</a></li>
        <li id="sharing_tab" onclick="EMAILMaker_EditJs.showHideTab('sharing');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_SHARING_TAB','EMAILMaker')}</a></li>
        {if $TYPE eq "professional"}
            <li id="display_tab" onclick="EMAILMaker_EditJs.showHideTab('display');" {if $SELECTMODULE eq ""}style="display:none"{/if}><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_DISPLAY_TAB','EMAILMaker')}</a></li>
        {/if}
    </ul>
</div>
{********************************************* PROPERTIES DIV*************************************************}
<table class="table table-bordered blockContainer ">
    <tbody id="properties_div">
    {* pdf module name and description *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px"><span class="redColor">*</span>{vtranslate('LBL_EMAIL_NAME','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3"><input name="filename" id="filename" type="text" value="{$FILENAME}" class="detailedViewTextBox" tabindex="1">&nbsp;

            <span class="muted">&nbsp;&nbsp;&nbsp;{vtranslate('LBL_DESCRIPTION','EMAILMaker')}:&nbsp;</span>

            <span class="small cellText">
                            <input name="description" type="text" value="{$DESCRIPTION}" class="detailedViewTextBox span5" tabindex="2">
                        </span>
        </td>
    </tr>
    {* pdf source module and its available fields *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{if $TEMPLATEID eq ""}<span class="redColor">*</span>{/if}{vtranslate('LBL_MODULENAMES','EMAILMaker')}:</label></td>
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
                    <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD','EMAILMaker')}</option>
                {else}
                    {html_options  options=$SELECT_MODULE_FIELD}
                {/if}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('modulefields');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
        </td>
    </tr>
    {* related modules and its fields *}
    <tr id="body_variables">
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_RELATED_MODULES','EMAILMaker')}:</label></td>
        <td class="fieldValue row-fluid" colspan="3">
            <select name="relatedmodulesorce" id="relatedmodulesorce" class="chzn-select span4">
                <option value="">{vtranslate('LBL_SELECT_MODULE','EMAILMaker')}</option>
                {foreach item=RelMod from=$RELATED_MODULES}
                    <option value="{$RelMod.0}" data-module="{$RelMod.3}">{$RelMod.1} ({$RelMod.2})</option>
                {/foreach}
            </select>
            <select name="relatedmodulefields" id="relatedmodulefields" class="chzn-select span5">
                <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD','EMAILMaker')}</option>
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('relatedmodulefields');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
        </td>
    </tr>
    {* related bloc tpl *}
    <tr id="related_block_tpl_row">
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_RELATED_BLOCK_TPL','EMAILMaker')}:</label></td>
        <td class="fieldValue row-fluid" colspan="3">
            <select name="related_block" id="related_block" class="chzn-select span4">
                {html_options options=$RELATED_BLOCKS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="EMAILMaker_EditJs.InsertRelatedBlock();">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
            <button type="button" class="btn addButton marginLeftZero" onclick="EMAILMaker_EditJs.CreateRelatedBlock();"><i class="icon-plus icon-white"></i>&nbsp;<strong>{vtranslate('LBL_CREATE')}</strong></button>
            <button type="button" class="btn marginLeftZero" onclick="EMAILMaker_EditJs.EditRelatedBlock();">{vtranslate('LBL_EDIT')}</button>
            <button class="btn btn-danger marginLeftZero" class="crmButton small delete" onclick="EMAILMaker_EditJs.DeleteRelatedBlock();">{vtranslate('LBL_DELETE')}</button>
        </td>
    </tr>
    <tr id="listview_block_tpl_row">
        <td class="fieldLabel">
            <label class="muted pull-right marginRight10px"><input type="checkbox" name="is_listview" id="isListViewTmpl" {$IS_LISTVIEW_CHECKED} onclick="EMAILMaker_EditJs.isLvTmplClicked();" title="{vtranslate('LBL_LISTVIEW_TEMPLATE','EMAILMaker')}"/>
                {vtranslate('LBL_LISTVIEWBLOCK','EMAILMaker')}:</label>
        </td>
        <td class="fieldValue" colspan="3">
                        <span>
                        <select name="listviewblocktpl" id="listviewblocktpl" class="chzn-select">
                            {html_options  options=$LISTVIEW_BLOCK_TPL}
                        </select>
                        </span>
            <button type="button" id="listviewblocktpl_butt" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('listviewblocktpl');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
        </td>
    </tr>
    </tbody>
    {********************************************* Labels *************************************************}
    <tbody style="display:none;" id="labels_div">
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_GLOBAL_LANG','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="global_lang" id="global_lang" class="chzn-select span9">
                {html_options  options=$GLOBAL_LANG_LABELS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('global_lang');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
        </td>
    </tr>
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_MODULE_LANG','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="module_lang" id="module_lang" class="chzn-select span9">
                {html_options  options=$MODULE_LANG_LABELS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('module_lang');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
        </td>
    </tr>
    {if $TYPE eq "professional"}
        <tr>
            <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_CUSTOM_LABELS','EMAILMaker')}:</label></td>
            <td class="fieldValue" colspan="3">
                <select name="custom_lang" id="custom_lang" class="chzn-select span9">
                    {html_options  options=$CUSTOM_LANG_LABELS}
                </select>
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('custom_lang');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
            </td>
        </tr>
    {/if}
    </tbody>
    {********************************************* Company and User information DIV *************************************************}
    <tbody style="display:none;" id="company_div">
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_COMPANY_USER_INFO','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="acc_info_type" id="acc_info_type" class="chzn-select span4" onChange="EMAILMaker_EditJs.change_acc_info(this)">
                {html_options  options=$CUI_BLOCKS}
            </select>
            <div id="acc_info_div" class="au_info_div" style="display:inline;">
                <select name="acc_info" id="acc_info" class="chzn-select span5">
                    {html_options  options=$ACCOUNTINFORMATIONS}
                </select>
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('acc_info');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
            </div>
            <div id="user_info_div" class="au_info_div" style="display:none;">
                <select name="user_info" id="user_info" class="chzn-select span5">
                    {html_options  options=$USERINFORMATIONS['a']}
                </select>
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('user_info');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
            </div>
            <div id="logged_user_info_div" class="au_info_div" style="display:none;">
                <select name="logged_user_info" id="logged_user_info" class="chzn-select span5">
                    {html_options  options=$USERINFORMATIONS['l']}
                </select>
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('logged_user_info');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
            </div>
            <div id="modifiedby_user_info_div" class="au_info_div" style="display:none;">
                <select name="modifiedby_user_info" id="modifiedby_user_info" class="chzn-select span5">
                    {html_options  options=$USERINFORMATIONS['m']}
                </select>
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('modifiedby_user_info');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
            </div>
            <div id="smcreator_user_info_div" class="au_info_div" style="display:none;">
                <select name="smcreator_user_info" id="smcreator_user_info" class="chzn-select span5">
                    {html_options  options=$USERINFORMATIONS['c']}
                </select>
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('smcreator_user_info');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
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
                <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('multicomapny');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
            </td>
        </tr>
    {/if}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('TERMS_AND_CONDITIONS','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="invterandcon" id="invterandcon" class="chzn-select span4">
                {html_options  options=$INVENTORYTERMSANDCONDITIONS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('invterandcon');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
        </td>
    </tr>
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_CURRENT_DATE','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="dateval" id="dateval" class="chzn-select span4">
                {html_options  options=$DATE_VARS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('dateval');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
        </td>
    </tr>
    {************************************ Custom Functions *******************************************}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('CUSTOM_FUNCTIONS','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="customfunction" id="customfunction" class="chzn-select span4">
                {html_options options=$CUSTOM_FUNCTIONS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('customfunction');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
        </td>
    </tr>
    </tbody>
    {********************************************* Header/Footer *************************************************}
    <tbody style="display:none;" id="headerfooter_div">
    {* pdf header variables*}
    <tr id="header_variables">
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_HEADER_FOOTER_VARIABLES','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="header_var" id="header_var" class="classname">
                {html_options  options=$HEAD_FOOT_VARS selected=""}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('header_var');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
        </td>
    </tr>
    {* don't display header on first page *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_DISPLAY_HEADER','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <b>{vtranslate('LBL_ALL_PAGES','EMAILMaker')}</b>&nbsp;<input type="checkbox" id="dh_allid" name="dh_all" onclick="EMAILMaker_EditJs.hf_checkboxes_changed(this, 'header');" {$DH_ALL}/>
            &nbsp;&nbsp;
            {vtranslate('LBL_FIRST_PAGE','EMAILMaker')}&nbsp;<input type="checkbox" id="dh_firstid" name="dh_first" onclick="EMAILMaker_EditJs.hf_checkboxes_changed(this, 'header');" {$DH_FIRST}/>
            &nbsp;&nbsp;
            {vtranslate('LBL_OTHER_PAGES','EMAILMaker')}&nbsp;<input type="checkbox" id="dh_otherid" name="dh_other" onclick="EMAILMaker_EditJs.hf_checkboxes_changed(this, 'header');" {$DH_OTHER}/>
            &nbsp;&nbsp;
        </td>
    </tr>
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_DISPLAY_FOOTER','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <b>{vtranslate('LBL_ALL_PAGES','EMAILMaker')}</b>&nbsp;<input type="checkbox" id="df_allid" name="df_all" onclick="EMAILMaker_EditJs.hf_checkboxes_changed(this, 'footer');" {$DF_ALL}/>
            &nbsp;&nbsp;
            {vtranslate('LBL_FIRST_PAGE','EMAILMaker')}&nbsp;<input type="checkbox" id="df_firstid" name="df_first" onclick="EMAILMaker_EditJs.hf_checkboxes_changed(this, 'footer');" {$DF_FIRST}/>
            &nbsp;&nbsp;
            {vtranslate('LBL_OTHER_PAGES','EMAILMaker')}&nbsp;<input type="checkbox" id="df_otherid" name="df_other" onclick="EMAILMaker_EditJs.hf_checkboxes_changed(this, 'footer');" {$DF_OTHER}/>
            &nbsp;&nbsp;
            {vtranslate('LBL_LAST_PAGE','EMAILMaker')}&nbsp;<input type="checkbox" id="df_lastid" name="df_last" onclick="EMAILMaker_EditJs.hf_checkboxes_changed(this, 'footer');" {$DF_LAST}/>
            &nbsp;&nbsp;
        </td>
    </tr>
    </tbody>

    {*********************************************Products bloc DIV*************************************************}
    <tbody style="display:none;" id="products_div">
    {* product bloc tpl which is the same as in main Properties tab*}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_PRODUCT_BLOC_TPL','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="productbloctpl2" id="productbloctpl2" class="classname">
                {html_options  options=$PRODUCT_BLOC_TPL}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('productbloctpl2');"/>
            {vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
        </td>
    </tr>
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_ARTICLE','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="articelvar" id="articelvar" class="classname">
                {html_options  options=$ARTICLE_STRINGS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('articelvar');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
        </td>
    </tr>
    {* insert products & services fields into text *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">*{vtranslate('LBL_PRODUCTS_AVLBL','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="psfields" id="psfields" class="classname">
                {html_options  options=$SELECT_PRODUCT_FIELD}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('psfields');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
        </td>
    </tr>
    {* products fields *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">*{vtranslate('LBL_PRODUCTS_FIELDS','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="productfields" id="productfields" class="classname">
                {html_options  options=$PRODUCTS_FIELDS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('productfields');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
        </td>
    </tr>
    {* services fields *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">*{vtranslate('LBL_SERVICES_FIELDS','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="servicesfields" id="servicesfields" class="classname">
                {html_options  options=$SERVICES_FIELDS}
            </select>
            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('servicesfields');">{vtranslate('LBL_INSERT_TO_TEXT','EMAILMaker')}</button>
        </td>
    </tr>
    <tr>
        <td class="fieldLabel" colspan="4"><label class="muted marginRight10px"><small>{vtranslate('LBL_PRODUCT_FIELD_INFO','EMAILMaker')}</small></label></td>
    </tr>
    </tbody>

    {********************************************* Settings DIV *************************************************}
    <tbody style="display:none;" id="settings_div">

    {* ignored picklist values settings *}
    <tr>
        <td class="fieldLabel" title="{vtranslate('LBL_IGNORE_PICKLIST_VALUES_DESC','PDFMaker')}"><label class="muted pull-right marginRight10px">{vtranslate('LBL_IGNORE_PICKLIST_VALUES','PDFMaker')}:</label></td>
        <td class="fieldValue" colspan="3" title="{vtranslate('LBL_IGNORE_PICKLIST_VALUES_DESC','PDFMaker')}"><input type="text" name="ignore_picklist_values" value="{$IGNORE_PICKLIST_VALUES}" class="detailedViewTextBox"/></td>
    </tr>
    {* decimal settings *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_DECIMALS','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <table>
                <tr>
                    <td align="right" nowrap>{vtranslate('LBL_DEC_POINT','EMAILMaker')}</td>
                    <td><input type="text" maxlength="2" name="dec_point" class="detailedViewTextBox" value="{$DECIMALS.point}" style="width:{$margin_input_width}"/></td>

                    <td align="right" nowrap>{vtranslate('LBL_DEC_DECIMALS','EMAILMaker')}</td>
                    <td><input type="text" maxlength="2" name="dec_decimals" class="detailedViewTextBox" value="{$DECIMALS.decimals}" style="width:{$margin_input_width}"/></td>

                    <td align="right" nowrap>{vtranslate('LBL_DEC_THOUSANDS','EMAILMaker')}</td>
                    <td><input type="text" maxlength="2" name="dec_thousands" class="detailedViewTextBox" value="{$DECIMALS.thousands}" style="width:{$margin_input_width}"/></td>
                </tr>
            </table>
        </td>
    </tr>
    {* status settings *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_STATUS','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="is_active" id="is_active" class="classname" onchange="EMAILMaker_EditJs.templateActiveChanged(this);">
                {html_options options=$STATUS selected=$IS_ACTIVE}
            </select>
        </td>
    </tr>
    {* is default settings *}
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_SETASDEFAULT','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            {vtranslate('LBL_FOR_DV','EMAILMaker')}&nbsp;&nbsp;<input type="checkbox" id="is_default_dv" name="is_default_dv" {$IS_DEFAULT_DV_CHECKED}/>
            &nbsp;&nbsp;
            {vtranslate('LBL_FOR_LV','EMAILMaker')}&nbsp;&nbsp;<input type="checkbox" id="is_default_lv" name="is_default_lv" {$IS_DEFAULT_LV_CHECKED}/>
            {* hidden variable for template order settings *}
            <input type="hidden" name="tmpl_order" value="{$ORDER}"/>
        </td>
    </tr>
    {* is designated for customerportal *}
    <tr id="is_portal_row" {if $SELECTMODULE neq "Invoice" && $SELECTMODULE neq "Quotes"}style="display: none;"{/if}>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_SETFORPORTAL','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <input type="checkbox" id="is_portal" name="is_portal" {$IS_PORTAL_CHECKED} onclick="return EMAILMaker_EditJs.ConfirmIsPortal(this);"/>
        </td>
    </tr>
    </tbody>
    {********************************************* Sharing DIV *************************************************}
    <tbody style="display:none;" id="sharing_div">
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_TEMPLATE_OWNER','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="template_owner" id="template_owner" class="classname">
                {html_options  options=$TEMPLATE_OWNERS selected=$TEMPLATE_OWNER}
            </select>
        </td>
    </tr>
    <tr>
        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_SHARING_TAB','EMAILMaker')}:</label></td>
        <td class="fieldValue" colspan="3">
            <select name="sharing" id="sharing" class="classname" onchange="EMAILMaker_EditJs.sharing_changed();">
                {html_options options=$SHARINGTYPES selected=$SHARINGTYPE}
            </select>

            <div id="sharing_share_div" style="display:none; border-top:2px dotted #DADADA; margin-top:10px; width:100%;">
                <table width="100%" border="0" align="center" cellpadding="5" cellspacing="0">
                    <tr>
                        <td width="40%" valign=top class="cellBottomDotLinePlain small"><strong>{vtranslate('LBL_MEMBER_AVLBL','EMAILMaker')}</strong></td>
                        <td width="10%">&nbsp;</td>
                        <td width="40%" class="cellBottomDotLinePlain small"><strong>{vtranslate('LBL_MEMBER_SELECTED','EMAILMaker')}</strong></td>
                    </tr>
                    <tr>
                        <td valign=top class="small">
                            {vtranslate('LBL_ENTITY','EMAILMaker')}:&nbsp;
                            <select id="sharingMemberType" name="sharingMemberType" class="small" onchange="EMAILMaker_EditJs.showSharingMemberTypes()">
                                <option value="groups" selected>{$APP.LBL_GROUPS}</option>
                                <option value="roles">{vtranslate('LBL_ROLES','EMAILMaker')}</option>
                                <option value="rs">{vtranslate('LBL_ROLES_SUBORDINATES','EMAILMaker')}</option>
                                <option value="users">{$APP.LBL_USERS}</option>
                            </select>
                            <input type="hidden" name="sharingFindStr" id="sharingFindStr">&nbsp;
                        </td>
                        <td width="50">&nbsp;</td>
                        <td class="small">&nbsp;</td>
                    </tr>
                    <tr class="small">
                        <td valign=top>{vtranslate('LBL_MEMBER_OF','EMAILMaker')} {vtranslate('LBL_ENTITY','EMAILMaker')}<br>
                            <select id="sharingAvailList" name="sharingAvailList" multiple size="10" class="small crmFormList"></select>
                        </td>
                        <td width="50">
                            <div align="center">
                                <input type="button" name="sharingAddButt" value="&nbsp;&rsaquo;&rsaquo;&nbsp;" onClick="EMAILMaker_EditJs.sharingAddColumn()" class="crmButton small"/><br/><br/>
                                <input type="button" name="sharingDelButt" value="&nbsp;&lsaquo;&lsaquo;&nbsp;" onClick="EMAILMaker_EditJs.sharingDelColumn()" class="crmButton small"/>
                            </div>
                        </td>
                        <td class="small" style="background-color:#ddFFdd" valign=top>{vtranslate('LBL_MEMBER_OF','EMAILMaker')} &quot;{$GROUPNAME}&quot; <br>
                            <select id="sharingSelectedColumns" name="sharingSelectedColumns" multiple size="10" class="small crmFormList">
                                {foreach item=element from=$MEMBER}
                                    <option value="{$element.0}">{$element.1}</option>
                                {/foreach}
                            </select>
                            <input type="hidden" name="sharingSelectedColumnsString" id="sharingSelectedColumnsString" value=""/>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
    </tbody>
</table>