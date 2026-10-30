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
        let previewContent = jQuery('#previewcontent_' + type).val(),
            previewFrame = document.getElementById('preview_' + type);

        previewFrame.srcdoc = previewContent;
        jQuery('#previewcontent_' + type).val('');
    }

}, {});
