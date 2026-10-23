{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Sending options: what is sent together with the e-mail (Edit.tpl shows the tab while it has an option) *}
{strip}
    <div class="tab-pane" id="editTabSendingOptions">
        <div id="sending_options_div">
            {* PDF Maker templates sent with this e-mail template (EMAILMaker_PDFTemplate_Model) *}
            {if $PDF_TEMPLATE_DATA.available}
                <div class="form-group row py-2" id="EMAILMakerPDFTemplatesRow">
                    <label class="control-label fieldLabel col-sm-3 text-muted" for="pdf_templates_select">{vtranslate('LBL_PDF_TEMPLATES',$MODULE)}:</label>
                    <div class="controls col-sm-9">
                        <input type="hidden" name="pdf_templates_sent" value="1">
                        <input type="hidden" name="pdf_templates" id="pdf_templates" value="{';'|implode:$PDF_TEMPLATE_DATA.selected}">
                        <select id="pdf_templates_select" multiple class="form-control select2" data-width="100%" data-placeholder="{vtranslate('LBL_SELECT_PDF_TEMPLATES',$MODULE)|escape}">
                            {foreach from=$PDF_TEMPLATE_DATA.options item=PDF_TEMPLATE_NAME key=PDF_TEMPLATE_ID}
                                <option value="{$PDF_TEMPLATE_ID}" {if in_array($PDF_TEMPLATE_ID, $PDF_TEMPLATE_DATA.selected)}selected="selected"{/if}>{$PDF_TEMPLATE_NAME|escape}</option>
                            {/foreach}
                        </select>
                        <div class="form-text">{vtranslate('LBL_PDF_TEMPLATES_HINT',$MODULE)}</div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
{/strip}
