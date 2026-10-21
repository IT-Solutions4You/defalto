/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/* Global HEADERSCRIPT (ITS4YouMailManager 4.76) — browser notifications of new
   mail on any page of the CRM, for a user who switched them on (My settings,
   Notifications). Once a minute for all open CRM tabs together; the server
   answers from its cache, the mail server is not asked. The inbox tab also
   carries the unread count of the Workspace in its title */
Vtiger.Class('MailManager_ProNotifier_Js', {

    instance: false,

    /**
     * @return {MailManager_ProNotifier_Js}
     */
    getInstance: function () {
        if (!this.instance) {
            this.instance = new MailManager_ProNotifier_Js();
        }

        return this.instance;
    },

    /**
     * Whether the browser can show notifications at all (an HTTPS page)
     *
     * @return {boolean}
     */
    isSupported: function () {
        return 'Notification' in window && window.isSecureContext !== false;
    }
}, {

    INTERVAL: 60000,
    // the newest message any tab of this browser already knows
    STORE_CURSOR: 'df_mm_notify_cursor',
    // when a tab last asked — the other tabs wait for their turn
    STORE_ASKED: 'df_mm_notify_asked',
    // switched off (or no Pro / no access) — this login does not ask again
    STORE_OFF: 'df_mm_notify_off',
    TITLE_COUNT: /^\(\d+\)\s/,
    timer: null,

    /**
     * @param {string} kind 'localStorage' | 'sessionStorage'
     * @param {string} key
     * @return {string}
     */
    read: function (kind, key) {
        try {
            return window[kind].getItem(key) || '';
        } catch (e) {
            return '';
        }
    },

    /**
     * @param {string} kind 'localStorage' | 'sessionStorage'
     * @param {string} key
     * @param {string|null} value null removes the key
     */
    write: function (kind, key, value) {
        try {
            if (value === null) {
                window[kind].removeItem(key);
            } else {
                window[kind].setItem(key, value);
            }
        } catch (e) {
            // private window or blocked site data — the notifier just asks more often
        }
    },

    /**
     * @return {boolean} the page is the Mail Manager inbox
     */
    isInbox: function () {
        return app.getModuleName() === 'MailManager' && app.view() === 'List';
    },

    /**
     * @return {boolean}
     */
    isPermitted: function () {
        return MailManager_ProNotifier_Js.isSupported() && Notification.permission === 'granted';
    },

    /**
     * Starts asking; My settings calls it again right after the switch is turned on
     */
    start: function () {
        const self = this;

        this.write('sessionStorage', this.STORE_OFF, null);

        // the inbox shows the count in its title, other pages only notify
        if (this.timer || (!this.isInbox() && !this.isPermitted())) {
            return;
        }

        this.ask(true);
        this.timer = setInterval(function () {
            self.ask(false);
        }, this.INTERVAL);
    },

    stop: function () {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }

        this.showCount(0);
    },

    /**
     * @param {boolean} first the page has just loaded
     */
    ask: function (first) {
        const self = this,
            now = Date.now(),
            inbox = this.isInbox(),
            asked = parseInt(this.read('localStorage', this.STORE_ASKED), 10) || 0,
            after = parseInt(this.read('localStorage', this.STORE_CURSOR), 10) || 0;

        // another tab asked a moment ago — the inbox still wants its count on load
        if (now - asked < this.INTERVAL - 5000 && !(inbox && first)) {
            return;
        }

        this.write('localStorage', this.STORE_ASKED, String(now));

        app.request.post({data: {
            module: 'MailManager',
            action: 'ProCheckNewMail',
            after: after,
            count: inbox ? 1 : 0
        }}).then(function (err, data) {
            if (err || !data || !data.enabled) {
                self.write('sessionStorage', self.STORE_OFF, '1');
                self.stop();

                return;
            }

            const known = parseInt(self.read('localStorage', self.STORE_CURSOR), 10) || 0;

            if (data.cursor > known) {
                self.write('localStorage', self.STORE_CURSOR, String(data.cursor));
            }

            // a tab that asked in the meantime has shown these already
            if (known <= after) {
                self.notify(data);
            }

            if (inbox) {
                self.showCount(data.unread || 0);
            }
        });
    },

    /**
     * @param {Object} data {messages: [{id, title, body, url}], more: string}
     */
    notify: function (data) {
        const self = this;

        if (!this.isPermitted()) {
            return;
        }

        if (data.more) {
            this.show('df-mm-more', data.more, '', 'index.php?module=MailManager&view=List&app=TOOLS');

            return;
        }

        jQuery.each(data.messages || [], function (index, message) {
            self.show('df-mm-' + message.id, message.title, message.body, message.url);
        });
    },

    /**
     * @param {string} tag one notification per message, also across tabs
     * @param {string} title
     * @param {string} body
     * @param {string} url what a click opens
     */
    show: function (tag, title, body, url) {
        let notification;

        try {
            notification = new Notification(title, {body: body, tag: tag});
        } catch (e) {
            return;
        }

        notification.onclick = function () {
            // the inbox opens in one tab of its own — a form being filled in here stays
            const target = window.open(url, 'df_mailmanager');

            if (target) {
                target.focus();
            }

            notification.close();
        };
    },

    /**
     * @param {number} count unread conversations of the Workspace
     */
    showCount: function (count) {
        const title = document.title.replace(this.TITLE_COUNT, '');

        document.title = count > 0 ? '(' + count + ') ' + title : title;
    },

    registerEvents: function () {
        // not inside the frames and popups of the CRM, not after switched off
        if (window.self !== window.top || app.view() === 'Popup'
            || this.read('sessionStorage', this.STORE_OFF) === '1') {
            return;
        }

        this.start();
    }
});

jQuery(function () {
    MailManager_ProNotifier_Js.getInstance().registerEvents();
});
