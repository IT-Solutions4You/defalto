/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */
/** @var Vtiger_Header_Js */
jQuery.Class('Vtiger_Header_Js', {
    previewFile: function (e, recordId) {
        e.preventDefault();
        e.stopPropagation();

        let currentTarget = e.currentTarget,
            currentTargetObject = jQuery(currentTarget);

        if (typeof recordId == 'undefined') {
            if (currentTargetObject.closest('tr').length) {
                recordId = currentTargetObject.closest('tr').data('id');
            } else {
                recordId = currentTargetObject.data('id');
            }
        }

        let fileLocationType = currentTargetObject.data('filelocationtype'),
            fileName = currentTargetObject.data('filename');

        console.log(fileName, fileLocationType);

        if ('I' === fileLocationType) {
            let params = {
                module: 'Documents',
                view: 'FilePreview',
                record: recordId
            };

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    app.helper.showModal(data);
                }
            });
        } else {
            let win = window.open(fileName, '_blank');

            win.focus();
        }
    }
}, {});