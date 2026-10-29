{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{if isset($RECORD)}{assign var=PDF_RECORD_ID value=$RECORD->getId()}{else}{assign var=PDF_RECORD_ID value=$ID}{/if}
{assign var=PDF_EXPORT_DENIED value=Vtiger_Module_Model::getInstance('PDFMaker')->getPdfExportDenyReason((int)$PDF_RECORD_ID)}
{if $PDF_EXPORT_DENIED}
    {include file='RecordActionDeniedButton.tpl'|vtemplate_path:'Core' DENY_REASON=$PDF_EXPORT_DENIED CONTAINER_ID='PDFMakerContentDiv' BUTTON_LABEL=vtranslate('LBL_EXPORT_TO_PDF','PDFMaker')}
{else}
<div class="btn-group" id="PDFMakerContentDiv">
    <button class="btn selectPDFTemplates {$DETAIL_VIEW_BASIC_LINK->getStyleClass()}">
        <i class="fa-solid fa-file-export"></i>
        <span class="ms-2">{vtranslate('LBL_EXPORT_TO_PDF','PDFMaker')}</span>
    </button>
    <button type="button" class="btn dropdown-toggle PDFMoreAction {$DETAIL_VIEW_BASIC_LINK->getStyleClass()}" data-bs-auto-close="outside" data-bs-target=".PDFMoreDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {vtranslate('LBL_MORE','PDFMaker')}
    </button>
    <ul class="dropdown-menu dropdown-menu-end PDFMoreDropdown">
        {include file='GetPDFActions.tpl'|vtemplate_path:'PDFMaker'}
    </ul>
</div>
{/if}
