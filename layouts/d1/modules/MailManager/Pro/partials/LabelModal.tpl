{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Label add/edit modal of the LabelEditor.js — include it as a sibling
   OUTSIDE the page container, its handlers bind to the modal element.
   Params: LABEL_PALETTE, SUGGESTED_OPTIONS *}
{strip}
{assign var=LABEL_LANG value='Settings:MailManager'}
<div class="modal fade its4you-label-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title its4you-label-modal-title" data-add="{vtranslate('LBL_ADD_LABEL', $LABEL_LANG)|escape}" data-edit="{vtranslate('LBL_EDIT_LABEL', $LABEL_LANG)|escape}"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{vtranslate('LBL_CLOSE', $LABEL_LANG)|escape}"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="labelid" value="0">
                <div class="row py-2 its4you-form-row">
                    <div class="col-sm-4 fieldLabel">{vtranslate('LBL_LABEL_NAME', $LABEL_LANG)}<span class="text-danger ms-2">*</span></div>
                    <div class="col-sm-8 fieldValue"><input type="text" class="form-control inputElement" name="name" maxlength="50"></div>
                </div>
                <div class="row py-2 its4you-form-row">
                    <div class="col-sm-4 fieldLabel">{vtranslate('LBL_SELECT_COLOR', $LABEL_LANG)}</div>
                    <div class="col-sm-8 fieldValue">
                        <input type="hidden" name="selectedColor" value="">
                        {* The Defalto palette (Label model) — the chip text follows the color *}
                        <div class="its4you-color-swatches">
                            {foreach key=SWATCH item=SWATCH_NAME from=$LABEL_PALETTE}
                                <span class="its4you-color-swatch" data-color="{$SWATCH}" title="{vtranslate($SWATCH_NAME, $LABEL_LANG)|escape}" style="background: {$SWATCH};"></span>
                            {/foreach}
                        </div>
                        {* how the label will look — name + color, updated as they change (LabelEditor.js) *}
                        <div class="its4you-label-preview">
                            <span class="its4you-hint">{vtranslate('LBL_LABEL_PREVIEW', $LABEL_LANG)}</span>
                            <span class="its4you-label-chip its4you-label-preview-chip"
                                  data-placeholder="{vtranslate('LBL_LABEL_NAME', $LABEL_LANG)|escape}"></span>
                        </div>
                    </div>
                </div>
                <div class="row py-2 its4you-form-row">
                    <div class="col-sm-4 fieldLabel">{vtranslate('LBL_AI_DESCRIPTION', $LABEL_LANG)}</div>
                    <div class="col-sm-8 fieldValue">
                        <textarea class="form-control" name="ai_description" rows="4"></textarea>
                        <div class="its4you-hint">{vtranslate('LBL_AI_DESCRIPTION_HINT', $LABEL_LANG)}</div>
                    </div>
                </div>
                <div class="row py-2 its4you-form-row">
                    <div class="col-sm-4 fieldLabel">{vtranslate('LBL_SUGGESTED_ACTION', $LABEL_LANG)}</div>
                    <div class="col-sm-8 fieldValue">
                        <select class="form-select its4you-mw-240 inputElement" name="suggested_module">
                            <option value="">&mdash;</option>
                            {foreach item=SUGGESTED_OPTION from=$SUGGESTED_OPTIONS}
                                <option value="{$SUGGESTED_OPTION}">{vtranslate("LBL_ACTION_"|cat:($SUGGESTED_OPTION|replace:':':'_'), $LABEL_LANG)}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                {* only a cleanup action (Move to Spam / Trash) can run by itself — LabelEditor.js shows the row *}
                <div class="row py-2 its4you-form-row row py-2 its4you-form-row-check its4you-auto-action-row hide">
                    <div class="col-sm-4 fieldLabel">{vtranslate('LBL_AUTO_ACTION', $LABEL_LANG)}</div>
                    <div class="col-sm-8 fieldValue">
                        <input type="checkbox" class="form-check-input" name="auto_action">
                        <div class="its4you-hint">{vtranslate('LBL_AUTO_ACTION_HINT', $LABEL_LANG)}</div>
                    </div>
                </div>
                <div class="row py-2 its4you-form-row row py-2 its4you-form-row-check">
                    <div class="col-sm-4 fieldLabel">{vtranslate('LBL_WORKSPACE', $LABEL_LANG)}</div>
                    <div class="col-sm-8 fieldValue">
                        <input type="checkbox" class="form-check-input" name="workspace">
                        <div class="its4you-hint">{vtranslate('LBL_WORKSPACE_HINT', $LABEL_LANG)}</div>
                    </div>
                </div>
                <div class="row py-2 its4you-form-row row py-2 its4you-form-row-check">
                    <div class="col-sm-4 fieldLabel">{vtranslate('LBL_ACTIVE', $LABEL_LANG)}</div>
                    <div class="col-sm-8 fieldValue"><input type="checkbox" class="form-check-input" name="active" checked></div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="container-fluid p-0">
                    <div class="row">
                        <div class="col-auto col-lg-6">
                            <a href="#" class="btn btn-primary cancelLink" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $LABEL_LANG)}</a>
                        </div>
                        <div class="col-auto col-lg-6 text-end ms-auto">
                            <button type="button" class="btn btn-primary active saveButton its4you-save-label">{vtranslate('LBL_SAVE', $LABEL_LANG)}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/strip}
