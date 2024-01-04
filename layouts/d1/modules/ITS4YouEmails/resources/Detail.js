/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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