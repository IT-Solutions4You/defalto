{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

<input type="hidden" name="email_function" id="email_function" value="{if isset($EMAIL_FUNCTION)}{$EMAIL_FUNCTION}{/if}"/>
<li>
    <a href="javascript:EMAILMaker_Actions_Js.getDetailViewPopup(this,'{$MODULE}');" class="dropdown-item">{vtranslate('LBL_SEND_EMAIL','PDFMaker')}</a>
</li>
<li>
    <a href="javascript:;" class="dropdown-item PDFMakerDownloadPDF PDFMakerTemplateAction">{vtranslate('LBL_DOWNLOAD','PDFMaker')}</a>
</li>
<li>
    <span class="dropdown-header">
        <i class="fa-solid fa-wrench"></i>
        <span class="ms-2">{vtranslate('LBL_SETTINGS', 'PDFMaker')}</span>
    </span>
</li>
<li>
    <a href="javascript:;" class="dropdown-item showPDFBreakline">{vtranslate('LBL_PRODUCT_BREAKLINE','PDFMaker')}</a>
</li>
<li>
    <a href="javascript:;" class="dropdown-item showProductImages">{vtranslate('LBL_PRODUCT_IMAGE', 'PDFMaker')}</a>
</li>
{assign var=PDFMAKER_MODEL value=Vtiger_Module_Model::getInstance('PDFMaker')}
{assign var=TEMPLATE_LANGUAGES value=$PDFMAKER_MODEL->GetAvailableLanguages()}
{if php7_count($TEMPLATE_LANGUAGES)}
    {if !isset($CURRENT_LANGUAGE)}
        {assign var=CURRENT_LANGUAGE value=''}
    {/if}
    <li class="dropdown-header">
        <i class="fa-solid fa-wrench"></i>
        <span class="ms-2">{vtranslate('LBL_PDF_LANGUAGE', 'PDFMaker')}</span>
    </li>
    <li>
        <div class="dropdown-item">
            <select name="template_language" id="template_language" data-width="100%">
                {html_options  options=$TEMPLATE_LANGUAGES selected=$CURRENT_LANGUAGE}
            </select>
        </div>
    </li>
{else}
    {foreach from=$TEMPLATE_LANGUAGES item="lang" key="lang_key"}
        <input type="text" name="template_language" id="template_language" value="{$lang_key}"/>
    {/foreach}
{/if}
