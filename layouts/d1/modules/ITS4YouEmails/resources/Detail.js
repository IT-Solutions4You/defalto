/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
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