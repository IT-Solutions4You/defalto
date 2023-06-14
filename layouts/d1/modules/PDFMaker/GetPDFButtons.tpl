{* /**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */ *}

{if $ENABLE_PDFMAKER eq 'true'}

     <div class="col-sm-4 pull-right" id="PDFMakerContentDiv">
        <div class="row clearfix">
                <div class="col-sm-6 padding0px pull-right">
                    <div class="btn-group pull-right">
                        <button class="btn btn-default selectPDFTemplates">{vtranslate('LBL_EXPORT_TO_PDF','PDFMaker')}</button>
                        <button type="button" class="btn btn-default dropdown-toggle dropdown-toggle-split PDFMoreAction" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {vtranslate('LBL_MORE','PDFMaker')}&nbsp;&nbsp;<span class="caret"></span></button>
                        </button>
                            <ul class="dropdown-menu">
                                {include file="GetPDFActions.tpl"|vtemplate_path:'PDFMaker'}
                            </ul>
                        </div>
                    </div>
                </div>
        </div>
    </div>
{/if}