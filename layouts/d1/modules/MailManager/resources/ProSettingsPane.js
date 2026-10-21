/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * My Settings inside the inbox — the main area (list + conversation) shows
 * the settings while the sidebar stays; a folder, Workspace or filter click
 * returns to the mail. The address keeps &settings=<tab> (Back, reload)
 *
 * Mixin of MailManager_ProIndex_Js — loaded before Index.js
 */
window.MailManager_ProSettingsPane_Mixin = {

    /**
     * Tells the fixed Save / Cancel footer where the settings area lies — it
     * is fixed to the window, the area begins right of the sidebar and ends
     * above the page footer
     *
     * @param {jQuery} pane the settings area
     */
    trackSettingsPaneLeft: function (pane) {
        let update = function () {
            let rect = pane[0].getBoundingClientRect();
            // a hidden area has no place — keep the last known one
            if (rect.width > 0) {
                pane[0].style.setProperty('--its4you-settings-left', Math.round(rect.left) + 'px');
                pane[0].style.setProperty('--its4you-settings-bottom',
                    Math.max(0, Math.round(window.innerHeight - rect.bottom)) + 'px');
            }
        };

        update();
        // once more when the page has settled (the first call may run
        // while the layout around is still changing)
        setTimeout(update, 300);
        if (this.settingsLeftTracked) {
            return;
        }
        this.settingsLeftTracked = true;
        if (window.ResizeObserver) {
            new ResizeObserver(update).observe(pane[0]);
        }
        jQuery(window).on('resize', update);
    },

    /**
     * @param {string} tab key of the settings tab ('' = the first one)
     * @param {number} mailboxId own mailbox to show (0 = the default one)
     * @param {boolean} pushHistory false when following the browser history
     */
    openMySettings: function (tab, mailboxId, pushHistory) {
        let self = this,
            container = this.getContainer(),
            pane = container.find('.its4you-settings-pane'),
            requestId = ++this.settingsRequest;

        // the Workspace digest gives way (opened from the history too)
        if (this.closeWorkspaceDigest) {
            this.closeWorkspaceDigest();
        }
        // the mailbox shown is part of the address (a reload returns to it)
        this.settingsMailboxId = parseInt(mailboxId, 10) || 0;
        container.addClass('its4you-settings-open');
        this.trackSettingsPaneLeft(pane);
        pane.html('<div class="its4you-thread-list-empty text-center"><i class="fa-solid fa-rotate fa-spin"></i></div>');
        // following the history (or switching the mailbox) rewrites the entry in place
        this.setSettingsUrl(tab || 'general', pushHistory === false);

        app.request.post({data: {
            module: app.getModuleName(),
            view: 'ProIndexAjax',
            mode: 'mySettings',
            mailbox: mailboxId || this.mailboxId
        }}).then(function (err, html) {
            if (requestId !== self.settingsRequest || !container.hasClass('its4you-settings-open')) {
                return;
            }
            if (err) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});
                return;
            }
            // the label modal of a previous opening leaves with it
            jQuery('body > .its4you-label-modal').remove();
            pane.html(html);
            new MailManager_ProMySettingsPanel_Js(pane, {
                tab: tab,
                onMailboxChange: function (id, openTab) {
                    self.openMySettings(openTab, id, false);
                },
                onClose: function () {
                    self.closeMySettings(true);
                }
            });
            // a modal inside the inbox layout would sit under its backdrop
            pane.find('.its4you-label-modal').appendTo('body');
            // the address follows the open tab — a reload returns to it
            // the pane outlives its content — one handler, not one per opening
            pane.off('shown.bs.tab.its4youSettings').on('shown.bs.tab.its4youSettings', '.nav-tabs a', function () {
                let key = String(jQuery(this).attr('href')).replace('#mmMyTab', '').toLowerCase();
                self.setSettingsUrl(key, true);
            });
        });
    },

    /**
     * Back to the mail — the list and the conversation show again
     *
     * @param {boolean} pushHistory false when following the browser history
     */
    closeMySettings: function (pushHistory) {
        let container = this.getContainer();
        if (!container.hasClass('its4you-settings-open')) {
            return;
        }
        this.settingsRequest++;
        this.settingsMailboxId = 0;
        container.removeClass('its4you-settings-open');
        container.find('.its4you-settings-pane').empty();
        jQuery('body > .its4you-label-modal').remove();
        if (pushHistory !== false) {
            this.setSettingsUrl('', false);
        }
    },

    /**
     * @param {string} tab '' = no settings in the address
     * @param {boolean} replace replace the history entry instead of adding one
     */
    setSettingsUrl: function (tab, replace) {
        if (!window.history || !window.history.pushState) {
            return;
        }
        let url = new URL(window.location.href);
        if (tab) {
            url.searchParams.set('settings', tab);
        } else {
            url.searchParams.delete('settings');
        }
        if (tab && this.settingsMailboxId) {
            url.searchParams.set('settings_mailbox', this.settingsMailboxId);
        } else {
            url.searchParams.delete('settings_mailbox');
        }
        window.history[replace ? 'replaceState' : 'pushState']({settings: tab}, '', url.toString());
    },

    /**
     * Sidebar menu, the way back to the mail and the browser history
     *
     * @param {jQuery} container
     */
    registerSettingsPaneEvents: function (container) {
        let self = this;

        container.on('click', '.its4you-open-settings', function (event) {
            event.preventDefault();
            self.openMySettings(jQuery(this).data('tab'), 0, true);
        });

        // any mail view in the sidebar leaves the settings
        container.on('click', '.its4you-folder-item, .its4you-filter-item', function () {
            self.closeMySettings(true);
        });

        jQuery(window).on('popstate', function () {
            let params = new URL(window.location.href).searchParams,
                tab = params.get('settings');
            if (tab) {
                self.openMySettings(tab, parseInt(params.get('settings_mailbox'), 10) || 0, false);
            } else {
                self.closeMySettings(false);
            }
        });

        // a reload / shared link with &settings= opens them right away
        let initialParams = new URL(window.location.href).searchParams,
            initialTab = initialParams.get('settings');
        if (initialTab) {
            this.openMySettings(initialTab, parseInt(initialParams.get('settings_mailbox'), 10) || 0, false);
        }
    }
};
