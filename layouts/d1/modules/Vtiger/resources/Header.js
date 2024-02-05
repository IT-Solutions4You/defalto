/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/
/** @var Vtiger_Header_Js */
jQuery.Class("Vtiger_Header_Js", {
   
    previewFile : function(e,recordId) {
        e.stopPropagation();
        var currentTarget = e.currentTarget;
        var currentTargetObject = jQuery(currentTarget);
        if(typeof recordId == 'undefined') {
            if(currentTargetObject.closest('tr').length) {
                recordId = currentTargetObject.closest('tr').data('id');
            } else {
                recordId = currentTargetObject.data('id');
            }
        }
        var fileLocationType = currentTargetObject.data('filelocationtype');
        var fileName = currentTargetObject.data('filename'); 
        if(fileLocationType == 'I'){
            var params = {
                module : 'Documents',
                view : 'FilePreview',
                record : recordId
            };
            app.request.post({"data":params}).then(function(err,data){
                app.helper.showModal(data);
            });
        } else {
            var win = window.open(fileName, '_blank');
            win.focus();
        }
    }
},{
});