/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger_Detail_Js("Inventory_Detail_Js", {
        triggerRecordPreview: function (recordId) {
            var thisInstance = app.controller();
            thisInstance.showRecordPreview(recordId);
        },
    },
    {
        showRecordPreview: function (recordId, templateId) {
            var thisInstance = this;
            var params = {};
            var moduleName = app.getModuleName();
            params['module'] = moduleName;
            params['record'] = recordId;
            params['view'] = 'InventoryQuickPreview';
            params['navigation'] = 'false';
            params['mode'] = 'Detail';

            if (templateId) {
                params['templateid'] = templateId;
            }
            app.helper.showProgress();
            app.request.get({data: params}).then(function (err, response) {
                app.helper.hideProgress();

                if (templateId) {
                    jQuery('#pdfViewer').html(response);
                    return;
                }
                app.helper.showModal(response, {
                    'cb': function (modal) {
                        jQuery('.modal-dialog').css({"width": "870px"});
                        thisInstance.registerChangeTemplateEvent(modal, recordId);
                    }
                });
            });
        },
        registerChangeTemplateEvent: function (container, recordId) {
            var thisInstance = this;
            var select = container.find('#fieldList');
            select.on("change", function () {
                var templateId = select.val();
                thisInstance.showRecordPreview(recordId, templateId);
            });

        },


        registerEvents: function () {
            const self = this;

            self._super();

            let popoverElements = document.querySelectorAll('[data-bs-toggle="popover"]')

            for (const popoverEl of popoverElements) {
                new bootstrap.Popover(popoverEl)
            }

            app.event.on('post.relatedListLoad.click', function () {
                let popoverElements = document.querySelectorAll('[data-bs-toggle="popover"]')

                for (const popoverEl of popoverElements) {
                    new bootstrap.Popover(popoverEl)
                }
            });
        },

    });