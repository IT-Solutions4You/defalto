/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Vtiger_MergeRecords_Js */
Vtiger.Class('Vtiger_MergeRecords_Js', {}, {

    showMergeUI: function (params) {
        let self = this,
            records = params.records;

        if (typeof records == "object") {
            records = records.join(',');
        }

        let defaultPrams = {
            'module': app.module(),
            'view': 'MergeRecord',
            'records': records
        }
        app.helper.showProgress();
        app.request.get({'data': defaultPrams}).then(function (error, data) {
            app.helper.hideProgress();
            if (data) {
                app.helper.loadPageContentOverlay(data).then(function (container) {
                    self.registerUIEvents(container);
                });
            }
        });
    },

    save: function (form) {
        var aDeferred = jQuery.Deferred();
        var formData = form.serializeFormData();
        app.helper.showProgress();
        app.request.post({'data': formData}).then(function (error, data) {
            app.helper.hideProgress();
            if (error === null) {
                jQuery('.vt-notification').remove();
                app.helper.hidePageContentOverlay();
                app.event.trigger('post.MergeRecords', formData);
                aDeferred.resolve();
            } else {
                app.event.trigger('post.save.failed', error);
                aDeferred.resolve();
            }
        })
        return aDeferred.promise();
    },

    registerUIEvents: function (container) {
        let self = this,
            offset = container.find('.modal-body .datacontent').offset(),
            viewPortHeight = $(window).height() - 60;

        if (offset) {
            viewPortHeight = (viewPortHeight - offset['top']);
        }

        let params = {
            setHeight: viewPortHeight + 'px'
        };
        app.helper.showVerticalScroll(container.find('.modal-body .datacontent'), params);

        container.find('[name="primaryRecord"]').on('change', function (event) {
            let id = jQuery(event.currentTarget).val();
            container.find('[data-id=' + id + ']').prop('checked', true);
        });

        container.find('form').on('submit', function (e) {
            e.preventDefault();
            let form = jQuery(e.currentTarget);

            app.helper.showConfirmationBox({message: self.getSaveMessage(container)}).then(function (result) {
                self.save(form);
            })
        })
    },
    getSaveMessage(container) {
        let message = '';

        container.find('[name="primaryRecord"]').not(':checked').each(function () {
            message += ' #' + $(this).val();
        });

        return app.vtranslate('JS_RECORDS_WILL_BE_DELETED') + ':<b class="text-primary">' + message + '</b>';
    },
    registerListener: function () {
        var self = this;
        app.event.on('Request.MergeRecords.show', function (event, params) {
            var vtigerInstance = Vtiger_Index_Js.getInstance();
            vtigerInstance.registerEventForPostSaveFail();
            self.showMergeUI(params);
        })
    },

    registerEvents: function () {
        this.registerListener();
    }
})