/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/** Modal-scoped record actions, owned by Core. */
Vtiger_Index_Js('Vtiger_QuickPreview_Js', {
    getInstance: function () {
        return jQuery('#helpPageOverlay').data('quickPreviewController');
    },

    registerPreview: function (container, context, params, inventory) {
        const instance = new Vtiger_QuickPreview_Js();

        container.data('quickPreviewController', instance);
        instance.registerPreview(container, context, params, inventory);

        return instance;
    },

    deleteRecord: function (url) {
        const instance = this.getInstance();

        if (instance) {
            return instance.deletePreviewRecord(url);
        }
    }
}, {
    disposed: false,

    registerPreview: function (container, context, params, inventory) {
        const self = this;

        self.container = container;
        self.sourceContext = context;
        self.previewClass = window.Vtiger_QuickPreview_Js;
        self.registerDetail(params.module, params.record);
        self.detailClass = window.Vtiger_Detail_Js;
        self.deleteHandler = self.getDeleteRecordHandler();
        self.detailClass.registerDeleteRecordHandler(self.deleteHandler);
        self.registerNavigationEvents(container);

        if (inventory) {
            self.registerChangeTemplateEvent(container, params.record);
            app.helper.showVerticalScroll(container.find('.quickPreview .modal-body'), {
                setHeight: '100%',
                alwaysShowScrollbar: 2,
                autoExpandScrollbar: true,
                setTop: 0,
                scrollInertia: 70,
                mouseWheel: {preventDefault: true}
            });
        } else {
            self.registerMoreRecentUpdatesClickEvent(container, params.record);
        }

        // Native listeners survive the legacy helper's jQuery unbind() during modal hide.
        self.hideHandler = function () {
            ++Vtiger_Index_Js.quickPreviewRequestId;
            app.helper.hideProgress();
            self.disposeTooltips(container);
        };
        self.hiddenHandler = function () {
            self.dispose();
        };
        container[0].addEventListener('hide.bs.modal', self.hideHandler);
        container[0].addEventListener('hidden.bs.modal', self.hiddenHandler);
    },

    registerDetail: function (moduleName, recordId) {
        const container = this.container.find('.quickPreview').last(),
            form = container.find('form#detailView');

        container.attr('data-preview-module', moduleName).attr('data-preview-record', recordId);

        if (!form.length) {
            return;
        }

        const detail = Vtiger_Detail_Js.getInstanceByModuleName(moduleName),
            fieldInfo = JSON.parse(form.find('[name="quickPreviewFieldInfo"]').val() || '{}');

        container.data('quickPreviewFieldInfo', fieldInfo);
        detail.setContainer(container);
        detail.setForm(form);
        detail.setModuleName(moduleName);
        detail.setQuickPreviewDetailMode(true);
        // The underlying full-page record is not the record currently displayed in this overlay.
        detail.getRecordId = function () {
            return this.getContainer().attr('data-preview-record');
        };
        container.data('quickPreviewDetail', detail);
        detail.registerEvents();
    },

    hasRecord: function (params) {
        const container = this.container.find('.quickPreview').last();

        return !this.disposed && this.container.hasClass('show')
            && this.container.data('quickPreviewController') === this
            && container.attr('data-preview-module') === params.module
            && String(container.attr('data-preview-record')) === String(params.record);
    },

    getDeleteRecordHandler: function () {
        const self = this,
            sourceContext = self.sourceContext;

        return {
            hasRecord: function (params) {
                return self.hasRecord(params);
            },
            onSuccess: function (params, listUrl) {
                // A completed request must not close another preview opened in the meantime.
                if (self.hasRecord(params)) {
                    self.disposeTooltips(self.container);
                    app.helper.hideHelpPageOverlay();
                }

                app.helper.showSuccessNotification({message: app.vtranslate('JS_RECORD_DELETED')});
                self.refreshSource(params, listUrl, sourceContext);
            },
            onError: function (error) {
                self.showError(error);
            }
        };
    },

    deletePreviewRecord: function (url) {
        return this.detailClass.deleteRecord(url, this.deleteHandler);
    },

    dispose: function () {
        if (this.disposed) {
            return;
        }

        this.disposed = true;
        this.detailClass.removeDeleteRecordHandler(this.deleteHandler);
        this.container[0].removeEventListener('hide.bs.modal', this.hideHandler);
        this.container[0].removeEventListener('hidden.bs.modal', this.hiddenHandler);
        this.disposeTooltips(this.container);

        if (this.container.data('quickPreviewController') === this) {
            this.container.removeData('quickPreviewController');
            // Removes the modal's script elements, scoped field data and element-bound handlers.
            this.container.empty();
        }

        if (window.Vtiger_QuickPreview_Js === this.previewClass) {
            delete window.Vtiger_QuickPreview_Js;
        }

        this.sourceContext = null;
    },

    refreshSource: function (params, listUrl, context) {
        const source = context.controller;

        if (source && typeof source.onQuickPreviewRecordDeleted === 'function') {
            source.onQuickPreviewRecordDeleted(params);

            return;
        }

        if (source && typeof source.loadListViewRecords === 'function') {
            source.loadListViewRecords();

            return;
        }

        if (context.module === params.module && String(context.record) === String(params.record) && typeof listUrl === 'string') {
            window.location.href = listUrl + '&app=' + encodeURIComponent(app.getAppName());

            return;
        }

        // Unknown host screens can contain references/widgets with no safe partial-refresh API.
        // Refresh the same page rather than redirecting it to the deleted reference's module.
        window.location.reload();
    },

    disposeTooltips: function (container) {
        container.find('[data-bs-toggle="tooltip"]').each(function () {
            const tooltip = bootstrap.Tooltip.getInstance(this);

            if (tooltip) {
                tooltip.dispose();
            }
        });
    },

    showError: function (error) {
        const message = typeof error === 'string' ? error : error && error.message;

        app.helper.showErrorNotification({message: app.vtranslate(message || 'JS_ERROR')});
    }
});
