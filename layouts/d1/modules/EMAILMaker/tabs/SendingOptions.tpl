{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Sending options: what is sent together with the e-mail — the PDF Maker templates (with an active PDF Maker) and the related documents *}
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
                    <select name="folders_related_documents[]" id="folders_related_documents" class="select2 form-control inputElement" multiple="multiple" data-width="100%">
                        {foreach from=$DOCUMENTS_FOLDERS item=DOCUMENT_FOLDER}
                            <option value="{$DOCUMENT_FOLDER->getId()}" {if in_array($DOCUMENT_FOLDER->getId(), $RELATED_DOCUMENTS_FOLDERS)}selected="selected"{/if}>{$DOCUMENT_FOLDER->getName()}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>
{/strip}
