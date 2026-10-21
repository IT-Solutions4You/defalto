/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Add Mailbox wizard (Edit.tpl) — the admin runs it in Settings (Edit.js),
 * a user adds an own mailbox from the module (AddMailbox.js). The steps and
 * the way back come from the page (data-wizard-url / data-list-url); the
 * actions go to Settings or to the module by app.getParentModuleName()
 */
window.MailManager_ProMailboxWizard_Mixin = {

    EMAIL_REGEX: /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,

    /**
     * @return {jQuery}
     */
    getContainer: function () {
        return jQuery('.its4you-mm-wizard');
    },

    /**
     * Marks a provider card as selected and stores the value in the hidden input
     *
     * @param {jQuery} container
     * @param {string} provider
     */
    selectProvider: function (container, provider) {
        let cards = container.find('.its4you-provider-card');
        cards.removeClass('selected');
        cards.filter('[data-provider="' + provider + '"]').addClass('selected');
        container.find('input[name="provider"]').val(provider);
    },

    /**
     * Provider card click selection
     *
     * @param {jQuery} container
     */
    registerProviderCards: function (container) {
        let self = this;
        container.on('click', '.its4you-provider-card', function () {
            self.selectProvider(container, jQuery(this).data('provider'));
        });
    },

    /**
     * MX autodetection on email change — preselects a card, user can override
     *
     * @param {jQuery} container
     */
    registerEmailDetection: function (container) {
        let self = this;
        container.on('change', 'input[name="email"]', function () {
            let email = jQuery(this).val().trim();
            if (!self.EMAIL_REGEX.test(email)) {
                return;
            }
            let params = {
                module: app.getModuleName(),
                parent: app.getParentModuleName(),
                action: ('Settings' === app.getParentModuleName() ? 'DetectProvider' : 'ProDetectProvider'),
                email: email
            };
            app.request.post({data: params}).then(function (err, data) {
                if (err || !data || !data.detected) {
                    return;
                }
                self.selectProvider(container, data.provider);
                let providerLabel = container
                    .find('.its4you-provider-card[data-provider="' + data.provider + '"] b').text();
                container.find('.its4you-detect-result')
                    .text(app.vtranslate('JS_PROVIDER_DETECTED').replace('%s', providerLabel))
                    .removeClass('hide');
            });
        });
    },

    /**
     * Validates step 1 and moves the wizard to the connection step
     *
     * @param {jQuery} container
     */
    registerNextStep: function (container) {
        let self = this;
        container.on('click', '.its4you-wizard-next', function () {
            let email = container.find('input[name="email"]').val().trim(),
                mailboxName = container.find('input[name="mailbox_name"]').val().trim(),
                provider = container.find('input[name="provider"]').val();

            if (!self.EMAIL_REGEX.test(email)) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_INVALID_EMAIL')});
                return;
            }
            if (!mailboxName) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_FILL_REQUIRED')});
                return;
            }
            if (!provider) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_SELECT_PROVIDER')});
                return;
            }

            window.location.href = container.data('wizard-url') + '&step=2'
                + '&email=' + encodeURIComponent(email)
                + '&name=' + encodeURIComponent(mailboxName)
                + '&provider=' + encodeURIComponent(provider);
        });
    },

    /**
     * Copies the redirect URI to the clipboard
     *
     * @param {jQuery} container
     */
    registerCopyRedirectUri: function (container) {
        container.on('click', '.its4you-copy-uri', function () {
            let input = container.find('.its4you-redirect-uri');
            input.trigger('select');
            document.execCommand('copy');
            app.helper.showSuccessNotification({message: app.vtranslate('JS_COPIED')});
        });
    },

    /**
     * "Sign in with Microsoft / Google" — Core auth.php opens in a new window with the
     * user's OAuth application (client id / secret)
     *
     * @param {jQuery} container
     */
    registerOAuthSignIn: function (container) {
        container.on('click', '.its4you-oauth-signin', function () {
            let step2 = container.find('.its4you-oauth-step'),
                clientId = step2.find('input[name="client_id"]').val().trim(),
                clientSecret = step2.find('input[name="client_secret"]').val();

            if (!clientId || !clientSecret) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_FILL_REQUIRED')});
                return;
            }

            step2.find('.its4you-conn-status, .its4you-conn-error').addClass('hide');
            step2.find('.its4you-oauth-connect').prop('disabled', true);

            app.getOAuth2Url(step2.data('server'), clientId, clientSecret).then(function (error, data) {
                if (error || !data) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_ERROR')});
                    return;
                }

                if (data['message']) {
                    app.helper.showErrorNotification({message: data['message']});
                }

                if (data['url']) {
                    window.open(data['url'], '_blank');
                }
            });
        });
    },

    /**
     * "Check sign-in" — asks Core whether auth.php stored the tokens in this session
     *
     * @param {jQuery} container
     */
    registerOAuthCheck: function (container) {
        container.on('click', '.its4you-oauth-check', function () {
            let step2 = container.find('.its4you-oauth-step'),
                clientId = step2.find('input[name="client_id"]').val().trim();

            if (!clientId) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_FILL_REQUIRED')});
                return;
            }

            app.getOAuth2Tokens(clientId).then(function (error, data) {
                const signedIn = !error && data && data['success'];

                step2.find('.its4you-conn-status').toggleClass('hide', !signedIn);
                step2.find('.its4you-conn-error').toggleClass('hide', signedIn);
                step2.find('.its4you-oauth-connect').prop('disabled', !signedIn);
            });
        });
    },

    /**
     * "Connect mailbox" — saves the signed-in mailbox as the user's personal mailbox
     *
     * @param {jQuery} container
     */
    registerOAuthConnect: function (container) {
        container.on('click', '.its4you-oauth-connect', function () {
            let step2 = container.find('.its4you-oauth-step'),
                params = {
                    module: app.getModuleName(),
                    action: 'ProSaveOAuthAccount',
                    provider: step2.data('provider'),
                    email: step2.data('email'),
                    client_id: step2.find('input[name="client_id"]').val().trim()
                };

            app.helper.showProgress();
            app.request.post({data: params}).then(function (err, data) {
                app.helper.hideProgress();

                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: (data && data.message) || (err && err.message) || app.vtranslate('JS_SAVE_ERROR')});
                    return;
                }

                window.location.href = container.data('list-url') + '&mailbox=' + data.mailboxid;
            });
        });
    },

    /**
     * Default IMAP port per encryption (only overwrites known defaults)
     *
     * @param {jQuery} container
     */
    registerEncryptionPortSync: function (container) {
        container.on('change', '.its4you-imap-encryption', function () {
            let portInput = container.find('.its4you-imap-port'),
                current = portInput.val().trim();
            if (current === '' || current === '993' || current === '143') {
                portInput.val(jQuery(this).val() === 'ssl' ? '993' : '143');
            }
        });
    },

    /**
     * IMAP "Test Connection" — on success the server stores credentials
     * in the wizard session and Next unlocks
     *
     * @param {jQuery} container
     */
    registerTestImap: function (container) {
        let self = this;
        container.on('click', '.its4you-test-imap', function () {
            let step2 = container.find('.its4you-wizard-step2'),
                statusOk = step2.find('.its4you-conn-status'),
                statusError = step2.find('.its4you-conn-error'),
                params = {
                    module: app.getModuleName(),
                    parent: app.getParentModuleName(),
                    action: ('Settings' === app.getParentModuleName() ? 'TestImap' : 'ProTestImap'),
                    email: step2.data('email'),
                    server: step2.find('input[name="server"]').val().trim(),
                    port: step2.find('input[name="port"]').val().trim(),
                    encryption: step2.find('select[name="encryption"]').val(),
                    cert_fingerprint: step2.find('input[name="cert_fingerprint"]').val() || '',
                    username: step2.find('input[name="username"]').val().trim(),
                    password: step2.find('input[name="password"]').val()
                };

            // Empty password is allowed when the connection was already verified in this
            // session — the server reuses the stored password (never rendered back)
            let alreadyTested = step2.data('connected') === 1;
            if (!params.server || !params.port || !params.username || (!params.password && !alreadyTested)) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_FILL_REQUIRED')});
                return;
            }

            app.helper.showProgress();
            app.request.post({data: params}).then(function (err, data) {
                app.helper.hideProgress();
                if (err || !data || !data.success) {
                    statusOk.addClass('hide');
                    statusError.removeClass('hide').text((data && data.message) || app.vtranslate('JS_SAVE_ERROR'));
                    step2.find('.its4you-wizard-next-step2').prop('disabled', true);
                    // Thunderbird-style: an unverifiable certificate is shown
                    // with the option to trust exactly this one
                    if (data && data.cert_error && data.certificate) {
                        self.showCertificate(step2.find('.its4you-cert-box'), data.certificate);
                    } else {
                        step2.find('.its4you-cert-box').addClass('hide');
                    }
                    return;
                }
                statusError.addClass('hide');
                step2.find('.its4you-cert-box').addClass('hide');
                statusOk.find('.its4you-folders-count').text(data.folders);
                statusOk.removeClass('hide');
                step2.find('.its4you-wizard-next-step2').prop('disabled', false);
            });
        });
    },

    /**
     * Fills the trust dialog with the certificate the server presents
     *
     * @param {jQuery} box .its4you-cert-box
     * @param {Object} certificate display values from the server
     */
    showCertificate: function (box, certificate) {
        box.find('.its4you-cert-subject').text(certificate.subject || '');
        box.find('.its4you-cert-issuer').text(certificate.issuer || '');
        box.find('.its4you-cert-valid').text(certificate.valid || '');
        box.find('.its4you-cert-fingerprint').text(certificate.fingerprint_display || '');
        box.data('fingerprint', certificate.fingerprint || '').removeClass('hide');
    },

    /**
     * Trusting a certificate (new mailbox: re-runs the connection test with
     * it; existing mailbox: stored on Save), checking the certificate of an
     * existing mailbox and revoking the trust
     *
     * @param {jQuery} container
     */
    registerCertificateTrust: function (container) {
        let self = this;

        container.on('click', '.its4you-cert-trust', function () {
            let box = jQuery(this).closest('.its4you-cert-box'),
                row = box.closest('.its4you-cert-row');

            box.addClass('hide');
            if (row.length) {
                row.find('input[name="imap_cert_fingerprint"]').val(box.data('fingerprint'));
                row.find('.its4you-cert-state-ca').addClass('hide');
                row.find('.its4you-cert-state-trusted').removeClass('hide');
                let ok = row.find('.its4you-cert-ok');
                ok.text(ok.data('pending')).removeClass('hide');
                return;
            }
            let step2 = container.find('.its4you-wizard-step2');
            step2.find('input[name="cert_fingerprint"]').val(box.data('fingerprint'));
            step2.find('.its4you-test-imap').trigger('click');
        });

        container.on('click', '.its4you-cert-revoke', function () {
            let row = jQuery(this).closest('.its4you-cert-row');
            row.find('input[name="imap_cert_fingerprint"]').val('');
            row.find('.its4you-cert-state-trusted').addClass('hide');
            row.find('.its4you-cert-state-ca').removeClass('hide');
            row.find('.its4you-cert-ok, .its4you-cert-box').addClass('hide');
        });

        container.on('click', '.its4you-cert-check', function () {
            let row = jQuery(this).closest('.its4you-cert-row'),
                ok = row.find('.its4you-cert-ok'),
                box = row.find('.its4you-cert-box');

            app.helper.showProgress();
            app.request.post({data: {
                module: app.getModuleName(),
                parent: app.getParentModuleName(),
                action: ('Settings' === app.getParentModuleName() ? 'CheckCertificate' : 'ProCheckCertificate'),
                // the wizard's settings step or the admin's mailbox detail
                record: row.data('record') || container.find('.its4you-wizard-step3').data('record')
            }}).then(function (err, data) {
                app.helper.hideProgress();
                ok.addClass('hide');
                box.addClass('hide');
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_ERROR')});
                    return;
                }
                if (data.verified || data.trusted) {
                    ok.text(ok.data(data.verified ? 'verified' : 'match')).removeClass('hide');
                    return;
                }
                self.showCertificate(box, data.certificate);
            });
        });
    },

    /**
     * Any change of the IMAP connection fields invalidates the previous test —
     * the success status hides and Next locks until Test Connection passes again
     *
     * @param {jQuery} container
     */
    registerImapStaleness: function (container) {
        container.on('input change', '.its4you-imap-step input, .its4you-imap-step select', function () {
            let step2 = container.find('.its4you-wizard-step2');
            if (jQuery(this).is('[name="server"], [name="port"], [name="encryption"]')) {
                step2.find('input[name="cert_fingerprint"]').val('');
                step2.find('.its4you-cert-box').addClass('hide');
            }
            step2.find('.its4you-conn-status').addClass('hide');
            step2.find('.its4you-conn-error').addClass('hide');
            step2.find('.its4you-wizard-next-step2').prop('disabled', true);
        });
    },

    /**
     * Step 2 → step 3 (outgoing mail) for IMAP; Microsoft / Google send
     * through their API and go on to step 4 (the server re-validates the
     * wizard session in every step)
     *
     * @param {jQuery} container
     */
    registerNextStep2: function (container) {
        container.on('click', '.its4you-wizard-next-step2', function () {
            let step2 = container.find('.its4you-wizard-step2'),
                nextStep = step2.data('provider') === 'imap' ? 3 : 4;
            window.location.href = container.data('wizard-url') + '&step=' + nextStep
                + '&email=' + encodeURIComponent(step2.data('email'))
                + '&name=' + encodeURIComponent(step2.data('name'))
                + '&provider=' + encodeURIComponent(step2.data('provider'));
        });
    },

    /**
     * Personal / Shared toggle shows the members picker
     *
     * @param {jQuery} container
     */
    registerMailboxTypeToggle: function (container) {
        container.on('change', 'input[name="mailbox_type"]', function () {
            let shared = container.find('input[name="mailbox_type"]:checked').val() === 'shared';
            container.find('.its4you-shared-with-row').toggleClass('hide', !shared);
        });
    },

    /**
     * Expand / collapse of a folder subtree — expanding shows direct children
     * (collapsed), collapsing hides all descendants and resets their carets
     *
     * @param {jQuery} container
     */
    registerFolderToggle: function (container) {
        container.on('click', '.its4you-folder-toggle', function () {
            let toggle = jQuery(this),
                delimiter = String(container.find('.its4you-folders-scroll').data('delimiter') || ''),
                prefix = String(toggle.data('path')) + delimiter,
                depth = parseInt(toggle.data('depth'), 10),
                expanding = toggle.hasClass('fa-caret-right');

            toggle.toggleClass('fa-caret-right', !expanding).toggleClass('fa-caret-down', expanding);

            container.find('.its4you-folder-row').each(function () {
                let row = jQuery(this);
                if (String(row.data('path')).indexOf(prefix) !== 0) {
                    return;
                }
                if (expanding) {
                    if (parseInt(row.data('depth'), 10) === depth + 1) {
                        row.removeClass('hide');
                    }
                } else {
                    row.addClass('hide');
                    row.find('.its4you-folder-toggle').removeClass('fa-caret-down').addClass('fa-caret-right');
                }
            });
        });
    },

    /**
     * Step 3 save — collects the form and persists the mailbox
     *
     * @param {jQuery} container
     */
    /**
     * Own SMTP fields show only for the custom outgoing mode
     *
     * @param {jQuery} container
     */
    /**
     * The outgoing server test — step 3 enables Next once it passed; the
     * edit form tests with the stored password when the field stays empty
     *
     * @param {jQuery} container
     */
    registerSmtpStep: function (container) {
        new MailManager_ProSmtpTest_Js(container.find('.its4you-smtp-box'), {
            module: app.getModuleName(),
            parent: app.getParentModuleName(),
            onChange: function (tested) {
                container.find('.its4you-wizard-next-step3').prop('disabled', !tested);
            }
        });

        container.on('click', '.its4you-wizard-next-step3', function () {
            let step = container.find('.its4you-wizard-smtp');
            window.location.href = container.data('wizard-url') + '&step=4'
                + '&email=' + encodeURIComponent(step.data('email'))
                + '&name=' + encodeURIComponent(step.data('name'))
                + '&provider=' + encodeURIComponent(step.data('provider'));
        });
    },

    registerSaveMailbox: function (container) {
        container.on('click', '.its4you-save-mailbox', function () {
            let step3 = container.find('.its4you-wizard-step3'),
                nameInput = step3.find('input[name="name"]'),
                // Create: name comes from step 1 (data-name); edit: from the visible input
                name = nameInput.length ? nameInput.val().trim() : String(step3.data('name') || '').trim(),
                mailboxType = step3.find('input[name="mailbox_type"]:checked').val(),
                // "select." prefix is mandatory (Select2 v3 copies the classes onto its
                // container div) and the value must be read via select2('val') — plain
                // .val() on a select2-driven multi select can return empty
                sharedSelect = step3.find('select.its4you-shared-with'),
                sharedWith = (sharedSelect.data('select2') ? sharedSelect.select2('val') : sharedSelect.val()) || [],
                folders = [];

            step3.find('.its4you-folder-check:checked').each(function () {
                folders.push(jQuery(this).val());
            });

            if (!name) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_FILL_REQUIRED')});
                return;
            }
            if (mailboxType === 'shared' && !sharedWith.length) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_SELECT_MEMBERS')});
                return;
            }

            let recordId = parseInt(step3.data('record'), 10) || 0;
            let params = {
                module: app.getModuleName(),
                parent: app.getParentModuleName(),
                // a user's own mailbox (module) is always new — editing is Settings only
                action: 'Settings' === app.getParentModuleName()
                    ? (recordId ? 'UpdateMailbox' : 'SaveMailbox')
                    : 'ProSaveMailbox',
                record: recordId,
                email: step3.data('email'),
                provider: step3.data('provider'),
                name: name,
                mailbox_type: mailboxType,
                shared_with: JSON.stringify(sharedWith),
                folders: JSON.stringify(folders),
                sync_from_days: step3.find('select[name="sync_from_days"]').val() || 0,
                is_default: step3.find('input[name="is_default"]').is(':checked') ? 1 : 0,
                imap_cert_fingerprint: step3.find('input[name="imap_cert_fingerprint"]').val() || '',
                // outgoing server — an empty password means "keep the stored one"
                // the edit form: a filled server switches a mailbox still on the
                // CRM outgoing server to its own (a new one got its SMTP in step 3)
                smtp_mode: (step3.find('input[name="smtp_server"]').val() || '').trim() ? 'custom' : 'crm',
                smtp_server: step3.find('input[name="smtp_server"]').val() || '',
                smtp_port: step3.find('input[name="smtp_port"]').val() || '',
                smtp_encryption: step3.find('select[name="smtp_encryption"]').val() || 'tls',
                smtp_username: step3.find('input[name="smtp_username"]').val() || '',
                smtp_password: step3.find('input[name="smtp_password"]').val() || ''
            };

            app.helper.showProgress();
            app.request.post({data: params}).then(function (err, data) {
                app.helper.hideProgress();
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: (data && data.message) || app.vtranslate('JS_SAVE_ERROR')});
                    return;
                }
                // the admin returns to the mailbox list, a user to the new mailbox
                window.location.href = container.data('user-mode') === 1
                    ? container.data('list-url') + '&mailbox=' + data.mailboxid
                    : container.data('list-url') + '&success=' + (recordId ? '2' : '1');
            });
        });
    },

    /**
     * @param {jQuery} container .its4you-mm-wizard
     */
    registerWizardEvents: function (container) {
        this.registerProviderCards(container);
        this.registerEmailDetection(container);
        this.registerNextStep(container);
        this.registerCopyRedirectUri(container);
        this.registerOAuthSignIn(container);
        this.registerOAuthCheck(container);
        this.registerOAuthConnect(container);
        this.registerEncryptionPortSync(container);
        this.registerTestImap(container);
        this.registerImapStaleness(container);
        this.registerCertificateTrust(container);
        this.registerNextStep2(container);
        this.registerMailboxTypeToggle(container);
        this.registerFolderToggle(container);
        this.registerSaveMailbox(container);
        this.registerSmtpStep(container);
        app.changeSelectElementView(container);
    }
};
