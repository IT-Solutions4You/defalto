/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Vtiger_Detail_Js("PDFMaker_DetailFree_Js",{

    setPreviewContent : function(type){
        var previewcontent =  jQuery('#previewcontent_'+type).html();
        var previewFrame = document.getElementById('preview_'+type);
        var preview =  previewFrame.contentDocument ||  previewFrame.contentWindow.document;
        preview.open();
        preview.write(previewcontent);
        preview.close();
        jQuery('#previewcontent_'+type).html('');
    }

    },{
});