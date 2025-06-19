/*
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
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

        app.request.post({data: params}).then(function(error, data) {
            if (data && $(data).find('.modal-body').length) {
                app.helper.showModal(data, {
                    modalName: 'tourGuideModal',
                });
            }
        });
    }
});

Tour_HeaderScript_Js.getInstance().registerEvents();