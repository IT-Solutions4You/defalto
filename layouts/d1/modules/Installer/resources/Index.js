/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Installer_Index_Js */
Vtiger_Index_Js('Installer_Index_Js', {}, {
    registerEvents() {
        this._super();
        this.registerRequirements();
        this.registerDownloadSystem();
        this.registerDownloadExtension();
        this.registerEditLicense();
        this.registerDeleteLicense();
        this.registerUpdateInformation();
    },
    getMainContainer() {
        return $('main');
    },
    registerUpdateInformation() {
       let self = this;

       self.getMainContainer().on('click', '[data-update-information]', function (e) {
           let params = {
               module: 'Installer',
               action: 'IndexAjax',
               mode: 'updateInformation',
           };

           app.request.post({data: params}).then(function (error, data) {
               if(!error) {
                   app.helper.showSuccessNotification({message: data['message']});
               }
           });
       })
    },
    registerDeleteLicense() {
        let self = this;

        self.getMainContainer().on('click', '[data-delete-license]', function (e) {
            let element = $(this),
                license = element.attr('data-delete-license'),
                params = {
                    module: 'Installer',
                    action: 'IndexAjax',
                    mode: 'licenseDelete',
                    license_id: license,
                };

            app.helper.showConfirmationBox({message: app.vtranslate('JS_LICENSE_DELETE_CONFIRMATION')}).then(function (e) {
                app.request.post({data: params}).then(function (error, data) {
                    if (!error) {
                        app.helper.showSuccessNotification({message: data['message']});
                        element.parents('.licenseContainer').remove();
                    }
                });
            });
        });
    },
    registerEditLicense() {
        let self = this;

        self.getMainContainer().on('click', '[data-edit-license]', function (e) {
            let license = $(this).attr('data-edit-license'),
                params = {
                    module: 'Installer',
                    view: 'IndexAjax',
                    mode: 'licenseModal',
                    license_id: license,
                };

            app.request.post({data: params}).then(function (error, data) {
                app.helper.showModal(data, {
                    cb: function() {
                        self.registerEditLicenseSubmit();
                    }
                });
            });
        });
    },
    registerEditLicenseSubmit() {
        $('#editLicenseForm').on('submit', function (e) {
            e.preventDefault();

            app.helper.showProgress();
            app.request.post({data: $(this).serializeFormData()}).then(function (error, data) {
                app.helper.hideProgress();

                if(!error) {
                    if('activated' === data['status']) {
                        app.helper.hideModal();
                        app.helper.showSuccessNotification({message: data['message']});

                        setTimeout(function () {
                            window.location.reload();
                        }, 1000);
                    } else {
                        app.helper.showErrorNotification({message: data['message']});
                    }
                }
                console.log(data);
            })
        });
    },
    registerDownloadSystem() {
        let self = this;

        self.getMainContainer().on('click', '[data-download-system]', function (e) {
            let version = $(this).attr('data-download-system');

            app.helper.showConfirmationBox({message: app.vtranslate('JS_CONFIRM_DOWNLOAD')}).then(function () {
                let params = {
                    module: 'Installer',
                    view: 'IndexAjax',
                    mode: 'systemModal',
                    version: version
                };

                app.request.post({data: params}).then(function (error, data) {
                    app.helper.showModal(data, {
                        cb: function (container) {
                            let downloadLogElement = container.find('[data-download-log]'),
                                params = app.convertUrlToDataParams(downloadLogElement.attr('data-download-log'));

                            app.request.post({data: params}).then(function (error, data) {
                                downloadLogElement.html(data);
                            });
                        }
                    });
                });
            })
        })
    },
    registerDownloadExtension() {
        let self = this;

        self.getMainContainer().on('click', '[data-download-extension]', function (e) {
            let version = $(this).attr('data-download-extension');

            app.helper.showConfirmationBox({message: app.vtranslate('JS_CONFIRM_DOWNLOAD')}).then(function () {
                let params = {
                    module: 'Installer',
                    view: 'IndexAjax',
                    mode: 'extensionModal',
                    version: version
                };

                app.request.post({data: params}).then(function (error, data) {
                    app.helper.showModal(data, {
                        cb: function (container) {
                            let downloadLogElement = container.find('[data-download-log]'),
                                params = app.convertUrlToDataParams(downloadLogElement.attr('data-download-log'));

                            app.request.post({data: params}).then(function (error, data) {
                                downloadLogElement.html(data);
                            });
                        }
                    });
                });
            });
        })
    },
    registerRequirements() {
        this.getMainContainer().on('change', '#source_module', function () {
            window.location.href = $(this).val();
        });
    }
})

Installer_Index_Js('Installer_Requirements_Js', {}, {})