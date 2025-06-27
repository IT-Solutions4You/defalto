/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    },
    getMainContainer() {
        return $('main');
    },
    registerDeleteLicense() {
        let self = this;

        self.getMainContainer().on('click', '[data-delete-license]', function (e) {
            let element = $(this),
                license = element.attr('data-delete-license'),
                params = {
                    module: 'Installer',
                    view: 'IndexAjax',
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

            app.request.post({data: params}).then(function(error, data) {
                app.helper.showModal(data);
            });
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

                app.request.post({data: params}).then(function(error, data) {
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