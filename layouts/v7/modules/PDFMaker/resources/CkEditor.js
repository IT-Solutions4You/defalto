/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

jQuery.Class("PDFMaker_CkEditor_Js", {}, {
    ckEditorInstance: false,

    getckEditorInstance: function() {
        if (this.ckEditorInstance == false) {
            this.ckEditorInstance = new Vtiger_CkEditor_Js();
        }
        return this.ckEditorInstance;
    },
    registerEvents: function() {
        var thisInstance = this;
        var ckEditorInstance = this.getckEditorInstance();
    }
});
jQuery(document).ready(function() {
    var PDFMakerCkEditorJsInstance = new PDFMaker_CkEditor_Js();
    PDFMakerCkEditorJsInstance.registerEvents();
});
