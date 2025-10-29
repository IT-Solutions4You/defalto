/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Tour_HeaderScript_Js */
Vtiger.Class('Tour_HeaderScript_Js', {
    instance: false,
    getInstance() {
        if (!this.instance) {
            this.instance = new Tour_HeaderScript_Js();
        }

        return this.instance;
    }
}, {
    registerEvents() {
        this.registerShowModal();
    },
    registerShowModal() {
        let params = {
            module: 'Tour',
            view: 'Guide',
            mode: 'modal',
        }

        app.request.post({data: params}).then(function (error, data) {
            if (data && $(data).find('.modal-body').length) {
                app.helper.showModal(data, {
                    modalName: 'tourGuideModal',
                });
            }
        });
    }
});

$(function() {
    Tour_HeaderScript_Js.getInstance().registerEvents();
})