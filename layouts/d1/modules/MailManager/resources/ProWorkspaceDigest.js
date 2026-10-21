/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * The Workspace digest (I19) — opens in place of the list and the conversation like My
 * Settings; a row opens its conversation in the Workspace, in its own mailbox
 *
 * Mixin of MailManager_ProIndex_Js — loaded before Index.js
 */
window.MailManager_ProWorkspaceDigest_Mixin = {

    /**
     * @param {boolean} refresh the summary on top is computed again
     * @param {boolean} quiet   a reload after a sync — no spinner, the scroll position stays
     */
    openWorkspaceDigest: function (refresh, quiet) {
        const self = this,
            container = this.getContainer(),
            pane = container.find('.its4you-digest-pane'),
            requestId = ++this.digestRequest,
            scrollTop = pane.scrollTop();

        if (container.hasClass('its4you-settings-open')) {
            this.closeMySettings(true);
        }

        container.addClass('its4you-digest-open');
        container.find('.its4you-folder-item, .its4you-filter-item').removeClass('active');
        container.find('.its4you-digest-item').addClass('active');

        if (!quiet) {
            pane.html(jQuery('<div class="its4you-thread-list-empty text-center"></div>')
                .append('<i class="fa-solid fa-rotate fa-spin me-2"></i>')
                .append(document.createTextNode(app.vtranslate('JS_DIGEST_LOADING'))));
        }

        app.request.post({data: {
            module: app.getModuleName(),
            view: 'ProIndexAjax',
            mode: 'workspaceDigest',
            refresh: refresh ? '1' : '0'
        }}).then(function (err, html) {
            // an answer for a digest the user already left
            if (requestId !== self.digestRequest || !container.hasClass('its4you-digest-open')) {
                return;
            }

            if (err) {
                // a quiet reload keeps what shows
                if (!quiet) {
                    pane.empty();
                    app.helper.showErrorNotification({message: app.vtranslate('JS_AI_FAILED')});
                }

                return;
            }

            pane.html(html);

            if (quiet) {
                pane.scrollTop(scrollTop);
            }
        });
    },

    /**
     * After a sync: an open digest reloads quietly when its fingerprint changed — a reply from
     * another client, a colleague taking a conversation, a record done, new mail, a new line
     * from the cron. The summary on top stays while its rows are the same (kept on the server)
     *
     * @param {object} data ProSyncNow result (digest_stamp while the digest is open)
     */
    refreshWorkspaceDigestAfterSync: function (data) {
        const container = this.getContainer(),
            shown = String(container.find('.its4you-digest-pane .its4you-digest').data('stamp') || '');

        if (data.digest_stamp && shown && data.digest_stamp !== shown && container.hasClass('its4you-digest-open')) {
            this.openWorkspaceDigest(false, true);
        }
    },

    /**
     * Back to the mail — the list and the conversation show again
     */
    closeWorkspaceDigest: function () {
        const container = this.getContainer();

        if (!container.hasClass('its4you-digest-open')) {
            return;
        }

        this.digestRequest++;
        container.removeClass('its4you-digest-open');
        container.find('.its4you-digest-item').removeClass('active');
        container.find('.its4you-digest-pane').empty();
    },

    /**
     * @param {jQuery} container
     */
    registerWorkspaceDigestEvents: function (container) {
        const self = this;

        container.on('click', '.its4you-digest-item', function () {
            self.openWorkspaceDigest(false);
        });
        container.on('click', '.its4you-digest-refresh', function () {
            self.openWorkspaceDigest(true);
        });
        // any mail view or My Settings leaves the digest
        container.on('click', '.its4you-folder-item, .its4you-filter-item, .its4you-open-settings', function () {
            self.closeWorkspaceDigest();
        });
        // a row: the Workspace with the conversation open, actions go to its mailbox
        container.on('click', '.its4you-digest-row', function () {
            const row = jQuery(this),
                threadId = parseInt(row.data('threadid'), 10) || 0,
                mailboxId = parseInt(row.data('mailbox'), 10) || 0;

            if (!threadId) {
                return;
            }

            self.threadMailboxes = self.threadMailboxes || {};

            if (mailboxId) {
                self.threadMailboxes[threadId] = mailboxId;
            }

            container.find('.its4you-workspace-item').trigger('click');
            self.loadConversationById(threadId);
        });
    }
};
