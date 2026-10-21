/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/* My Settings (MySettings.tpl) — the own mailboxes, labels and history. Runs on the
   My Settings page, inside the inbox (the main area next to the sidebar)
   and as the admin's mailbox detail in Settings (options.admin); the host
   decides what a mailbox switch and "Back" do */
Vtiger.Class('MailManager_ProMySettingsPanel_Js', {}, {

    /** URL tab key (the sidebar menu) => pane id suffix */
    TABS: {general: 'General', folders: 'Folders', smtp: 'Smtp', style: 'Style', signature: 'Signature', notifications: 'Notifications', list: 'List', knowledge: 'Knowledge', labels: 'Labels', learning: 'Learning', history: 'History', sharing: 'Sharing', sending: 'Sending'},

    // id of the signature editor — one instance, rebuilt with the panel
    SIGNATURE_EDITOR: 'its4youMySignature',

    /**
     * @param {jQuery} root element holding .its4you-my-settings and the label modal
     * @param {Object} options {tab: string, onMailboxChange: function(mailboxId),
     *                          onClose: function() — "Back" links, none = they navigate,
     *                          admin: boolean — the admin's mailbox detail}
     */
    init: function (root, options) {
        let container = root.find('.its4you-my-settings');
        if (!container.length) {
            return;
        }
        this.options = options || {};

        this.registerFolderTree(container);
        this.openTab(container, this.options.tab);
        // own labels — the user-scoped actions; the admin edits them for
        // the owner of the mailbox shown (mailboxid)
        new MailManager_ProLabelEditor_Js(
            container.find('.its4you-own-labels .its4you-label-editor'),
            root.find('.its4you-own-label-modal .its4you-label-modal'),
            {
                module: app.getModuleName(),
                saveAction: 'ProSaveMyLabel',
                deleteAction: 'ProDeleteMyLabel',
                extra: this.options.admin ? {mailboxid: container.find('.its4you-my-mailbox').val()} : {}
            }
        );
        new MailManager_ProLabelRuleList_Js(
            container.find('.its4you-label-rules'),
            {module: app.getModuleName(), deleteAction: 'ProDeleteMyLabelRule'}
        );
        new MailManager_ProHistory_Js(container.find('.its4you-history'));
        this.registerSharing(container);
        this.registerActions(container);
    },

    /**
     * Opens the requested tab; a tab the mailbox does not have leaves the first one
     *
     * @param {jQuery} container
     * @param {string} tab key of TABS
     */
    openTab: function (container, tab) {
        let pane = tab ? this.TABS[tab] : null;
        if (pane) {
            const link = container.find('.nav-tabs a[href="#mmMyTab' + pane + '"]');

            // Bootstrap 5 has no jQuery .tab() plugin
            if (link.length && window.bootstrap) {
                bootstrap.Tab.getOrCreateInstance(link[0]).show();
            }
        }
    },

    /**
     * The Signature tab — the rich-text editor loads when the tab opens (it
     * measures itself wrong inside a hidden pane) and saves into the core
     * user field
     *
     * @param {jQuery} container
     */
    registerSignature: function (container) {
        let self = this,
            pane = container.find('#mmMyTabSignature'),
            mount = function () {
                // the panel is rebuilt on a mailbox change — never two editors on one id
                if (window.CKEDITOR && CKEDITOR.instances[self.SIGNATURE_EDITOR]) {
                    CKEDITOR.instances[self.SIGNATURE_EDITOR].destroy(true);
                }
                new Vtiger_CkEditor_Js().loadCkEditor(pane.find('#' + self.SIGNATURE_EDITOR), {height: '240px'});
            };

        if (!pane.length) {
            return;
        }
        container.find('.nav-tabs a[href="#mmMyTabSignature"]').on('shown.bs.tab', mount);
        if (pane.hasClass('active')) {
            mount();
        }
        container.on('click', '.its4you-my-signature-save', function () {
            let editor = window.CKEDITOR && CKEDITOR.instances[self.SIGNATURE_EDITOR];

            app.helper.showProgress();
            app.request.post({data: {
                module: app.getModuleName(),
                action: 'ProSaveMySignature',
                signature: editor ? editor.getData() : pane.find('#' + self.SIGNATURE_EDITOR).val()
            }}).then(function (err, data) {
                app.helper.hideProgress();
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_SAVE_FAILED')});
                    return;
                }
                app.helper.showSuccessNotification({message: pane.data('saved')});
            });
        });
    },

    /**
     * The Notifications tab — the browser asks for its permission when the
     * switch is saved on (a click is the only moment it may ask); a browser
     * that blocks it keeps the warning in sight
     *
     * @param {jQuery} container
     */
    registerNotifications: function (container) {
        // the global header script (absent until the Pro links are registered)
        const Notifier = window.MailManager_ProNotifier_Js,
            pane = container.find('#mmMyTabNotifications'),
            supported = Notifier ? Notifier.isSupported() : 'Notification' in window,
            showState = function () {
                const on = pane.find('.its4you-my-notify').is(':checked');

                pane.find('.its4you-notify-unsupported').toggleClass('hide', !on || supported);
                pane.find('.its4you-notify-blocked').toggleClass('hide', !on || !supported || Notification.permission !== 'denied');
            },
            save = function (enabled) {
                app.helper.showProgress();
                app.request.post({data: {
                    module: app.getModuleName(),
                    action: 'ProSaveMyNotifications',
                    enabled: enabled ? 1 : 0
                }}).then(function (err, data) {
                    const notifier = Notifier ? Notifier.getInstance() : null;

                    app.helper.hideProgress();

                    if (err || !data || !data.success) {
                        app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});

                        return;
                    }

                    app.helper.showSuccessNotification({message: pane.data('saved')});
                    showState();

                    if (notifier && enabled) {
                        notifier.start();
                    } else if (notifier) {
                        notifier.stop();
                    }
                });
            };

        if (!pane.length) {
            return;
        }

        showState();
        pane.on('change', '.its4you-my-notify', showState);
        pane.on('click', '.its4you-my-notify-save', function () {
            const enabled = pane.find('.its4you-my-notify').is(':checked');

            if (enabled && supported && Notification.permission === 'default') {
                Notification.requestPermission().then(function () {
                    save(enabled);
                });

                return;
            }

            save(enabled);
        });
    },

    /**
     * The Mail list tab — the list tab the inbox opens with
     *
     * @param {jQuery} container
     */
    registerListTab: function (container) {
        const pane = container.find('#mmMyTabList');

        pane.on('click', '.its4you-my-list-save', function () {
            app.helper.showProgress();
            app.request.post({data: {
                module: app.getModuleName(),
                action: 'ProSaveMyListTab',
                tab: pane.find('.its4you-my-list-tab').val()
            }}).then(function (err, data) {
                app.helper.hideProgress();

                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});

                    return;
                }

                app.helper.showSuccessNotification({message: pane.data('saved')});
            });
        });
    },

    /**
     * The Sending tab — how many seconds a sent mail waits for "Undo"
     *
     * @param {jQuery} container
     */
    registerSendDelay: function (container) {
        const pane = container.find('#mmMyTabSending');

        pane.on('click', '.its4you-my-send-delay-save', function () {
            app.helper.showProgress();
            app.request.post({data: {
                module: app.getModuleName(),
                action: 'ProSaveMySendDelay',
                delay: pane.find('.its4you-my-send-delay').val()
            }}).then(function (err, data) {
                app.helper.hideProgress();

                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});

                    return;
                }

                app.helper.showSuccessNotification({message: pane.data('saved')});
            });
        });
    },

    /**
     * The common labels as the owner sets them up for the personal mailboxes
     * — one row saves at a time, "reset" returns to the common setting; the
     * panel reloads to show what applies now
     *
     * @param {jQuery} container
     */
    registerDefaultLabels: function (container) {
        let self = this,
            send = function (row, params, message) {
                let extra = self.options.admin ? {mailboxid: container.find('.its4you-my-mailbox').val()} : {};

                app.request.post({data: jQuery.extend({
                    module: app.getModuleName(),
                    action: 'ProSaveMyLabelOverride',
                    labelid: row.data('labelid')
                }, params, extra)}).then(function (err, data) {
                    if (err || !data || !data.success) {
                        app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_SAVE_FAILED')});
                        return;
                    }
                    app.helper.showSuccessNotification({message: message});
                    if (typeof self.options.onMailboxChange === 'function') {
                        self.options.onMailboxChange(container.find('.its4you-my-mailbox').val(), 'labels');
                    }
                });
            };

        // only a cleanup action (Move to Spam / Trash) can run by itself
        container.on('change', '.its4you-default-module', function () {
            let row = jQuery(this).closest('tr'),
                cleanup = String(jQuery(this).val()).indexOf('action:') === 0;

            row.find('.its4you-default-auto').prop('disabled', !cleanup).prop('checked', function (index, checked) {
                return cleanup && checked;
            });
        });
        container.find('.its4you-default-module').trigger('change');

        container.on('click', '.its4you-save-default-label', function () {
            let row = jQuery(this).closest('tr');

            send(row, {
                ai_description: row.find('.its4you-default-description').val(),
                suggested_module: row.find('.its4you-default-module').val(),
                auto_action: row.find('.its4you-default-auto').is(':checked') ? '1' : '0',
                ai_enabled: row.find('.its4you-default-ai').is(':checked') ? '1' : '0'
            }, row.closest('.its4you-default-labels').data('saved'));
        });
        container.on('click', '.its4you-reset-default-label', function () {
            let row = jQuery(this).closest('tr');

            send(row, {op: 'reset'}, row.closest('.its4you-default-labels').data('reset'));
        });
    },

    /**
     * @param {jQuery} container
     * @return {string} key of the open tab
     */
    getOpenTab: function (container) {
        let href = String(container.find('.nav-tabs li.active a').attr('href') || ''),
            tab = '';
        jQuery.each(this.TABS, function (key, pane) {
            if (href === '#mmMyTab' + pane) {
                tab = key;
            }
        });

        return tab;
    },

    /**
     * Opens or closes one folder of the tree — opening shows the direct
     * children, closing hides the whole branch
     *
     * @param {jQuery} tree .its4you-my-folders
     * @param {jQuery} row the folder row
     * @param {string} delimiter hierarchy delimiter of the folder names
     */
    toggleFolder: function (tree, row, delimiter) {
        let toggle = row.find('.its4you-my-folder-toggle'),
            prefix = String(row.data('path')) + delimiter,
            depth = parseInt(row.data('depth'), 10),
            expanding = toggle.hasClass('fa-caret-right');

        toggle.toggleClass('fa-caret-right', !expanding).toggleClass('fa-caret-down', expanding);
        tree.find('.its4you-my-folder-row').each(function () {
            let item = jQuery(this);
            if (String(item.data('path')).indexOf(prefix) !== 0) {
                return;
            }
            if (expanding) {
                if (parseInt(item.data('depth'), 10) === depth + 1) {
                    item.removeClass('hide');
                }
            } else {
                item.addClass('hide');
                item.find('.its4you-my-folder-toggle').removeClass('fa-caret-down').addClass('fa-caret-right');
            }
        });
    },

    /**
     * Collapsible folder tree (the Mail Manager sidebar pattern). The
     * branches leading to a checked folder start open — the choice stays
     * visible; a subfolder whose parent the server does not list (no row to
     * open it from) is shown right away
     *
     * @param {jQuery} container
     */
    registerFolderTree: function (container) {
        let self = this,
            tree = container.find('.its4you-my-folders'),
            delimiter = String(tree.data('delimiter') || ''),
            rows = tree.find('.its4you-my-folder-row'),
            rowByPath = {};

        if (!tree.length || delimiter === '') {
            rows.removeClass('hide');
            return;
        }
        rows.each(function () {
            rowByPath[String(jQuery(this).data('path'))] = jQuery(this);
        });

        tree.on('click', '.its4you-my-folder-toggle', function () {
            self.toggleFolder(tree, jQuery(this).closest('.its4you-my-folder-row'), delimiter);
        });

        rows.each(function () {
            let row = jQuery(this),
                segments = String(row.data('path')).split(delimiter);
            if (segments.length > 1 && !rowByPath[segments.slice(0, -1).join(delimiter)]) {
                row.removeClass('hide');
            }
        });
        tree.find('.its4you-my-folder:checked').each(function () {
            let segments = String(jQuery(this).closest('.its4you-my-folder-row').data('path')).split(delimiter);
            for (let i = 1; i < segments.length; i++) {
                let parent = rowByPath[segments.slice(0, i).join(delimiter)];
                if (parent && parent.find('.its4you-my-folder-toggle').hasClass('fa-caret-right')) {
                    self.toggleFolder(tree, parent, delimiter);
                }
            }
        });
    },

    /**
     * Deputies of the personal mailbox — granting and revoking reload the
     * settings on the Sharing tab (the host's mailbox switch)
     *
     * @param {jQuery} container
     */
    registerSharing: function (container) {
        let self = this,
            sharing = container.find('.its4you-sharing'),
            reload = function () {
                if (typeof self.options.onMailboxChange === 'function') {
                    self.options.onMailboxChange(sharing.data('mailbox'), 'sharing');
                }
            },
            save = function (params) {
                app.request.post({data: jQuery.extend({
                    module: app.getModuleName(),
                    action: 'ProSaveMySharing',
                    mailboxid: sharing.data('mailbox')
                }, params)}).then(function (err, data) {
                    if (err || !data || !data.success) {
                        app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_SAVE_FAILED')});
                        return;
                    }
                    app.helper.showSuccessNotification({message: app.vtranslate('JS_SETTINGS_SAVED')});
                    reload();
                });
            };

        if (!sharing.length) {
            return;
        }

        sharing.on('click', '.its4you-sharing-grant', function () {
            // select2 v3 copies the classes onto its container — the type prefix finds the select
            let select = sharing.find('select.its4you-sharing-user'),
                userId = select.data('select2') ? select.select2('val') : select.val();
            if (!userId) {
                app.helper.showErrorNotification({message: sharing.data('pick-user')});
                return;
            }
            save({mode: 'grant', userid: userId, valid_until: sharing.find('.its4you-sharing-until input').val()});
        });

        sharing.on('click', '.its4you-sharing-revoke', function () {
            let row = jQuery(this).closest('tr'),
                message = String(sharing.data('confirm')).replace('%s', app.htmlEncode(String(row.data('name'))));

            app.helper.showConfirmationBox({message: message}).then(function () {
                save({mode: 'revoke', userid: row.data('userid')});
            });
        });

        // the picker widgets last — the buttons work even if one fails
        vtUtils.showSelect2ElementView(sharing.find('select.its4you-sharing-user'));
        vtUtils.registerEventForDateFields(sharing.find('.its4you-sharing-until'));
    },

    /**
     * Mailbox switch, Back, saving the mailbox and the reply style
     *
     * @param {jQuery} container
     */
    registerActions: function (container) {
        let self = this;

        // the settings show one mailbox — the host reloads them for another
        container.on('change', '.its4you-my-mailbox', function () {
            if (typeof self.options.onMailboxChange === 'function') {
                self.options.onMailboxChange(jQuery(this).val(), self.getOpenTab(container));
            }
        });

        if (typeof self.options.onClose === 'function') {
            container.on('click', '.its4you-settings-close', function (event) {
                event.preventDefault();
                self.options.onClose();
            });
        }

        self.registerSignature(container);
        self.registerNotifications(container);
        self.registerListTab(container);
        self.registerSendDelay(container);
        self.registerDefaultLabels(container);
        // the knowledge files of the panel just built
        if (window.MailManager_ProKnowledge) {
            window.MailManager_ProKnowledge.init();
        }

        // a learned spam sender of an own mailbox is forgotten (the AI unlearns it)
        container.on('click', '.its4you-delete-my-spamrule', function () {
            let row = jQuery(this).closest('tr'),
                spam = row.closest('.its4you-my-spam'),
                message = String(spam.data('confirm')).replace('%s', app.htmlEncode(String(row.data('sender'))));

            app.helper.showConfirmationBox({message: message}).then(function () {
                app.request.post({data: {
                    module: app.getModuleName(),
                    action: 'ProDeleteMySpamRule',
                    ruleid: row.data('ruleid')
                }}).then(function (err, data) {
                    if (err || !data || !data.success) {
                        app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});
                        return;
                    }
                    row.remove();
                    app.helper.showSuccessNotification({message: spam.data('deleted')});
                });
            });
        });

        // the outgoing server test (an empty password tests the stored one)
        new MailManager_ProSmtpTest_Js(container.find('.its4you-smtp-box'), {module: app.getModuleName()});

        container.on('click', '.its4you-my-save', function () {
            let folders = [],
                mailboxId = container.find('.its4you-my-mailbox').val(),
                sharedSelect = container.find('select.its4you-shared-with'),
                // the admin also saves the type, the members and the trusted certificate
                target = self.options.admin
                    ? {
                        parent: 'Settings',
                        action: 'UpdateMailbox',
                        record: mailboxId,
                        mailbox_type: container.find('input[name="mailbox_type"]:checked').val(),
                        shared_with: JSON.stringify(
                            (sharedSelect.data('select2') ? sharedSelect.select2('val') : sharedSelect.val()) || []
                        ),
                        imap_cert_fingerprint: container.find('input[name="imap_cert_fingerprint"]').val() || ''
                    }
                    : {action: 'ProSaveMyMailbox', mailboxid: mailboxId};

            container.find('.its4you-my-folder:checked').each(function () {
                folders.push(jQuery(this).val());
            });

            app.helper.showProgress();
            app.request.post({data: jQuery.extend({
                module: app.getModuleName(),
                name: container.find('input[name="name"]').val().trim(),
                is_default: container.find('input[name="is_default"]').is(':checked') ? 1 : 0,
                folders: JSON.stringify(folders),
                // a filled server switches a mailbox still on the CRM outgoing server to its own
                smtp_mode: (container.find('input[name="smtp_server"]').val() || '').trim() ? 'custom' : 'crm',
                smtp_server: container.find('input[name="smtp_server"]').val() || '',
                smtp_port: container.find('input[name="smtp_port"]').val() || '',
                smtp_encryption: container.find('select[name="smtp_encryption"]').val() || 'tls',
                smtp_username: container.find('input[name="smtp_username"]').val() || '',
                smtp_password: container.find('input[name="smtp_password"]').val() || ''
            }, target)}).then(function (err, data) {
                app.helper.hideProgress();
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_SAVE_FAILED')});
                    return;
                }
                app.helper.showSuccessNotification({message: app.vtranslate('JS_SETTINGS_SAVED')});
                // the type decides the tabs — the admin's detail renders again
                if (self.options.admin && typeof self.options.onMailboxChange === 'function') {
                    self.options.onMailboxChange(mailboxId, self.getOpenTab(container));
                }
            });
        });

        container.on('click', '.its4you-my-style-generate', function () {
            let button = jQuery(this);
            button.prop('disabled', true).find('.fa-wand-magic-sparkles').addClass('fa-spin');
            app.request.post({data: {
                module: app.getModuleName(),
                action: 'ProMyStyleProfile',
                mode: 'generate',
                mailboxid: container.find('.its4you-my-mailbox').val()
            }}).then(function (err, data) {
                button.prop('disabled', false).find('.fa-wand-magic-sparkles').removeClass('fa-spin');
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_SAVE_FAILED')});
                    return;
                }
                container.find('.its4you-my-style-text').val(data.profile);
                app.helper.showSuccessNotification({message: app.vtranslate('JS_STYLE_LEARNED')});
            });
        });

        container.on('click', '.its4you-my-style-save', function () {
            app.request.post({data: {
                module: app.getModuleName(),
                action: 'ProMyStyleProfile',
                mode: 'save',
                mailboxid: container.find('.its4you-my-mailbox').val(),
                profile: container.find('.its4you-my-style-text').val()
            }}).then(function (err, data) {
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_SAVE_FAILED')});
                    return;
                }
                app.helper.showSuccessNotification({message: app.vtranslate('JS_SETTINGS_SAVED')});
            });
        });
    }
});
