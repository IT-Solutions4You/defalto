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
        this.registerLicenseKeyActions();
        this.registerModuleFilters();
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
            if ($(this).hasClass('disabled')) {
                e.preventDefault();

                return;
            }

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
        });
    },
    registerLicenseKeyActions() {
        const self = this;

        this.getMainContainer().on('click', '[data-toggle-license-key]', function () {
            const button = $(this),
                container = button.closest('.licenseContainer'),
                key = container.find('[data-license-key-display]'),
                icon = button.find('i'),
                isVisible = key.data('visible') === true;

            key.text(isVisible ? key.data('maskedKey') : key.data('fullKey')).data('visible', !isVisible);
            button.find('span').text(isVisible ? button.data('showLabel') : button.data('hideLabel'));
            icon.toggleClass('fa-eye', isVisible).toggleClass('fa-eye-slash', !isVisible);
        });

        this.getMainContainer().on('click', '[data-copy-license-key]', function () {
            const key = $(this).closest('.licenseContainer').find('[data-license-key-display]').data('fullKey');

            self.copyText(key);
        });
    },
    copyText(text) {
        const input = $('<textarea class="installerClipboardInput" aria-hidden="true"></textarea>').val(text).appendTo(document.body);

        input[0].select();
        document.execCommand('copy');
        input.remove();
    },
    registerModuleFilters() {
        const container = this.getMainContainer(),
            search = container.find('[data-module-search]'),
            filters = container.find('[data-module-filter]'),
            rows = container.find('[data-module-row]'),
            empty = container.find('[data-module-empty]');

        function applyFilters() {
            const query = search.val().trim().toLocaleLowerCase(),
                filter = filters.filter('.active').data('moduleFilter');
            let visibleRows = 0;

            rows.each(function () {
                const row = $(this),
                    matchesSearch = row.data('moduleName').toLocaleLowerCase().includes(query),
                    matchesFilter = 'all' === filter
                        || ('updates' === filter && 1 === row.data('moduleUpdate'))
                        || ('not-installed' === filter && 0 === row.data('moduleInstalled')),
                    isVisible = matchesSearch && matchesFilter;

                row.toggleClass('d-none', !isVisible);

                if (isVisible) {
                    ++visibleRows;
                }
            });

            empty.toggleClass('d-none', visibleRows > 0);
        }

        search.on('input', applyFilters);
        filters.on('click', function () {
            filters.removeClass('active text-primary').addClass('text-body-secondary').attr('aria-pressed', 'false');
            $(this).removeClass('text-body-secondary').addClass('active text-primary').attr('aria-pressed', 'true');
            applyFilters();
        });
        container.on('click', '[data-clear-module-search]', function () {
            search.val('');
            filters.filter('[data-module-filter="all"]').trigger('click');
            search.trigger('focus');
        });
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
        const self = this;

        $('#editLicenseForm').on('submit', function (e) {
            const form = $(this),
                button = form.find('[type="submit"]');

            e.preventDefault();

            if (button.prop('disabled')) {
                return;
            }

            button.prop('disabled', true);
            app.helper.showProgress();
            app.request.post({data: form.serializeFormData()}).then(function (error, data) {
                app.helper.hideProgress();
                button.prop('disabled', false);

                if (!error) {
                    if ('activated' === data['status']) {
                        app.helper.hideModal();
                        app.helper.showSuccessNotification({message: data['message']});
                        // License status controls system and extension sections, so the complete view must be rebuilt.
                        window.location.reload();
                    } else {
                        self.showLicenseErrorHelp(form, data);
                    }
                }
            });
        });
    },
    showLicenseErrorHelp(form, data) {
        const panel = form.find('.licenseErrorHelp'),
            steps = panel.find('.licenseErrorSteps'),
            helpSteps = data.helpSteps || data.help || [];

        panel.find('.licenseErrorMessage').text(data.message);
        steps.empty();

        helpSteps.forEach(function (step, index) {
            const title = typeof step === 'string' ? step : step.title,
                description = typeof step === 'string' ? '' : step.description,
                item = $('<li class="mb-2"></li>').appendTo(steps),
                details = $('<details class="licenseHelpStep py-1"></details>').prop('open', index === 0).appendTo(item),
                summary = $('<summary class="d-flex align-items-center gap-3 py-2"></summary>').appendTo(details);

            $('<span class="badge rounded-pill"></span>')
                .addClass(index === 0 ? 'bg-primary' : 'bg-light text-secondary')
                .text(index + 1).appendTo(summary);
            $('<span class="fw-semibold"></span>').text(title).appendTo(summary);
            $('<i class="licenseHelpChevron fa-solid fa-chevron-down ms-auto text-secondary" aria-hidden="true"></i>').appendTo(summary);
            $('<p class="text-secondary mt-2 mb-2"></p>').text(description).appendTo(details);
        });

        panel.removeClass('d-none');
        panel[0].focus();
        panel[0].scrollIntoView({block: 'nearest', behavior: 'smooth'});
    },
    registerDownloadSystem() {
        const self = this;

        self.getMainContainer().on('click', '[data-download-system]', function (e) {
            const button = $(this),
                version = button.attr('data-download-system'),
                params = {
                module: 'Installer',
                view: 'IndexAjax',
                mode: 'systemModal',
                version: version
            };

            if (button.prop('disabled')) {
                return;
            }

            button.prop('disabled', true);

            app.request.post({data: params}).then(function (error, data) {
                button.prop('disabled', false);

                if (error) {
                    return;
                }

                app.helper.showModal(data, {
                    cb: function (container) {
                        self.registerDownloadLog(container);
                    }
                });
            });
        })
    },
    registerDownloadLog(container) {
        const self = this;

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
                    self.showInstallationLog(downloadLogElement, data);

                    container.find('.modal-body').animate({scrollTop: downloadLogElement.height()});
                    container.find('a.finish').removeClass('hide');
                    container.find('a.close').addClass('hide');
                }
            });
        })
    },
    requestExtensionInstallation(params) {
        const deferred = $.Deferred();

        // Preserve progress output on HTTP errors; app.request discards the response body.
        $.ajax({url: 'index.php', method: 'POST', data: params})
            .done(function (response) {
                if (response && typeof response === 'object' && response.error) {
                    deferred.resolve(response.error, '');
                } else {
                    deferred.resolve(null, response);
                }
            })
            .fail(function (xhr) {
                deferred.resolve({message: ''}, xhr.responseText || '');
            });

        return deferred.promise();
    },
    getInstallationErrorMessage(response, error) {
        const content = $('<div>').append($.parseHTML(typeof response === 'string' ? response : '') || []),
            logError = content.find('pre').filter(function () {
                return this.style.color === 'indianred';
            }).last().text().trim();

        return logError || (error && error.message) || app.vtranslate('JS_INSTALLATION_FAILED');
    },
    showInstallationError(container, message) {
        container.find('[data-install-status]')
            .addClass('alert alert-danger text-start')
            .attr('role', 'alert')
            .text(message);
        container.find('.extensionInstallLogDetails').prop('open', false);
        container.find('[data-bs-dismiss="modal"]').removeClass('hide');
    },
    showInstallationLog(container, response) {
        const content = $('<div>').append($.parseHTML(typeof response === 'string' ? response : '') || []),
            entries = content.find('pre'),
            list = $('<ul class="list-unstyled font-monospace small mb-0"></ul>'),
            result = content.find('.installerExtensionResult').first();

        entries.each(function () {
            const entry = $(this),
                isError = this.style.color === 'indianred',
                row = $('<li class="mb-2 d-flex gap-2"></li>'),
                marker = $('<span aria-hidden="true"></span>').text(isError ? '✗' : '•'),
                message = $(isError ? '<strong>' : '<span>').text(entry.text());

            row.toggleClass('text-danger', isError).append(marker, message);
            list.append(row);
        });

        container.empty();

        if (entries.length) {
            container.append(list);
        } else {
            container.append($('<pre class="bg-body-tertiary border rounded p-3 text-wrap"></pre>').text(content.text()));
        }

        // Preserve completion metadata without rendering server-provided HTML.
        if (result.length) {
            container.append($('<div class="installerExtensionResult hide"></div>')
                .attr('data-status', result.attr('data-status'))
                .attr('data-redirect', result.attr('data-redirect')));
        }
    },
    registerDownloadExtension() {
        const self = this;

        this.getMainContainer().on('click', '[data-download-extension]', function (e) {
            const button = $(this),
                version = button.attr('data-download-extension');

            e.preventDefault();

            if (button.data('operationPending')) {
                return;
            }

            button.data('operationPending', true).prop('disabled', true);

            const params = {
                module: 'Installer',
                view: 'IndexAjax',
                mode: 'extensionModal',
                version: version
            };

            app.request.post({data: params}).then(function (error, data) {
                if (error) {
                    button.data('operationPending', false).prop('disabled', false);

                    return;
                }

                app.helper.showModal(data, {
                    cb: function (container) {
                        container.on('click', '.extensionInstallStart', function () {
                            const startButton = $(this),
                                downloadLogElement = container.find('[data-download-log]'),
                                installParams = app.convertUrlToDataParams(downloadLogElement.attr('data-download-log'));

                            if (startButton.data('operationPending')) {
                                return;
                            }

                            startButton.data('operationPending', true).prop('disabled', true);
                            startButton.addClass('hide');
                            container.find('[data-bs-dismiss="modal"]').addClass('hide');
                            container.find('.extensionChangelog').addClass('hide');
                            container.find('.extensionInstallProgress').removeClass('hide');

                            self.requestExtensionInstallation(installParams).then(function (installError, installData) {
                                const progressBar = container.find('.progress-bar'),
                                    statusElement = container.find('[data-install-status]'),
                                    spinner = container.find('.extensionInstallSpinner');

                                progressBar.removeClass('progress-bar-animated');
                                spinner.addClass('hide');

                                if (installError) {
                                    const errorMessage = self.getInstallationErrorMessage(installData, installError);

                                    progressBar.removeClass('bg-primary').addClass('bg-danger');
                                    self.showInstallationLog(downloadLogElement, installData);
                                    self.showInstallationError(container, errorMessage);

                                    return;
                                }

                                progressBar.removeClass('bg-primary').addClass('bg-success');
                                statusElement.text(app.vtranslate('JS_INSTALLED'));
                                self.showInstallationLog(downloadLogElement, installData);

                                const result = downloadLogElement.find('.installerExtensionResult');

                                if ('success' === result.data('status')) {
                                    container.data('installComplete', true);
                                    container.find('.extensionInstallFinish')
                                        .attr('href', result.data('redirect'))
                                        .removeClass('hide');
                                } else {
                                    progressBar.removeClass('bg-success').addClass('bg-danger');
                                    self.showInstallationError(container, self.getInstallationErrorMessage(installData, null));
                                }
                            });
                        });

                        container.on('click', '.extensionInstallFinish', function () {
                            container.data('openInstalledModule', true);
                        });

                        container.on('hidden.bs.modal', function () {
                            if (container.data('installComplete') && !container.data('openInstalledModule')) {
                                // The module/menu/resource state changed and cannot be synchronized safely in place.
                                window.location.reload();
                            } else if (!container.data('installComplete')) {
                                button.data('operationPending', false).prop('disabled', false);
                            }
                        });
                    }
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
