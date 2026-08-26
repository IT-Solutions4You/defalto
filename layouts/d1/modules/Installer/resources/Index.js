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
        this.registerLicenseCheck();
        this.registerUpdateInformation();
    },
    getMainContainer() {
        return $('main');
    },
    registerLicenseCheck() {
        const self = this;

        self.getMainContainer().on('click', '[data-license-check]', function (e) {
            const params = {
                module: 'Installer',
                view: 'IndexAjax',
                mode: 'licenseCheckModal',
            };

            e.preventDefault();

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    app.helper.showModal(data, {
                        cb: function (container) {
                            self.registerLicenseCheckContinue(container);
                        }
                    });
                }
            });
        });
    },
    registerLicenseCheckContinue(container) {
        container.on('click', '.licenseCheckContinue', function (e) {
            const button = $(this),
                logElement = container.find('.licenseCheckLog'),
                params = {
                    module: 'Installer',
                    view: 'IndexAjax',
                    mode: 'licenseCheckProgress',
                };

            e.preventDefault();
            button.prop('disabled', true);
            logElement.html('<div class="text-center py-4"><span class="spinner-border" role="status"></span></div>');

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    logElement.html(data);
                    container.find('.licenseCheckContinue, .licenseCheckClose').addClass('hide');
                    container.find('.licenseCheckFinish').removeClass('hide');
                    container.find('.modal-body').animate({scrollTop: logElement.height()});
                } else {
                    button.prop('disabled', false);
                }
            });
        });
    },
    registerUpdateInformation() {
       const self = this;

       self.getMainContainer().on('click', '[data-update-information]', function (e) {
           const params = {
               module: 'Installer',
               view: 'IndexAjax',
               mode: 'updateInformation',
               type: $(this).data('updateInformation'),
           };

           if ('system' === params['type']) {
               $(this).parents('.systemUpdateContainer').find('.systemUpdatedState').toggleClass('hide');
               $(this).addClass('disabled');
           }
       })
    },
    registerDeleteLicense() {
        const self = this;

        self.getMainContainer().on('click', '[data-delete-license]', function (e) {
            const element = $(this),
                license = element.attr('data-delete-license'),
                params = {
                    module: 'Installer',
                    action: 'IndexAjax',
                    mode: 'licenseDelete',
                    license_id: license,
                };

            app.helper.showConfirmationBox({message: app.vtranslate('JS_LICENSE_DELETE_CONFIRMATION')}).then(function (e) {
                app.helper.showProgress();
                app.request.post({data: params}).then(function (error, data) {
                    app.helper.hideProgress();

                    if (!error) {
                        if ('deleted' === data['status']) {
                            app.helper.showSuccessNotification({message: data['message']});
                            // License status controls system and extension sections, so the complete view must be rebuilt.
                            window.location.reload();
                        } else {
                            app.helper.showErrorNotification({message: data['message']});
                        }
                    }
                });
            });
        });
    },
    registerEditLicense() {
        const self = this;

        self.getMainContainer().on('click', '[data-edit-license]', function (e) {
            const license = $(this).attr('data-edit-license'),
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
                        // License status controls system and extension sections, so the complete view must be rebuilt.
                        window.location.reload();
                    } else {
                        app.helper.showErrorNotification({message: data['message']});
                    }
                }
            })
        });
    },
    registerDownloadSystem() {
        const self = this;

        self.getMainContainer().on('click', '[data-download-system]', function (e) {
            const version = $(this).attr('data-download-system'),
                params = {
                module: 'Installer',
                view: 'IndexAjax',
                mode: 'systemModal',
                version: version
            };

            app.request.post({data: params}).then(function (error, data) {
                app.helper.showModal(data, {
                    cb: function (container) {
                        self.registerDownloadLog(container);
                    }
                });
            });
        })
    },
    registerDownloadLog(container) {
        container.on('click', '.downloadSystem', function (e) {
            if (3 !== $('.updateValidation:checked', container).length) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_PLEASE_CONFIRM_BACKUP_DATABASE_AND_SOURCE_CODE')});
                return;
            }

            $('.downloadInfoContainer, .downloadLogContainer, .downloadSystem').toggleClass('hide');

            const downloadLogElement = container.find('[data-download-log]'),
                params = app.convertUrlToDataParams(downloadLogElement.attr('data-download-log'));

            app.request.post({data: params}).then(function (error, data) {
                if(!error) {
                    downloadLogElement.html(data);

                    container.find('.modal-body').animate({scrollTop: downloadLogElement.height()});
                    container.find('a.finish').removeClass('hide');
                    container.find('a.close').addClass('hide');
                }
            });
        })
    },
    registerDownloadExtension() {
        this.getMainContainer().on('click', '[data-download-extension]', function (e) {
            const version = $(this).attr('data-download-extension');

            e.preventDefault();

            app.helper.showConfirmationBox({message: app.vtranslate('JS_CONFIRM_DOWNLOAD')}).then(function () {
                const params = {
                    module: 'Installer',
                    view: 'IndexAjax',
                    mode: 'extensionModal',
                    version: version
                };

                app.request.post({data: params}).then(function (error, data) {
                    app.helper.showModal(data, {
                        cb: function (container) {
                            const downloadLogElement = container.find('[data-download-log]'),
                                params = app.convertUrlToDataParams(downloadLogElement.attr('data-download-log'));

                            app.request.post({data: params}).then(function (error, data) {
                                if (error) {
                                    const errorMessage = error.message || app.vtranslate('JS_INSTALLATION_FAILED');

                                    downloadLogElement.empty().append(
                                        $('<div class="alert alert-danger"></div>').text(errorMessage)
                                    );
                                    container.find('.extensionInstallClose').removeClass('hide');

                                    return;
                                }

                                downloadLogElement.html(data);

                                const result = downloadLogElement.find('.installerExtensionResult');

                                if ('success' === result.data('status')) {
                                    container.data('installComplete', true);
                                    container.find('.extensionInstallFinish')
                                        .attr('href', result.data('redirect'))
                                        .removeClass('hide');
                                    container.find('.extensionInstallClose').addClass('hide');
                                }

                                container.find('.modal-body').animate({scrollTop: downloadLogElement.height()});
                            });

                            container.on('click', '.extensionInstallFinish', function () {
                                container.data('openInstalledModule', true);
                            });

                            container.on('hidden.bs.modal', function () {
                                if (container.data('installComplete') && !container.data('openInstalledModule')) {
                                    // The module/menu/resource state changed and cannot be synchronized safely in place.
                                    window.location.reload();
                                }
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
