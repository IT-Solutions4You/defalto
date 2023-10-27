{* /**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */ *}
{strip}
<div class="detailview-content">
    <div class="details container-fluid">
        <form id="detailView" method="post" action="index.php" name="etemplatedetailview">
            <input type="hidden" name="action" value="">
            <input type="hidden" name="view" value="">
            <input type="hidden" name="module" value="PDFMaker">
            <input type="hidden" name="retur_module" value="PDFMaker">
            <input type="hidden" name="return_action" value="PDFMaker">
            <input type="hidden" name="return_view" value="Detail">
            <input type="hidden" name="templateid" value="{$TEMPLATEID}">
            <input type="hidden" name="parenttab" value="{$PARENTTAB}">
            <input type="hidden" name="subjectChanged" value="">
            <input type="hidden" name="record" id="recordId" value="{$TEMPLATEID}" >
            <div class="row">
                <div class="left-block col-lg-4">
                    <div class="summaryView rounded bg-body my-3">
                        <div class="summaryViewHeader p-3 border-bottom">
                            <h4>{vtranslate('LBL_TEMPLATE_INFORMATIONS','PDFMaker')}</h4>
                        </div>
                        <div class="summaryViewFields">
                            <div class="recordDetails">
                                <div class="container-fluid p-3">
                                    <div class="row summaryViewEntries py-2">
                                        <div class="col-4 fieldLabel">
                                            <label class="muted text-truncate">{vtranslate('LBL_DESCRIPTION','PDFMaker')}</label>
                                        </div>
                                        <div class="col-lg fieldValue">{$DESCRIPTION}</div>
                                    </div>
                                    <div class="row summaryViewEntries py-2">
                                        <div class="col-4 fieldLabel">
                                            <label class="muted text-truncate">{vtranslate('LBL_MODULENAMES','PDFMaker')}</label>
                                        </div>
                                        <div class="col-lg fieldValue">{$MODULENAME}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="middle-block col-lg">
                    <div class="rounded bg-body my-3 p-3">
                        <div id="ContentEditorTabs">
                            <ul class="nav nav-pills">
                                <li class="nav-item" data-type="body">
                                    <a class="nav-link active" href="#body_div2" aria-expanded="false" data-bs-toggle="tab">{vtranslate('LBL_BODY',$MODULE)}</a>
                                </li>
                                <li class="nav-item" data-type="header">
                                    <a class="nav-link" href="#header_div2" aria-expanded="false" data-bs-toggle="tab">{vtranslate('LBL_HEADER_TAB',$MODULE)}</a>
                                </li>
                                <li class="nav-item" data-type="footer">
                                    <a class="nav-link" href="#footer_div2" aria-expanded="false" data-bs-toggle="tab">{vtranslate('LBL_FOOTER_TAB',$MODULE)}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="tab-content">
                            {*********************************************BODY DIV*************************************************}
                            <div class="tab-pane active" id="body_div2">
                                <div id="previewcontent_body" class="hide">{$BODY}</div>
                                <iframe id="preview_body" class="col-lg-12" style="height:1200px;"></iframe>
                            </div>
                            {*********************************************Header DIV*************************************************}
                            <div class="tab-pane" id="header_div2">
                                <div id="previewcontent_header" class="hide">{$HEADER}</div>
                                <iframe id="preview_header" class="col-lg-12" style="height:500px;"></iframe>
                            </div>
                            {*********************************************Footer DIV*************************************************}
                            <div class="tab-pane" id="footer_div2">
                                <div id="previewcontent_footer" class="hide">{$FOOTER}</div>
                                <iframe id="preview_footer" class="col-lg-12" style="height:500px;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function() {
        PDFMaker_DetailFree_Js.setPreviewContent('body');
        PDFMaker_DetailFree_Js.setPreviewContent('header');
        PDFMaker_DetailFree_Js.setPreviewContent('footer');
    });
</script>
{/strip}