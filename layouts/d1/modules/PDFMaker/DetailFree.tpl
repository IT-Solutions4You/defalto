{* /**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */ *}
{strip}
<div class="detailview-content container-fluid">
    <div class="details row">
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
            <div class="col-lg-12">
                <div class="left-block col-lg-4">

                    <div class="summaryView">
                        <div class="summaryViewHeader">
                            <h4 class="display-inline-block">{vtranslate('LBL_TEMPLATE_INFORMATIONS','PDFMaker')}</h4>
                        </div>

                        <div class="summaryViewFields">
                            <div class="recordDetails">
                                <table class="summary-table no-border">
                                    <tbody>
                                    <tr class="summaryViewEntries">
                                        <td class="fieldLabel"><label class="muted text-truncate">{vtranslate('LBL_DESCRIPTION','PDFMaker')}</label></td>
                                        <td class="fieldValue" valign=top>{$DESCRIPTION}</td>
                                    </tr>
                                    <tr class="summaryViewEntries">
                                        <td class="fieldLabel"><label class="muted text-truncate">{vtranslate('LBL_MODULENAMES','PDFMaker')}</label></td>
                                        <td class="fieldValue" valign=top>{$MODULENAME}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <br>
                    <br>
                </div>
                <div class="middle-block col-lg-8">

                    <div id="ContentEditorTabs">
                        <ul class="nav nav-pills">
                            <li class="active" data-type="body">
                                <a href="#body_div2" aria-expanded="false" data-toggle="tab">{vtranslate('LBL_BODY',$MODULE)}</a>
                            </li>
                            <li data-type="header">
                                <a href="#header_div2" aria-expanded="false" data-toggle="tab">{vtranslate('LBL_HEADER_TAB',$MODULE)}</a>
                            </li>
                            <li data-type="footer">
                                <a href="#footer_div2" aria-expanded="false" data-toggle="tab">{vtranslate('LBL_FOOTER_TAB',$MODULE)}</a>
                            </li>
                        </ul>
                    </div>
                    {*********************************************BODY DIV*************************************************}
                    <div class="tab-content">
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
            <center style="color: rgb(153, 153, 153);">{vtranslate('PDF_MAKER','PDFMaker')} {$VERSION} {vtranslate('COPYRIGHT','PDFMaker')}</center>
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