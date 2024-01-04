{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    <div class="tab-pane active" id="pdfContentEdit">
        <div class="edit-template-content">
            {********************************************* PROPERTIES DIV*************************************************}
            <div id="properties_div">
                {* pdf module name *}
                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted">
                        <span>{if $THEME_MODE eq "true"}{vtranslate('LBL_THEME_NAME',$MODULE)}{else}{vtranslate('LBL_EMAIL_NAME',$MODULE)}{/if}:</span>
                        <span class="text-danger ms-2">*</span>
                    </label>
                    <div class="controls col-sm-9">
                        <input name="templatename" id="templatename" type="text" value="{$TEMPLATENAME}" data-rule-required="true" class="inputElement nameField form-control" tabindex="1">
                    </div>
                </div>
                {* EMAIL source module and its available fields *}
                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted">{vtranslate('LBL_RECIPIENT_FIELDS','EMAILMaker')}:</label>
                    <div class="controls col-sm-9">
                        <select name="r_modulename" id="r_modulename" class="select2 form-control">
                            {html_options  options=$RECIPIENTMODULENAMES}
                        </select>
                    </div>
                </div>
                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted"></label>
                    <div class="controls col-sm-9">
                        <div class="input-group">
                            <select name="recipientmodulefields" id="recipientmodulefields" class="select2 form-control">
                                <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD','EMAILMaker')}</option>
                            </select>
                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="recipientmodulefields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                <i class="fa fa-usd"></i>
                            </button>
                            <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="recipientmodulefields" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}">
                                <i class="fa fa-text-width"></i>
                            </button>
                        </div>
                    </div>
                </div>
                {* pdf source module and its available fields *}
                {if $THEME_MODE neq "true"}
                    <div class="form-group row py-2">
                        <label class="control-label fieldLabel col-sm-3 text-muted">{vtranslate('LBL_MODULENAMES',$MODULE)}:</label>
                        <div class="controls col-sm-9">
                            <select name="modulename" id="modulename" class="select2 form-control">
                                {if $TEMPLATEID neq "" || $SELECTMODULE neq ""}
                                    {html_options  options=$MODULENAMES selected=$SELECTMODULE}
                                {else}
                                    {html_options  options=$MODULENAMES}
                                {/if}
                            </select>
                        </div>
                    </div>
                    <div class="form-group row py-2">
                        <label class="control-label fieldLabel col-sm-3 text-muted"></label>
                        <div class="controls col-sm-9">
                            <div class="input-group">
                                <select name="modulefields" id="modulefields" class="select2 form-control" data-width="50%">
                                    {if $TEMPLATEID eq "" && $SELECTMODULE eq ""}
                                        <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD',$MODULE)}</option>
                                    {else}
                                        {html_options  options=$SELECT_MODULE_FIELD}
                                    {/if}
                                </select>
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="modulefields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-usd"></i>
                                </button>
                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="modulefields" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-text-width"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    {* related modules and its fields *}
                    <div class="form-group row py-2">
                        <label class="control-label fieldLabel col-sm-3 text-muted">{vtranslate('LBL_RELATED_MODULES',$MODULE)}:</label>
                        <div class="controls col-sm-9">
                            <select name="relatedmodulesorce" id="relatedmodulesorce" class="select2 form-control">
                                <option value="">{vtranslate('LBL_SELECT_MODULE',$MODULE)}</option>
                                {foreach item=RelMod from=$RELATED_MODULES}
                                    <option value="{$RelMod.3}|{$RelMod.0}" data-module="{$RelMod.3}">{$RelMod.1} ({$RelMod.2})</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-group row py-2">
                        <label class="control-label fieldLabel col-sm-3 text-muted"></label>
                        <div class="controls col-sm-9">
                            <div class="input-group">
                                <select name="relatedmodulefields" id="relatedmodulefields" class="select2 form-control" data-width="50%">
                                    <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD',$MODULE)}</option>
                                </select>
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="relatedmodulefields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-usd"></i>
                                </button>
                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="relatedmodulefields" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-text-width"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    {* related bloc tpl *}
                    <div class="form-group row py-2">
                        <label class="control-label fieldLabel col-sm-3 text-muted">{vtranslate('LBL_RELATED_BLOCK_TPL',$MODULE)}:</label>
                        <div class="controls col-sm-9">
                            <div class="input-group">
                                <select name="related_block" id="related_block" class="select2 form-control" data-width="50%">
                                    {html_options options=$RELATED_BLOCKS}
                                </select>
                                <button type="button" class="btn btn-success" onclick="EMAILMaker_EditJs.InsertRelatedBlock();" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-usd"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary addButton" onclick="EMAILMaker_EditJs.CreateRelatedBlock();" title="{vtranslate('LBL_CREATE')}">
                                    <i class="fa fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="EMAILMaker_EditJs.EditRelatedBlock();" title="{vtranslate('LBL_EDIT')}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary crmButton small delete" onclick="EMAILMaker_EditJs.DeleteRelatedBlock();" title="{vtranslate('LBL_DELETE')}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                {/if}

                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted">{vtranslate('LBL_COMPANY_INFO',$MODULE)}:</label>
                    <div class="controls col-sm-9">
                        <div class="input-group">
                            <select name="acc_info" id="acc_info" class="select2 form-control" data-width="50%">
                                {html_options  options=$ACCOUNTINFORMATIONS}
                            </select>
                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="acc_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                <i class="fa fa-usd"></i>
                            </button>
                            <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="acc_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}">
                                <i class="fa fa-text-width"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted">{vtranslate('LBL_SELECT_USER_INFO',$MODULE)}:</label>
                    <div class="controls col-sm-9">
                        <select name="acc_info_type" id="acc_info_type" class="select2 form-control" onChange="EMAILMaker_EditJs.change_acc_info(this)">
                            {html_options  options=$CUI_BLOCKS}
                        </select>
                    </div>
                </div>

                <div class="form-group row py-2">
                    <label class="control-label fieldLabel col-sm-3 text-muted"></label>
                    <div class="controls col-sm-9">
                        <div id="user_info_div" class="au_info_div">
                            <div class="input-group">
                                <select name="user_info" id="user_info" class="select2 form-control" data-width="50%">
                                    {html_options  options=$USERINFORMATIONS['s']}
                                </select>
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="user_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-usd"></i>
                                </button>
                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="user_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-text-width"></i>
                                </button>
                            </div>
                        </div>
                        <div id="logged_user_info_div" class="au_info_div" style="display:none;">
                            <div class="input-group">
                                <select name="logged_user_info" id="logged_user_info" class="select2 form-control" data-width="50%">
                                    {html_options  options=$USERINFORMATIONS['l']}
                                </select>
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="logged_user_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-usd"></i>
                                </button>
                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="logged_user_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-text-width"></i>
                                </button>
                            </div>
                        </div>
                        <div id="modifiedby_user_info_div" class="au_info_div" style="display:none;">
                            <div class="input-group">
                                <select name="modifiedby_user_info" id="modifiedby_user_info" class="select2 form-control" data-width="50%">
                                    {html_options  options=$USERINFORMATIONS['m']}
                                </select>
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="modifiedby_user_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-usd"></i>
                                </button>
                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="modifiedby_user_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-text-width"></i>
                                </button>
                            </div>
                        </div>
                        <div id="smcreator_user_info_div" class="au_info_div" style="display:none;">
                            <div class="input-group">
                                <select name="smcreator_user_info" id="smcreator_user_info" class="select2 form-control" data-width="50%">
                                    {html_options  options=$USERINFORMATIONS['c']}
                                </select>
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="smcreator_user_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-usd"></i>
                                </button>
                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="smcreator_user_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-text-width"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                {if $MULTICOMPANYINFORMATIONS neq ''}
                    <div class="form-group row py-2">
                        <label class="control-label fieldLabel col-sm-3 text-muted">{$LBL_MULTICOMPANY}:</label>
                        <div class="controls col-sm-9">
                            <div class="input-group">
                                <select name="multicomapny" id="multicomapny" class="select2 form-control" data-width="50%">
                                    {html_options  options=$MULTICOMPANYINFORMATIONS}
                                </select>
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="multicomapny" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}">
                                    <i class="fa fa-usd"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
{/strip}