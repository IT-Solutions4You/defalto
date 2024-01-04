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
    <div class="tab-pane" id="editTabSettings">
        <br>
        <div id="settings_div">

            <div class="form-group">
                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                    {vtranslate('LBL_DESCRIPTION',$MODULE)}:
                </label>
                <div class="controls col-sm-9">
                    <input name="description" type="text" value="{$EMAIL_TEMPLATE_RESULT.description}" class="inputElement" tabindex="2">
                </div>
            </div>
            {if $THEME_MODE neq "true"}
                {* email category setting *}
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                        {vtranslate('Category')}:
                    </label>
                    <div class="controls col-sm-9">
                        <input type="text" name="email_category" value="{$EMAIL_CATEGORY}" class="inputElement"/>
                    </div>
                </div>
                {* default from setting *}
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                        {vtranslate('LBL_DEFAULT_FROM','EMAILMaker')}:
                    </label>
                    <div class="controls col-sm-9">
                        <select name="default_from_email" class="select2 form-control">
                            {html_options  options=$DEFAULT_FROM_OPTIONS selected=$SELECTED_DEFAULT_FROM}
                        </select>
                    </div>
                </div>
                {* ignored picklist values settings *}
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                        {vtranslate('LBL_IGNORE_PICKLIST_VALUES',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        <input type="text" name="ignore_picklist_values" value="{$IGNORE_PICKLIST_VALUES}" class="inputElement"/>
                    </div>
                </div>
                {* status settings *}
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                        {vtranslate('LBL_STATUS',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        <select name="is_active" id="is_active" class="select2 form-control" onchange="EMAILMaker_EditJs.templateActiveChanged(this);">
                            {html_options options=$STATUS selected=$IS_ACTIVE}
                        </select>
                    </div>
                </div>
                {* decimal settings *}
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                        {vtranslate('LBL_DECIMALS',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        <table class="table table-bordered">
                            <tr>
                                <td align="right" nowrap>{vtranslate('LBL_DEC_POINT',$MODULE)}</td>
                                <td><input type="text" maxlength="2" name="dec_point" class="inputElement" value="{$DECIMALS.point}" style="width:{$margin_input_width}"/></td>
                            </tr>
                            <tr>
                                <td align="right" nowrap>{vtranslate('LBL_DEC_DECIMALS',$MODULE)}</td>
                                <td><input type="text" maxlength="2" name="dec_decimals" class="inputElement" value="{$DECIMALS.decimals}" style="width:{$margin_input_width}"/></td>
                            </tr>
                            <tr>
                                <td align="right" nowrap>{vtranslate('LBL_DEC_THOUSANDS',$MODULE)}</td>
                                <td><input type="text" maxlength="2" name="dec_thousands" class="inputElement" value="{$DECIMALS.thousands}" style="width:{$margin_input_width}"/></td>
                            </tr>
                        </table>
                    </div>
                </div>
                {* is default settings *}
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                        {vtranslate('LBL_SETASDEFAULT',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        {vtranslate('LBL_FOR_DV',$MODULE)}&nbsp;&nbsp;<input type="checkbox" id="is_default_dv" name="is_default_dv" {$IS_DEFAULT_DV_CHECKED}/>
                        &nbsp;&nbsp;
                        {vtranslate('LBL_FOR_LV',$MODULE)}&nbsp;&nbsp;<input type="checkbox" id="is_default_lv" name="is_default_lv" {$IS_DEFAULT_LV_CHECKED}/>
                        {* hidden variable for template order settings *}
                        <input type="hidden" name="tmpl_order" value="{$ORDER}"/>
                    </div>
                </div>
            {/if}
        </div>
    </div>
{/strip}