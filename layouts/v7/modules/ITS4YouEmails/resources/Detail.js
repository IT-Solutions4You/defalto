/** *******************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
/** @var ITS4YouEmails_Detail_Js */
Vtiger_Detail_Js('ITS4YouEmails_Detail_Js', {}, {
    registerEvents: function () {
        this._super();
        this.registerBody();
    },
    registerBody: function () {
        const self = this;

        self.setBody();

        app.event.on('post.relatedListLoad.click', function (event, searchRow) {
            self.setBody();
        });

        app.event.on('post.overlay.load', function (event, parentRecordId, params) {
            self.setBody();
        });
    },
    setBody: function () {
        $('#ITS4YouEmails_detailView_fieldLabel_body').remove();
        $('#ITS4YouEmails_detailView_fieldValue_body').html('<iframe sandbox="" src="index.php?module=ITS4YouEmails&view=Body&record=' + app.getRecordId() + '" style="border:0; width: 100%; height: 60vh"></iframe>');
    },
    registerBasicEvents: function () {
        this._super();
        this.setBody();
    },
});