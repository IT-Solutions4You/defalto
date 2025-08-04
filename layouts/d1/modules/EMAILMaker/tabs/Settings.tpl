{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="tab-pane" id="editTabSettings">
        <div id="settings_div">
            <div class="form-group row py-2">
                <label class="control-label fieldLabel col-sm-3 text-muted">
                    {vtranslate('LBL_DESCRIPTION',$MODULE)}:
                </label>
                <div class="controls col-sm-9">
                    <input name="description" type="text" value="{$EMAIL_TEMPLATE_RESULT.description}" class="inputElement form-control" tabindex="2">
                </div>
            </div>
            {if $THEME_MODE neq "true"}
                {* email category setting *}
                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted">
                        {vtranslate('Category')}:
                    </label>
                    <div class="controls col-sm-9">
                        <input type="text" name="email_category" value="{$EMAIL_CATEGORY}" class="inputElement form-control"/>
                    </div>
                </div>
                {* default from setting *}
                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted">
                        {vtranslate('LBL_DEFAULT_FROM','EMAILMaker')}:
                    </label>
                    <div class="controls col-sm-9">
                        <select name="default_from_email" class="select2 form-select">
                            {html_options  options=$DEFAULT_FROM_OPTIONS selected=$SELECTED_DEFAULT_FROM}
                        </select>
                    </div>
                </div>
                {* ignored picklist values settings *}
                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted">
                        {vtranslate('LBL_IGNORE_PICKLIST_VALUES',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        <input type="text" name="ignore_picklist_values" value="{$IGNORE_PICKLIST_VALUES}" class="inputElement form-control"/>
                    </div>
                </div>
                {* status settings *}
                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted">
                        {vtranslate('LBL_STATUS',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        <select name="is_active" id="is_active" class="select2 form-control" onchange="EMAILMaker_EditJs.templateActiveChanged(this);">
                            {html_options options=$STATUS selected=$IS_ACTIVE}
                        </select>
                    </div>
                </div>
                {* decimal settings *}
                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted">
                        {vtranslate('LBL_DECIMALS',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        <div class="row pb-2">
                            <div class="col text-muted">
                                <label for="dec_point">{vtranslate('LBL_DEC_POINT',$MODULE)}</label>
                            </div>
                            <div class="col">
                                <input type="text" maxlength="2" name="dec_point" id="dec_point" class="inputElement form-control" value="{$DECIMALS.point}" style="width:{$margin_input_width}"/>
                            </div>
                        </div>
                        <div class="row pb-2">
                            <div class="col text-muted">
                                <label for="dec_decimals">{vtranslate('LBL_DEC_DECIMALS',$MODULE)}</label>
                            </div>
                            <div class="col">
                                <input type="text" maxlength="2" name="dec_decimals" id="dec_decimals" class="inputElement form-control" value="{$DECIMALS.decimals}" style="width:{$margin_input_width}"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col text-muted">
                                <label for="dec_thousands">{vtranslate('LBL_DEC_THOUSANDS',$MODULE)}</label>
                            </div>
                            <div class="col">
                                <input type="text" maxlength="2" name="dec_thousands" id="dec_thousands" class="inputElement form-control" value="{$DECIMALS.thousands}" style="width:{$margin_input_width}"/>
                            </div>
                        </div>
                    </div>
                </div>
                {* is default settings *}
                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted">
                        {vtranslate('LBL_SETASDEFAULT',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        <div class="row pb-2">
                            <div class="col text-muted">
                                <label for="is_default_dv">{vtranslate('LBL_FOR_DV',$MODULE)}</label>
                            </div>
                            <div class="col">
                                <input type="checkbox" class="form-check-input" id="is_default_dv" name="is_default_dv" {$IS_DEFAULT_DV_CHECKED}/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col text-muted">
                                <label for="is_default_lv">{vtranslate('LBL_FOR_LV',$MODULE)}</label>
                            </div>
                            <div class="col">
                                <input type="checkbox" class="form-check-input" id="is_default_lv" name="is_default_lv" {$IS_DEFAULT_LV_CHECKED}/>
                            </div>
                        </div>
                        {* hidden variable for template order settings *}
                        <input type="hidden" name="tmpl_order" value="{$ORDER}"/>
                    </div>
                </div>
                {* load related documents settings *}
                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted" for="load_related_documents">
                        {vtranslate('LBL_LOAD_RELATED_DOCUMENTS',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        <input type="hidden" name="load_related_documents" value="0"/>
                        <input type="checkbox" id="load_related_documents" name="load_related_documents" {if $LOAD_RELATED_DOCUMENTS}checked="checked"{/if}/>
                    </div>
                </div>
                {* load related documents by folder settings *}
                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted" for="folders_related_documents">
                        {vtranslate('LBL_RELATED_DOCUMENTS_BY_FOLDER',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        <select name="folders_related_documents[]" id="folders_related_documents" class="select2 form-control inputElement" multiple="multiple">
                            {foreach from=$DOCUMENTS_FOLDERS item=DOCUMENT_FOLDER}
                                <option value="{$DOCUMENT_FOLDER->getId()}" {if in_array($DOCUMENT_FOLDER->getId(), $RELATED_DOCUMENTS_FOLDERS)}selected="selected"{/if}>{$DOCUMENT_FOLDER->getName()}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            {/if}
        </div>
    </div>
{/strip}