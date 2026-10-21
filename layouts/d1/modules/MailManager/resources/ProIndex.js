/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/* Must extend Vtiger_Index_Js — app.controller() instantiates this class INSTEAD
   of the core one, so without inheritance the global page events (app menu
   hamburger, menu toggle, quick create) would never be registered. The methods
   come from the MailManager_Pro*_Mixin objects loaded before this file */
/** @var MailManager_ProIndex_Js */
Vtiger_Index_Js('MailManager_ProIndex_Js', {}, jQuery.extend({},
    window.MailManager_ProThreadList_Mixin,
    window.MailManager_ProConversation_Mixin,
    window.MailManager_ProCrmPanel_Mixin,
    window.MailManager_ProComposer_Mixin,
    window.MailManager_ProSettingsPane_Mixin,
    window.MailManager_ProWorkspaceDigest_Mixin,
    window.MailManager_ProTriageQueue_Mixin,
    window.MailManager_ProAssistant_Mixin, {

    mailboxId: 0,
    folderId: 0,
    listFilter: 'all',
    searchTerm: '',
    searchTimer: null,
    labelFilter: 0,
    priorityFilter: '',
    // sequence numbers of the latest list / composer request — older answers are dropped
    threadListRequest: 0,
    threadListKey: '',
    /** threadId => list request number at the time the server confirmed its delete / move (ProThreadList.js) */
    removedThreads: null,
    /** batchId => deletes / spam moves waiting for their "Undo" (ProThreadList.js) */
    pendingThreadActions: null,
    pendingBatchSeq: 0,
    threadListPage: 1,
    composerRequest: 0,
    settingsRequest: 0,
    // the latest Workspace digest request — an older answer is dropped (ProWorkspaceDigest.js)
    digestRequest: 0,

    /**
     * @return {jQuery}
     */
    getContainer: function () {
        return jQuery('.its4you-inbox');
    },

    /**
     * Fits the inbox exactly into the viewport so the page itself never scrolls —
     * only the folder / thread / conversation panels do
     */
    adjustHeight: function () {
        let container = this.getContainer();
        if (!container.length) {
            return;
        }
        let height = jQuery(window).height() - container.offset().top - 6;
        container.css('height', Math.max(height, 300) + 'px');

        // Self-correction: whatever still overflows the viewport (vtiger footer,
        // page paddings) gets subtracted so the page itself never scrolls
        let overflow = jQuery(document).height() - jQuery(window).height();
        if (overflow > 0) {
            container.css('height', Math.max(height - overflow, 300) + 'px');
        }
    },

    /**
     * Drag handle between the thread list and the conversation — the user
     * resizes the list column; the width persists in localStorage
     *
     * @param {jQuery} container
     */
    registerListResizer: function (container) {
        let list = container.find('.its4you-inbox-list'),
            resizer = container.find('.its4you-list-resizer'),
            stored = parseInt(window.localStorage.getItem('its4youMMListWidth'), 10);

        if (!list.length || !resizer.length) {
            return;
        }
        if (stored) {
            list.css('width', Math.min(Math.max(stored, 280), 680) + 'px');
        }

        resizer.on('mousedown', function (event) {
            event.preventDefault();
            let startX = event.pageX,
                startWidth = list.outerWidth();

            // message iframes swallow mousemove — disable them while dragging
            container.addClass('its4you-resizing');

            jQuery(document).on('mousemove.mmresize', function (moveEvent) {
                let width = Math.min(Math.max(startWidth + moveEvent.pageX - startX, 280), 680);
                list.css('width', width + 'px');
            });
            jQuery(document).on('mouseup.mmresize', function () {
                jQuery(document).off('.mmresize');
                container.removeClass('its4you-resizing');
                window.localStorage.setItem('its4youMMListWidth', String(list.outerWidth()));
            });
        });
    },

    /** true once the page started reloading because the Membership ended */
    membershipReloading: false,

    /**
     * Returns true when a request failed because the Pro edition is no longer
     * available (the Membership license ended during the session)
     *
     * @param {string} message error message or response text
     * @return {boolean}
     */
    isMembershipError: function (message) {
        const required = app.vtranslate('LBL_PRO_MEMBERSHIP_REQUIRED');

        return 'string' === typeof message && '' !== message
            && required !== 'LBL_PRO_MEMBERSHIP_REQUIRED' && -1 !== message.indexOf(required);
    },

    /**
     * Without the Membership every Pro request fails with LBL_PRO_MEMBERSHIP_REQUIRED.
     * The whole UI changes edition then, so the page reloads once and lands in Basic.
     * Actions answer with a JSON error, views with the "not permitted" page
     */
    registerMembershipCheck: function () {
        const self = this;

        jQuery(document).ajaxComplete(function (event, xhr) {
            const json = xhr && xhr.responseJSON,
                message = json && json.error ? json.error.message : (xhr && xhr.responseText) || '';

            if (self.membershipReloading || !self.isMembershipError(message)) {
                return;
            }

            self.membershipReloading = true;
            window.location.reload();
        });
    },

    registerEvents: function () {
        this._super();

        let self = this,
            container = this.getContainer();
        if (!container.length) {
            return;
        }

        this.mailboxId = container.data('mailbox');
        this.folderId = container.data('default-folder');
        // Workspace (work mail only) is the entry view; folders are for triage
        this.workspaceMode = true;
        this.registerThreadListEvents(container);
        this.registerConversationEvents(container);
        this.registerComposerEvents(container);
        this.registerCrmPanelEvents(container);
        this.registerAssistantEvents(container);
        this.registerListResizer(container);
        this.registerMembershipCheck();
        this.registerSettingsPaneEvents(container);
        this.registerWorkspaceDigestEvents(container);

        this.adjustHeight();
        jQuery(window).on('resize load', function () {
            self.adjustHeight();
        });
        // Late chrome rendering (footer, fonts) shifts heights after DOM ready
        setTimeout(function () {
            self.adjustHeight();
        }, 600);

        // the tab the user opens the inbox with (My settings)
        this.listFilter = container.data('list-tab') || 'all';
        this.loadThreads(1, false);
        // Deep link from the record-detail widget opens the thread directly.
        // One-shot: the params leave the address bar right away, otherwise
        // every later F5 would jump back to this old conversation
        let openThread = parseInt(container.data('open-thread'), 10) || 0;
        if (openThread) {
            this.loadConversationById(openThread);
            if (window.history && window.history.replaceState) {
                let url = new URL(window.location.href);
                url.searchParams.delete('thread');
                url.searchParams.delete('mailbox');
                window.history.replaceState(null, '', url.toString());
            }
        }
        // Background refresh on open — the thread list reloads when it finishes
        this.syncNow(true);
        // New mail arrives without a manual Refresh click while the inbox is
        // open; hidden browser tabs skip the tick to spare the server
        setInterval(function () {
            if (!document.hidden) {
                self.syncNow(true);
            }
        }, 180000);
    }
}));
