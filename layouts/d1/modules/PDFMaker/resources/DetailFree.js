/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger_Detail_Js("PDFMaker_DetailFree_Js", {

    setPreviewContent: function (type) {
        var previewcontent = jQuery('#previewcontent_' + type).html();
        var previewFrame = document.getElementById('preview_' + type);
        var preview = previewFrame.contentDocument || previewFrame.contentWindow.document;
        preview.open();
        preview.write(previewcontent);
        preview.close();
        jQuery('#previewcontent_' + type).html('');
    }

}, {});