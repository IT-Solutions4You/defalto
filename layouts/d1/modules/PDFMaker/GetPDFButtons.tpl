{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
<div class="btn-group" id="PDFMakerContentDiv">
    <button class="btn btn-primary selectPDFTemplates">
        <i class="fa-solid fa-file-export"></i>
        <span class="ms-2">{vtranslate('LBL_EXPORT_TO_PDF','PDFMaker')}</span>
    </button>
    <button type="button" class="btn btn-primary dropdown-toggle PDFMoreAction" data-bs-auto-close="outside" data-bs-target=".PDFMoreDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {vtranslate('LBL_MORE','PDFMaker')}
    </button>
    <ul class="dropdown-menu dropdown-menu-end PDFMoreDropdown">
        {include file='GetPDFActions.tpl'|vtemplate_path:'PDFMaker'}
    </ul>
</div>
